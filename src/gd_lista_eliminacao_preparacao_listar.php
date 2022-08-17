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

    $strTitulo = 'Preparação da Listagem de Eliminação';
    
    switch ($_GET['acao']) {

        case 'gd_lista_eliminacao_preparacao_listar':
            PaginaSEI::getInstance()->salvarCamposPost(array('selOrgao', 'selUnidade', 'selAssunto', 'selTipoProcedimento'));

            break;

        case 'gd_lista_eliminacao_preparacao_gerar':
            $arrNumIdsArquivamento = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            
            //Busca os arquivamentos dos processos que serão enviados para a listagem de eliminação
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

            $objMdGdArquivamentoDTO->setNumIdArquivamento($arrNumIdsArquivamento, InfraDTO::$OPER_IN);
            $objMdGdArquivamentoDTO->retNumIdArquivamento();
            $objMdGdArquivamentoDTO->retDblIdProcedimento();
            $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
            $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
            $objMdGdArquivamentoDTO->retStrObservacaoEliminacao();
            $objMdGdArquivamentoDTO->retDthDataArquivamento();
            $objMdGdArquivamentoDTO->retDthDataGuardaCorrente();
            $objMdGdArquivamentoDTO->retDthDataGuardaIntermediaria();
            
            $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

            //Envia os processos para a listagem de eliminação
            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setArrObjMdGdArquivamentoDTO($arrObjMdGdArquivamentoDTO);
            
            $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
            $objMdGdListaEliminacaoRN->cadastrar($objMdGdListaEliminacaoDTO);
            
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            break;

        case 'gd_lista_eliminacao_preparacao_excluir':
            $arrNumIdsArquivamento = PaginaSEI::getInstance()->getArrStrItensSelecionados();

            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            foreach ($arrNumIdsArquivamento as $numIdArquivamento) {
                $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                $objMdGdArquivamentoDTO->setNumIdArquivamento($numIdArquivamento);

                $objMdGdArquivamentoRN->retirarListaArquivamento($objMdGdArquivamentoDTO);
            }

            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $bolAcaoGerar = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_preparacao_gerar');
    $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_preparacao_excluir');
    $bolAcaoObservar = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_preparacao_observar');

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    if ($bolAcaoGerar) {
        $arrComandos[] = '<button type="button" accesskey="P" id="btnGerarListagem" value="Gerar Listagem de Eliminação" onclick="acaoGerarListagemEliminacao();" class="infraButton"><span class="infraTeclaAtalho">G</span>erar Listagem de Eliminação</button>';
        $strLinkGerar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_preparacao_gerar&acao_origem=' . $_GET['acao']);
    }

    if ($bolAcaoExcluir) {
        $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
        $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_preparacao_excluir&acao_origem=' . $_GET['acao']);
    }

    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

    $objMdGdArquivamentoDTO->retNumIdArquivamento();
    $objMdGdArquivamentoDTO->retDthDataArquivamento();
    $objMdGdArquivamentoDTO->retDthDataGuardaIntermediaria();
    $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
    $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
    $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
    $objMdGdArquivamentoDTO->retStrObservacaoEliminacao();
    $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
    $objMdGdArquivamentoDTO->retDblIdProcedimento();
    $objMdGdArquivamentoDTO->retStrDescricao();
    $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_PREPARACAO_ELIMINACAO);
    $objMdGdArquivamentoDTO->setStrSinAtivo('S');
    $objMdGdArquivamentoDTO->setNumIdUnidadeIntermediaria(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

    $selUnidade = PaginaSEI::getInstance()->recuperarCampo('selUnidade');
    if ($selUnidade && $selUnidade !== 'null') {
        $objMdGdArquivamentoDTO->setNumIdUnidadeCorrente($selUnidade);
    }

    $selTipoProcedimento = PaginaSEI::getInstance()->recuperarCampo('selTipoProcedimento');
    if ($selTipoProcedimento && $selTipoProcedimento !== 'null') {
        $objMdGdArquivamentoDTO->setNumIdTipoProcedimento($selTipoProcedimento);
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
        $objRelProtocoloAssuntoDTO->retNumIdAssunto();
        $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();
        $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();

        $arrObjRelProtocoloAssuntoDTO = InfraArray::indexarArrInfraDTO($objRelProtoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO),'IdProtocolo', true);
        $arrIdsAssuntos = [];
        $arrAssuntosObservacoes = [];

        foreach($arrObjRelProtocoloAssuntoDTO as $objRelProtocoloAssuntoDTO){
            foreach($objRelProtocoloAssuntoDTO as $objRelProtocoloAssuntoDTO2){
                $arrIdsAssuntos[$objRelProtocoloAssuntoDTO2->getNumIdAssunto()] = $objRelProtocoloAssuntoDTO2->getNumIdAssunto();
            }
        }
        
        if($arrIdsAssuntos){
            $objAssuntoDTO = new AssuntoDTO();
            $objAssuntoDTO->setNumIdAssunto($arrIdsAssuntos, InfraDTO::$OPER_IN);
            $objAssuntoDTO->retNumIdAssunto();
            $objAssuntoDTO->retStrObservacao();
    
            $objAssuntoRN = new AssuntoRN();
            $arrAssuntosObservacoes = InfraArray::indexarArrInfraDTO($objAssuntoRN->listarRN0247($objAssuntoDTO),'IdAssunto');
        }

        $strResultado = '';

        $strSumarioTabela = 'Lista de Processos';
        $strCaptionTabela = 'Processos para Eliminação';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="12%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Unidade', 'DescricaoUnidadeCorrente', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";        
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Nº do Processo', 'ProtocoloFormatado', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="5%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Especificação', 'Descricao', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Código de Classificação</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Tipo de Processo', 'NomeTipoProcedimento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Data de Destinação', 'DataGuardaIntermediaria', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Observações e/ou Justificativas', 'ObservacaoEliminacao', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="20%">Observação do Código</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="12%">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();

        for ($i = 0; $i < $numRegistros; $i++) {

            // Isola os assuntos do processo e concatena as observações
            $arrObjRelProtocoloAssuntoDTOProcedimento = $arrObjRelProtocoloAssuntoDTO[$arrObjMdGdArquivamentoDTO[$i]->getDblIdProcedimento()];
            $strAssuntosProcedimento = '';
            $strObservacoesAssuntos = '';
            $strObservacoes = '';

            foreach($arrObjRelProtocoloAssuntoDTOProcedimento as $k => $objRelProtocoloAssuntoDTO){
                $strAssuntosProcedimento .= '<a alt="'.PaginaSEI::tratarHTML($objRelProtocoloAssuntoDTO->getStrDescricaoAssunto()).'" title="'.PaginaSEI::tratarHTML($objRelProtocoloAssuntoDTO->getStrDescricaoAssunto()).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto()).'</a>';
                if($k + 1 != count($arrObjRelProtocoloAssuntoDTOProcedimento)){
                    $strAssuntosProcedimento .= ' <br><br> ';
                }

                $objMdGdArquivamentoINT = new MdGdArquivamentoINT();

                if(empty($strObservacoesAssuntos)){
                    $strObservacoes = $arrAssuntosObservacoes[$objRelProtocoloAssuntoDTO->getNumIdAssunto()]->getStrObservacao();
                    $strObservacoesAssuntos .= '<a alt="'.PaginaSEI::tratarHTML($strObservacoes).'" title="'.PaginaSEI::tratarHTML($strObservacoes).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($objMdGdArquivamentoINT->reduzirCampoTexto($strObservacoes, 100)).'</a>';
                    if($k + 1 != count($arrAssuntosObservacoes)){
                        $strObservacoesAssuntos .= ' <br><br> ';
                    }
                }
            }

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento(), $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoUnidadeCorrente()) . '</td>';
            $strResultado .= '<td nowrap="nowrap"><a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjMdGdArquivamentoDTO[$i]->getDblIdProtocoloProcedimento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '</a></td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricao()) . '</td>';
            $strResultado .= '<td>' . $strAssuntosProcedimento . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML(substr($arrObjMdGdArquivamentoDTO[$i]->getDthDataGuardaIntermediaria(), 0, 10)) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrObservacaoEliminacao()) . '</td>';
            $strResultado .= '<td>' .$strObservacoesAssuntos . '</td>';
            $strResultado .= '<td align="center">';

            if ($bolAcaoObservar) {
                $strLinkObservar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_preparacao_observar&acao_origem=' . $_GET['acao'] . '&id_arquivamento=' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento());
                $strResultado .= '<a href="#" onclick="exibirJanelaObservacao(\'' . $strLinkObservar . '\');"><img src="/infra_css/svg/alterar.svg" title="Adicionar Observação e/ou Justificativa" title="Adicionar Observação e/ou Justificativa" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoExcluir) {
                //$strResultado .= '<a href="#ID-' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '" onclick="acaoExcluir(\'' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '\',\'' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="imagens/excluir.gif" title="Excluir da Preparação para Eliminação" alt="Excluir da Preparação para Eliminação" class="infraImg" /></a>&nbsp;';
            }

            $strResultado .= '</td></tr>' . "\n";
            $strResultado .= '</tr>' . "\n";
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
#lblOrgao {position:absolute;left:0%;top:0%;width:20%;}
#selOrgao {position:absolute;left:0%;top:38%;width:20%;}

