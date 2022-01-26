<?
try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    //////////////////////////////////////////////////////////////////////////////
    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(false);
    InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

    SessaoSEI::getInstance()->validarLink();
    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    $objMdGdParametroDTO = new MdGdParametroDTO();
    $objMdGdParametroDTO->retTodos();

    $objMdGdParametroRN = new MdGdParametroRN();
    $arrObjMdGdParametroDTO = InfraArray::indexarArrInfraDTO($objMdGdParametroRN->listar($objMdGdParametroDTO), 'Nome');
    $arrComandos = array();

    switch ($_GET['acao']) {

        // Ação de alteração
        case 'gd_parametro_alterar':
            
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
    
    // Cria os botões de salvar e cancelar
    $arrComandos[] = '<button type="submit" accesskey="S" id="sbmSalvarParametro" name="sbmSalvarParametro" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" name="btnFechar" value="Fechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao']) . '\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

    // Busca os valores dos selects
    $strSelSerieArquivamento = MdGdArquivamentoINT::montarSelectsSerieNomeGerados('null', '&nbsp;', $arrObjMdGdParametroDTO['DESPACHO_ARQUIVAMENTO']->getStrValor());
    $strSelSerieDesarquivamento = MdGdArquivamentoINT::montarSelectsSerieNomeGerados('null', '&nbsp;', $arrObjMdGdParametroDTO['DESPACHO_DESARQUIVAMENTO']->getStrValor());
    $strSelTipoProcedimentoListagemEliminacao = TipoProcedimentoINT::montarSelectNome('null', '&nbsp;', $arrObjMdGdParametroDTO['TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO']->getStrValor());
    $strSelSerieListagemEliminacao = MdGdArquivamentoINT::montarSelectsSerieNomeGerados('null', '&nbsp;', $arrObjMdGdParametroDTO['TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO']->getStrValor());   
    $strSelSerieDocumentoEliminacao = MdGdArquivamentoINT::montarSelectsSerieNomeGerados('null', '&nbsp;', $arrObjMdGdParametroDTO['TIPO_DOCUMENTO_ELIMINACAO']->getStrValor());
    $strSelTipoProcedimentoListagemRecolhimento = TipoProcedimentoINT::montarSelectNome('null', '&nbsp;', $arrObjMdGdParametroDTO['TIPO_PROCEDIMENTO_LISTAGEM_RECOLHIMENTO']->getStrValor()); // MUDARRRR
    $strSelSerieListagemRecolhimento = MdGdArquivamentoINT::montarSelectsSerieNomeGerados('null', '&nbsp;', $arrObjMdGdParametroDTO['TIPO_DOCUMENTO_LISTAGEM_RECOLHIMENTO']->getStrValor());   
    $strSelSerieDocumentoRecolhimento = MdGdArquivamentoINT::montarSelectsSerieNomeGerados('null', '&nbsp;', $arrObjMdGdParametroDTO['TIPO_DOCUMENTO_RECOLHIMENTO']->getStrValor());

    $strNomeDespachoArquivamento = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_DESPACHO_ARQUIVAMENTO]->getStrNome();
    $strNomeDespachoDesarquivamento = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_DESPACHO_DESARQUIVAMENTO]->getStrNome();
    $strNomeTipoProcedimentoListagemEliminacao = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO]->getStrNome();
    $strNomeTipoDocumentoListagemEliminacao = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO]->getStrNome();
    $strNomeTipoDocumentoEliminacao = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_TIPO_DOCUMENTO_ELIMINACAO]->getStrNome();
    $strNomeTipoProcedimentoListagemRecolhimento = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_TIPO_PROCEDIMENTO_LISTAGEM_RECOLHIMENTO]->getStrNome();
    $strNomeTipoDocumentoListagemRecolhimento = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_TIPO_DOCUMENTO_LISTAGEM_RECOLHIMENTO]->getStrNome();
    $strNomeTipoDocumentoRecolhimento = $arrObjMdGdParametroDTO[MdGdParametroRN::$PAR_TIPO_DOCUMENTO_RECOLHIMENTO]->getStrNome();

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
#sel<?= $strNomeDespachoArquivamento; ?> {position:absolute;left:0%;top:5%;width:50%;}

#lbl<?= $strNomeDespachoDesarquivamento; ?> {position:absolute;left:0%;top:12%;width:30%;}
#sel<?= $strNomeDespachoDesarquivamento; ?> {position:absolute;left:0%;top:17%;width:40%;}

