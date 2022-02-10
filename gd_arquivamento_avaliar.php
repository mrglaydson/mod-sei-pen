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
    PaginaSEI::getInstance()->salvarCamposPost(array('hdnInfraItemId', 'selUnidade', 'selTipoProcedimento', 'selDestinacaoFinal', 'txtPeriodoDe', 'txtPeriodoA', 'selAssunto', 'txtAnoDestinacao', 'selCondicionante'));

    $strTitulo = 'Avaliação de Processos';

    switch ($_GET['acao']) {

        case 'gd_arquivamento_avaliar':
            break;
               
        case 'gd_arquivamento_eliminacao_enviar':   
            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            
            // Valida a destinação final dos arquivamentos enviados
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
            $objMdGdArquivamentoDTO->setNumIdArquivamento($arrStrIds, InfraDTO::$OPER_IN);
            $objMdGdArquivamentoDTO->setStrStaDestinacaoFinal(MdGdArquivamentoRN::$DF_ELIMINACAO);
            $objMdGdArquivamentoDTO->retNumIdArquivamento();

            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $arrObjMdGdrquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

            // Envia os arquivamentos para eliminação
            foreach($arrObjMdGdrquivamentoDTO as $objMdGdArquivamentoDTO){
                $objMdGdArquivamentoRN->enviarEliminacao($objMdGdArquivamentoDTO);
            }
            break;

        case 'gd_arquivamento_recolhimento_enviar':
            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            
            // Valida a destinação final dos arquivamentos enviados
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
            $objMdGdArquivamentoDTO->setNumIdArquivamento($arrStrIds, InfraDTO::$OPER_IN);
            $objMdGdArquivamentoDTO->setStrStaDestinacaoFinal(MdGdArquivamentoRN::$DF_RECOLHIMENTO);
            $objMdGdArquivamentoDTO->retNumIdArquivamento();

            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $arrObjMdGdrquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

            // Envias o arquivamentos para recolhimento
            foreach($arrObjMdGdrquivamentoDTO as $objMdGdArquivamentoDTO){
                $objMdGdArquivamentoRN->enviarRecolhimento($objMdGdArquivamentoDTO);
            }
            
            break;
        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    // Ação de envio para eliminação
    $bolAcaoProcedimentoEliminacaoEnviar = SessaoSEI::getInstance()->verificarPermissao('gd_arquivamento_eliminacao_enviar');
    $strLinkProcedimentoEliminacaoEnviar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivamento_eliminacao_enviar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']);

    // Ação de envio para recolhimento
    $bolAcaoProcedimentoRecolhimentoEnviar = SessaoSEI::getInstance()->verificarPermissao('gd_arquivamento_recolhimento_enviar');
    $strLinkProcedimentoRecolhimentoEnviar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivamento_recolhimento_enviar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']);

    // Ação de devolver arquivamento
    $bolAcaoDevolverArquivamento = SessaoSEI::getInstance()->verificarPermissao('gd_arquivamento_devolver');

    // Busca os arquivamentos em fase intermediária para listagem
    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

    $objMdGdArquivamentoDTO->retStrDescricao();
    $objMdGdArquivamentoDTO->retStrSituacao();
    $objMdGdArquivamentoDTO->retNumIdArquivamento();
    $objMdGdArquivamentoDTO->retDthDataArquivamento();
    $objMdGdArquivamentoDTO->retDthDataGuardaIntermediaria();
    $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
    $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
    $objMdGdArquivamentoDTO->retStrNomeUsuario();
    $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
    $objMdGdArquivamentoDTO->retDblIdProcedimento();
    $objMdGdArquivamentoDTO->retStrStaDestinacaoFinal();
    $objMdGdArquivamentoDTO->setStrSinAtivo('S');
    $objMdGdArquivamentoDTO->setStrSituacao([MdGdArquivamentoRN::$ST_FASE_INTERMEDIARIA], InfraDTO::$OPER_IN);
    $objMdGdArquivamentoDTO->setNumIdUnidadeIntermediaria(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    
    $selUnidade = PaginaSEI::getInstance()->recuperarCampo('selUnidade');
    if ($selUnidade && $selUnidade !== 'null') {
        $objMdGdArquivamentoDTO->setNumIdUnidadeCorrente($selUnidade);
    }

    $selTipoProcedimento = PaginaSEI::getInstance()->recuperarCampo('selTipoProcedimento');
    if ($selTipoProcedimento && $selTipoProcedimento !== 'null') {
        $objMdGdArquivamentoDTO->setNumIdTipoProcedimento($selTipoProcedimento);
    }

    $txtPeriodoDe = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoDe');
    if ($txtPeriodoDe && !$txtPeriodoA) {
        $objMdGdArquivamentoDTO->setDthDataArquivamento($txtPeriodoDe, InfraDTO::$OPER_MAIOR_IGUAL);
    }

    $txtPeriodoA = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoA');
    if ($txtPeriodoA && !$txtPeriodoDe) {
        $objMdGdArquivamentoDTO->setDthDataArquivamento($txtPeriodoA, InfraDTO::$OPER_MENOR_IGUAL);
    }

    if($txtPeriodoDe && $txtPeriodoA) {
        $objMdGdArquivamentoDTO->adicionarCriterio(array('DataArquivamento','DataArquivamento'),
                    array(InfraDTO::$OPER_MAIOR_IGUAL,InfraDTO::$OPER_MENOR_IGUAL),
                    array($txtPeriodoDe, $txtPeriodoA),
                    array(InfraDTO::$OPER_LOGICO_AND));
    }

    // Faz a pesquisa por assunto caso o filtro tenha sido acionado
    $objRelProtoloAssuntoRN = new RelProtocoloAssuntoRN();
    $selAssunto = PaginaSEI::getInstance()->recuperarCampo('selAssunto');

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

    $selDestinacaoFinal = PaginaSEI::getInstance()->recuperarCampo('selDestinacaoFinal');
    if ($selDestinacaoFinal && $selDestinacaoFinal !== 'null') {
        $objMdGdArquivamentoDTO->setStrStaDestinacaoFinal($selDestinacaoFinal);
    }

    $selCondicionante = PaginaSEI::getInstance()->recuperarCampo('selCondicionante');
    if ($selCondicionante && $selCondicionante !== 'null') {
        $objMdGdArquivamentoDTO->setStrSinCondicionante($selCondicionante);
    }

    $txtAnoDestinacao = PaginaSEI::getInstance()->recuperarCampo('txtAnoDestinacao');
    if ($txtAnoDestinacao) {
        $objMdGdArquivamentoDTO->adicionarCriterio(array('DataGuardaIntermediaria','DataGuardaIntermediaria'),
                            array(InfraDTO::$OPER_MAIOR_IGUAL,InfraDTO::$OPER_MENOR_IGUAL),
                            array("01/01/".$txtAnoDestinacao." 00:00:00", "31/12/".$txtAnoDestinacao." 23:59:59"),
                            InfraDTO::$OPER_LOGICO_AND);
    }

    $objRelProtoloAssuntoRN = new RelProtocoloAssuntoRN();


    if($bolAcaoProcedimentoEliminacaoEnviar){
        $arrComandos[] = '<button type="button" accesskey="E" id="sbmEliminacao" value="Eliminar" class="infraButton" onclick="acaoEnviarEliminacaoMultiplo()"><span class="infraTeclaAtalho">E</span>nviar para eliminação</button>';
    }

    if($bolAcaoProcedimentoRecolhimentoEnviar){
        $arrComandos[] = '<button type="button" accesskey="R" id="sbmRecolhimento" value="Recolher" class="infraButton"  onclick="acaoEnviarRecolhimentoMultiplo()"><span class="infraTeclaAtalho">E</span>nviar para recolhimento</button>';
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

        $strSumarioTabela = 'Processos Arquivados';
        $strCaptionTabela = 'Processos Arquivados';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";

        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Unidade', 'DescricaoUnidadeCorrente', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Processo', 'ProtocoloFormatado', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Tipo', 'NomeTipoProcedimento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">Assunto</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="5%">Destinação Final</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Data de Arquivamento', 'DataArquivamento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Observações</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        for ($i = 0; $i < $numRegistros; $i++) {
            
            // Isola os assuntos do processo e concatena as observações
            $arrObjRelProtocoloAssuntoDTOProcedimento = $arrObjRelProtocoloAssuntoDTO[$arrObjMdGdArquivamentoDTO[$i]->getDblIdProcedimento()];
            $strAssuntosProcedimento = '';
            $strObservacoesAssuntos = '';

            foreach($arrObjRelProtocoloAssuntoDTOProcedimento as $k => $objRelProtocoloAssuntoDTO){
                $strAssuntosProcedimento .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto() .' - ' .$objRelProtocoloAssuntoDTO->getStrDescricaoAssunto();
                if($k + 1 != count($arrObjRelProtocoloAssuntoDTOProcedimento)){
                    $strAssuntosProcedimento .= ' <br><br> ';
                }

                if(empty($strObservacoesAssuntos)){
                    $strObservacoesAssuntos .= $arrAssuntosObservacoes[$objRelProtocoloAssuntoDTO->getNumIdAssunto()]->getStrObservacao();
                }else{
                    $strObservacoesAssuntos .= ' <br><br> '.$arrAssuntosObservacoes[$objRelProtocoloAssuntoDTO->getNumIdAssunto()]->getStrObservacao();
                }
            }

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            if (InfraData::compararDatas($arrObjMdGdArquivamentoDTO[$i]->getDthDataGuardaIntermediaria(), date('d/m/Y H:i:s')) >= 0) {
                $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento(), $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado()) . '</td>';
            }else{
                $strResultado .= '<td valign="top"></td>';
            }


            
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoUnidadeCorrente()) . '</td>';
            $strResultado .= '<td><a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjMdGdArquivamentoDTO[$i]->getDblIdProcedimento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '</a></td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
            $strResultado .= '<td>' . $strAssuntosProcedimento . '</td>';

            if ($arrObjMdGdArquivamentoDTO[$i]->getStrStaDestinacaoFinal() == MdGdArquivamentoRN::$DF_ELIMINACAO) {
                $strResultado .= '<td>Eliminação</td>';
            } else {
                $strResultado .= '<td>Recolhimento</td>';
            }
            $strResultado .= '<td>' . PaginaSEI::tratarHTML(substr($arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento(), 0, 10)) . '</td>';

            $strResultado .= '<td>' .$strObservacoesAssuntos . '</td>';
            $strAcoes = '<td align="center">';
            
            // Ações para fase intermediária
            if($arrObjMdGdArquivamentoDTO[$i]->getStrSituacao() == MdGdArquivamentoRN::$ST_FASE_INTERMEDIARIA){
                // Ação de envio para recolhimento / eliminação
                if (InfraData::compararDatas($arrObjMdGdArquivamentoDTO[$i]->getDthDataGuardaIntermediaria(), date('d/m/Y H:i:s')) >= 0) {
                    if ($bolAcaoProcedimentoEliminacaoEnviar && $arrObjMdGdArquivamentoDTO[$i]->getStrStaDestinacaoFinal() == MdGdArquivamentoRN::$DF_ELIMINACAO) {
                        $strAcoes .= '<a href="#ID-' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '" onclick="acaoEnviarEliminacao(\'' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '\', \'' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '\');"   tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/transicao.png" title="Gerar Listagem de Eliminação" title="Gerar Listagem de Eliminação" class="infraImg" /></a><br>';
                    }
                    
                    if ($bolAcaoProcedimentoRecolhimentoEnviar && $arrObjMdGdArquivamentoDTO[$i]->getStrStaDestinacaoFinal() == MdGdArquivamentoRN::$DF_RECOLHIMENTO){
                        $strAcoes .= '<a href="#ID-' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '" onclick="acaoEnviarRecolhimento(\'' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '\', \'' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/transicao.png" title="Gerar Listagem de Recolhimento" title="Gerar Listagem de Recolhimento" class="infraImg" /></a><br>';
                    }
                } 

                // Ação de edição de metadados
                if($bolAcaoDevolverArquivamento){
                    $strLinkDevolver = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivamento_devolver&acao_origem=' . $_GET['acao'] . '&id_arquivamento=' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento());
                    $strAcoes .= '<a href="#ID-' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '" onclick="acaoDevolver(\'' . $strLinkDevolver . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/transicao.png" title="Enviar para Correção" title="Enviar para Correção" class="infraImg" style="transform: scaleX(-1);" /></a>';
                }
            }
            
            $strAcoes .= '</td></tr>';
            $strResultado .= $strAcoes;

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
#selOrgao {position:absolute;left:0%;top:16%;width:20%;}

#lblUnidade {position:absolute;left:21%;top:0%;width:20%;}
#selUnidade {position:absolute;left:21%;top:16%;width:20%;}

#lblTiposProcedimento {position:absolute;left:42%;top:0%;width:20%;}
#selTipoProcedimento {position:absolute;left:42%;top:16%;width:20%;}

#lblDestinacaoFinal {position:absolute;left:63%;top:0%;width:20%;}
#selDestinacaoFinal {position:absolute;left:63%;top:16%;width:20%;}

#lblPeriodoDe {position:absolute;left:0%;top:33%;width:20%;}
#txtPeriodoDe {position:absolute;left:0%;top:48%;width:17%;}
#imgCalPeriodoD {position:absolute;left:18%;top:49%;}

#lblPeriodoA {position:absolute;left:21%;top:33%;width:20%;}
#txtPeriodoA {position:absolute;left:21%;top:48%;width:17%;}
#imgCalPeriodoA {position:absolute;left:39%;top:49%;}

#lblSelAssunto {position:absolute;left:42%;top:33%;width:20%;}
#selAssunto {position:absolute;left:42%;top:48%;width:41%;}

#lblAnoDestinacao {position:absolute;left:0%;top:66%;width:20%;}
#txtAnoDestinacao {position:absolute;left:0%;top:80%;width:20%;}

#lblCondicionante {position:absolute;left:21%;top:66%;width:20%;}
#selCondicionante {position:absolute;left:21%;top:80%;width:20%;}

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
            if (confirm("Confirma o envio do processo  \"" + protocolo_formatado + "\" para a eliminação?")) {
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
            if (document.getElementById('hdnInfraItensSelecionados').value == '') {
                alert('Nenhum processo selecionado.');
                return;
            }

            if (confirm("Confirma o envio dos processos selecionados para eliminação? Se um processo com destinação final diferente de eliminação foi selecionado ele será desconsiderado.")) {
                document.getElementById('hdnInfraItemId').value = '';
                document.getElementById('frmAvaliacaoProcessoLista').action = '<?= $strLinkProcedimentoEliminacaoEnviar ?>';
                document.getElementById('frmAvaliacaoProcessoLista').submit();
            }
        }
<? } ?>