#lblUnidade {position:absolute;left:22%;top:0%;width:20%;}
#selUnidade {position:absolute;left:22%;top:38%;width:20%;}

#lblTiposProcedimento {position:absolute;left:44%;top:0%;width:20%;}
#selTipoProcedimento {position:absolute;left:44%;top:38%;width:20%;}

#lblAssunto {position:absolute;left:0%;top:0%;width:20%;}
#selAssunto {position:absolute;left:0%;top:38%;width:38%;}
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
    if (confirm("Confirma a retirada do processo \"" + protocolo_formatado + "\" da preparação de listagem de eliminação  ?")) {
    document.getElementById('hdnInfraItemId').value = id;
    document.getElementById('frmPrepararListagemEliminacao').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmPrepararListagemEliminacao').submit();
    }
    }

    function acaoExclusaoMultipla() {
    if (document.getElementById('hdnInfraItensSelecionados').value == '') {
    alert('Nenhum Processo Selecionado.');
    return;
    }
    if (confirm("Confirma a retirada dos processo selecionados da preparação de listagem de eliminação?")) {
    document.getElementById('hdnInfraItemId').value = '';
    document.getElementById('frmPrepararListagemEliminacao').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmPrepararListagemEliminacao').submit();
    }
    }
<? } ?>

<? if ($bolAcaoObservar) { ?>
    function exibirJanelaObservacao(link) {
    infraAbrirJanela(link, 'janelaObservarPreparacaoListagemEliminacao', 720, 300, 'location=0,status=1,resizable=1,scrollbars=1', false);
    }
<? } ?>

