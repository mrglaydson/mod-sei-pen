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
    PaginaSEI::getInstance()->salvarCamposPost(array('hdnInfraItemId', 'selTipoProcedimento', 'selDestinacaoFinal', 'selCondicionante', 'txtPeriodoDe', 'txtPeriodoA', 'selAssunto'));

    switch ($_GET['acao']) {

        case 'gd_arquivamento_listar':
            $strTitulo = 'Arquivo da Unidade';
            break;
        case 'gd_arquivamento_editar':
            $numIdArquivamento = PaginaSEI::getInstance()->recuperarCampo('hdnInfraItemId');

            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
            $objMdGdArquivamentoDTO->setNumIdArquivamento($numIdArquivamento);
            
            // Muda a situação do arquivamento para editado
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $objMdGdArquivamentoRN->editarArquivamento($objMdGdArquivamentoDTO);
            break;
        case 'gd_arquivamento_edicao_concluir':
            $numIdArquivamento = PaginaSEI::getInstance()->recuperarCampo('hdnInfraItemId');

            // Instancia o arquivamento 
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
            $objMdGdArquivamentoDTO->setNumIdArquivamento($numIdArquivamento);

            // Muda a situação do arquivamento para conclusão da edição
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $objMdGdArquivamentoRN->concluirEdicaoArquivamento($objMdGdArquivamentoDTO);
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    // Ação de desarquivar processo
    $bolAcaoDesarquivar = SessaoSEI::getInstance()->verificarPermissao('gd_procedimento_desarquivar');
    
    // Ação de devolver arquivamento
    $bolAcaoEditarArquivamento = SessaoSEI::getInstance()->verificarPermissao('gd_arquivamento_editar');
    
    // Ação de conclusão da edição do arquivamento
    $bolAcaoConcluirEdicaoArquivamento = SessaoSEI::getInstance()->verificarPermissao('gd_arquivamento_edicao_concluir');

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
    
    if($bolAcaoDesarquivar){
        $strLinkDesarquivar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_desarquivar&acao_origem=' . $_GET['acao']);
        $arrComandos[] = '<button type="submit" accesskey="D" id="sbmDesarquivar" value="Desarquivar" class="infraButton" onclick="acaoDesarquivarMultiplo()"><span class="infraTeclaAtalho">D</span>esarquivar</button>';
    }

    if($bolAcaoEditarArquivamento){
        $strLinkAcaoEditarArquivamento = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivamento_editar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']);
    }

    if($bolAcaoConcluirEdicaoArquivamento){
        $strLinkAcaoConcluirEdicaoArquivamento = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivamento_edicao_concluir&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']);
    }



    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
    $objMdGdArquivamentoDTO->setNumIdUnidadeCorrente(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    $objMdGdArquivamentoDTO->setStrSinAtivo('S');
    $objMdGdArquivamentoDTO->setStrSituacao([MdGdArquivamentoRN::$ST_FASE_CORRENTE, MdGdArquivamentoRN::$ST_DEVOLVIDO, MdGdArquivamentoRN::$ST_FASE_EDICAO], InfraDTO::$OPER_IN);
    $objMdGdArquivamentoDTO->retNumIdArquivamento();
    $objMdGdArquivamentoDTO->retDthDataArquivamento();
    $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
    $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
    $objMdGdArquivamentoDTO->retStrNomeUsuario();
    $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
    $objMdGdArquivamentoDTO->retStrDescricao();
    $objMdGdArquivamentoDTO->retStrSituacao();
    $objMdGdArquivamentoDTO->retDblIdProcedimento();
    $objMdGdArquivamentoDTO->retNumGuardaCorrente();
    $objMdGdArquivamentoDTO->retNumGuardaIntermediaria();
    $objMdGdArquivamentoDTO->retStrStaDestinacaoFinal();
    $objMdGdArquivamentoDTO->retStrStaNivelAcessoGlobal();
    $objMdGdArquivamentoDTO->retDthDataGuardaCorrente();
    $objMdGdArquivamentoDTO->retDthDataGuardaIntermediaria();
    $objMdGdArquivamentoDTO->retStrObservacaoDevolucao();
    $objMdGdArquivamentoDTO->setOrdDthDataGuardaCorrente(InfraDTO::$TIPO_ORDENACAO_ASC);

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
        $strResultado .= '<th class="infraTh" width="5%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Processo', 'ProtocoloFormatado', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Especificação', 'Descricao', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Assunto</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="5%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Tipo', 'NomeTipoProcedimento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="5%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Usuário', 'NomeUsuario', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Data de arquivamento', 'DataArquivamento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Fase Corrente</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Fase Intermediária</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Destinação Final</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Ação</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        for ($i = 0; $i < $numRegistros; $i++) {
            // Isola os assuntos do processo
            $arrObjRelProtocoloAssuntoDTOProcedimento = $arrObjRelProtocoloAssuntoDTO[$arrObjMdGdArquivamentoDTO[$i]->getDblIdProcedimento()];
            $strAssuntosProcedimento = '';

            foreach($arrObjRelProtocoloAssuntoDTOProcedimento as $k => $objRelProtocoloAssuntoDTO){
                $strAssuntosProcedimento .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto() .' - ' .$objRelProtocoloAssuntoDTO->getStrDescricaoAssunto();
                if($k + 1 != count($arrObjRelProtocoloAssuntoDTOProcedimento)){
                    $strAssuntosProcedimento .= '  <br><br>  ';
                }
            }

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdArquivamentoDTO[$i]->getDblIdProcedimento(), $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado()) . '</td>';
            
            $strResultado .= '<td>';
            $strResultado .= '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjMdGdArquivamentoDTO[$i]->getDblIdProcedimento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '</a>';
            if($arrObjMdGdArquivamentoDTO[$i]->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_RESTRITO){
                $strResultado .= '<img src="imagens/sei_chave_restrito.gif" title="Processo Restrito" title="Processo Restrito" class="infraImg" />';
            }

            if($arrObjMdGdArquivamentoDTO[$i]->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_SIGILOSO){
                $strResultado .= '<img src="imagens/sei_chave_sigiloso.gif" title="Processo Sigiloso" title="Processo Sigiloso" class="infraImg" />';
            }

            $strResultado .= '</td>';

            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricao()) . '</td>';

            
            // Obtem o tempo formatado de guarda corrente
            $strDthDataArquivamento = $arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento();
            $strDthDataArquivamento = InfraData::formatarDataBanco(substr($strDthDataArquivamento, 0, 10));
            $strDthDataArquivamento = substr($strDthDataArquivamento, 0, 10);
            $strTempoGuardaCorrente = MdGdArquivamentoRN::descreverTempoArquivamentoCorrente($strDthDataArquivamento, $arrObjMdGdArquivamentoDTO[$i]->getNumGuardaCorrente());

            // Obtem o tempo formatado de guarda intermediária
            $strTempoGuardaIntermediaria = '';
            if($arrObjMdGdArquivamentoDTO[$i]->getStrSituacao() != MdGdArquivamentoRN::$ST_DEVOLVIDO){
                $strTempoGuardaIntermediaria = MdGdArquivamentoRN::descreverTempoArquivamentoIntermediario($strDthDataArquivamento, $arrObjMdGdArquivamentoDTO[$i]->getNumGuardaCorrente(), $arrObjMdGdArquivamentoDTO[$i]->getNumGuardaIntermediaria());
            }else{
                $strTempoGuardaIntermediaria = '<b>Devolvido para correção</b><br>';
                $strTempoGuardaIntermediaria .= '<b>Motivo: </b> '. (!is_null($arrObjMdGdArquivamentoDTO[$i]->getStrObservacaoDevolucao()) ? $arrObjMdGdArquivamentoDTO[$i]->getStrObservacaoDevolucao() : 'Não Informado');
            }
            
            $strResultado .= '</td>';
            $strResultado .= '<td>' . $strAssuntosProcedimento . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeUsuario()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento()) . '</td>';
            $strResultado .= '<td>' . $strTempoGuardaCorrente. '</td>';
            $strResultado .= '<td>' . $strTempoGuardaIntermediaria. '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML(MdGdArquivamentoRN::obterDestinacoesFinalArquivamento()[$arrObjMdGdArquivamentoDTO[$i]->getStrStaDestinacaoFinal()]) . '</td>';
            
            // Ações
            $strResultado .= '<td align="center">';
            
            $strAcoes = '';
            if($bolAcaoEditarArquivamento && $arrObjMdGdArquivamentoDTO[$i]->getStrSituacao() == MdGdArquivamentoRN::$ST_DEVOLVIDO){
                $strAcoes .= '<a href="#ID-' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '" onclick="acaoEditarArquivamento(\'' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/alterar_metadados.gif" title="Editar Processo" title="Editar Processo" class="infraImg" /></a>';
            }

            if($bolAcaoDesarquivar && $arrObjMdGdArquivamentoDTO[$i]->getStrSituacao() == MdGdArquivamentoRN::$ST_FASE_CORRENTE){
                $strAcoes .= '<a href="#" onclick="acaoDesarquivar('.$arrObjMdGdArquivamentoDTO[$i]->getDblIdProcedimento().');"><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/desarquivamento.png" title="Desarquivar Processo" alt="Desarquivar Processo" class="infraImg" style="width: 22px; height: 22px;"/></a>&nbsp;';
            }

            if($bolAcaoConcluirEdicaoArquivamento && $arrObjMdGdArquivamentoDTO[$i]->getStrSituacao() == MdGdArquivamentoRN::$ST_FASE_EDICAO){
                $strAcoes .= '<a href="#ID-' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '" onclick="acaoConcluirEdicaoArquivamento(\'' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/concluir_edicao.gif" title="Concluir Edição" title="Concluir Edição" class="infraImg" /></a>';
            }
            
            $strResultado .= $strAcoes.'</td></tr>' . "\n";
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
#imgCalPeriodoD {position:absolute;left:74%;top:20%;}