<? if ($bolAcaoProcedimentoRecolhimentoEnviar) { ?>
        function acaoEnviarRecolhimentoMultiplo() {
            if (document.getElementById('hdnInfraItensSelecionados').value == '') {
                alert('Nenhum processo selecionado.');
                return;
            }

            if (confirm("Confirma o envio dos processos selecionados para o recolhimento? Se um processo com destinação final diferente de recolhimento foi selecionado ele será desconsiderado.")) {
                document.getElementById('hdnInfraItemId').value = '';
                document.getElementById('frmAvaliacaoProcessoLista').action = '<?= $strLinkProcedimentoRecolhimentoEnviar ?>';
                document.getElementById('frmAvaliacaoProcessoLista').submit();
            }
        }
<? } ?>

<? if ($bolAcaoDevolverArquivamento) { ?>
    function acaoDevolver(link){
        infraAbrirJanela(link, 'janelaAnotacao', 720, 300, 'location=0,status=1,resizable=1,scrollbars=1', false);
        if (confirm("Deseja devolver o processo para correção na unidade corrente?")) {
            document.getElementById('hdnInfraItemId').value = id_arquivamento;
            document.getElementById('frmAvaliacaoProcessoLista').action = '<?= $strLinkAcaoDevolverArquivamento ?>';
            document.getElementById('frmAvaliacaoProcessoLista').submit();
        }
    }
<? } ?>

