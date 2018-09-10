<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 15/09/2008 - criado por marcio_db
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

    // SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
//PaginaSEI::getInstance()->salvarCamposPost(array('selCargoFuncao'));

    PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);


    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="S" name="sbmSalvar" value="Arquivar" class="infraButton"><span class="infraTeclaAtalho">A</span>rquivar</button>';

    $strItensSelOrgaos = OrgaoINT::montarSelectSiglaRI1358('null', '&nbsp;', SessaoSEI::getInstance()->getNumIdOrgaoUsuario());
    $strItensSelCargoFuncao = AssinanteINT::montarSelectCargoFuncaoUnidadeUsuarioRI1344('null', '&nbsp;', 'null', SessaoSEI::getInstance()->getNumIdUsuario());
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

#lblOrgao {position:absolute;left:0%;top:0%;}
#selOrgao {position:absolute;left:0%;top:40%;width:40%;}

#lblUsuario {position:absolute;left:0%;top:0%;}
#txtUsuario {position:absolute;left:0%;top:40%;width:60%;}

#divAutenticacao {<?= $strDisplayAutenticacao ?>}
#pwdSenha {width:15%;}

#lblCargoFuncao {position:absolute;left:0%;top:0%;}
#selCargoFuncao {position:absolute;left:0%;top:40%;width:99%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

//<script>

    var objAjaxContexto = null;
    var objAutoCompletarUsuario = null;
    var objAjaxCargoFuncao = null;
    var bolAssinandoSenha = false;

    function OnSubmitForm() {

        if (!infraSelectSelecionado(document.getElementById('selOrgao'))) {
            alert('Selecione um Órgão.');
            document.getElementById('selOrgao').focus();
            return false;
        }

        if (!infraSelectSelecionado(document.getElementById('selCargoFuncao'))) {
            alert('Selecione um Cargo/Função.');
            document.getElementById('selCargoFuncao').focus();
            return false;
        }

        if (infraTrim(document.getElementById('pwdSenha').value) == '') {
            alert('Senha não informada.');
            document.getElementById('pwdSenha').focus();
            return false;
        }
       
        document.getElementById('hdnIdProtocolos').value = window.opener.document.getElementById('hdnIdProtocolos').value;
        document.getElementById('selJustificativa').value = window.opener.document.getElementById('selJustificativa').value;
        alert('wefwe');
        window.close();
    }


//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo);
?>

<form id="frmAssinaturas" method="post" onsubmit="return OnSubmitForm();" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivar_procedimento&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&hash_documentos=' . $strHashDocumentos . $strParametros) ?>">


    <div id="divOrgao" class="infraAreaDados" style="height:4.5em;">
        <label id="lblOrgao" for="selOrgao" accesskey="r" class="infraLabelObrigatorio">Ó<span class="infraTeclaAtalho">r</span>gão do Assinante:</label>
        <select id="selOrgao" name="selOrgao" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $strItensSelOrgaos ?>
        </select>
    </div>

    <div id="divUsuario" class="infraAreaDados" style="height:4.5em;">
        <label id="lblUsuario" for="txtUsuario" accesskey="e" class="infraLabelObrigatorio">Assinant<span class="infraTeclaAtalho">e</span>:</label>
        <input type="text" id="txtUsuario" name="txtUsuario" class="infraText" value="<?= SessaoSEI::getInstance()->getStrNomeUsuario() ?>" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" disabled />
    </div>  

    <div id="divCargoFuncao" class="infraAreaDados" style="height:4.5em;">
        <label id="lblCargoFuncao" for="selCargoFuncao" accesskey="F" class="infraLabelObrigatorio">Cargo / <span class="infraTeclaAtalho">F</span>unção:</label>
        <select id="selCargoFuncao" name="selCargoFuncao" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $strItensSelCargoFuncao ?>
        </select>
    </div>
    <br />
    <div id="divAutenticacao" class="infraAreaDados" style="height:2.5em;">
        <label id="lblSenha" for="pwdSenha" accesskey="S" class="infraLabelRadio infraLabelObrigatorio" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"><span class="infraTeclaAtalho">S</span>enha</label>&nbsp;&nbsp;
        <input type="password" id="pwdSenha" name="pwdSenha" autocomplete="off" class="infraText"  value="" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
    </div>

    <input type="hidden" id="hdnOrigem" name="hdnOrigem" value="<?= $_GET['acao_origem']; ?>"/>
    <input type="hidden" id="hdnIdProtocolos" name="hdnIdProtocolos" />
    <input type="hidden" id="selJustificativa" name="selJustificativa" />

    <?
    //PaginaSEI::getInstance()->fecharAreaDados();
    PaginaSEI::getInstance()->montarAreaDebug();
    PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
    ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>