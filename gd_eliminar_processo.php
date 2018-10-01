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
    SessaoSEI::getInstance()->validarPermissao('gestao_documental_eliminacao');
    PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmEliminar" name="sbmEliminar" value="Eliminar" class="infraButton"><span class="infraTeclaAtalho">E</span>liminar</button>';

    switch ($_GET['acao']) {

        case 'gd_eliminacao':

            $strTitulo = 'Confirmar Eliminação de Processos';

            if (isset($_POST['sbmEliminar'])) {
                // Registra a eliminação
                $objMdGdEliminacaoDTO = new MdGdEliminacaoDTO();
                $objMdGdEliminacaoDTO->setNumIdListaEliminacao($_POST['hdnIdListagemEliminacao']);
                $objMdGdEliminacaoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                $objMdGdEliminacaoDTO->setStrAssinante($_POST['selCargoFuncao']);
                $objMdGdEliminacaoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $objMdGdEliminacaoDTO->setNumIdSecaoImprensaNacional($_POST['selSecaoImprensaNacional']);
                $objMdGdEliminacaoDTO->setNumIdVeiculoPublicacao($_POST['selVeiculoPublicacao']);
                $objMdGdEliminacaoDTO->setDthDataImprensa($_POST['txtData']);
                $objMdGdEliminacaoDTO->setDthDataEliminacao(date('d/m/Y'));

                $objMdGdEliminacaoRN = new MdGdEliminacaoRN();
                $objMdGdEliminacaoRN->cadastrar($objMdGdEliminacaoDTO);
            }

            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }
    $strItensSelCargoFuncao = AssinanteINT::montarSelectCargoFuncaoUnidadeUsuarioRI1344('null', '&nbsp;', 'null', SessaoSEI::getInstance()->getNumIdUsuario());
    $selVeiculoPublicacao = VeiculoPublicacaoINT::montarSelectNome('null', '&nbsp;', '');
    $selSecaoImprensaNacional = SecaoImprensaNacionalINT::montarSelectNome('null', '&nbsp;', '');
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

#lblOrgao {position:absolute;left:3%;top:16%;}
#txtOrgao {position:absolute;left:3%;top:20%;width:50%;}

#lblUsuario {position:absolute;left:3%;top:25%;}
#txtUsuario {position:absolute;left:3%;top:29%;width:50%;}

#lblSelCargoFuncao {position:absolute;left:3%;top:34%;}
#selCargoFuncao {position:absolute;left:3%;top:38%;width:50%;}

#fieldsetDadosImprensaNacional {position:absolute;left:3%;top:45%;width: 90%; height: 13%;}

#lblSelVeiculoPublicacao {position:absolute;left:3%;top:28%;}
#selVeiculoPublicacao {position:absolute;left:3%;top:52%;width:25%;}

#lblSelSecaoImprensaNacional {position:absolute;left:32%;top:28%;}
#selSecaoImprensaNacional {position:absolute;left:32%;top:52%;width: 15%;}

#lblPagina {position:absolute;left:50%;top:28%;}
#txtPagina {position:absolute;left:50%;top:52%;width: 15%;}

#lblData {position:absolute;left:69%;top:28%;}
#txtData {position:absolute;left:69%;top:52%;width: 15%;}
#imgData {position:absolute;left:85%;top:52%;width:3%;height:22%;}

#lblSenha {position:absolute;left:3%;top:62%;}
#pwdSenha {position:absolute;left:3%;top:65%;width: 25%;}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script>
<? if ($_POST['sbmEliminar']) { ?>
        window.opener.location.href = '<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_gestao_listagem_eliminacao&acao_origem=' . $_GET['acao']); ?>';
        window.close();
