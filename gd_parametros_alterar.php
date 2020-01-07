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
                        $objMdGdParametroDTO->setStrValor($_POST['sel' . $objMdGdParametroDTO->getStrNome()]);
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
    $strSelSerieArquivamento = MdGdArquivamentoINT::montarSelectsSerieNomeGerados('null', '&nbsp;', $arrObjMdGdParametroDTO['DESPACHO_ARQUIVAMENTO']->getStrValor());
    $strSelSerieDesarquivamento = MdGdArquivamentoINT::montarSelectsSerieNomeGerados('null', '&nbsp;', $arrObjMdGdParametroDTO['DESPACHO_DESARQUIVAMENTO']->getStrValor());
    $strSelTipoProcedimentoListagemEliminacao = TipoProcedimentoINT::montarSelectNome('null', '&nbsp;', $arrObjMdGdParametroDTO['TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO']->getStrValor());
    $strSelSerieListagemEliminacao = MdGdArquivamentoINT::montarSelectsSerieNomeGerados('null', '&nbsp;', $arrObjMdGdParametroDTO['TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO']->getStrValor());
   
    $strSelTipoProcedimetoEliminacao =TipoProcedimentoINT::montarSelectNome('null', '&nbsp;', $arrObjMdGdParametroDTO['TIPO_PROCEDIMENTO_ELIMINACAO']->getStrValor());  
    $strSelSerieDocumentoEliminacao = MdGdArquivamentoINT::montarSelectsSerieNomeGerados('null', '&nbsp;', $arrObjMdGdParametroDTO['TIPO_DOCUMENTO_ELIMINACAO']->getStrValor());

    $strNomeDespachoArquivamento = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_DESPACHO_ARQUIVAMENTO]->getStrNome();
    $strNomeDespachoDesarquivamento = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_DESPACHO_DESARQUIVAMENTO]->getStrNome();
    $strNomeTipoProcedimentoListagemEliminacao = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO]->getStrNome();
    $strNomeTipoDocumentoListagemEliminacao = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO]->getStrNome();
    $strNomeTipoProcedimentoEliminacao = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_TIPO_PROCEDIMENTO_ELIMINACAO]->getStrNome();
    $strNomeTipoDocumentoEliminacao = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_TIPO_DOCUMENTO_ELIMINACAO]->getStrNome();

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

#lbl<?= $strNomeDespachoArquivamento; ?> {position:absolute;left:0%;top:0%;width:50%;}
#sel<?= $strNomeDespachoArquivamento; ?> {position:absolute;left:0%;top:6%;width:50%;}

#lbl<?= $strNomeDespachoDesarquivamento; ?> {position:absolute;left:0%;top:14%;width:20%;}
#sel<?= $strNomeDespachoDesarquivamento; ?> {position:absolute;left:0%;top:20%;width:20%;}

#lbl<?= $strNomeTipoProcedimentoListagemEliminacao; ?> {position:absolute;left:0%;top:28%;width:30%;}
#sel<?= $strNomeTipoProcedimentoListagemEliminacao; ?> {position:absolute;left:0%;top:34%;width:40%;}

#lbl<?= $strNomeTipoDocumentoListagemEliminacao; ?> {position:absolute;left:0%;top:43%;width:30%;}
#sel<?= $strNomeTipoDocumentoListagemEliminacao; ?> {position:absolute;left:0%;top:49%;width:40%;}

#lbl<?= $strNomeTipoProcedimentoEliminacao; ?> {position:absolute;left:0%;top:56%;width:30%;}
#sel<?= $strNomeTipoProcedimentoEliminacao; ?> {position:absolute;left:0%;top:62%;width:40%;}

#lbl<?= $strNomeTipoDocumentoEliminacao; ?> {position:absolute;left:0%;top:69%;width:30%;}
#sel<?= $strNomeTipoDocumentoEliminacao; ?> {position:absolute;left:0%;top:75%;width:40%;}
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

if (infraTrim(document.getElementById('sel<?= $strNomeTipoProcedimentoListagemEliminacao; ?>').value)=='') {
alert('Informe o tipo de processo da listagem de eliminação.');
document.getElementById('sel<?= $strNomeTipoProcedimentoListagemEliminacao; ?>').focus();
return false;
}

if (infraTrim(document.getElementById('sel<?= $strNomeTipoDocumentoListagemEliminacao; ?>').value)=='') {
alert('Informe o tipo de documento da listagem de eliminação.');
document.getElementById('sel<?= $strNomeTipoDocumentoListagemEliminacao; ?>').focus();
return false;
}

if (infraTrim(document.getElementById('sel<?= $strNomeTipoProcedimentoEliminacao; ?>').value)=='') {
alert('Informe o tipo de processo da eliminação.');
document.getElementById('sel<?= $strNomeTipoProcedimentoEliminacao; ?>').focus();
return false;
}

if (infraTrim(document.getElementById('sel<?= $strNomeTipoDocumentoEliminacao; ?>').value)=='') {
alert('Informe o tipo de documento da eliminação.');
document.getElementById('sel<?= $strNomeTipoDocumentoEliminacao; ?>').focus();
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

    <label id="lbl<?= $strNomeTipoProcedimentoListagemEliminacao ?>" for="sel<?= $strNomeTipoProcedimentoListagemEliminacao ?>" accesskey="p"
           class="infraLabelObrigatorio"><span
            class="infraTeclaAtalho">T</span>ipo de processo da listagem de eliminação:</label>
    <select name="sel<?= $strNomeTipoProcedimentoListagemEliminacao ?>" id="sel<?= $strNomeTipoProcedimentoListagemEliminacao ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelTipoProcedimentoListagemEliminacao; ?>
    </select>

    <label id="lbl<?= $strNomeTipoDocumentoListagemEliminacao ?>" for="sel<?= $strNomeTipoDocumentoListagemEliminacao ?>" accesskey="p"
           class="infraLabelObrigatorio"><span
            class="infraTeclaAtalho">T</span>ipo de documento da listagem de eliminação:</label>
    <select name="sel<?= $strNomeTipoDocumentoListagemEliminacao ?>" id="sel<?= $strNomeTipoDocumentoListagemEliminacao ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelSerieListagemEliminacao; ?>
    </select>


    <label id="lbl<?= $strNomeTipoProcedimentoEliminacao ?>" for="sel<?= $strNomeTipoProcedimentoEliminacao ?>" accesskey="p"
           class="infraLabelObrigatorio"><span
            class="infraTeclaAtalho">T</span>ipo de processo da eliminação:</label>
    <select name="sel<?= $strNomeTipoProcedimentoEliminacao ?>" id="sel<?= $strNomeTipoProcedimentoEliminacao ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelTipoProcedimetoEliminacao; ?>
    </select>


    <label id="lbl<?= $strNomeTipoDocumentoEliminacao ?>" for="sel<?= $strNomeTipoDocumentoEliminacao ?>" accesskey="p"
           class="infraLabelObrigatorio"><span
            class="infraTeclaAtalho">T</span>ipo de documento da eliminação:</label>
    <select name="sel<?= $strNomeTipoDocumentoEliminacao ?>" id="sel<?= $strNomeTipoDocumentoEliminacao ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelSerieDocumentoEliminacao; ?>
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