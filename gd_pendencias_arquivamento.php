<?
try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    //////////////////////////////////////////////////////////////////////////////
    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(false);
    InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

    SessaoSEI::getInstance()->validarLink();
    SessaoSEI::getInstance()->validarPermissao('gestao_documental_pendencias_arquivamento');


    switch ($_GET['acao']) {
        case 'gd_pendencias_arquivamento':
            $strTitulo = 'Pendências de Arquivamento';
            break;

        case 'gd_procedimento_reabrir':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();

                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $objReabrirProcedimentoDTO = new ReabrirProcessoDTO();
                    $objReabrirProcedimentoDTO->setDblIdProcedimento($arrStrIds[$i]);
                    $objReabrirProcedimentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                    $objReabrirProcedimentoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());

                    $objProcedimentoRN = new ProcedimentoRN();
                    $objProcedimentoRN->reabrirRN0966($objReabrirProcedimentoDTO);
                }

                PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;
        case 'gd_procedimento_arquivar':
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivar_procedimento&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $_GET['id_procedimento']));
            die;
        default:
            break;
    }

    //Ações de reabrir e arquivar
    $bolAcaoArquivar = true;
    $strLinkArquivar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivar_procedimento&acao_origem=' . $_GET['acao']);

    $bolAcaoReabrir = true;
    $strLinkReabrir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_reabrir&acao_origem=' . $_GET['acao']);

    // Busca os ids de todos os processo concluídos na unidade
    $objAtividadeDTO = new AtividadeDTO();
    $objAtividadeDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    $objAtividadeDTO->setNumIdTarefa(array(TarefaRN::$TI_CONCLUSAO_PROCESSO_UNIDADE, TarefaRN::$TI_REABERTURA_PROCESSO_UNIDADE), InfraDTO::$OPER_IN);
    $objAtividadeDTO->retDblIdProtocolo();
    $objAtividadeDTO->retDthAbertura();
    $objAtividadeDTO->retNumIdTarefa();
    $objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objAtividadeRN = new AtividadeRN();
    $arrObjAtividadeDTO = InfraArray::indexarArrInfraDTO($objAtividadeRN->listarRN0036($objAtividadeDTO), 'IdProtocolo');

    foreach ($arrObjAtividadeDTO as $atividade) {
        if ($atividade->getNumIdTarefa() == TarefaRN::$TI_CONCLUSAO_PROCESSO_UNIDADE) {
            $arrIdsProcedimento[] = $atividade->getDblIdProtocolo();
        }
    }

    // Retira dos id's encontrados aqueles processos que se encontram arquivados
    if ($arrIdsProcedimento) {
        foreach ($arrIdsProcedimento as $k => $item) {
            $arquivamentoDTO = new MdGdArquivamentoDTO();
            $arquivamentoDTO->setDblIdProcedimento($item);
            $arquivamentoDTO->setStrSinAtivo('S');

            $arquivamentoRN = new MdGdArquivamentoRN();
            if ($arquivamentoRN->contar($arquivamentoDTO)) {
                unset($arrIdsProcedimento[$k]);
            }
        }
    }
    if ($arrIdsProcedimento) {

        // Busca os processos concluídos
        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->setDblIdProtocolo($arrIdsProcedimento, InfraDTO::$OPER_IN);
        $objProtocoloDTO->retDblIdProtocolo();
        $objProtocoloDTO->retStrProtocoloFormatado();
        $objProtocoloDTO->retStrNomeTipoProcedimentoProcedimento();
        $objProtocoloDTO->retObjAnotacaoDTO();

        $arrComandos = array();
        $arrComandos[] = '<button type="submit" accesskey="P" id="sbmReabrir" value="Pesquisar" class="infraButton" onclick="acaoReabrirMultiplo()"><span class="infraTeclaAtalho">R</span>eabrir</button>';
        $arrComandos[] = '<button type="submit" accesskey="P" id="sbmArquivar" value="Pesquisar" class="infraButton" onclick="acaoArquivarMultiplo()"><span class="infraTeclaAtalho">A</span>rquivar</button>';

        PaginaSEI::getInstance()->prepararPaginacao($objProtocoloDTO);

        $objProtocoloRN = new ProtocoloRN();
        $arrObjProtocoloDTO = $objProtocoloRN->listarRN0668($objProtocoloDTO);

        PaginaSEI::getInstance()->processarPaginacao($objProtocoloDTO);
        $numRegistros = count($arrObjProtocoloDTO);
        $c = 1;
        if ($numRegistros > 0) {


            $strResultado = '';

            $strSumarioTabela = 'Tabela de Pendências de Arquivamento.';
            $strCaptionTabela = 'Pendências de Arquivamento';

            $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
            $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
            $strResultado .= '<tr>';
            $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="1%">Seq.</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="39%">Processo</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="25%">Data</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="20%">Tipo</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
            $strResultado .= '</tr>' . "\n";
            $strCssTr = '';
            for ($i = 0; $i < $numRegistros; $i++) {

                $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
                $strResultado .= $strCssTr;

                $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjProtocoloDTO[$i]->getDblIdProtocolo(), $arrObjProtocoloDTO[$i]->getStrProtocoloFormatado()) . '</td>';
                $strResultado .= '<td>' . $c . '</td>';
                $strResultado .= '<td>' . $arrObjProtocoloDTO[$i]->getStrProtocoloFormatado() . '</td>';
                $strResultado .= '<td>' . $arrObjAtividadeDTO[$arrObjProtocoloDTO[$i]->getDblIdProtocolo()]->getDthAbertura() . '</td>';
                $strResultado .= '<td>' . $arrObjProtocoloDTO[$i]->getStrNomeTipoProcedimentoProcedimento() . '</td>';
                $strResultado .= '<td align="center">';

                $strId = $arrObjProtocoloDTO[$i]->getDblIdProtocolo();
                $strProtocoloFormatado = $arrObjProtocoloDTO[$i]->getStrProtocoloFormatado();

                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoReabrir(\'' . $strId . '\',\'' . $strProtocoloFormatado . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="modulos/sei-mod-gestao-documental/imagens/reabrir_procedimento.gif" title="Reabrir Processo" alt="Reabrir Processo" class="infraImg"  /></a>&nbsp;';
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoArquivar(\'' . $strId . '\',\'' . $strProtocoloFormatado . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="modulos/sei-mod-gestao-documental/imagens/arquivar.gif" title="Arquivar Processo" alt="Arquivar Processo" class="infraImg" /></a>&nbsp;';

                $strResultado .= '</td></tr>' . "\n";
                $c++;
            }
            $strResultado .= '</table>';
        }

        //  $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
    }
} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
#lblNomeJustificativaPesquisa {position:absolute;left:0%;top:0%;width:30%;}
#txtNomeJustificativaPesquisa {position:absolute;left:0%;top:40%;width:30%;}

