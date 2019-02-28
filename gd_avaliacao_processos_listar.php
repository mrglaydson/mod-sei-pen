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

    SessaoSEI::getInstance()->validarPermissao('gestao_documental_unidade_arquivamento_listar');

    PaginaSEI::getInstance()->salvarCamposPost(array('selUnidade', 'selTipoProcedimento', 'selDestinacaoFinal', 'txtPeriodoDe', 'txtPeriodoA'));

    switch ($_GET['acao']) {

        case 'gd_avaliacao_processos_listar':
            $strTitulo = 'Avalia��o de Processos';
            break;

        case 'gd_procedimento_eliminacao_enviar':   
            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            
            foreach($arrStrIds as $strId){
                // Cria o objeto do arquivamento
                $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                $objMdGdArquivamentoDTO->setNumIdArquivamento($strId);

                // Envia o arquivamento para elimina��o
                $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
                $objMdGdArquivamentoRN->enviarEliminacao($objMdGdArquivamentoDTO);
            }
            break;

        case 'gd_procedimento_recolhimento_enviar':
            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            
            foreach($arrStrIds as $strId){
                // Cria o objeto do arquivamento
                $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                $objMdGdArquivamentoDTO->setNumIdArquivamento($strId);

                // Envia o arquivamento para recolhimento
                $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
                $objMdGdArquivamentoRN->enviarRecolhimento($objMdGdArquivamentoDTO);
            }
            
            break;

        default:
            throw new InfraException("A��o '" . $_GET['acao'] . "' n�o reconhecida.");
    }

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    // A��o de envio para elimina��o
    $bolAcaoProcedimentoEliminacaoEnviar = true;
    $strLinkProcedimentoEliminacaoEnviar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_eliminacao_enviar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']);

    // A��o de envio para recolhimento
    $bolAcaoProcedimentoRecolhimentoEnviar = true;
    $strLinkProcedimentoRecolhimentoEnviar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_recolhimento_enviar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']);

    // Busca os arquivamentos em fase intermedi�ria para listagem
    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

    $objMdGdArquivamentoDTO->retNumIdArquivamento();
    $objMdGdArquivamentoDTO->retDthDataArquivamento();
    $objMdGdArquivamentoDTO->retDthDataGuardaIntermediaria();
    $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
    $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
    $objMdGdArquivamentoDTO->retStrNomeUsuario();
    $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
    $objMdGdArquivamentoDTO->retDblIdProcedimento();
    $objMdGdArquivamentoDTO->retStrStaDestinacaoFinal();
    $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_FASE_INTERMEDIARIA);

    $selUnidade = PaginaSEI::getInstance()->recuperarCampo('selUnidade');
    if ($selUnidade && $selUnidade !== 'null') {
        $objMdGdArquivamentoDTO->setNumIdUnidadeCorrente($selUnidade);
    }

    $selTipoProcedimento = PaginaSEI::getInstance()->recuperarCampo('selTipoProcedimento');
    if ($selTipoProcedimento && $selTipoProcedimento !== 'null') {
        $objMdGdArquivamentoDTO->setNumIdTipoProcedimento($selTipoProcedimento);
    }

    $txtPeriodoDe = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoDe');
    if ($txtPeriodoDe) {
        $objMdGdArquivamentoDTO->setDthDataArquivamento($txtPeriodoDe, InfraDTO::$OPER_MAIOR_IGUAL);
    }

    $txtPeriodoA = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoA');
    if ($txtPeriodoA) {
        $objMdGdArquivamentoDTO->setDthDataArquivamento($txtPeriodoA, InfraDTO::$OPER_MENOR_IGUAL);
    }

    $selDestinacaoFinal = PaginaSEI::getInstance()->recuperarCampo('selDestinacaoFinal');
    if ($selDestinacaoFinal && $selDestinacaoFinal !== 'null') {
        $objMdGdArquivamentoDTO->setStrStaDestinacaoFinal($selDestinacaoFinal);

        if($bolAcaoProcedimentoEliminacaoEnviar && $selDestinacaoFinal == MdGdArquivamentoRN::$DF_ELIMINACAO){
            $arrComandos[] = '<button type="button" accesskey="E" id="sbmEliminacao" value="Eliminar" class="infraButton" onclick="acaoEnviarEliminacaoMultiplo()"><span class="infraTeclaAtalho">E</span>liminar</button>';
        }

        if($bolAcaoProcedimentoRecolhimentoEnviar && $selDestinacaoFinal == MdGdArquivamentoRN::$DF_RECOLHIMENTO){
            $arrComandos[] = '<button type="button" accesskey="R" id="sbmRecolhimento" value="Recolher" class="infraButton"  onclick="acaoEnviarRecolhimentoMultiplo()"><span class="infraTeclaAtalho">R</span>ecolher</button>';
        }

    }
    
    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    PaginaSEI::getInstance()->prepararOrdenacao($objMdGdArquivamentoDTO, 'DataArquivamento', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objMdGdArquivamentoDTO);

    $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

    PaginaSEI::getInstance()->processarPaginacao($objMdGdArquivamentoDTO);
    $numRegistros = count($arrObjMdGdArquivamentoDTO);

    if ($numRegistros > 0) {
        $strResultado = '';

        $strSumarioTabela = 'Processos Arquivados';
        $strCaptionTabela = 'Processos Arquivados';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="9%">�rg�o</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Unidade', 'DescricaoUnidadeCorrente', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Processo', 'ProtocoloFormatado', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="20%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Tipo', 'NomeTipoProcedimento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Usu�rio', 'NomeUsuario', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Data de arquivamento', 'DataArquivamento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Destina��o Final</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="5%">A��es</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        for ($i = 0; $i < $numRegistros; $i++) {

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento(), $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrDescricaoOrgaoSistema()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoUnidadeCorrente()) . '</td>';
            $strResultado .= '<td><a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjMdGdArquivamentoDTO[$i]->getDblIdProcedimento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '</a></td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeUsuario()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento()) . '</td>';

            if ($arrObjMdGdArquivamentoDTO[$i]->getStrStaDestinacaoFinal() == MdGdArquivamentoRN::$DF_ELIMINACAO) {
                $strResultado .= '<td>Elimina��o</td>';
            } else {
                $strResultado .= '<td>Recolhimento</td>';
            }

            if (InfraData::compararDatas($arrObjMdGdArquivamentoDTO[$i]->getDthDataGuardaIntermediaria(), date('d/m/Y H:i:s')) >= 0) {
                if ($bolAcaoProcedimentoEliminacaoEnviar && $arrObjMdGdArquivamentoDTO[$i]->getStrStaDestinacaoFinal() == MdGdArquivamentoRN::$DF_ELIMINACAO) {
                    $strResultado .= '<td align="center"><a href="#ID-' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '" onclick="acaoEnviarEliminacao(\'' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '\', \'' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '\');"   tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="modulos/sei-mod-gestao-documental/imagens/transicao.png" title="Enviar para Elimina��o" title="Enviar para Elimina��o" class="infraImg" /></a></td></tr>';
                }
                
                if ($bolAcaoProcedimentoRecolhimentoEnviar && $arrObjMdGdArquivamentoDTO[$i]->getStrStaDestinacaoFinal() == MdGdArquivamentoRN::$DF_RECOLHIMENTO){
                    $strResultado .= '<td align="center"><a href="#ID-' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '" onclick="acaoEnviarRecolhimento(\'' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '\', \'' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="modulos/sei-mod-gestao-documental/imagens/transicao.png" title="Enviar para Recolhimento" title="Enviar para Recolhimento" class="infraImg" /></a></td></tr>';
                }
                
            } else {
                $strResultado .= '<td align="center"></td></tr>' . "\n";
            }
        }
        $strResultado .= '</table>';
    }

    // Busca uma lista de unidades
    $strItensSelUnidade = UnidadeINT::montarSelectSiglaDescricao('null', '&nbsp;', $selUnidade);
    $strItensSelTipoProcedimento = TipoProcedimentoINT::montarSelectNome('null', 'Todos', $selTipoProcedimento);

    //  $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
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
#lblOrgao {position:absolute;left:0%;top:0%;width:20%;}
#selOrgao {position:absolute;left:0%;top:20%;width:20%;}

