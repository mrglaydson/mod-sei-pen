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

    case 'gd_arquivamento_devolver':
        $strTitulo = 'Devolver para Correção';

      if($_POST['sbmDevolver']){

        if (trim($_REQUEST['txtObservacaoDevolucao']) == ""){
            $objInfraException = new InfraException();
            $objInfraException->lancarValidacao('Informe o motivo da devolução.');
        }

        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
        $objMdGdArquivamentoDTO->setNumIdArquivamento((int) $_REQUEST['id_arquivamento']);
        $objMdGdArquivamentoDTO->setStrObservacaoDevolucao($_REQUEST['txtObservacaoDevolucao']);
        $objMdGdArquivamentoDTO->retDblIdProcedimento();
        $objMdGdArquivamentoDTO->retNumIdUnidadeCorrente();
                
        // Muda a situação do arquivamento para editado
        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        $dlbIdProtocolo = $objMdGdArquivamentoRN->devolverArquivamento($objMdGdArquivamentoDTO);

      }
        break;

    default:
        throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
  }
} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

$arrComandos[] = '<button type="submit" accesskey="D" name="sbmDevolver" id="sbmDevolver" value="Devolver" onclick="return acaoDevolver();" class="infraButton"><span class="infraTeclaAtalho">D</span>evolver</button>';
$arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="window.opener.location.reload(); window.close();" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';


PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->montarJavaScript(); 
PaginaSEI::getInstance()->abrirJavaScript(); ?>

<? if($_POST['sbmDevolver']){ ?>
    window.opener.location.reload(); 
    window.close();
<? } ?>

    function acaoDevolver(){
        if (document.getElementById('txtObservacaoDevolucao').value.trim() == '') {
            alert('Informe o motivo da devolução.');
            document.getElementById('txtObservacaoDevolucao').focus();
            return false;
        }

        if (confirm("Confirma a devolução do processo para correção?")) {
            return true;
        }else{
            return false;
        }
    }

<?
PaginaSEI::getInstance()->fecharJavaScript(); 
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo);
PaginaSEI::getInstance()->abrirStyle();
?>

#lblMotivoDevolucao {position:absolute;top:0%;width:30%;}
#txtObservacaoDevolucao {position:absolute;top:12%;width: 657px;}

<?
PaginaSEI::getInstance()->fecharStyle();
?>
<form id="frmDevolver" method="post" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao_origem'] . '&id_arquivamento=' . $_GET['id_arquivamento']) ?>">
    <?
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    PaginaSEI::getInstance()->abrirAreaDados('20em');
    ?>
    <label id="lblMotivoDevolucao" accesskey="" class="infraLabelOpcional">Motivo da Devolução:</label>
    <textarea id="txtObservacaoDevolucao" rows="10" name="txtObservacaoDevolucao" rows="<?= PaginaSEI::getInstance()->isBolNavegadorFirefox() ? '2' : '3' ?>" class="infraTextArea" onkeypress="return infraLimitarTexto(this, event, 500);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"></textarea>
    <?
    PaginaSEI::getInstance()->fecharAreaDados();
    ?>
</form>

<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>