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

    SessaoSEI::getInstance()->validarPermissao('gestao_documental_arquivamento_listar');

    PaginaSEI::getInstance()->salvarCamposPost(array('selTipoProcedimento', 'selDestinacaoFinal', 'selCondicionante', 'txtPeriodoDe', 'txtPeriodoA', 'selAssunto'));

    switch ($_GET['acao']) {

        case 'gd_arquivamento_listar':
            $strTitulo = 'Arquivo da Unidade';

            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
    $objMdGdArquivamentoDTO->setNumIdUnidadeCorrente(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    $objMdGdArquivamentoDTO->setStrSinAtivo('S');
    $objMdGdArquivamentoDTO->retNumIdArquivamento();
    $objMdGdArquivamentoDTO->retDthDataArquivamento();
    $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
    $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
    $objMdGdArquivamentoDTO->retStrNomeUsuario();
    $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
    $objMdGdArquivamentoDTO->retDblIdProcedimento();
    $objMdGdArquivamentoDTO->retNumGuardaCorrente();
    $objMdGdArquivamentoDTO->retNumGuardaIntermediaria();
    $objMdGdArquivamentoDTO->retStrStaDestinacaoFinal();


    $selTipoProcedimento = PaginaSEI::getInstance()->recuperarCampo('selTipoProcedimento');
    if ($selTipoProcedimento && $selTipoProcedimento !== 'null') {
        $objMdGdArquivamentoDTO->setNumIdTipoProcedimento($selTipoProcedimento);
    }

    $selDestinacaoFinal = PaginaSEI::getInstance()->recuperarCampo('selDestinacaoFinal');
    if ($selDestinacaoFinal && $selDestinacaoFinal !== 'null') {
        $objMdGdArquivamentoDTO->setStrStaDestinacaoFinal($selDestinacaoFinal);
    }


    $selCondicionante = PaginaSEI::getInstance()->recuperarCampo('selCondicionante');
    if ($selCondicionante && $selCondicionante !== 'null') {
        $objMdGdArquivamentoDTO->setStrSinCondicionante($selCondicionante);
    }

    $txtPeriodoDe = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoDe');
    if ($txtPeriodoDe) {
        $objMdGdArquivamentoDTO->setDthDataArquivamento($txtPeriodoDe, InfraDTO::$OPER_MAIOR_IGUAL);
    }

    $txtPeriodoA = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoA');
    if ($txtPeriodoA) {
        $objMdGdArquivamentoDTO->setDthDataArquivamento($txtPeriodoA, InfraDTO::$OPER_MENOR_IGUAL);
    }

    $objRelProtoloAssuntoRN = new RelProtocoloAssuntoRN();
    $selAssunto = PaginaSEI::getInstance()->recuperarCampo('selAssunto');

    // Faz a pesquisa por assunto caso o filtro tenha sido acionado
    if($selAssunto && $selAssunto !== 'null'){
        $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
        $objRelProtocoloAssuntoDTO->setNumIdAssunto($selAssunto);
        $objRelProtocoloAssuntoDTO->retDblIdProtocolo();

        $arrIdsProcedimento = InfraArray::converterArrInfraDTO($objRelProtoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO),'IdProtocolo');

        if($arrIdsProcedimento){
            $objMdGdArquivamentoDTO->setDblIdProcedimento($arrIdsProcedimento, InfraDTO::$OPER_IN);
        }else{
            $objMdGdArquivamentoDTO->setDblIdProcedimento([0], InfraDTO::$OPER_IN);
        }
    }



    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    PaginaSEI::getInstance()->prepararOrdenacao($objMdGdArquivamentoDTO, 'DataArquivamento', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objMdGdArquivamentoDTO);

    $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

    PaginaSEI::getInstance()->processarPaginacao($objMdGdArquivamentoDTO);
    $numRegistros = count($arrObjMdGdArquivamentoDTO);

    if ($numRegistros > 0) {
        
        // Busca os assuntos dos processos da página
        $arrIdsProcedimento = InfraArray::converterArrInfraDTO($arrObjMdGdArquivamentoDTO,'IdProcedimento');

        $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
        $objRelProtocoloAssuntoDTO->setDblIdProtocolo($arrIdsProcedimento, InfraDTO::$OPER_IN);
        $objRelProtocoloAssuntoDTO->retDblIdProtocolo();
        $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();
        $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();

        $arrObjRelProtocoloAssuntoDTO = InfraArray::indexarArrInfraDTO($objRelProtoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO),'IdProtocolo', true);

          
        $strSumarioTabela = 'Processos Arquivados';
        $strCaptionTabela = 'Processos Arquivados';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="9%">Órgão</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Unidade', 'DescricaoUnidadeCorrente', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Processo', 'ProtocoloFormatado', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">Assunto</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Tipo', 'NomeTipoProcedimento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Usuário', 'NomeUsuario', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Data de arquivamento', 'DataArquivamento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Fase Corrente</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Fase Intermediária</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Destinação Final</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        for ($i = 0; $i < $numRegistros; $i++) {

            // Isola os assuntos do processo
            $arrObjRelProtocoloAssuntoDTOProcedimento = $arrObjRelProtocoloAssuntoDTO[$arrObjMdGdArquivamentoDTO[$i]->getDblIdProcedimento()];
            $strAssuntosProcedimento = '';

            foreach($arrObjRelProtocoloAssuntoDTOProcedimento as $k => $objRelProtocoloAssuntoDTO){
                $strAssuntosProcedimento .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto() .' - ' .$objRelProtocoloAssuntoDTO->getStrDescricaoAssunto();
                if($k + 1 != count($arrObjRelProtocoloAssuntoDTOProcedimento)){
                    $strAssuntosProcedimento .= ' / ';
                }
            }

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento(), $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrDescricaoOrgaoSistema()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoUnidadeCorrente()) . '</td>';
            $strResultado .= '<td><a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjMdGdArquivamentoDTO[$i]->getDblIdProcedimento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '</a></td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($strAssuntosProcedimento) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeUsuario()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getNumGuardaCorrente()) . 'a</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getNumGuardaCorrente() + $arrObjMdGdArquivamentoDTO[$i]->getNumGuardaIntermediaria()) . 'a</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML(MdGdArquivamentoRN::obterDestinacoesFinalArquivamento()[$arrObjMdGdArquivamentoDTO[$i]->getStrStaDestinacaoFinal()]) . '</td>';
        }
        $strResultado .= '</table>';
    }

    // Busca uma lista de unidades
    $strItensSelUnidade = UnidadeINT::montarSelectSiglaDescricao('null', '&nbsp;', $selUnidade);
    $strItensSelTipoProcedimento = TipoProcedimentoINT::montarSelectNome('null', 'Todos', $selTipoProcedimento);
    $strItensSelAssunto = MdGdArquivamentoINT::montarSelectAssuntos('null', 'Todos', $selAssunto);

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