#lblUnidade {position:absolute;left:21%;top:0%;width:20%;}
#selUnidade {position:absolute;left:21%;top:20%;width:20%;}

#lblTiposProcedimento {position:absolute;left:42%;top:0%;width:20%;}
#selTipoProcedimento {position:absolute;left:42%;top:20%;width:20%;}

#lblDestinacaoFinal {position:absolute;left:63%;top:0%;width:20%;}
#selDestinacaoFinal {position:absolute;left:63%;top:20%;width:20%;}

#lblPeriodoDe {position:absolute;left:0%;top:55%;width:20%;}
#txtPeriodoDe {position:absolute;left:0%;top:70%;width:17%;}
#imgCalPeriodoD {position:absolute;left:18%;top:72%;width:2%;}

#lblPeriodoA {position:absolute;left:21%;top:55%;width:20%;}
#txtPeriodoA {position:absolute;left:21%;top:70%;width:17%;}
#imgCalPeriodoA {position:absolute;left:39%;top:72%;width:2%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
//PaginaSEI::getInstance()->abrirJavaScript();
?>
<script>
    function inicializar() {
        infraEfeitoTabelas();
        document.getElementById('btnFechar').focus();

    }

<? if ($bolAcaoProcedimentoEliminacaoEnviar) { ?>
        function acaoEnviarEliminacao(id_arquivamento, protocolo_formatado) {
            if (confirm("Confirma o envio do processo  \"" + protocolo_formatado + "\" para a elimina��o?")) {
                document.getElementById('hdnInfraItemId').value = id_arquivamento;
                document.getElementById('frmAvaliacaoProcessoLista').action = '<?= $strLinkProcedimentoEliminacaoEnviar ?>';
                document.getElementById('frmAvaliacaoProcessoLista').submit();
            }
        }
<? } ?>

