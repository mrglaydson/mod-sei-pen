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

    PaginaSEI::getInstance()->salvarCamposPost(array('selUnidade', 'selTipoProcedimento', 'selDestinacaoFinal', 'selCondicionante', 'selFaseGestaoDocumental', 'txtPeriodoDe', 'txtPeriodoA'));

    switch ($_GET['acao']) {

        case 'gd_relatorio':
            $strTitulo = 'Relatório de Gestão Documental';

            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

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

    $objMdGdArquivamentoDTO->setStrSinAtivo('S');

    $selUnidade = PaginaSEI::getInstance()->recuperarCampo('selUnidade');
    if ($selUnidade && $selUnidade !== 'null') {
        $objMdGdArquivamentoDTO->setNumIdUnidadeCorrente($selUnidade);
    }

    $selTipoProcedimento = PaginaSEI::getInstance()->recuperarCampo('selTipoProcedimento');
    if ($selTipoProcedimento && $selTipoProcedimento !== 'null') {
        $objMdGdArquivamentoDTO->setNumIdTipoProcedimento($selTipoProcedimento);
    }

    $selDestinacaoFinal = PaginaSEI::getInstance()->recuperarCampo('selDestinacaoFinal');
    if ($selDestinacaoFinal && $selDestinacaoFinal !== 'null') {
        $objMdGdArquivamentoDTO->setStrStaDestinacaoFinal($selDestinacaoFinal);
    }

    $selFaseGestaoDocumental = PaginaSEI::getInstance()->recuperarCampo('selFaseGestaoDocumental');
    if ($selFaseGestaoDocumental && $selFaseGestaoDocumental !== 'null') {
        $objMdGdArquivamentoDTO->setStrStaGuarda($selFaseGestaoDocumental);
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

    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    PaginaSEI::getInstance()->prepararOrdenacao($objMdGdArquivamentoDTO, 'DataArquivamento', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objMdGdArquivamentoDTO);

    $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

    // Realiza a contabilização
    $arrContadores = [];
    $arrSituacoesArquivamento = MdGdArquivamentoRN::obterSituacoesArquivamento();

    foreach($arrSituacoesArquivamento as $sit => $label){
        $objMdGdArquivamentoDTO->setStrSituacao($sit);
        $arrContadores[$sit] = $objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO);
    }

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
        $strResultado .= '<th class="infraTh" width="9%">Órgão</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Unidade', 'DescricaoUnidadeCorrente', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Processo', 'ProtocoloFormatado', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="20%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Tipo', 'NomeTipoProcedimento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Usuário', 'NomeUsuario', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Data de arquivamento', 'DataArquivamento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Fase Corrente</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Fase Intermediária</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Destinação Final</th>' . "\n";
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
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getNumGuardaCorrente()) . 'a</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getNumGuardaIntermediaria()) . 'a</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML(MdGdArquivamentoRN::obterDestinacoesFinalArquivamento()[$arrObjMdGdArquivamentoDTO[$i]->getStrStaDestinacaoFinal()]) . '</td>';
        }
        $strResultado .= '</table>';
    }

    $strResultado .= '<table width="20%" class="infraTable" summary="" style="float: left; margin-top: 10px;">' . "\n";
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="100%">'.MdGdArquivamentoRN::obterSituacoesArquivamento()[MdGdArquivamentoRN::$ST_FASE_CORRENTE].'</th>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '<tr class="infraTrClara">';
    $strResultado .= '<td><center>'.$arrContadores[MdGdArquivamentoRN::$ST_FASE_CORRENTE].'</center></td>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '</table>';

    $strResultado .= '<table width="20%" class="infraTable" summary="" style="float: left; margin-top: 10px; margin-left: 10px">' . "\n";
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="100%">'.MdGdArquivamentoRN::obterSituacoesArquivamento()[MdGdArquivamentoRN::$ST_FASE_INTERMEDIARIA].'</th>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '<tr class="infraTrClara">';
    $strResultado .= '<td><center>'.$arrContadores[MdGdArquivamentoRN::$ST_FASE_INTERMEDIARIA].'</center></td>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '</table>';

    $strResultado .= '<table width="20%" class="infraTable" summary="" style="float: left; margin-top: 10px; margin-left: 10px">' . "\n";
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="100%">'.MdGdArquivamentoRN::obterSituacoesArquivamento()[MdGdArquivamentoRN::$ST_PREPARACAO_RECOLHIMENTO].'</th>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '<tr class="infraTrClara">';
    $strResultado .= '<td><center>'.$arrContadores[MdGdArquivamentoRN::$ST_PREPARACAO_RECOLHIMENTO].'</center></td>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '</table>';

    $strResultado .= '<table width="20%" class="infraTable" summary="" style="float: left; margin-top: 10px; margin-left: 10px">' . "\n";
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="100%">'.MdGdArquivamentoRN::obterSituacoesArquivamento()[MdGdArquivamentoRN::$ST_PREPARACAO_ELIMINACAO].'</th>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '<tr class="infraTrClara">';
    $strResultado .= '<td><center>'.$arrContadores[MdGdArquivamentoRN::$ST_PREPARACAO_ELIMINACAO].'</center></td>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '</table>';

    $strResultado .= '<table width="20%" class="infraTable" summary="" style="float: left; margin-top: 10px;">' . "\n";
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="100%">'.MdGdArquivamentoRN::obterSituacoesArquivamento()[MdGdArquivamentoRN::$ST_ENVIADO_RECOLHIMENTO].'</th>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '<tr class="infraTrClara">';
    $strResultado .= '<td><center>'.$arrContadores[MdGdArquivamentoRN::$ST_ENVIADO_RECOLHIMENTO].'</center></td>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '</table>';

    $strResultado .= '<table width="20%" class="infraTable" summary="" style="float: left; margin-top: 10px; margin-left: 10px">' . "\n";
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="100%">'.MdGdArquivamentoRN::obterSituacoesArquivamento()[MdGdArquivamentoRN::$ST_ENVIADO_ELIMINACAO].'</th>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '<tr class="infraTrClara">';
    $strResultado .= '<td><center>'.$arrContadores[MdGdArquivamentoRN::$ST_ENVIADO_ELIMINACAO].'</center></td>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '</table>';

    $strResultado .= '<table width="20%" class="infraTable" summary="" style="float: left; margin-top: 10px; margin-left: 10px">' . "\n";
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="100%">'.MdGdArquivamentoRN::obterSituacoesArquivamento()[MdGdArquivamentoRN::$ST_RECOLHIDO].'</th>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '<tr class="infraTrClara">';
    $strResultado .= '<td><center>'.$arrContadores[MdGdArquivamentoRN::$ST_RECOLHIDO].'</center></td>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '</table>';

    $strResultado .= '<table width="20%" class="infraTable" summary="" style="float: left; margin-top: 10px; margin-left: 10px">' . "\n";
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="100%">'.MdGdArquivamentoRN::obterSituacoesArquivamento()[MdGdArquivamentoRN::$ST_ELIMINADO].'</th>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '<tr class="infraTrClara">';
    $strResultado .= '<td><center>'.$arrContadores[MdGdArquivamentoRN::$ST_ELIMINADO].'</center></td>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strResultado .= '</table>';

    

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