#lblDescricaoJustificativaPesquisa {position:absolute;left:32%;top:0%;width:30%;}
#txtDescricaoJustificativaPesquisa {position:absolute;left:32%;top:40%;width:30%;}

#lblTipoJustificativaPesquisa {position:absolute;left:64%;top:2%;width:15%;}
#selTipoJustificativaPesquisa {position:absolute;left:64%;top:42%;width:15%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
//PaginaSEI::getInstance()->abrirJavaScript();
?>
<script type="text/javascript">
    function inicializar() {
        infraEfeitoTabelas();
        document.getElementById('btnFechar').focus();
    }

<? if ($bolAcaoReabrir) { ?>
        function acaoReabrir(id_procedimento, protocolo_formatado) {
            if (confirm("Confirma a reabertura do processo \"" + protocolo_formatado + "\"?")) {
                document.getElementById('hdnInfraItemId').value = id_procedimento;
                document.getElementById('frmPendenciasArquivamento').action = '<?= $strLinkReabrir ?>';
                document.getElementById('frmPendenciasArquivamento').submit();
            }
        }

        function acaoReabrirMultiplo() {
            if (document.getElementById('hdnInfraItensSelecionados').value == '') {
                alert('Nenhum processo selecionado.');
                return;
            }

            if (confirm("Confirma a reabertura dos processos selecionados?")) {
                document.getElementById('hdnInfraItemId').value = '';
                document.getElementById('frmPendenciasArquivamento').action = '<?= $strLinkReabrir ?>';
                document.getElementById('frmPendenciasArquivamento').submit();
            }
        }
<? } ?>

<? if ($bolAcaoArquivar) { ?>
        function acaoArquivar(id_procedimento) {
            document.getElementById('hdnInfraItemId').value = id_procedimento;
            document.getElementById('frmPendenciasArquivamento').action = '<?= $strLinkArquivar ?>';
            document.getElementById('frmPendenciasArquivamento').submit();
        }

        function acaoArquivarMultiplo() {
            if (document.getElementById('hdnInfraItensSelecionados').value == '') {
                alert('Nenhum processo selecionado.');
                return;
            }

            document.getElementById('frmPendenciasArquivamento').action = '<?= $strLinkArquivar ?>';
            document.getElementById('frmPendenciasArquivamento').submit();
        }
<? } ?>

</script>
<?
//PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmPendenciasArquivamento" method="post"
      action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
          <?
          PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
          PaginaSEI::getInstance()->abrirAreaDados('4.5em');
          PaginaSEI::getInstance()->fecharAreaDados();
          PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros);
          PaginaSEI::getInstance()->montarAreaDebug();
          PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
          ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>