#lblPeriodoA {position:absolute;left:77%;top:0%;width:10%;}
#txtPeriodoA {position:absolute;left:77%;top:20%;width:10%;}
#imgCalPeriodoA {position:absolute;left:88%;top:20%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar() {
infraEfeitoTabelas();
document.getElementById('btnFechar').focus();

}

<? if ($bolAcaoDesarquivar) { ?>
        function acaoDesarquivar(id_procedimento) {
            document.getElementById('hdnInfraItemId').value = id_procedimento;
            document.getElementById('frmArquivamentoListar').action = '<?= $strLinkDesarquivar ?>';
            document.getElementById('frmArquivamentoListar').submit();
        }

        function acaoDesarquivarMultiplo() {
            if (document.getElementById('hdnInfraItensSelecionados').value == '') {
                alert('Nenhum processo selecionado.');
                return;
            }

            document.getElementById('frmArquivamentoListar').action = '<?= $strLinkDesarquivar ?>';
            document.getElementById('frmArquivamentoListar').submit();
        }
<? } ?>

<? if ($bolAcaoEditarArquivamento) { ?>
    function acaoEditarArquivamento(id_arquivamento){
        if (confirm("Deseja editar o processo para correção? O processo será reaberto porém seu registro de arquivamento será mantido.")) {
            document.getElementById('hdnInfraItemId').value = id_arquivamento;
            document.getElementById('frmArquivamentoListar').action = '<?= $strLinkAcaoEditarArquivamento ?>';
            document.getElementById('frmArquivamentoListar').submit();
        }
    }
<? } ?>

<? if ($bolAcaoConcluirEdicaoArquivamento) { ?>
    function acaoConcluirEdicaoArquivamento(id_arquivamento){
        if (confirm("Confirma a conclusão da edição desse processo?")) {
            document.getElementById('hdnInfraItemId').value = id_arquivamento;
            document.getElementById('frmArquivamentoListar').action = '<?= $strLinkAcaoConcluirEdicaoArquivamento ?>';
            document.getElementById('frmArquivamentoListar').submit();
        }
    }
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmArquivamentoListar" method="post"
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
    <img id="imgCalPeriodoD" title="Selecionar Data Inicial" alt="Selecionar Data Inicial" src="/infra_css/svg/calendario.svg" class="infraImg" onclick="infraCalendario('txtPeriodoDe', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    

    <label id="lblPeriodoA" for="txtPeriodoA" accesskey="" class="infraLabelOpcional">Até</label>
    <input type="text" id="txtPeriodoA" value="<?= $txtPeriodoA ?>" name="txtPeriodoA" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoA) ?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
    <img id="imgCalPeriodoA" title="Selecionar Data Final" alt="Selecionar Data Final" src="/infra_css/svg/calendario.svg" class="infraImg" onclick="infraCalendario('txtPeriodoA', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    


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