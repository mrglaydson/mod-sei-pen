<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 14/04/2008 - criado por mga
 *
 * Versão do Gerador de Código: 1.14.0
 *
 * Versão no CVS: $Id$
 */
try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    //////////////////////////////////////////////////////////////////////////////
    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(true);
    InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

    SessaoSEI::getInstance()->validarLink();
        PaginaSEI::getInstance()->prepararSelecao('gd_unidade_arquivamento_selecionar');  

    PaginaSEI::getInstance()->salvarCamposPost(array('txtSiglaUnidade', 'txtDescricaoUnidade'));


    switch ($_GET['acao']) {

        case 'gd_unidade_arquivamento_selecionar':
            $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Unidade', 'Selecionar Unidades');
            break;
        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $arrComandos = array();

    $arrComandos[] = '<input type="submit" id="btnPesquisar" value="Pesquisar" class="infraButton" />';

    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';

    $objMdGdUnidadeArquivamento = new MdGdUnidadeArquivamentoDTO();
    $objMdGdUnidadeArquivamento->retNumIdUnidadeOrigem();
    $objMdGdUnidadeArquivamento->retNumIdUnidadeArquivamento();

    $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
    $arrObjMdGdUnidadeArquivamento = $objMdGdUnidadeArquivamentoRN->listar($objMdGdUnidadeArquivamento);

    $arrIdUnidades = InfraArray::mapearArrInfraDTO($arrObjMdGdUnidadeArquivamento, 'IdUnidadeOrigem', 'IdUnidadeOrigem');

    $objUnidadeDTO = new UnidadeDTO();
    $objUnidadeDTO->retNumIdUnidade();
    $objUnidadeDTO->retStrSigla();
    $objUnidadeDTO->retStrDescricao();

    if ($arrIdUnidades) {
        $objUnidadeDTO->setNumIdUnidade($arrIdUnidades, InfraDTO::$OPER_NOT_IN);
    }

    $strSiglaPesquisa = PaginaSEI::getInstance()->recuperarCampo('txtSiglaUnidade');
    if ($strSiglaPesquisa !== '') {
        $objUnidadeDTO->setStrSigla($strSiglaPesquisa);
    }

    $strDescricaoPesquisa = PaginaSEI::getInstance()->recuperarCampo('txtDescricaoUnidade');
    if ($strDescricaoPesquisa !== '') {
        $objUnidadeDTO->setStrDescricao($strDescricaoPesquisa);
    }

    PaginaSEI::getInstance()->prepararOrdenacao($objUnidadeDTO, 'Sigla', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objUnidadeDTO);

    $objUnidadeRN = new UnidadeRN();
    $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);

    PaginaSEI::getInstance()->processarPaginacao($objUnidadeDTO);
    $numRegistros = count($arrObjUnidadeDTO);

    if ($numRegistros > 0) {

        $bolCheck = true;

        $strResultado = '';

        $strSumarioTabela = 'Tabela de Unidades.';
        $strCaptionTabela = 'Unidades';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        if ($bolCheck) {
            $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        }
        $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objUnidadeDTO, 'Sigla', 'Sigla', $arrObjUnidadeDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objUnidadeDTO, 'Descrição', 'Descricao', $arrObjUnidadeDTO) . '</th>' . "\n";

        //$strResultado .= '<th align="left" class="infraTh">Sigla</th>'."\n";
        $strResultado .= '<th class="infraTh">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';
        for ($i = 0; $i < $numRegistros; $i++) {

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            if ($bolCheck) {
                $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjUnidadeDTO[$i]->getNumIdUnidade(), UnidadeINT::formatarSiglaDescricao($arrObjUnidadeDTO[$i]->getStrSigla(), $arrObjUnidadeDTO[$i]->getStrDescricao())) . '</td>';
            }
            $strResultado .= '<td width="15%">' . $arrObjUnidadeDTO[$i]->getStrSigla() . '</td>';
            $strResultado .= '<td>' . $arrObjUnidadeDTO[$i]->getStrDescricao() . '</td>';
            $strResultado .= '<td align="center">';
            $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $arrObjUnidadeDTO[$i]->getNumIdUnidade());
            $strResultado .= '</td></tr>' . "\n";
        }
        $strResultado .= '</table>';
    }
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
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

#lblSiglaUnidade {position:absolute;left:0%;top:0%;width:20%;}
#txtSiglaUnidade {position:absolute;left:0%;top:40%;width:20%;}

#lblDescricaoUnidade {position:absolute;left:25%;top:0%;width:73%;}
#txtDescricaoUnidade {position:absolute;left:25%;top:40%;width:73%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
infraReceberSelecao();
document.getElementById('btnFecharSelecao').focus();

infraEfeitoTabelas();
}
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmUnidadeLista" method="post" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . '&id_orgao=' . $_GET['id_orgao']) ?>">
    <?
//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    PaginaSEI::getInstance()->abrirAreaDados('5em');
    ?>
    <label id="lblSiglaUnidade" for="txtSiglaUnidade" class="infraLabelOpcional">Sigla:</label>
    <input type="text" id="txtSiglaUnidade" name="txtSiglaUnidade" class="infraText" value="<?= $strSiglaPesquisa ?>" maxlength="15" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />

    <label id="lblDescricaoUnidade" for="txtDescricaoUnidade" class="infraLabelOpcional">Descrição:</label>
    <input type="text" id="txtDescricaoUnidade" name="txtDescricaoUnidade" class="infraText" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value="<?= $strDescricaoPesquisa ?>" />

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