#lblCondicionante {position:absolute;left:0%;top:0%;width:20%;}
#selCondicionante {position:absolute;left:0%;top:20%;width:20%;}

#lblSelAssunto {position:absolute;left:0%;top:41%;width:20%;}
#selAssunto {position:absolute;left:0%;top:57%;width:38%;}

#lblTiposProcedimento {position:absolute;left:21%;top:0%;width:20%;}
#selTipoProcedimento {position:absolute;left:21%;top:20%;width:20%;}

#lblDestinacaoFinal {position:absolute;left:42%;top:0%;width:20%;}
#selDestinacaoFinal {position:absolute;left:42%;top:20%;width:20%;}

#lblPeriodoDe {position:absolute;left:63%;top:0%;width:10%;}
#txtPeriodoDe {position:absolute;left:63%;top:20%;width:10%;}
#imgCalPeriodoD {position:absolute;left:74%;top:20%;width:2%;}

#lblPeriodoA {position:absolute;left:77%;top:0%;width:10%;}
#txtPeriodoA {position:absolute;left:77%;top:20%;width:10%;}
#imgCalPeriodoA {position:absolute;left:88%;top:20%;width:2%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar() {
infraEfeitoTabelas();
document.getElementById('btnFechar').focus();

}

<? if ($bolAcaoExcluir) { ?>
    function acaoExcluir(id, unidade_origem, unidade_destino) {
    if (confirm("Confirma exclusão da Unidade de Arquivamento \"" + unidade_destino + "\" para a unidade \"" + unidade_origem + "\" ?")) {
    document.getElementById('hdnInfraItemId').value = id;
    document.getElementById('frmUnidadesArquivamentoLista').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmUnidadesArquivamentoLista').submit();
    }
    }

    function acaoExclusaoMultipla() {
    if (document.getElementById('hdnInfraItensSelecionados').value == '') {
    alert('Nenhuma Unidade selecionada.');
    return;
    }
    if (confirm("Confirma exclusão das Unidades de Arquivamento selecionadas?")) {
    document.getElementById('hdnInfraItemId').value = '';
    document.getElementById('frmUnidadesArquivamentoLista').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmUnidadesArquivamentoLista').submit();
    }
    }
<? } ?>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmUnidadesArquivamentoLista" method="post"
      action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
          <?
          PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
          PaginaSEI::getInstance()->abrirAreaDados('9.5em');
          ?>

    <label id="lblCondicionante" for="selCondicionante" accesskey="" class="infraLabelOpcional">Processos com Condicionantes:</label>
    <select id="selCondicionante" name="selCondicionante" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= MdGdArquivamentoINT::montarSelectCondicionantesArquivamento($selCondicionante); ?>
    </select>

    <label id="lblTiposProcedimento" for="selTipoProcedimento" accesskey="" class="infraLabelOpcional">Tipo de Processo:</label>
    <select id="selTipoProcedimento" name="selTipoProcedimento" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelTipoProcedimento; ?>
    </select>

    <label id="lblSelAssunto" for="selAssunto" accesskey="" class="infraLabelOpcional">Assunto:</label>
    <select id="selAssunto" name="selAssunto" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelAssunto; ?>
    </select>

    <label id="lblDestinacaoFinal" for="selDestinacaoFinal" accesskey="" class="infraLabelOpcional">Destinação Final:</label>
    <select id="selDestinacaoFinal" name="selDestinacaoFinal" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= MdGdArquivamentoINT::montarSelectDestinacoesFinalArquivamento($selDestinacaoFinal); ?>
    </select>

    <label id="lblPeriodoDe" for="txtPeriodoDe" accesskey="" class="infraLabelOpcional">De:</label>
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