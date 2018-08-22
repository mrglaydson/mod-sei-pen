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

            $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeOrigem(null);
            $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeDestino(null);
            $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeArquivamento(null);

            $strTitulo = 'Nova Unidade de Arquivo';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarUnidadeArquivamento" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao']) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';


            if (isset($_POST['sbmCadastrarUnidadeArquivamento'])) {
                $arrUnidadesOrigem = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnUnidadesOrigem']);

                try {
                    foreach ($arrUnidadesOrigem as $numIdUnidadeOrigem) {
                        $objMdGdUnidadeArquivamentoDTO = new MdGdUnidadeArquivamentoDTO();
                        $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeArquivamento(null);
                        $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeOrigem($numIdUnidadeOrigem);
                        $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeDestino($_POST['selUnidadeDestino']);

                        $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
                        $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoRN->cadastrar($objMdGdUnidadeArquivamentoDTO);
                    }

                    PaginaSEI::getInstance()->adicionarMensagem('Unidade de arquivo cadastrada com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&id_unidade_arquivamento=' . $objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeArquivamento() . PaginaSEI::getInstance()->montarAncora($objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeArquivamento())));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'gd_unidade_arquivamento_alterar':
            SessaoSEI::getInstance()->validarPermissao('gestao_documental_unidade_arquivamento_alterar');

            $strTitulo = 'Alterar Unidade de Arquivo';
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
                    PaginaSEI::getInstance()->adicionarMensagem('Unidade de arquivo alterado com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeArquivamento())));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'gd_unidade_arquivamento_visualizar':
            SessaoSEI::getInstance()->validarPermissao('gestao_documental_unidade_arquivamento_visualizar');

            $strTitulo = 'Consultar Unidade de Arquivo';
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

    //Monta os links de seleção das unidades
    $strLinkAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar_todas');
    $strLinkUnidadeSelecao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_selecionar_todas&tipo_selecao=2&id_object=objLupaUnidades');
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

#lblUnidadeDestino {position:absolute;left:0%;top:0%;width:35%;}
#selUnidadeDestino {position:absolute;left:0%;top:6%;width:35%;}

<? if ($_GET['acao'] == 'gd_unidade_arquivamento_cadastrar') { ?>
    #lblUnidadesOrigem {position:absolute;left:0%;top:16%;}
    #txtUnidadeOrigem {position:absolute;left:0%;top:22%;width:67%;}
    #selUnidadesOrigem {position:absolute;left:0%;top:30%;width:86%;}
    #divOpcoesUnidadesOrigem {position:absolute;left:87%;top:30%;}
<? } else { ?>
    #lblUnidadeOrigem {position:absolute;left:0%;top:16%;}
    #selUnidadeOrigem {position:absolute;left:0%;top:22%;width:35%;}
<? } ?>


<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
// PaginaSEI::getInstance()->abrirJavaScript();
?>
<script type='text/javascript'>
    var objLupaUnidades = null;
    var objAutoCompletarUnidade = null;
    var objLupaTramitacao = null;

    function inicializar() {
        if ('<?= $_GET['acao'] ?>' == 'gd_unidade_arquivamento_cadastrar') {
            document.getElementById('selUnidadesOrigem').focus();

            objLupaUnidades = new infraLupaSelect('selUnidadesOrigem', 'hdnUnidadesOrigem', '<?= $strLinkUnidadeSelecao ?>');
            objAutoCompletarUnidade = new infraAjaxAutoCompletar('hdnIdUnidadeOrigem', 'txtUnidadeOrigem', '<?= $strLinkAjaxUnidade ?>');
            objAutoCompletarUnidade.limparCampo = true;

            objAutoCompletarUnidade.prepararExecucao = function () {
                return 'palavras_pesquisa=' + document.getElementById('txtUnidadeOrigem').value;
            };

            objAutoCompletarUnidade.processarResultado = function (id, descricao, complemento) {
                if (id != '') {
                    objLupaUnidades.adicionar(id, descricao, document.getElementById('txtUnidadeOrigem'));
                }
            };
            document.getElementById('txtUnidadeOrigem').focus();


        } else if ('<?= $_GET['acao'] ?>' == 'gd_unidade_arquivamento_visualizar') {
            infraDesabilitarCamposAreaDados();
        } else {
            document.getElementById('btnCancelar').focus();
        }
        infraEfeitoTabelas();
    }

    function validarCadastro() {
        if (infraTrim(document.getElementById('selUnidadeDestino').value) == 'null') {
            alert('Informe a Unidade de Arquivo.');
            document.getElementById('selUnidadeDestino').focus();
            return false;
        }

<? if ($_GET['acao'] == 'gd_unidade_arquivamento_cadastrar') { ?>
            if (infraTrim(document.getElementById('hdnUnidadesOrigem').value) == '') {
                alert('Informe uma Unidade de Origem.');
                document.getElementById('txtUnidadeOrigem').focus();
                return false;
            }
<? } else { ?>
            if (infraTrim(document.getElementById('selUnidadeOrigem').value) == 'null') {
                alert('Informe a Unidade de Origem.');
                document.getElementById('selUnidadeOrigem').focus();
                return false;
            }
<? } ?>



        return true;
    }

    function OnSubmitForm() {
        return validarCadastro();
    }
</script>
<?
//PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmUnidadeArquivamento" method="post" onsubmit="return OnSubmitForm();" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
    <?
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    // PaginaSEI::getInstance()->montarAreaValidacao();
    PaginaSEI::getInstance()->abrirAreaDados('30em');
    ?>

    <label id="lblUnidadeDestino" for="selUnidadeDestino" accesskey="" class="infraLabelObrigatorio">Unidade de Arquivo:</label>
    <select id="selUnidadeDestino" name="selUnidadeDestino" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelUnidadesDestino ?>
    </select>

    <? if ($_GET['acao'] == 'gd_unidade_arquivamento_cadastrar') { ?>
        <label id="lblUnidadesOrigem" for="selUnidadesOrigem" class="infraLabelObrigatorio">Unidades de Origem:</label>
        <input type="text" id="txtUnidadeOrigem" name="txtUnidadeOrigem" class="infraText" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
        <?= $strLinkUnidadesTramitacao ?>
        <input type="hidden" id="hdnIdUnidadeOrigem" name="hdnIdUnidadeOrigem" class="infraText" value="" />
        <select id="selUnidadesOrigem" name="selUnidadesOrigem" size="4" multiple="multiple" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
        </select>
        <div id="divOpcoesUnidadesOrigem">
            <img id="imgLupaUnidades" onclick="objLupaUnidades.selecionar(700, 500);" src="/infra_css/imagens/lupa.gif" alt="Selecionar Unidades" title="Selecionar Unidades" class="infraImg" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
            <br />
            <img id="imgExcluirUnidades" onclick="objLupaUnidades.remover();" src="/infra_css/imagens/remover.gif" alt="Remover Unidades Selecionadas" title="Remover Unidades Selecionadas" class="infraImg" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
        </div>
        <input type="hidden" id="hdnUnidadesOrigem" name="hdnUnidadesOrigem" value="<?= $_POST['hdnUnidadesOrigem'] ?>" />
    <? } else { ?>
        <label id="lblUnidadeOrigem" for="selUnidadeOrigem" accesskey="" class="infraLabelObrigatorio">Unidade de Origem:</label>
        <select id="selUnidadeOrigem" name="selUnidadeOrigem" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
            <?= $strItensSelUnidadesOrigem ?>
        </select>
    <? } ?>

    <input type="hidden" id="hdnIdUnidadeArquivamento" name="hdnIdUnidadeArquivamento" value="<?= $objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeArquivamento(); ?>" />

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