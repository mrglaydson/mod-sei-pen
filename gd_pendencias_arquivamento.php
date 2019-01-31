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
    PaginaSEI::getInstance()->salvarCamposPost(array('selTipoProcedimento', 'txtPeriodoDe', 'txtPeriodoA'));


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

    $txtPeriodoDe = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoDe');
    if ($txtPeriodoDe) {
        $objAtividadeDTO->setDthAbertura($txtPeriodoDe, InfraDTO::$OPER_MAIOR_IGUAL);
    }

    $txtPeriodoA = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoA');
    if ($txtPeriodoA) {
        $objAtividadeDTO->setDthAbertura($txtPeriodoA, InfraDTO::$OPER_MENOR_IGUAL);
    }

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
    
    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    if ($arrIdsProcedimento) {

        // Busca os processos concluídos
        $objProcedimentoDTO = new ProcedimentoDTO();
        $objProcedimentoDTO->setDblIdProcedimento($arrIdsProcedimento, InfraDTO::$OPER_IN);
        $objProcedimentoDTO->retDblIdProcedimento();
        $objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
        $objProcedimentoDTO->retStrNomeTipoProcedimento();
        $objProcedimentoDTO->retObjAnotacaoDTO();

        $selTipoProcedimento = PaginaSEI::getInstance()->recuperarCampo('selTipoProcedimento');
        if ($selTipoProcedimento && $selTipoProcedimento !== 'null') {
            $objProcedimentoDTO->setNumIdTipoProcedimento($selTipoProcedimento);
        }

        $arrComandos[] = '<button type="button" accesskey="R" id="sbmReabrir" value="Reabrir" class="infraButton" onclick="acaoReabrirMultiplo()"><span class="infraTeclaAtalho">R</span>eabrir</button>';
        $arrComandos[] = '<button type="button" accesskey="A" id="sbmArquivar" value="Arquivar" class="infraButton" onclick="acaoArquivarMultiplo()"><span class="infraTeclaAtalho">A</span>rquivar</button>';

        PaginaSEI::getInstance()->prepararPaginacao($objProcedimentoDTO);

        $objProcedimentoRN = new ProcedimentoRN();
        $arrObjProcedimentoDTO = $objProcedimentoRN->listarRN0278($objProcedimentoDTO);

        PaginaSEI::getInstance()->processarPaginacao($objProcedimentoDTO);
        $numRegistros = count($arrObjProcedimentoDTO);
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

                $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjProcedimentoDTO[$i]->getDblIdProcedimento(), $arrObjProcedimentoDTO[$i]->getStrProtocoloProcedimentoFormatado()) . '</td>';
                $strResultado .= '<td>' . $c . '</td>';
                $strResultado .= '<td><a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjProcedimentoDTO[$i]->getDblIdProcedimento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjProcedimentoDTO[$i]->getStrProtocoloProcedimentoFormatado() . '</a></td>';
                $strResultado .= '<td>' . $arrObjAtividadeDTO[$arrObjProcedimentoDTO[$i]->getDblIdProcedimento()]->getDthAbertura() . '</td>';
                $strResultado .= '<td>' . $arrObjProcedimentoDTO[$i]->getStrNomeTipoProcedimento() . '</td>';
                $strResultado .= '<td align="center">';

                $strId = $arrObjProcedimentoDTO[$i]->getDblIdProcedimento();
                $strProtocoloProcedimentoFormatado = $arrObjProcedimentoDTO[$i]->getStrProtocoloProcedimentoFormatado();

                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoReabrir(\'' . $strId . '\',\'' . $strProtocoloProcedimentoFormatado . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="modulos/sei-mod-gestao-documental/imagens/reabrir_procedimento.gif" title="Reabrir Processo" alt="Reabrir Processo" class="infraImg"  /></a>&nbsp;';
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoArquivar(\'' . $strId . '\',\'' . $strProtocoloProcedimentoFormatado . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="modulos/sei-mod-gestao-documental/imagens/arquivar.gif" title="Arquivar Processo" alt="Arquivar Processo" class="infraImg" /></a>&nbsp;';

                $strResultado .= '</td></tr>' . "\n";
                $c++;
            }
            $strResultado .= '</table>';
        }

        $strItensSelTipoProcedimento = TipoProcedimentoINT::montarSelectNome('null', 'Todos', $selTipoProcedimento);
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

#lblTiposProcedimento {position:absolute;left:42%;top:0%;width:20%;}
#selTipoProcedimento {position:absolute;left:42%;top:36%;width:40%;}

#lblPeriodoDe {position:absolute;left:0%;top:0%;width:20%;}
#txtPeriodoDe {position:absolute;left:0%;top:36%;width:17%;}
#imgCalPeriodoD {position:absolute;left:18%;top:36%;width:2%;}

#lblPeriodoA {position:absolute;left:21%;top:0%;width:20%;}
#txtPeriodoA {position:absolute;left:21%;top:36%;width:17%;}
#imgCalPeriodoA {position:absolute;left:39%;top:36%;width:2%;}

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
          PaginaSEI::getInstance()->abrirAreaDados('5em');
          ?>

    <label id="lblTiposProcedimento" for="selTipoProcedimento" accesskey="" class="infraLabelOpcional">Tipo de Processo:</label>
    <select id="selTipoProcedimento" name="selTipoProcedimento" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelTipoProcedimento ?>
    </select>

    <label id="lblPeriodoDe" for="txtPeriodoDe" accesskey="" class="infraLabelOpcional">Datas Limite de:</label>
    <input type="text" id="txtPeriodoDe" value="<?= $txtPeriodoDe ?>" name="txtPeriodoDe" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoDe) ?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
    <img id="imgCalPeriodoD" title="Selecionar Data Inicial" alt="Selecionar Data Inicial" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/calendario.gif" class="infraImg" onclick="infraCalendario('txtPeriodoDe', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    

    <label id="lblPeriodoA" for="txtPeriodoA" accesskey="" class="infraLabelOpcional">Até</label>
    <input type="text" id="txtPeriodoA" value="<?= $txtPeriodoA ?>" name="txtPeriodoA" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoA) ?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
    <img id="imgCalPeriodoA" title="Selecionar Data Final" alt="Selecionar Data Final" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/calendario.gif" class="infraImg" onclick="infraCalendario('txtPeriodoA', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    

    <?
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