#lbl<?= $strNomeTipoProcedimentoListagemEliminacao; ?> {position:absolute;left:0%;top:24%;width:30%;}
#sel<?= $strNomeTipoProcedimentoListagemEliminacao; ?> {position:absolute;left:0%;top:29%;width:40%;}

#lbl<?= $strNomeTipoDocumentoListagemEliminacao; ?> {position:absolute;left:0%;top:36%;width:30%;}
#sel<?= $strNomeTipoDocumentoListagemEliminacao; ?> {position:absolute;left:0%;top:41%;width:40%;}

#lbl<?= $strNomeTipoDocumentoEliminacao; ?> {position:absolute;left:0%;top:48%;width:30%;}
#sel<?= $strNomeTipoDocumentoEliminacao; ?> {position:absolute;left:0%;top:53%;width:40%;}

#lbl<?= $strNomeTipoProcedimentoListagemRecolhimento; ?> {position:absolute;left:0%;top:61%;width:30%;}
#sel<?= $strNomeTipoProcedimentoListagemRecolhimento; ?> {position:absolute;left:0%;top:66%;width:40%;}

#lbl<?= $strNomeTipoDocumentoListagemRecolhimento; ?> {position:absolute;left:0%;top:73%;width:30%;}
#sel<?= $strNomeTipoDocumentoListagemRecolhimento; ?> {position:absolute;left:0%;top:78%;width:40%;}

#lbl<?= $strNomeTipoDocumentoRecolhimento; ?> {position:absolute;left:0%;top:85%;width:30%;}
#sel<?= $strNomeTipoDocumentoRecolhimento; ?> {position:absolute;left:0%;top:90%;width:40%;}
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
if (infraTrim(document.getElementById('sel<?= $strNomeDespachoArquivamento; ?>').value)=='null') {
alert('Informe o Despacho de Arquivamento.');
document.getElementById('sel<?= $strNomeDespachoArquivamento; ?>').focus();
return false;
}

if (infraTrim(document.getElementById('sel<?= $strNomeDespachoDesarquivamento; ?>').value)=='null') {
alert('Informe o Despacho de Desarquivamento.');
document.getElementById('sel<?= $strNomeDespachoDesarquivamento; ?>').focus();
return false;
}

if (infraTrim(document.getElementById('sel<?= $strNomeTipoProcedimentoListagemEliminacao; ?>').value)=='null') {
alert('Informe o tipo de processo da listagem de eliminação.');
document.getElementById('sel<?= $strNomeTipoProcedimentoListagemEliminacao; ?>').focus();
return false;
}

if (infraTrim(document.getElementById('sel<?= $strNomeTipoDocumentoListagemEliminacao; ?>').value)=='null') {
alert('Informe o tipo de documento da listagem de eliminação.');
document.getElementById('sel<?= $strNomeTipoDocumentoListagemEliminacao; ?>').focus();
return false;
}

if (infraTrim(document.getElementById('sel<?= $strNomeTipoDocumentoEliminacao; ?>').value)=='null') {
alert('Informe o tipo de documento da eliminação.');
document.getElementById('sel<?= $strNomeTipoDocumentoEliminacao; ?>').focus();
return false;
}

if (infraTrim(document.getElementById('sel<?= $strNomeTipoProcedimentoListagemRecolhimento; ?>').value)=='null') {
alert('Informe o tipo de processo da listagem de recolhimento.');
document.getElementById('sel<?= $strNomeTipoProcedimentoListagemRecolhimento; ?>').focus();
return false;
}

if (infraTrim(document.getElementById('sel<?= $strNomeTipoDocumentoListagemRecolhimento; ?>').value)=='null') {
alert('Informe o tipo de documento da listagem de recolhimento.');
document.getElementById('sel<?= $strNomeTipoDocumentoListagemRecolhimento; ?>').focus();
return false;
}