<? } ?>

    function inicializar() {
        document.getElementById('pwdSenha').focus();
    }

    function OnSubmitForm() {
        if (document.getElementById('selCargoFuncao').value == 'null') {
            alert('Informe o cargo e função');
            return false;
        }

        if (document.getElementById('selVeiculoPublicacao').value == 'null') {
            alert('Informe o veículo de publicação');
            return false;
        }

        if (document.getElementById('selSecaoImprensaNacional').value == 'null') {
            alert('Informe a imprensa nacional');
            return false;
        }

        if (document.getElementById('txtPagina').value == '') {
            alert('Informe a página');
            return false;
        }

        if (document.getElementById('txtData').value == '') {
            alert('Informe a data');
            return false;
        }

        if (OnSubmitForm()) {
            bolProcessando = true;
            document.getElementById('frmIdentificacaoAcesso').submit();
            return true;
        }
        return true;
    }

    function tratarSenha(obj, ev) {
        if (infraGetCodigoTecla(ev) == 13) {
            if (infraTrim(obj.value) == '') {
                alert('Senha não informada.');
                return false;
            }


        }
    }


//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();" onunload="finalizar();"');
?>

<form id="frmConfirmarEliminacao"  style="height:4.5em;" method="post" onsubmit="return OnSubmitForm();" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&hash_documentos=' . $strHashDocumentos . $strParametros) ?>">

    <?
//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
    ?>


    <label id="lblOrgao" for="selOrgao" accesskey="" class="infraLabelObrigatorio">Órgão:</label>
    <input type="text" id="txtOrgao" name="txtOrgao" class="infraText" value="<?= SessaoSEI::getInstance()->getStrSiglaOrgaoUsuario(); ?>" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" disabled/>

    <label id="lblUsuario" for="txtUsuario" accesskey="A" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">U</span>suário:</label>
    <input type="text" id="txtUsuario" name="txtUsuario" class="infraText" value="<?= SessaoSEI::getInstance()->getStrNomeUsuario(); ?>" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" disabled/>

    <label id="lblSelCargoFuncao" for="selCargoFuncao" accesskey="F" class="infraLabelObrigatorio">Cargo / <span class="infraTeclaAtalho">F</span>unção:</label>
    <select id="selCargoFuncao" name="selCargoFuncao" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
        <?= $strItensSelCargoFuncao ?>
    </select>
    <br />

    <fieldset class="infraFieldset" id="fieldsetDadosImprensaNacional">
        <legend class="infraLegend">Imprensa Nacional</legend>

        <label id="lblSelVeiculoPublicacao" for="selVeiculoPublicacao" accesskey="F" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">V</span>eículo:</label>
        <select id="selVeiculoPublicacao" name="selVeiculoPublicacao" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $selVeiculoPublicacao ?>
        </select>

        <label id="lblSelSecaoImprensaNacional" for="selSecaoImprensaNacional" accesskey="F" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">S</span>eção:</label>
        <select id="selSecaoImprensaNacional" name="selSecaoImprensaNacional" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $selSecaoImprensaNacional ?>
        </select>

        <label id="lblPagina" for="txtPagina" accesskey="A" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">P</span>ágina:</label>
        <input type="text" id="txtPagina" name="txtPagina" class="infraText" value="" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

        <label id="lblData" for="txtData" accesskey="A" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">D</span>ata:</label>
        <input type="text" id="txtData" name="txtData" class="infraText" value="" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" onkeypress="return infraMascaraData(this, event)" />
        <img id="imgData" title="Selecionar Data" alt="Selecionar Data" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/calendario.gif" class="infraImg" onclick="infraCalendario('txtData', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    

    </fieldset>

    <label id="lblSenha" for="pwdSenha" accesskey="" class="infraLabelObrigatorio">Senha:</label>
    <input type="password" id="pwdSenha" name="pwdSenha" class="infraText" onkeypress="return tratarSenha(this, event);" value="" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />

    <?
//PaginaSEI::getInstance()->fecharAreaDados();
    PaginaSEI::getInstance()->montarAreaDebug();
//PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
    ?>
    <input type="hidden" id="hdnIdListagemEliminacao" name="hdnIdListagemEliminacao" value="<?= $_GET['id_listagem_eliminacao']; ?>" />
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>