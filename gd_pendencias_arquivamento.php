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

    PaginaSEI::getInstance()->salvarCamposPost(array('txtNomeJustificativaPesquisa', 'txtDescricaoJustificativaPesquisa', 'selTipoJustificativaPesquisa'));

    switch ($_GET['acao']) {
        case 'gd_procedimento_arquivar':
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivar_procedimento&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $_GET['id_procedimento']));
            die;


        case 'gd_procedimento_reabrir':

            try {
                if (isset($_GET['id_procedimento'])) {
                    $arrStrIds = array($_GET['id_procedimento']);
                } else {
                    $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                }

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
            break;

        default:
            break;
    }

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
    $strTitulo = 'Pendências de Arquivamento';

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


        switch ($_GET['acao']) {
            case 'gd_justificativas_excluir':
                try {
                    $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();

                    $arrObjMdGdJustificativa = array();

                    for ($i = 0; $i < count($arrStrIds); $i++) {
                        $objMdGdJustificativaDTO = new MdGdJustificativaDTO();
                        $objMdGdJustificativaDTO->setNumIdJustificativa($arrStrIds[$i]);
                        $arrObjMdGdJustificativa[] = $objMdGdJustificativaDTO;
                    }

                    $objMdGdJustificativaRN = new MdGdJustificativaRN();
                    $objMdGdJustificativaRN->excluir($arrObjMdGdJustificativa);

                    PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
                header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
                die;


            case 'gd_pendencias_arquivamento':
                $strTitulo = 'Pendências de Arquivamento';
                break;

            default:
                throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
        }

        $arrComandos = array();
        $arrComandos[] = '<button type="submit" accesskey="P" id="sbmReabrir" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">R</span>eabrir</button>';
        $arrComandos[] = '<button type="submit" accesskey="P" id="sbmReabrir" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">A</span>rquivar</button>';
        //         $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_justificativas_excluir&acao_origem=' . $_GET['acao']);

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
            $strResultado .= '<th class="infraTh" width="29%">Processo</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="15%">Data</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="20%">Tipo</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="20%">Anotações</th>' . "\n";
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
                $strResultado .= '<td></td>';
                $strResultado .= '<td align="center">';

                $strLinkReabrir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_reabrir&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $arrObjProtocoloDTO[$i]->getDblIdProtocolo());
                $strLinkArquivar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivar_procedimento&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $arrObjProtocoloDTO[$i]->getDblIdProtocolo());

                $strResultado .= '<a href="' . $strLinkReabrir . '"><img src="modulos/sei-mod-gestao-documental/imagens/reabrir_procedimento.gif" title="Reabrir Processo" alt="Reabrir Processo" class="infraImg" /></a>&nbsp;';
                $strResultado .= '<a href="' . $strLinkArquivar . '"><img src="modulos/sei-mod-gestao-documental/imagens/arquivar.gif" title="Arquivar Processo" alt="Arquivar Processo" class="infraImg" /></a>&nbsp;';

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
PaginaSEI::getInstance()->abrirJavaScript();
?>
<?php if ($_GET['acao_origem'] == 'gd_procedimento_reabrir'): ?>
    alert('Processo(s) reaberto(s) na unidade!');
<?php endif; ?>
function inicializar() {

infraEfeitoTabelas();
document.getElementById('btnFechar').focus();

}

<? if ($bolAcaoExcluir) { ?>
    function acaoExcluir(id, desc) {
    if (confirm("Confirma exclusão da Justificativa \"" + desc + "\"?")) {
    document.getElementById('hdnInfraItemId').value = id;
    document.getElementById('frmJustificativasLista').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmJustificativasLista').submit();
    }
    }

    function acaoExclusaoMultipla() {
    if (document.getElementById('hdnInfraItensSelecionados').value == '') {
    alert('Nenhuma Justificativa selecionado.');
    return;
    }
    if (confirm("Confirma exclusão das Justificativas selecionadas?")) {
    document.getElementById('hdnInfraItemId').value = '';
    document.getElementById('frmJustificativasLista').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmJustificativasLista').submit();
    }
    }
<? } ?>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmJustificativasLista" method="post"
      action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
          <?
          //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
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