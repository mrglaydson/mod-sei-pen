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


    switch ($_GET['acao']) {

        case 'gd_lista_eliminacao_procedimento_remover':
            PaginaSEI::getInstance()->salvarCamposPost(array('hdnInfraItensSelecionados', 'hdnInfraItemId'));

            $strTitulo = 'Remover Processos da Listagem';
            
            if($_SERVER['REQUEST_METHOD'] == 'POST' && PaginaSEI::getInstance()->recuperarCampo('hdnInfraItensSelecionados')){

                $numIdListaEliminacao  = (int) PaginaSEI::getInstance()->recuperarCampo('hdnInfraItemId');
                $arrNumIdsArquivamento = explode(',', PaginaSEI::getInstance()->recuperarCampo('hdnInfraItensSelecionados'));
            

                // Obtem os processos do arquivamento
                $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                $objMdGdArquivamentoDTO->setNumIdArquivamento($arrNumIdsArquivamento, InfraDTO::$OPER_IN);
                $objMdGdArquivamentoDTO->retDblIdProcedimento();
                $objMdGdArquivamentoDTO->retNumIdArquivamento();

                $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
                $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

                // Adiciona os processos a listagem de eliminação
                $objMdGdListaElimProcedimentoRN = new MdGdListaElimProcedimentoRN();
                
                foreach($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO){
                    // Atualiza a situação do arquivamento
                    $objMdGdArquivamentoDTO->setNumIdListaEliminacao(null);
                    $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_PREPARACAO_ELIMINACAO);
                    $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);

                    // Excluí da listagem
                    $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
                    $objMdGdListaElimProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
                    $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($numIdListaEliminacao);
                    $objMdGdListaElimProcedimentoDTO->retNumIdListaEliminacao();
                    $objMdGdListaElimProcedimentoDTO->retDblIdProcedimento();

                    $objMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoRN->consultar($objMdGdListaElimProcedimentoDTO);                    
                    $objMdGdListaElimProcedimentoRN->excluir([$objMdGdListaElimProcedimentoDTO]);
                }

                // Atualiza o total de processos da listagem
                $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
                $objMdGdListaEliminacaoDTO->setNumIdListaEliminacao($numIdListaEliminacao);

                $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
                $objMdGdListaEliminacaoRN->atualizarNumeroProcessos($objMdGdListaEliminacaoDTO);

            }

            break;
        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $arrComandos = array();
    $arrComandos[] = '<button type="button" accesskey="P" id="btnExcluirListagem" value="Excluir da Listagem" onclick="acaoExcluirListagemEliminacao('.$_GET['id_listagem_eliminacao'].');" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir da Listagem de Eliminação</button>';
    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    $arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" id="btnFechar" value="Fechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_listar&acao_origem=' . $_GET['acao']) . '\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

    $strLinkExcluirListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_procedimento_remover&acao_origem=' . $_GET['acao'].'&id_listagem_eliminacao='.$_GET['id_listagem_eliminacao']);
    
    // Busca os processos daquela listagem de eliminacao implodindo seus id's
    $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
    $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($_GET['id_listagem_eliminacao']);
    $objMdGdListaElimProcedimentoDTO->retDblIdProcedimento();

    $objMdGdListaElimProcedimentoRN = new MdGdListaElimProcedimentoRN();
    $arrObjMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoRN->listar($objMdGdListaElimProcedimentoDTO);

    $arrIdsEliminacao = explode(',', InfraArray::implodeArrInfraDTO($arrObjMdGdListaElimProcedimentoDTO, 'IdProcedimento'));

    // Busca todos os arquivamentos dos processos daquela listagem
    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

    $objMdGdArquivamentoDTO->retNumIdArquivamento();
    $objMdGdArquivamentoDTO->retDthDataArquivamento();
    $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
    $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
    $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
    $objMdGdArquivamentoDTO->retStrObservacaoEliminacao();
    $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
    $objMdGdArquivamentoDTO->setStrSinAtivo('S');
    $objMdGdArquivamentoDTO->setDblIdProcedimento($arrIdsEliminacao, InfraDTO::$OPER_IN);

    $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);
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
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Observações e/ou Justificativas', 'ObservacaoEliminacao', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
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
                    $strCodigoClassificacao .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto() . "  <br><br>  ";
                    $strDescritorCodigo .= $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto() . "  <br><br>  ";
                }
            }

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;
            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento(), $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoUnidadeCorrente()) . '</td>';
            $strResultado .= '<td>' . $strCodigoClassificacao . '</td>';
            $strResultado .= '<td>' . $strDescritorCodigo . '</td>';
            $strResultado .= '<td><a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjMdGdArquivamentoDTO[$i]->getDblIdProtocoloProcedimento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '</a></td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrObservacaoEliminacao()) . '</td>';
            $strResultado .= '</tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

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
#selOrgao {position:absolute;left:0%;top:20%;width:20%;}
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


function acaoExcluirListagemEliminacao(id_listagem_eliminacao){
    if (document.getElementById('hdnInfraItensSelecionados').value == '') {
        alert('Nenhum Processo Selecionado.');
        return;
    }

    if(confirm('Confirma a retirada dos processos selecionados da listagem de eliminação?')){
        document.getElementById('hdnInfraItemId').value = id_listagem_eliminacao;
        document.getElementById('frmPrepararListagemEliminacao').action = '<?= $strLinkExcluirListagem ?>';
        document.getElementById('frmPrepararListagemEliminacao').submit();
    }
}

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