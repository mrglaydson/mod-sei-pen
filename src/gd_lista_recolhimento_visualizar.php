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

    $strTitulo = 'Visualizar Processos da Listagem de Recolhimento';

    switch ($_GET['acao']) {

        case 'gd_lista_recolhimento_visualizar':
            break;

        case 'gd_lista_recolhimento_pdf_gerar':
            $objMdGdListaRecolhimentoRN = new MdGdListaRecolhimentoRN();
            $objMdGdListaRecolhimentoRN->gerarPdfConectado($_GET['id_listagem_recolhimento']);
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $bolAcaoGerarPdf = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_pdf_gerar');
    $arrComandos = array();


    // Busca os processos daquela listagem de recolhimento implodindo seus id's
    $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
    $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($_GET['id_listagem_recolhimento']);
    $objMdGdListaRecolProcedimentoDTO->retDblIdProcedimento();

    $objMdGdListaRecolProcedimentoRN = new MdGdListaRecolProcedimentoRN();
    $arrObjMdGdListaRecolProcedimentoDTO = $objMdGdListaRecolProcedimentoRN->listar($objMdGdListaRecolProcedimentoDTO);

    $arrIdsRecolhimento = explode(',', InfraArray::implodeArrInfraDTO($arrObjMdGdListaRecolProcedimentoDTO, 'IdProcedimento'));

    // Busca todos os arquivamentos dos processos daquela listagem
    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

    $objMdGdArquivamentoDTO->retNumIdArquivamento();
    $objMdGdArquivamentoDTO->retDthDataArquivamento();
    $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
    $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
    $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
    $objMdGdArquivamentoDTO->retStrObservacaoRecolhimento();
    $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
    $objMdGdArquivamentoDTO->setStrSinAtivo('S');
    $objMdGdArquivamentoDTO->setDblIdProcedimento($arrIdsRecolhimento, InfraDTO::$OPER_IN);

    $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);
    $numRegistros = count($arrObjMdGdArquivamentoDTO);

    if ($bolAcaoGerarPdf && $numRegistros) {
        $strLinkGerarPdf = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_pdf_gerar&acao_origem=' . $_GET['acao'] . '&id_listagem_recolhimento=' . $_GET['id_listagem_recolhimento']);
        $arrComandos[] = '<button type="button" accesskey="P" id="btnGerarPdf" value="Gerar PDF" class="infraButton" onclick="gerarPdfMultiplo()"><span class="infraTeclaAtalho">G</span>erar PDF</button>';
    }

    if ($numRegistros) {
        $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }

    $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_listar&acao_origem=' . $_GET['acao']) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';


    if ($numRegistros > 0) {
        $strResultado = '';

        $strSumarioTabela = 'Processos';
        $strCaptionTabela = 'Processos';

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
            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdArquivamentoDTO[$i]->getDblIdProtocoloProcedimento(), $arrObjMdGdArquivamentoDTO[$i]->getDblIdProtocoloProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoUnidadeCorrente()) . '</td>';
            $strResultado .= '<td>' . $strCodigoClassificacao . '</td>';
            $strResultado .= '<td>' . $strDescritorCodigo . '</td>';
            $strResultado .= '<td><a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjMdGdArquivamentoDTO[$i]->getDblIdProtocoloProcedimento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '</a></td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML(substr($arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento(), 0, 10)) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrObservacaoRecolhimento()) . '</td>';
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

function gerarPdfMultiplo() {
  /*  if (document.getElementById('hdnInfraItensSelecionados').value == '') {
        alert('Nenhum processo selecionado.');
        return;
    }*/
    document.getElementById('frmPrepararListagemRecolhimento').action = '<?= $strLinkGerarPdf ?>';
    document.getElementById('frmPrepararListagemRecolhimento').submit();
}

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