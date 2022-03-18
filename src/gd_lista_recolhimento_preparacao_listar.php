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

    $strTitulo = 'Preparação da Listagem de Recolhimento';
    switch ($_GET['acao']) {

        case 'gd_lista_recolhimento_preparacao_listar':
            PaginaSEI::getInstance()->salvarCamposPost(array('selOrgao', 'selUnidade'));
            break;

        case 'gd_lista_recolhimento_preparacao_gerar':
            $arrNumIdsArquivamento = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            
            //Busca os arquivamentos dos processos que serão enviados para a listagem de Recolhimento
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

            $objMdGdArquivamentoDTO->setNumIdArquivamento($arrNumIdsArquivamento, InfraDTO::$OPER_IN);
            $objMdGdArquivamentoDTO->retNumIdArquivamento();
            $objMdGdArquivamentoDTO->retDblIdProcedimento();
            $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
            $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
            $objMdGdArquivamentoDTO->retDthDataArquivamento();
            $objMdGdArquivamentoDTO->retDthDataGuardaCorrente();
            $objMdGdArquivamentoDTO->retDthDataGuardaIntermediaria();
            $objMdGdArquivamentoDTO->retStrObservacaoRecolhimento();
            
            $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

            //Envia os processos para a listagem de Recolhimento
            $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
            $objMdGdListaRecolhimentoDTO->setArrObjMdGdArquivamentoDTO($arrObjMdGdArquivamentoDTO);
            
            $objMdGdListaRecolhimentoRN = new MdGdListaRecolhimentoRN();
            $objMdGdListaRecolhimentoRN->cadastrar($objMdGdListaRecolhimentoDTO);
            
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            break;

        case 'gd_lista_recolhimento_preparacao_excluir':
            $arrNumIdsArquivamento = PaginaSEI::getInstance()->getArrStrItensSelecionados();

            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            foreach ($arrNumIdsArquivamento as $numIdArquivamento) {
                $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                $objMdGdArquivamentoDTO->setNumIdArquivamento($numIdArquivamento);

                $objMdGdArquivamentoRN->retirarListaArquivamento($objMdGdArquivamentoDTO);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $bolAcaoGerar = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_preparacao_gerar');
    $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_preparacao_excluir');
    $bolAcaoObservar = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_preparacao_observar');

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    if ($bolAcaoGerar) {
        $arrComandos[] = '<button type="button" accesskey="P" id="btnGerarListagem" value="Gerar Listagem de Recolhimento" onclick="acaoGerarListagemRecolhimento();" class="infraButton"><span class="infraTeclaAtalho">G</span>erar Listagem de Recolhimento</button>';
        $strLinkGerar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_preparacao_gerar&acao_origem=' . $_GET['acao']);
    }

    if ($bolAcaoExcluir) {
        $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
        $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_preparacao_excluir&acao_origem=' . $_GET['acao']);
    }

    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

    $objMdGdArquivamentoDTO->retNumIdArquivamento();
    $objMdGdArquivamentoDTO->retDthDataArquivamento();
    $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
    $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
    $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
    $objMdGdArquivamentoDTO->retStrObservacaoRecolhimento();
    $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
    $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_PREPARACAO_RECOLHIMENTO);
    $objMdGdArquivamentoDTO->setNumIdUnidadeIntermediaria(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    $objMdGdArquivamentoDTO->setStrSinAtivo('S');
    
    $selUnidade = PaginaSEI::getInstance()->recuperarCampo('selUnidade');
    if ($selUnidade && $selUnidade !== 'null') {
        $objMdGdArquivamentoDTO->setNumIdUnidadeCorrente($selUnidade);
    }

    PaginaSEI::getInstance()->prepararOrdenacao($objMdGdArquivamentoDTO, 'DataArquivamento', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objMdGdArquivamentoDTO);

    $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

    PaginaSEI::getInstance()->processarPaginacao($objMdGdArquivamentoDTO);
    $numRegistros = count($arrObjMdGdArquivamentoDTO);

    if ($numRegistros > 0) {
        $strResultado = '';

        $strSumarioTabela = 'Lista de Processos';
        $strCaptionTabela = 'Lista de Processos';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="13%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Unidade', 'DescricaoUnidadeCorrente', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Código de Classificação</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="20%">Descritor do Código</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="14%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Nº do Processo', 'ProtocoloFormatado', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Tipo de Processo', 'NomeTipoProcedimento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Data de arquivamento', 'DataArquivamento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Observações e/ou Justificativas', 'ObservacaoRecolhimento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="12%">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();

        for ($i = 0; $i < $numRegistros; $i++) {

            // Obtem os dados do assunto
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($arrObjMdGdArquivamentoDTO[$i]->getDblIdProtocoloProcedimento());
            $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();
            $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();

            $arrObjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);

            $strCodigoClassificacao = '';
            $strDescritorCodigo = '';

            foreach ($arrObjRelProtocoloAssuntoDTO as $key => $objRelProtocoloAssuntoDTO) {
                if ($key + 1 == count($arrObjRelProtocoloAssuntoDTO)) {
                    $strCodigoClassificacao .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto();
                    $strDescritorCodigo .= $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto();
                } else {
                    $strCodigoClassificacao .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto() . " <br><br> ";
                    $strDescritorCodigo .= $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto() . " <br><br> ";
                }
            }

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento(), $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoUnidadeCorrente()) . '</td>';
            $strResultado .= '<td>' . $strCodigoClassificacao . '</td>';
            $strResultado .= '<td>' . $strDescritorCodigo . '</td>';
            $strResultado .= '<td><a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjMdGdArquivamentoDTO[$i]->getDblIdProtocoloProcedimento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '</a></td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrObservacaoRecolhimento()) . '</td>';
            $strResultado .= '<td align="center">';

            if ($bolAcaoObservar) {
                $strLinkObservar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_preparacao_observar&acao_origem=' . $_GET['acao'] . '&id_arquivamento=' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento());
                $strResultado .= '<a href="#" onclick="exibirJanelaObservacao(\'' . $strLinkObservar . '\');"><img src="/infra_css/svg/alterar.svg" title="Adicionar Observação e/ou Justificativa" title="Adicionar Observação e/ou Justificativa" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoExcluir) {
                $strResultado .= '<a href="#ID-' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '" onclick="acaoExcluir(\'' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '\',\'' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="/infra_css/svg/excluir.svg" title="Excluir da Preparação para Recolhimento" alt="Excluir da Preparação para Recolhimento" class="infraImg" /></a>&nbsp;';
            }

            $strResultado .= '</td></tr>' . "\n";
            $strResultado .= '</tr>' . "\n";
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
    function acaoExcluir(id, protocolo_formatado) {
    if (confirm("Confirma a retirada do processo \"" + protocolo_formatado + "\" da preparação de listagem de Recolhimento  ?")) {
    document.getElementById('hdnInfraItemId').value = id;
    document.getElementById('frmPrepararListagemRecolhimento').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmPrepararListagemRecolhimento').submit();
    }
    }

    function acaoExclusaoMultipla() {
    if (document.getElementById('hdnInfraItensSelecionados').value == '') {
    alert('Nenhum Processo Selecionado.');
    return;
    }
    if (confirm("Confirma a retirada dos processo selecionados da preparação de listagem de Recolhimento?")) {
    document.getElementById('hdnInfraItemId').value = '';
    document.getElementById('frmPrepararListagemRecolhimento').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmPrepararListagemRecolhimento').submit();
    }
    }
<? } ?>

<? if ($bolAcaoObservar) { ?>
    function exibirJanelaObservacao(link) {
    infraAbrirJanela(link, 'janelaObservarPreparacaoListagemRecolhimento', 720, 300, 'location=0,status=1,resizable=1,scrollbars=1', false);
    }
<? } ?>

<? if ($bolAcaoObservar) { ?>
    function acaoGerarListagemRecolhimento(){
        if (document.getElementById('hdnInfraItensSelecionados').value == '') {
            alert('Nenhum Processo Selecionado.');
            return;
        }
        
        document.getElementById('hdnInfraItemId').value = '';
        document.getElementById('frmPrepararListagemRecolhimento').action = '<?= $strLinkGerar ?>';
        document.getElementById('frmPrepararListagemRecolhimento').submit();
    }
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmPrepararListagemRecolhimento" method="post"
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
    <?= $strItensSelUnidade ?>
    </select>
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