<?

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    //////////////////////////////////////////////////////////////////////////////
    //InfraDebug::getInstance()->setBolLigado(false);
    //InfraDebug::getInstance()->setBolDebugInfra(true);
    //InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

    SessaoSEI::getInstance()->validarLink();

    $objMdGdParametroDTO = new MdGdParametroDTO();
    $objMdGdParametroDTO->retTodos();

    $objMdGdParametroRN = new MdGdParametroRN();
    $arrObjMdGdParametroDTO = InfraArray::indexarArrInfraDTO($objMdGdParametroRN->listar($objMdGdParametroDTO), 'Nome');
    $arrComandos = array();

    switch ($_GET['acao']) {

        // Ação de alteração
        case 'gd_parametros_alterar':

            // Valida a permissão da ação de alteração
            SessaoSEI::getInstance()->validarPermissao('gestao_documental_justificativas_alterar');

            // Cria os botões de salvar e cancelar
            $arrComandos[] = '<button type="submit" accesskey="S" id="sbmSalvarParametro" name="sbmSalvarParametro" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" name="btnFechar" value="Fechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao']) . '\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

            $strTitulo = 'Configurações da Gestão Documental';


            if (isset($_POST['sbmSalvarParametro'])) {
                try {

                    foreach ($arrObjMdGdParametroDTO as $objMdGdParametroDTO) {
                        $objMdGdParametroDTO->setStrValor($_POST['sel'.$objMdGdParametroDTO->getStrNome()]);
                        $objMdGdParametroRN->alterar($objMdGdParametroDTO);
                    }

                    PaginaSEI::getInstance()->adicionarMensagem('Parâmetros alterados com sucesso!');
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;
        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    // Busca os valores dos selects
    $strSelSerieArquivamento = SerieINT::montarSelectNomeGerados('', '&nbsp;', $arrObjMdGdParametroDTO['DESPACHO_ARQUIVAMENTO']->getStrValor());
    $strSelSerieDesarquivamento = SerieINT::montarSelectNomeGerados('', '&nbsp;', $arrObjMdGdParametroDTO['DESPACHO_DESARQUIVAMENTO']->getStrValor());
    $strSelUnidadeArquivamento = UnidadeINT::montarSelectSiglaDescricao('', '', $arrObjMdGdParametroDTO['UNIDADE_ARQUIVAMENTO']->getStrValor());

    $strNomeUnidadeArquivamento = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_UNIDADE_ARQUIVAMENTO]->getStrNome();
    $strNomeDespachoArquivamento = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_DESPACHO_ARQUIVAMENTO]->getStrNome();
    $strNomeDespachoDesarquivamento = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_DESPACHO_DESARQUIVAMENTO]->getStrNome();

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

    #lbl<?= $strNomeUnidadeArquivamento; ?> {position:absolute;left:0%;top:0%;width:50%;}
    #sel<?= $strNomeUnidadeArquivamento; ?> {position:absolute;left:0%;top:6%;width:50%;}

    #lbl<?= $strNomeDespachoArquivamento; ?> {position:absolute;left:0%;top:14%;width:20%;}
    #sel<?= $strNomeDespachoArquivamento; ?> {position:absolute;left:0%;top:20%;width:20%;}

    #lbl<?= $strNomeDespachoDesarquivamento; ?> {position:absolute;left:0%;top:28%;width:30%;}
    #sel<?= $strNomeDespachoDesarquivamento; ?> {position:absolute;left:0%;top:34%;width:40%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
    function inicializar(){
    document.getElementById('sbmSalvarParametro').focus();
    infraEfeitoTabelas();
    }

    function validarCadastro() {
    if (infraTrim(document.getElementById('sel<?= $strNomeUnidadeArquivamento; ?>').value)=='') {
    alert('Informe a Unidade de Arquivamento.');
    document.getElementById('sel<?= $strNomeUnidadeArquivamento; ?>').focus();
    return false;
    }

    if (infraTrim(document.getElementById('sel<?= $strNomeDespachoArquivamento; ?>').value)=='') {
    alert('Informe o Despacho de Arquivamento.');
    document.getElementById('sel<?= $strNomeDespachoArquivamento; ?>').focus();
    return false;
    }

    if (infraTrim(document.getElementById('sel<?= $strNomeDespachoDesarquivamento; ?>').value)=='') {
    alert('Informe o Despacho de Desarquivamento.');
    document.getElementById('sel<?= $strNomeDespachoDesarquivamento; ?>').focus();
    return false;
    }

    return true;
    }

    function OnSubmitForm() {
    return validarCadastro();
    }

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <form id="frmParametros" method="post" onsubmit="return OnSubmitForm();"
          action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
        <?
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        //PaginaSEI::getInstance()->montarAreaValidacao();
        PaginaSEI::getInstance()->abrirAreaDados('30em');
        ?>

        <label id="lbl<?= $strNomeUnidadeArquivamento ?>" for="sel<?= $strNomeUnidadeArquivamento ?>" accesskey="p"
               class="infraLabelObrigatorio"><span
                    class="infraTeclaAtalho">U</span>nidades de Arquivamento:</label>
        <select name="sel<?= $strNomeUnidadeArquivamento ?>" id="sel<?= $strNomeUnidadeArquivamento ?>"
                class="infraSelect"
                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $strSelUnidadeArquivamento; ?>
        </select>

        <label id="lbl<?= $strNomeDespachoArquivamento ?>" for="sel<?= $strNomeDespachoArquivamento ?>" accesskey="p"
               class="infraLabelObrigatorio"><span class="infraTeclaAtalho">D</span>espacho de Arquivamento:</label>
        <select name="sel<?= $strNomeDespachoArquivamento ?>" id="sel<?= $strNomeDespachoArquivamento ?>"
                class="infraSelect"
                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $strSelSerieArquivamento; ?>
        </select>

        <label id="lbl<?= $strNomeDespachoDesarquivamento ?>" for="sel<?= $strNomeDespachoDesarquivamento ?>"
               accesskey="p"
               class="infraLabelObrigatorio"><span class="infraTeclaAtalho">D</span>espacho de Desarquivamento:</label>
        <select name="sel<?= $strNomeDespachoDesarquivamento ?>" id="sel<?= $strNomeDespachoDesarquivamento ?>"
                class="infraSelect"
                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $strSelSerieDesarquivamento; ?>
        </select>

        <?
        PaginaSEI::getInstance()->fecharAreaDados();
        //PaginaSEI::getInstance()->montarAreaDebug();
        //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
        ?>
    </form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>