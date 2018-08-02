<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 01/03/2012 - criado por bcu
 *
 * Versão do Gerador de Código: 1.32.1
 *
 * Versão no CVS: $Id$
 */
try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    //////////////////////////////////////////////////////////////////////////////
    //InfraDebug::getInstance()->setBolLigado(false);
    //InfraDebug::getInstance()->setBolDebugInfra(true);
    //InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

    SessaoSEI::getInstance()->validarLink();

    $objMdGdUnidadeArquivamentoDTO = new MdGdUnidadeArquivamentoDTO();

    $strDesabilitar = '';

    $arrComandos = array();

    switch ($_GET['acao']) {
        case 'gd_unidade_arquivamento_cadastrar':
            SessaoSEI::getInstance()->validarPermissao('gestao_documental_unidade_arquivamento_cadastrar');

            $strTitulo = 'Nova Unidade de Arquivamento';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarUnidadeArquivamento" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao']) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeArquivamento(null);
            $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeOrigem($_POST['selUnidadeOrigem']);
            $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeDestino($_POST['selUnidadeDestino']);

            if (isset($_POST['sbmCadastrarUnidadeArquivamento'])) {
                try {
                    $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
                    $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoRN->cadastrar($objMdGdUnidadeArquivamentoDTO);

                    PaginaSEI::getInstance()->adicionarMensagem('Unidade de arquivamento cadastrada com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&id_unidade_arquivamento=' . $objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeArquivamento() . PaginaSEI::getInstance()->montarAncora($objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeArquivamento())));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'gd_unidade_arquivamento_alterar':
            SessaoSEI::getInstance()->validarPermissao('gestao_documental_unidade_arquivamento_alterar');

            $strTitulo = 'Alterar Unidade de Arquivamento';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarJustificativa" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $strDesabilitar = 'disabled="disabled"';
            
            if (isset($_GET['id_unidade_arquivamento'])) {
                $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeArquivamento($_GET['id_unidade_arquivamento']);
                $objMdGdUnidadeArquivamentoDTO->retTodos();

                $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
                $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoRN->consultar($objMdGdUnidadeArquivamentoDTO);
                if ($objMdGdUnidadeArquivamentoDTO == null) {
                    throw new InfraException("Registro não encontrado.");
                }
            } else {
                $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeArquivamento($_POST['hdnIdUnidadeArquivamento']);
                $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeOrigem($_POST['selUnidadeOrigem']);
                $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeDestino($_POST['selUnidadeDestino']);
            }

            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeArquivamento())) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            if (isset($_POST['sbmAlterarJustificativa'])) {
                try {
                    $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
                    $objMdGdUnidadeArquivamentoRN->alterar($objMdGdUnidadeArquivamentoDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Unidade de arquivamento alterado com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeArquivamento())));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'gd_unidade_arquivamento_visualizar':
            SessaoSEI::getInstance()->validarPermissao('gestao_documental_unidade_arquivamento_visualizar');

            $strTitulo = 'Consultar Unidade de Arquivamento';
            $arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($_GET['id_justificativa'])) . '\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
            $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeArquivamento($_GET['id_unidade_arquivamento']);
            $objMdGdUnidadeArquivamentoDTO->retTodos();

            $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
            $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoRN->consultar($objMdGdUnidadeArquivamentoDTO);
            if ($objMdGdUnidadeArquivamentoDTO === null) {
                throw new InfraException("Registro não encontrado.");
            }
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    // Busca uma lista de unidades
    $strItensSelUnidadesOrigem = UnidadeINT::montarSelectSiglaDescricao('null', '&nbsp;', $objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeOrigem());
    $strItensSelUnidadesDestino = UnidadeINT::montarSelectSiglaDescricao('null', '&nbsp;', $objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeDestino());
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

#lblUnidadeOrigem {position:absolute;left:0%;top:0%;width:35%;}
#selUnidadeOrigem {position:absolute;left:0%;top:6%;width:35%;}

#lblUnidadeDestino {position:absolute;left:0%;top:16%;width:35%;}
#selUnidadeDestino {position:absolute;left:0%;top:22%;width:35%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
function inicializar() {
    if ('<?= $_GET['acao'] ?>' == 'gd_unidade_arquivamento_cadastrar') {
        document.getElementById('selUnidadeOrigem').focus();

    } else if ('<?= $_GET['acao'] ?>' == 'gd_unidade_arquivamento_visualizar') {
        infraDesabilitarCamposAreaDados();
    } else {
        document.getElementById('btnCancelar').focus();
    }
    infraEfeitoTabelas();
}

function validarCadastro() {

if (infraTrim(document.getElementById('selUnidadeOrigem').value) == 'null') {
alert('Informe a Unidade de Origem.');
document.getElementById('selUnidadeOrigem').focus();
return false;
}

if (infraTrim(document.getElementById('selUnidadeDestino').value) == 'null') {
alert('Informe a Unidade de Arquivamento.');
document.getElementById('selUnidadeDestino').focus();
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
<form id="frmUnidadeArquivamento" method="post" onsubmit="return OnSubmitForm();" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
    <?
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    // PaginaSEI::getInstance()->montarAreaValidacao();
    PaginaSEI::getInstance()->abrirAreaDados('30em');
    ?>

    <label id="lblUnidadeOrigem" for="selUnidadeOrigem" accesskey="" class="infraLabelObrigatorio">Unidade de Origem:</label>
    <select id="selUnidadeOrigem" name="selUnidadeOrigem" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelUnidadesOrigem ?>
    </select>

    <label id="lblUnidadeDestino" for="selUnidadeDestino" accesskey="" class="infraLabelObrigatorio">Unidade de Arquivamento:</label>
    <select id="selUnidadeDestino" name="selUnidadeDestino" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelUnidadesDestino ?>
    </select>

    <input type="hidden" id="hdnIdJustificativa" name="hdnIdUnidadeArquivamento" value="<?= $objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeArquivamento(); ?>" />
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