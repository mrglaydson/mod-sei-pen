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
    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
    PaginaSEI::getInstance()->salvarCamposPost(array('selTipoProcedimento', 'txtPeriodoDe', 'txtPeriodoA', 'selAssunto'));


    switch ($_GET['acao']) {
        case 'gd_pendencia_arquivamento_listar':
            $strTitulo = 'Pendências de Arquivamento';
            break;

        case 'gd_procedimento_reabrir':
            try {
                SessaoSEI::getInstance()->validarPermissao('procedimento_reabrir');

                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();

                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $objReabrirProcedimentoDTO = new ReabrirProcessoDTO();
                    $objReabrirProcedimentoDTO->setDblIdProcedimento($arrStrIds[$i]);
                    $objReabrirProcedimentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                    $objReabrirProcedimentoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());

                    $objProcedimentoRN = new ProcedimentoRN();
                    $objProcedimentoRN->reabrirRN0966($objReabrirProcedimentoDTO);
                }

                PaginaSEI::getInstance()->setStrMensagem('Operaï¿½ï¿½o realizada com sucesso.');
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;
        case 'gd_procedimento_arquivar':
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_arquivar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $_GET['id_procedimento']));
            die;
        default:
            break;
    }

    //Ações de arquivar 
    $bolAcaoArquivar = SessaoSEI::getInstance()->verificarPermissao('gd_procedimento_arquivar');    
    $strLinkArquivar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_arquivar&acao_origem=' . $_GET['acao']);

    // Ações de reabrir
    $bolAcaoReabrir = SessaoSEI::getInstance()->verificarPermissao('procedimento_reabrir');
    $strLinkReabrir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_reabrir&acao_origem=' . $_GET['acao']);

    // Permissão de anotação
    $bolAcaoAnotacao = SessaoSEI::getInstance()->verificarPermissao('gd_pendencia_arquivamento_anotar');
    
    // Botões superiores
    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    // Obtem os filtros da pesquisa
    $txtPeriodoDe = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoDe');
    $txtPeriodoA = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoA');
    $selTipoProcedimento = PaginaSEI::getInstance()->recuperarCampo('selTipoProcedimento');
    $selAssunto = PaginaSEI::getInstance()->recuperarCampo('selAssunto');

    // Combos de filtro
    $strItensSelTipoProcedimento = TipoProcedimentoINT::montarSelectNome('null', 'Todos', $selTipoProcedimento);
    $strItensSelAssunto = MdGdArquivamentoINT::montarSelectAssuntos('null', 'Todos', $selAssunto);

    //Monta o DTO de pesquisa
    $objMdGdPesquisaPendenciasArquivamentoDTO = new MdGdPesquisarPendenciasArquivamentoDTO();
    
    if($txtPeriodoDe && $txtPeriodoDe !== 'null'){
        $objMdGdPesquisaPendenciasArquivamentoDTO->setDthPeriodoInicial($txtPeriodoDe);
    }

    if($txtPeriodoA && $txtPeriodoA !== 'null'){
        $objMdGdPesquisaPendenciasArquivamentoDTO->setDthPeriodoFinal($txtPeriodoA);
    }

    if($selTipoProcedimento && $selTipoProcedimento !== 'null'){
        $objMdGdPesquisaPendenciasArquivamentoDTO->setNumIdTipoProcedimento($selTipoProcedimento);
    }

    if($selAssunto && $selAssunto !== 'null'){
        $objMdGdPesquisaPendenciasArquivamentoDTO->setNumIdProtocoloAssunto($selAssunto);
    }

    // Obtem os procedimentos pendentes
    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $arrObjProcedimentoDTO = $objMdGdArquivamentoRN->obterProcedimentosPendentes($objMdGdPesquisaPendenciasArquivamentoDTO);

    $objProcedimentoDTO = $arrObjProcedimentoDTO[0];

    $arrDthConclusaoProcedimento = $arrObjProcedimentoDTO[1];


    if ($objProcedimentoDTO) {
    
        PaginaSEI::getInstance()->prepararPaginacao($objProcedimentoDTO);

        $objProcedimentoRN = new ProcedimentoRN();
        $arrObjProcedimentoDTO = $objProcedimentoRN->listarRN0278($objProcedimentoDTO);
        
        PaginaSEI::getInstance()->processarPaginacao($objProcedimentoDTO);
        $numRegistros = count($arrObjProcedimentoDTO);
        $c = 1;
        if ($numRegistros > 0) {

            // Botões de ação de arquivamento e reabertura
            if($bolAcaoReabrir){
                $arrComandos[] = '<button type="button" accesskey="R" id="sbmReabrir" value="Reabrir" class="infraButton" onclick="acaoReabrirMultiplo()"><span class="infraTeclaAtalho">R</span>eabrir</button>';
            }

            if($bolAcaoArquivar){
                $arrComandos[] = '<button type="button" accesskey="A" id="sbmArquivar" value="Arquivar" class="infraButton" onclick="acaoArquivarMultiplo()"><span class="infraTeclaAtalho">A</span>rquivar</button>';
            }
    
            // Busca os assuntos dos processos da página
            $arrIdsProcedimento = InfraArray::converterArrInfraDTO($arrObjProcedimentoDTO,'IdProcedimento');

            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($arrIdsProcedimento, InfraDTO::$OPER_IN);
            $objRelProtocoloAssuntoDTO->retDblIdProtocolo();
            $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();
            $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();

            $objRelProtoloAssuntoRN = new RelProtocoloAssuntoRN();
            $arrObjRelProtocoloAssuntoDTO = InfraArray::indexarArrInfraDTO($objRelProtoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO),'IdProtocolo', true);

            // Busca as anotações
            $objMdGdAnotacaoPendenciaDTO = new MdGdAnotacaoPendenciaDTO();
            $objMdGdAnotacaoPendenciaDTO->setDblIdProcedimento($arrIdsProcedimento, InfraDTO::$OPER_IN);
            $objMdGdAnotacaoPendenciaDTO->retStrAnotacao();
            $objMdGdAnotacaoPendenciaDTO->retDblIdProcedimento();

            $objMdGdAnotacaoPendenciaRN = new MdGdAnotacaoPendenciaRN();
            $arrObjMdGdAnotacaoPendenciaDTO = InfraArray::indexarArrInfraDTO($objMdGdAnotacaoPendenciaRN->listar($objMdGdAnotacaoPendenciaDTO),'IdProcedimento');

            $strResultado = '';

            $strSumarioTabela = 'Tabela de Pendências de Arquivamento.';
            $strCaptionTabela = 'Pendências de Arquivamento';

            $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
            $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
            $strResultado .= '<tr>';
            $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="1%">Seq.</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="19%">Processo</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="10%">Especificação</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="15%">Data</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="10%">Tipo</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="15%">Assunto</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="15%">Anotações</th>' . "\n";
            $strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
            $strResultado .= '</tr>' . "\n";
            $strCssTr = '';

            for ($i = 0; $i < $numRegistros; $i++) {

                // Isola os assuntos do processo
                $arrObjRelProtocoloAssuntoDTOProcedimento = $arrObjRelProtocoloAssuntoDTO[$arrObjProcedimentoDTO[$i]->getDblIdProcedimento()];
                $strAssuntosProcedimento = '';

                foreach($arrObjRelProtocoloAssuntoDTOProcedimento as $k => $objRelProtocoloAssuntoDTO){
                    $strAssuntosProcedimento .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto() .' - ' .$objRelProtocoloAssuntoDTO->getStrDescricaoAssunto();
                    if($k + 1 != count($arrObjRelProtocoloAssuntoDTOProcedimento)){
                        $strAssuntosProcedimento .= ' <br><br>  ';
                    }
                }

                // Isola a anotação
                $strAnotacao = isset($arrObjMdGdAnotacaoPendenciaDTO[$arrObjProcedimentoDTO[$i]->getDblIdProcedimento()]) ? $arrObjMdGdAnotacaoPendenciaDTO[$arrObjProcedimentoDTO[$i]->getDblIdProcedimento()]->getStrAnotacao() : '';

                $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
                $strResultado .= $strCssTr;

                $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjProcedimentoDTO[$i]->getDblIdProcedimento(), $arrObjProcedimentoDTO[$i]->getStrProtocoloProcedimentoFormatado()) . '</td>';
                $strResultado .= '<td>' . $c . '</td>';
                
                $strResultado .= '<td>';
                $strResultado .= '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjProcedimentoDTO[$i]->getDblIdProcedimento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjProcedimentoDTO[$i]->getStrProtocoloProcedimentoFormatado() . '</a>';
                
                if($arrObjProcedimentoDTO[$i]->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_RESTRITO){
                    $strResultado .= '<img src="imagens/sei_chave_restrito.gif" title="Processo Restrito" title="Processo Restrito" class="infraImg" />';
                }
    
                if($arrObjProcedimentoDTO[$i]->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_SIGILOSO){
                    $strResultado .= '<img src="imagens/sei_chave_sigiloso.gif" title="Processo Sigiloso" title="Processo Sigiloso" class="infraImg" />';
                }
                $strResultado .= '</td>';

                $strResultado .= '<td>'. $arrObjProcedimentoDTO[$i]->getStrDescricaoProtocolo().'</td>';
                $strResultado .= '<td>' . $arrDthConclusaoProcedimento[$arrObjProcedimentoDTO[$i]->getDblIdProcedimento()] . '</td>';
                $strResultado .= '<td>' . $arrObjProcedimentoDTO[$i]->getStrNomeTipoProcedimento() . '</td>';
                $strResultado .= '<td>' . $strAssuntosProcedimento. '</td>';
                $strResultado .= '<td>'.  $strAnotacao.'</td>';
                $strResultado .= '<td align="center">';

                $strId = $arrObjProcedimentoDTO[$i]->getDblIdProcedimento();
                $strProtocoloProcedimentoFormatado = $arrObjProcedimentoDTO[$i]->getStrProtocoloProcedimentoFormatado();

                if($bolAcaoReabrir){
                    $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoReabrir(\'' . $strId . '\',\'' . $strProtocoloProcedimentoFormatado . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="imagens/sei_reabrir_processo.gif" title="Reabrir Processo" alt="Reabrir Processo" class="infraImg" style="width: 29px; height: 29px;" /></a>&nbsp;';
                } 

                if($bolAcaoArquivar){
                    $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoArquivar(\'' . $strId . '\',\'' . $strProtocoloProcedimentoFormatado . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/arquivamento.png" title="Arquivar Processo" alt="Arquivar Processo" class="infraImg" style="width: 22px; height: 22px; padding-bottom: 3px;"/></a>&nbsp;';
                }

                if($bolAcaoAnotacao){
                    $strLinkAnotar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_pendencia_arquivamento_anotar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $arrObjProcedimentoDTO[$i]->getDblIdProcedimento());
                    $strResultado .= '<a href="#" onclick="exibirJanelaAnotacao(\'' . $strLinkAnotar . '\');"><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/anotacoes.gif" title="Realizar Anotação" alt="Realizar Anotação" class="infraImg" style="width: 18px; height: 18px;padding-bottom: 5px;"/></a>&nbsp;';
                }
        

                $strResultado .= '</td></tr>' . "\n";
                $c++;
            }
            $strResultado .= '</table>';
        }

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
#selTipoProcedimento {position:absolute;left:42%;top:17%;width:40%;}

