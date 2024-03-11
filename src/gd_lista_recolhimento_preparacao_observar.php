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

    case 'gd_lista_recolhimento_preparacao_observar':
        $strTitulo = 'Observações e/ou Justificativas';
        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
        $objMdGdArquivamentoDTO->setNumIdArquivamento($_GET['id_arquivamento']);
        $objMdGdArquivamentoDTO->retStrObservacaoRecolhimento();
            
        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        $objMdGdArquivamentoDTO = $objMdGdArquivamentoRN->consultar($objMdGdArquivamentoDTO);
            
      if ($_POST['sbmObservar']) {
        $objMdGdArquivamentoDTO->setNumIdArquivamento($_GET['id_arquivamento']);
        $objMdGdArquivamentoDTO->setStrObservacaoRecolhimento($_POST['txaObservacao']);
        $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
      }
        break;

    default:
        throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
  }
} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

$arrComandos[] = '<button type="submit" accesskey="S" name="sbmObservar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
$arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="window.opener.location.reload(); window.close();" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';


PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->montarJavaScript(); 
PaginaSEI::getInstance()->abrirJavaScript(); ?>

<? if($_POST['sbmObservar']){ ?>
    window.opener.location.reload(); 
    window.close();
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript(); 
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo);
?>
<form id="frmObservar" method="post" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . '&id_arquivamento=' . $_GET['id_arquivamento']) ?>">
    <?
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    PaginaSEI::getInstance()->abrirAreaDados('20em');
    ?>
    <textarea id="txaObservacao" style="width: 657px;" rows="10" name="txaObservacao" rows="<?= PaginaSEI::getInstance()->isBolNavegadorFirefox() ? '2' : '3' ?>" class="infraTextArea" onkeypress="return infraLimitarTexto(this, event, 500);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"><?= PaginaSEI::tratarHTML($objMdGdArquivamentoDTO->getStrObservacaoRecolhimento()) ?></textarea>
    <?
    PaginaSEI::getInstance()->fecharAreaDados();
    ?>
</form>

<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>