<? if ($bolAcaoObservar) { ?>
    function acaoGerarListagemEliminacao(){
        if (document.getElementById('hdnInfraItensSelecionados').value == '') {
            alert('Nenhum Processo Selecionado.');
            return;
        }
        
        document.getElementById('hdnInfraItemId').value = '';
        document.getElementById('frmPrepararListagemEliminacao').action = '<?= $strLinkGerar ?>';
        document.getElementById('frmPrepararListagemEliminacao').submit();
    }
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmPrepararListagemEliminacao" method="post"
      action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
          <?
          PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
          PaginaSEI::getInstance()->abrirAreaDados('9.5em');
          ?>

        <div class="infraAreaDados" style="height:5em;">
            <label id="lblOrgao" for="selOrgao" accesskey="" class="infraLabelOpcional">Órgão:</label>
            <select id="selOrgao" name="selOrgao" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
                <option><?= SessaoSEI::getInstance()->getStrDescricaoOrgaoSistema() ?></option>
            </select>

            <label id="lblUnidade" for="selUnidade" accesskey="" class="infraLabelOpcional">Unidade:</label>
            <select id="selUnidade" name="selUnidade" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
            <?= $strItensSelUnidade ?>
            </select>

            <label id="lblTiposProcedimento" for="selTipoProcedimento" accesskey="" class="infraLabelOpcional">Tipo de Processo:</label>
            <select id="selTipoProcedimento" name="selTipoProcedimento" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
                <?= $strItensSelTipoProcedimento; ?>
            </select>
        </div>

        <div class="infraAreaDados" style="height:5em;">
            <label id="lblAssunto" for="lblAssunto" accesskey="" class="infraLabelOpcional">Assunto:</label>
            <select id="selAssunto" name="selAssunto" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
                <?= $strItensSelAssunto; ?>
            </select>
        </div>
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