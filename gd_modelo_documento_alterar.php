<?php
try {
    require_once dirname(__FILE__) . '/../../SEI.php';
    session_start();
    $strTitulo = "Modelos de Documentos";

    $objSessao = SessaoSEI::getInstance();
    $objPagina = PaginaSEI::getInstance();

    $objSessao->validarLink();

    $objSessao->validarPermissao('gestao_documental_modelo_documento_alterar');
    if (isset($_POST['selModeloDocumento']) && !empty($_POST['selModeloDocumento'])) {
        $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarModeloAlterar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
    }

    $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao']) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
    if ($_GET['acao'] != 'gd_modelo_documento_alterar') {

        throw new InfraException("Ao '" . $_GET['acao'] . "' no reconhecida.");
    }

    $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
    $objMdGdModeloDocumentoRN = new MdGdModeloDocumentoRN();

    if (isset($_POST['selModeloDocumento']) && !empty($_POST['selModeloDocumento'])) {
        $objMdGdModeloDocumentoDTO->setStrNome($_POST['selModeloDocumento']);
        $objMdGdModeloDocumentoDTO->retTodos();

        $objMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoRN->consultar($objMdGdModeloDocumentoDTO);
    }

    if (isset($_POST['hdnSalvamento']) && $_POST['hdnSalvamento'] == '1') {
        try {
            $objMdGdModeloDocumentoDTO->setStrValor($_POST['txaConteudo']);
            $objMdGdModeloDocumentoRN->alterar($objMdGdModeloDocumentoDTO);
        } catch (Exception $e) {
            PaginaSEI::getInstance()->processarExcecao($e);
        }
    }

    switch ($_POST['selModeloDocumento']) {
        case MdGdModeloDocumentoRN::MODELO_DESPACHO_ARQUIVAMENTO:
            $link = 'gd_ajuda_variaveis_modelo_arquivamento';
            break;
        case MdGdModeloDocumentoRN::MODELO_DESPACHO_DESARQUIVAMENTO:
            $link = 'gd_ajuda_variaveis_modelo_desarquivamento';
            break;
        case MdGdModeloDocumentoRN::MODELO_LISTAGEM_ELIMINACAO:
            $link = 'gd_ajuda_variaveis_modelo_listagem_eliminacao';
            break;
        case MdGdModeloDocumentoRN::MODELO_DOCUMENTO_ELIMINACAO:
            $link = 'gd_ajuda_variaveis_modelo_documento_eliminacao';
            break;
    }
    //Cria o editor que ir ser exibido na tela
    $objEditorRN = new EditorRN();
    $objEditorDTO = new EditorDTO();

    $objEditorDTO->setStrNomeCampo('txaConteudo');
    $objEditorDTO->setStrSinSomenteLeitura('N');
    $objEditorDTO->setStrSinLinkSei('S');
    $retEditor = $objEditorRN->montarSimples($objEditorDTO);
} catch (Exception $e) {

    $objPagina->processarExcecao($e);
}
$objPagina->montarDocType();
$objPagina->abrirHtml();
$objPagina->abrirHead();
$objPagina->montarMeta();
$objPagina->montarTitle($objPagina->getStrNomeSistema() . ' - ' . $strTitulo);
$objPagina->montarStyle();
$objPagina->montarJavaScript();
?>
<script type="text/javascript" charset="iso-8859-1">
    function exibirAjuda() {
        infraAbrirJanela('<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $link) ?>', 'janelaAjudaVariaveisModelo', 800, 600, 'location=0,status=1,resizable=1,scrollbars=1', false);
    }

    function OnSubmitForm() {
        $('#hdnSalvamento').val('1');
    }
</script>
<?php echo $retEditor->getStrInicializacao(); ?>
<?php $objPagina->fecharHead(); ?>
<?php $objPagina->abrirBody($strTitulo, 'onload="inicializar();"'); ?>
<form  method="post" onsubmit="return OnSubmitForm();">
    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
    <?php $objPagina->abrirAreaDados('100em'); ?>
    <br />

    <table style="width: 100%">
        <label id="lblModeloDocumento" for="selModeloDocumento" accesskey="" class="infraLabelObrigatorio">Tipo:</label>
        <select id="selModeloDocumento" name="selModeloDocumento" onchange="this.form.submit();"  class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value="<?= $_POST['selModeloDocumento'] ?>">
            <option value=""></option>
            <option value="<?= MdGdModeloDocumentoRN::MODELO_DESPACHO_ARQUIVAMENTO ?>" <?= $_POST['selModeloDocumento'] == MdGdModeloDocumentoRN::MODELO_DESPACHO_ARQUIVAMENTO ? 'selected' : '' ?>>
                Despacho de arquivamento
            </option>
            <option value="<?= MdGdModeloDocumentoRN::MODELO_DESPACHO_DESARQUIVAMENTO ?>" <?= $_POST['selModeloDocumento'] == MdGdModeloDocumentoRN::MODELO_DESPACHO_DESARQUIVAMENTO ? 'selected' : '' ?>>
                Despacho de desarquivamento
            </option>
            <option value="<?= MdGdModeloDocumentoRN::MODELO_LISTAGEM_ELIMINACAO ?>" <?= $_POST['selModeloDocumento'] == MdGdModeloDocumentoRN::MODELO_LISTAGEM_ELIMINACAO ? 'selected' : '' ?>>
                Listagem de eliminação
            </option>
            <option value="<?= MdGdModeloDocumentoRN::MODELO_DOCUMENTO_ELIMINACAO ?>" <?= $_POST['selModeloDocumento'] == MdGdModeloDocumentoRN::MODELO_DOCUMENTO_ELIMINACAO ? 'selected' : '' ?>>
                Documento de eliminação
            </option>
        </select><br>
        <? if (isset($_POST['selModeloDocumento']) && !empty($_POST['selModeloDocumento'])): ?>
            <td style="width: 95%">
                <div id="divEditores" style="overflow: auto;">
                    <textarea id="txaConteudo" name="txaConteudo" rows="10" class="infraTextarea" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"><?= $objMdGdModeloDocumentoDTO->getStrValor(); ?></textarea>
                    <script type="text/javascript">
    <?= $retEditor->getStrEditores(); ?>
                    </script>
                </div>
            </td>
            <td style="vertical-align: top;"> <a id="ancAjuda" onclick="exibirAjuda();" title="Ajuda" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"><img src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/ajuda.gif" class="infraImg"/></a>
            </td>
            <input type='hidden' id='hdnSalvamento' name='hdnSalvamento' value='0' />
        <? endif; ?>
    </table>
    <?php print $objPagina->fecharAreaDados(); ?>
</form>
<?php $objPagina->fecharBody(); ?>
<?php $objPagina->fecharHtml(); ?>



