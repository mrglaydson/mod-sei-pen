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
    PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);
    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    switch ($_GET['acao']) {

        case 'gd_listar_recolhimento_anotar':
            $strTitulo = 'Realizar Anotação';
            $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
            $objMdGdListaRecolhimentoDTO->setNumIdListaRecolhimento($_GET['id_lista_recolhimento']);
            $objMdGdListaRecolhimentoDTO->retStrAnotacao();
            
            $objMdGdListaRecolhimentoRN = new MdGdListaRecolhimentoRN();
            $objMdGdListaRecolhimentoDTO = $objMdGdListaRecolhimentoRN->consultar($objMdGdListaRecolhimentoDTO);

            $strAnotacao = '';
            if($objMdGdListaRecolhimentoDTO){
                $strAnotacao = $objMdGdListaRecolhimentoDTO->getStrAnotacao();
            }

            if ($_POST['sbmAnotar']) {
                $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
                $objMdGdListaRecolhimentoDTO->setNumIdListaRecolhimento($_GET['id_lista_recolhimento']);
                $objMdGdListaRecolhimentoDTO->setStrAnotacao($_POST['txaAnotacao']);
                $objMdGdListaRecolhimentoRN->alterar($objMdGdListaRecolhimentoDTO);
            }
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }
} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

$arrComandos[] = '<button type="submit" accesskey="S" name="sbmAnotar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
$arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="window.opener.location.reload(); window.close();" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';


PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->montarJavaScript(); 
PaginaSEI::getInstance()->abrirJavaScript(); ?>

<? if($_POST['sbmAnotar']){ ?>
    window.opener.location.reload(); 
    window.close();
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript(); 
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo);
?>
<form id="frmObservar" method="post" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . '&id_lista_recolhimento=' . $_GET['id_lista_recolhimento']) ?>">
    <?
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    PaginaSEI::getInstance()->abrirAreaDados('20em');
    ?>
    <textarea id="txaAnotacao" style="width: 657px;" rows="10" name="txaAnotacao" rows="<?= PaginaSEI::getInstance()->isBolNavegadorFirefox() ? '2' : '3' ?>" class="infraTextArea" onkeypress="return infraLimitarTexto(this, event, 500);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"><?= PaginaSEI::tratarHTML($strAnotacao) ?></textarea>
    <?
    PaginaSEI::getInstance()->fecharAreaDados();
    ?>
</form>

<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>