#lblSelAssunto {position:absolute;left:0%;top:41%;width:20%;}
#selAssunto {position:absolute;left:0%;top:57%;width:38%;}

#lblPeriodoDe {position:absolute;left:0%;top:0%;width:20%;}
#txtPeriodoDe {position:absolute;left:0%;top:17%;width:17%;}
#imgCalPeriodoD {position:absolute;left:18%;top:17%;}

#lblPeriodoA {position:absolute;left:21%;top:0%;width:20%;}
#txtPeriodoA {position:absolute;left:21%;top:17%;width:17%;}
#imgCalPeriodoA {position:absolute;left:39%;top:17%;}

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

<? if ($bolAcaoAnotacao) { ?>
    function exibirJanelaAnotacao(link) {
        infraAbrirJanela(link, 'janelaAnotacao', 720, 300, 'location=0,status=1,resizable=1,scrollbars=1', false);
    }
<? } ?>

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
          PaginaSEI::getInstance()->abrirAreaDados('10em');
          ?>

    <label id="lblTiposProcedimento" for="selTipoProcedimento" accesskey="" class="infraLabelOpcional">Tipo de Processo:</label>
    <select id="selTipoProcedimento" name="selTipoProcedimento" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelTipoProcedimento ?>
    </select>

    <label id="lblSelAssunto" for="selAssunto" accesskey="" class="infraLabelOpcional">Assunto:</label>
    <select id="selAssunto" name="selAssunto" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelAssunto; ?>
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