<? if ($bolAcaoConcluirEdicaoArquivamento) { ?>
    function acaoConcluirEdicaoArquivamento(id_arquivamento){
        if (confirm("Confirma a conclusão da edição desse processo?")) {
            document.getElementById('hdnInfraItemId').value = id_arquivamento;
            document.getElementById('frmAvaliacaoProcessoLista').action = '<?= $strLinkAcaoConcluirEdicaoArquivamento ?>';
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
          PaginaSEI::getInstance()->abrirAreaDados('13em');
          ?>

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
        <?= $strItensSelTipoProcedimento ?>
    </select>

    <label id="lblDestinacaoFinal" for="selDestinacaoFinal" accesskey="" class="infraLabelOpcional">Destinação Final:</label>
    <select id="selDestinacaoFinal" name="selDestinacaoFinal" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= MdGdArquivamentoINT::montarSelectDestinacoesFinalArquivamento($selDestinacaoFinal); ?>
    </select>

    <label id="lblPeriodoDe" for="txtPeriodoDe" accesskey="" class="infraLabelOpcional">De:</label>
    <input type="text" id="txtPeriodoDe" value="<?= $txtPeriodoDe ?>" name="txtPeriodoDe" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoDe) ?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
    <img id="imgCalPeriodoD" title="Selecionar Data Inicial" alt="Selecionar Data Inicial" onkeypress="return infraMascaraData(this, event)" src="/infra_css/svg/calendario.svg" class="infraImg" onclick="infraCalendario('txtPeriodoDe', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    

    <label id="lblPeriodoA" for="txtPeriodoA" accesskey="" class="infraLabelOpcional">Até:</label>
    <input type="text" id="txtPeriodoA" value="<?= $txtPeriodoA ?>" name="txtPeriodoA" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoA) ?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
    <img id="imgCalPeriodoA" title="Selecionar Data Final" alt="Selecionar Data Final" src="/infra_css/svg/calendario.svg" class="infraImg" onclick="infraCalendario('txtPeriodoA', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    

    <label id="lblSelAssunto" for="selAssunto" accesskey="" class="infraLabelOpcional">Assunto:</label>
    <select id="selAssunto" name="selAssunto" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelAssunto; ?>
    </select>

    <label id="lblAnoDestinacao" for="txtAnoDestinacao" accesskey="" class="infraLabelOpcional">Ano de Destinação:</label>
    <input type="text" id="txtAnoDestinacao" value="<?= $txtAnoDestinacao ?>" name="txtAnoDestinacao" class="infraText" value="<?= PaginaSEI::tratarHTML($txtAnoDestinacao) ?>" onkeypress="return infraMascaraNumero(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" maxlength="4" />

    <label id="lblCondicionante" for="selCondicionante" accesskey="" class="infraLabelOpcional">Condicionante:</label>
    <select id="selCondicionante" name="selCondicionante" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <option value="null"></option>
        <option value="S">Sim</option>
        <option value="N">Não</option>
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