if (infraTrim(document.getElementById('sel<?= $strNomeTipoDocumentoRecolhimento; ?>').value)=='null') {
alert('Informe o tipo de documento da recolhimento.');
document.getElementById('sel<?= $strNomeTipoDocumentoRecolhimento; ?>').focus();
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
          PaginaSEI::getInstance()->abrirAreaDados('40em');
          ?>

    <label id="lbl<?= $strNomeDespachoArquivamento ?>" for="sel<?= $strNomeDespachoArquivamento ?>" accesskey="p"
           class="infraLabelObrigatorio"><span class="infraTeclaAtalho">T</span>ipo de Documento de Arquivamento:</label>
    <select name="sel<?= $strNomeDespachoArquivamento ?>" id="sel<?= $strNomeDespachoArquivamento ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelSerieArquivamento; ?>
    </select>

    <label id="lbl<?= $strNomeDespachoDesarquivamento ?>" for="sel<?= $strNomeDespachoDesarquivamento ?>"
           accesskey="p"
           class="infraLabelObrigatorio"><span class="infraTeclaAtalho">T</span>ipo de Documento de Desarquivamento:</label>
    <select name="sel<?= $strNomeDespachoDesarquivamento ?>" id="sel<?= $strNomeDespachoDesarquivamento ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelSerieDesarquivamento; ?>
    </select>

    <label id="lbl<?= $strNomeTipoProcedimentoListagemEliminacao ?>" for="sel<?= $strNomeTipoProcedimentoListagemEliminacao ?>" accesskey="p"
           class="infraLabelObrigatorio"><span
            class="infraTeclaAtalho">T</span>ipo de Processo de Eliminação:</label>
    <select name="sel<?= $strNomeTipoProcedimentoListagemEliminacao ?>" id="sel<?= $strNomeTipoProcedimentoListagemEliminacao ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelTipoProcedimentoListagemEliminacao; ?>
    </select>

    <label id="lbl<?= $strNomeTipoDocumentoListagemEliminacao ?>" for="sel<?= $strNomeTipoDocumentoListagemEliminacao ?>" accesskey="p"
           class="infraLabelObrigatorio"><span
            class="infraTeclaAtalho">T</span>ipo de Documento da Listagem de Eliminação:</label>
    <select name="sel<?= $strNomeTipoDocumentoListagemEliminacao ?>" id="sel<?= $strNomeTipoDocumentoListagemEliminacao ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelSerieListagemEliminacao; ?>
    </select>

    <label id="lbl<?= $strNomeTipoDocumentoEliminacao ?>" for="sel<?= $strNomeTipoDocumentoEliminacao ?>" accesskey="p"
           class="infraLabelObrigatorio"><span
            class="infraTeclaAtalho">T</span>ipo de Documento de Eliminação:</label>
    <select name="sel<?= $strNomeTipoDocumentoEliminacao ?>" id="sel<?= $strNomeTipoDocumentoEliminacao ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelSerieDocumentoEliminacao; ?>
    </select>

    <label id="lbl<?= $strNomeTipoProcedimentoListagemRecolhimento ?>" for="sel<?= $strNomeTipoProcedimentoListagemRecolhimento ?>" accesskey="p"
           class="infraLabelObrigatorio"><span
            class="infraTeclaAtalho">T</span>ipo de Processo de Recolhimento:</label>
    <select name="sel<?= $strNomeTipoProcedimentoListagemRecolhimento ?>" id="sel<?= $strNomeTipoProcedimentoListagemRecolhimento ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelTipoProcedimentoListagemRecolhimento; ?>
    </select>

    <label id="lbl<?= $strNomeTipoDocumentoListagemRecolhimento ?>" for="sel<?= $strNomeTipoDocumentoListagemRecolhimento ?>" accesskey="p"
           class="infraLabelObrigatorio"><span
            class="infraTeclaAtalho">T</span>ipo de Documento da Listagem de Recolhimento:</label>
    <select name="sel<?= $strNomeTipoDocumentoListagemRecolhimento ?>" id="sel<?= $strNomeTipoDocumentoListagemRecolhimento ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelSerieListagemRecolhimento; ?>
    </select>

    <label id="lbl<?= $strNomeTipoDocumentoRecolhimento ?>" for="sel<?= $strNomeTipoDocumentoRecolhimento ?>" accesskey="p"
           class="infraLabelObrigatorio"><span
            class="infraTeclaAtalho">T</span>ipo de Documento de Recolhimento:</label>
    <select name="sel<?= $strNomeTipoDocumentoRecolhimento ?>" id="sel<?= $strNomeTipoDocumentoRecolhimento ?>"
            class="infraSelect"
            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strSelSerieDocumentoRecolhimento; ?>
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