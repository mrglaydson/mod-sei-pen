<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 08/04/2011 - criado por mga
 *
 *
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
    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
    PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);


    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmEliminar" name="sbmEliminar" value="Eliminar" class="infraButton"><span class="infraTeclaAtalho">E</span>liminar Documentos Físicos</button>';

  switch ($_GET['acao']) {

    case 'gd_lista_eliminacao_documentos_fisicos_eliminar':
        $strTitulo = 'Confirmar Eliminação de Documentos Físicos';

      if (isset($_POST['pwdSenha'])) {

        try {

            $objInfraSip = new InfraSip(SessaoSEI::getInstance());
            $objAuthSip = $objInfraSip->autenticar(SessaoSEI::getInstance()->getNumIdOrgaoUsuario(), null, SessaoSEI::getInstance()->getStrSiglaUsuario(), $_POST['pwdSenha']);

          if ($objAuthSip) {
            $numIdListagemEliminacao = $_POST['hdnIdListagemEliminacao'];
            $arrIdsDocumentos = explode(',', $_POST['hdnIdsDocumentos']);

            $objMdGdDocumentoFisicoElimRN = new MdGdDocumentoFisicoElimRN();

            foreach ($arrIdsDocumentos as $dblIdDocumento) {
                  $objMdGdDocumentoFisicoElimDTO = new MdGdDocumentoFisicoElimDTO();
                  $objMdGdDocumentoFisicoElimDTO->setDblIdDocumento($dblIdDocumento);
                  $objMdGdDocumentoFisicoElimDTO->setNumIdListaEliminacao($numIdListagemEliminacao);

                  $objMdGdDocumentoFisicoElimRN->cadastrar($objMdGdDocumentoFisicoElimDTO);
            }
          }
        } catch (Exception $e) {
            PaginaSEI::getInstance()->processarExcecao($e, true);
        }
      }


        break;

    default:
        throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
  }
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

#lblUsuario {position:absolute;left:0%;top:0%;}
#txtUsuario {position:absolute;left:0%;top:20%;width:90%;}

#lblSenha {position:absolute;left:0%;top:50%;}
#pwdSenha {position:absolute;left:0%;top:70%;width:20%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script>
<? if ($_POST['sbmEliminar']) { ?>
        window.opener.location.reload();
        window.close();
<? } ?>

    function inicializar() {
        document.getElementById('pwdSenha').focus();
    }

    function OnSubmitForm() {
        document.getElementById('hdnIdsDocumentos').value = window.opener.document.getElementById('hdnInfraItensSelecionados').value;
        return true;
    }

    function tratarSenha(obj, ev) {
        if (infraGetCodigoTecla(ev) == 13) {
            if (infraTrim(obj.value) == '') {
                alert('Senha não informada.');
                return false;
            }
            if (OnSubmitForm()) {
                bolProcessando = true;
                document.getElementById('frmIdentificacaoAcesso').submit();
                return true;
            }
        }
        
    }


//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();" onunload="finalizar();"');
?>
<form id="frmIdentificacaoAcesso" method="post" onsubmit="return OnSubmitForm();" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . '&acao_destino=' . $_GET['acao_destino'] . $strParametros) ?>">

    <?
//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
    PaginaSEI::getInstance()->montarBarraComandosSuperior(array());
//PaginaSEI::getInstance()->montarAreaValidacao();
    PaginaSEI::getInstance()->abrirAreaDados('10em');
    ?>
    <label id="lblUsuario" for="txtUsuario" accesskey="" class="infraLabelObrigatorio">Usuário:</label>
    <input type="text" id="txtUsuario" name="txtUsuario" class="infraText infraReadOnly" readonly="readonly" value="<?= PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrNomeUsuario()) ?>" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />

    <label id="lblSenha" for="pwdSenha" accesskey="" class="infraLabelObrigatorio">Senha:</label>
    <input type="password" id="pwdSenha" name="pwdSenha" class="infraText" onkeypress="return tratarSenha(this, event);" value="" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
    <input type="hidden" name="hdnIdListagemEliminacao" id="hdnIdListagemEliminacao" value="<?= $_GET['id_listagem_eliminacao'] ?>" />
    <input type="hidden" name="hdnIdsDocumentos" id="hdnIdsDocumentos" value="" />
    <?
    PaginaSEI::getInstance()->fecharAreaDados();
    PaginaSEI::getInstance()->montarAreaDebug();
    PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
    ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>