<? if ($bolAcaoProcedimentoRecolhimentoEnviar) { ?>
        function acaoEnviarRecolhimento(id_arquivamento, protocolo_formatado) {
            if (confirm("Confirma o envio do processo  \"" + protocolo_formatado + "\" para o recolhimento?")) {
                document.getElementById('hdnInfraItemId').value = id_arquivamento;
                document.getElementById('frmAvaliacaoProcessoLista').action = '<?= $strLinkProcedimentoRecolhimentoEnviar ?>';
                document.getElementById('frmAvaliacaoProcessoLista').submit();
            }
        }
<? } ?>

<? if ($bolAcaoProcedimentoEliminacaoEnviar) { ?>
        function acaoEnviarEliminacaoMultiplo() {
            if (confirm("Confirma o envio dos processos selecionados para elimina��o?")) {
                document.getElementById('hdnInfraItemId').value = '';
                document.getElementById('frmAvaliacaoProcessoLista').action = '<?= $strLinkProcedimentoEliminacaoEnviar ?>';
                document.getElementById('frmAvaliacaoProcessoLista').submit();
            }
        }
<? } ?>

<? if ($bolAcaoProcedimentoRecolhimentoEnviar) { ?>
        function acaoEnviarRecolhimentoMultiplo() {
            if (confirm("Confirma o envio dos processos selecionados para o recolhimento?")) {
                document.getElementById('hdnInfraItemId').value = '';
                document.getElementById('frmAvaliacaoProcessoLista').action = '<?= $strLinkProcedimentoRecolhimentoEnviar ?>';
                document.getElementById('frmAvaliacaoProcessoLista').submit();
            }
        }
<? } ?>


</script>
<?
//PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmAvaliacaoProcessoLista" method="post"
      action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
          <?
          PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
          PaginaSEI::getInstance()->abrirAreaDados('9.5em');
          ?>

    <label id="lblOrgao" for="selOrgao" accesskey="" class="infraLabelOpcional">�rg�o:</label>
    <select id="selOrgao" name="selOrgao" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <option><?= SessaoSEI::getInstance()->getStrDescricaoOrgaoSistema() ?></option>
    </select>

    <label id="lblUnidade" for="selUnidade" accesskey="" class="infraLabelOpcional">Unidade:</label>
    <select id="selUnidade" name="selUnidade" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelUnidade ?>
    </select>

    <label id="lblTiposProcedimento" for="selTipoProcedimento" accesskey="" class="infraLabelOpcional">Tipo de Processo:</label>
    <select id="selTipoProcedimento" name="selTipoProcedimento" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelTipoProcedimento ?>
    </select>

    <label id="lblDestinacaoFinal" for="selDestinacaoFinal" accesskey="" class="infraLabelOpcional">Destina��o Final:</label>
    <select id="selDestinacaoFinal" name="selDestinacaoFinal" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= MdGdArquivamentoINT::montarSelectDestinacoesFinalArquivamento($selDestinacaoFinal); ?>
    </select>

    <label id="lblPeriodoDe" for="txtPeriodoDe" accesskey="" class="infraLabelOpcional">De:</label>
    <input type="text" id="txtPeriodoDe" value="<?= $txtPeriodoDe ?>" name="txtPeriodoDe" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoDe) ?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
    <img id="imgCalPeriodoD" title="Selecionar Data Inicial" alt="Selecionar Data Inicial" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/calendario.gif" class="infraImg" onclick="infraCalendario('txtPeriodoDe', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    

    <label id="lblPeriodoA" for="txtPeriodoA" accesskey="" class="infraLabelOpcional">At�:</label>
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