#lblFaseGestaoDocumental {position:absolute;left:0%;top:55%;width:20%;}
#selFaseGestaoDocumental {position:absolute;left:0%;top:70%;width:20%;}

#lblCondicionante {position:absolute;left:21%;top:55%;width:20%;}
#selCondicionante {position:absolute;left:21%;top:70%;width:20%;}

#lblPeriodoDe {position:absolute;left:42%;top:55%;width:20%;}
#txtPeriodoDe {position:absolute;left:42%;top:70%;width:17%;}
#imgCalPeriodoD {position:absolute;left:60%;top:72%;}

#lblPeriodoA {position:absolute;left:63%;top:55%;width:20%;}
#txtPeriodoA {position:absolute;left:63%;top:70%;width:17%;}
#imgCalPeriodoA {position:absolute;left:81%;top:72%;}

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

    <label id="lblOrgao" for="selOrgao" accesskey="" class="infraLabelOpcional">Órgão:</label>
    <select id="selOrgao" name="selOrgao" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <option><?= SessaoSEI::getInstance()->getStrDescricaoOrgaoSistema() ?></option>
    </select>

    <label id="lblUnidade" for="selUnidade" accesskey="" class="infraLabelOpcional">Unidade:</label>
    <select id="selUnidade" name="selUnidade" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelUnidade; ?>
    </select>

    <label id="lblTiposProcedimento" for="selTipoProcedimento" accesskey="" class="infraLabelOpcional">Tipo de Processo:</label>
    <select id="selTipoProcedimento" name="selTipoProcedimento" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelTipoProcedimento; ?>
    </select>

    <label id="lblDestinacaoFinal" for="selDestinacaoFinal" accesskey="" class="infraLabelOpcional">Destinação Final:</label>
    <select id="selDestinacaoFinal" name="selDestinacaoFinal" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= MdGdArquivamentoINT::montarSelectDestinacoesFinalArquivamento(); ?>
    </select>

    <label id="lblFaseGestaoDocumental" for="selFaseGestaoDocumental" accesskey="" class="infraLabelOpcional">Fase da Gestão Documental:</label>
    <select id="selFaseGestaoDocumental" name="selFaseGestaoDocumental" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= MdGdArquivamentoINT::montarSelectGuardasArquivamento(); ?>
    </select>

    <label id="lblCondicionante" for="selCondicionante" accesskey="" class="infraLabelOpcional">Processos com Condicionantes:</label>
    <select id="selCondicionante" name="selCondicionante" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <option value=""></option>
        <option value="S">Com Condicionante</option>
        <option value="N">Sem Condicionante</option>
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