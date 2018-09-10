<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 20/12/2007 - criado por mga
 *
 * Versão do Gerador de Código: 1.12.0
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

    //PaginaSEI::getInstance()->verificarSelecao('rel_protocolo_protocolo_selecionar');

    SessaoSEI::getInstance()->validarPermissao('gestao_documental_arquivar_processo');


    $arrComandos = array();

    $strParametros = '';

    if (isset($_GET['id_procedimento'])) {
        $strParametros .= "&id_procedimento=" . $_GET['id_procedimento'];
    }

    if (isset($_GET['arvore'])) {
        PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
        $strParametros .= '&arvore=' . $_GET['arvore'];
    }

    $objMdArquivamentoRN = new MdGdArquivamentoRN();
    $strTitulo = 'Concluir e Arquivar Processo';

    switch ($_GET['acao']) {
        case 'gd_arquivar_procedimento':

            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmSalvar" id="sbmSalvar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            if ($_GET['acao_origem'] == 'gd_pendencias_arquivamento') {
                $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_pendencias_arquivamento&acao_origem=' . $_GET['acao'] . $strParametros) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
            } else {
                $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . $strParametros) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
            }

            if ($_GET['acao_origem'] == 'arvore_visualizar') {
                $arrProtocolosOrigem = array($_GET['id_procedimento']);
            } else if ($_GET['acao_origem'] == 'procedimento_controlar') {
                $arrProtocolosOrigem = array_merge(PaginaSEI::getInstance()->getArrStrItensSelecionados('Gerados'), PaginaSEI::getInstance()->getArrStrItensSelecionados('Recebidos'), PaginaSEI::getInstance()->getArrStrItensSelecionados('Detalhado'));
            } else if (isset($_POST['hdnIdProtocolos'])) {
                $arrProtocolosOrigem = explode(',', $_POST['hdnIdProtocolos']);
            } else {
                $arrProtocolosOrigem = array($_GET['id_procedimento']);
            }

            if (isset($_POST['sbmSalvar'])) {
                try {
                    foreach ($arrProtocolosOrigem as $numIdProcedimento) {
                        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                        $objMdGdArquivamentoDTO->setDblIdProcedimento($numIdProcedimento);
                        $objMdGdArquivamentoDTO->setNumIdJustificativa($_POST['selJustificativa']);

                        if ($_POST['hdnOrigem'] == 'gd_pendencias_arquivamento') {
                            $objMdArquivamentoRN->reabrir = true;
                        }

                        $objMdArquivamentoRN->arquivar($objMdGdArquivamentoDTO);
                    }

                    PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
                    if ($_POST['hdnOrigem'] == 'gd_pendencias_arquivamento') {
                        header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_pendencias_arquivamento&acao_origem=' . $_GET['acao']));
                    } else {
                        header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&atualizar_arvore=1' . $strParametros));
                    }
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }

            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    // Busca as justificativas
    $objMdGdJustificativaDTO = new MdGdJustificativaDTO();
    $objMdGdJustificativaDTO->setStrStaTipo(MdGdJustificativaRN::$STA_TIPO_ARQUIVAMENTO);
    $objMdGdJustificativaDTO->retTodos();

    $objMdGdJustificativaRN = new MdGdJustificativaRN();
    $arrMdGdJustificativaDTO = $objMdGdJustificativaRN->listar($objMdGdJustificativaDTO);

    // Monta as combos de seleção que irão aparecer na tela
    $strItensSelProcedimentos = ProcedimentoINT::conjuntoCompletoFormatadoRI0903($arrProtocolosOrigem);
    $strItensSelJustificativas = InfraINT::montarSelectArrInfraDTO('null', '', '', $arrMdGdJustificativaDTO, 'IdJustificativa', 'Nome');

    $strIdProtocolos = implode(',', $arrProtocolosOrigem);

    $numTotalCondicionantes = 0;
    foreach ($arrProtocolosOrigem as $numIdProtocolo) {
        $numTotalCondicionantes += $objMdArquivamentoRN->contarCondicionantes($numIdProtocolo);
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

#lblProcedimentos {position:absolute;left:1%;top:9%;}
#selProcedimentos {position:absolute;left:1%;top:16%;width:96%;}

#lblJustificativa {position:absolute;left:0%;top:66%;}
#selJustificativa {position:absolute;left:0%;top:77%;width:99%;}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script>

    function inicializar() {
        document.getElementById('sbmSalvar').focus();
        infraEfeitoTabelas();
    }

    function OnSubmitForm() {
        return validarConcluirArquivar();
    }

    function validarConcluirArquivar() {
        if (document.getElementById('selJustificativa').value == 'null') {
            alert('Informe uma motivo.');
            return false;
        }

        if (document.getElementById('hdnTotalCondicionantes').value > 0) {
            if (confirm('Esse processo possuí condicional de arquivamento. Deseja prosseguir com o arquivamento?')) {
                return validarAssinatura();
            } else {
                return false;
            }
        }

        return validarAssinatura();

    }

    function validarAssinatura() {
        if (document.getElementById('hdnSenhaAssinatura').value == '') {
            exibirJanelaAssinatura();
            return false;

        } else {
            return true;
        }
    }

    function exibirJanelaAssinatura() {
        infraAbrirJanela('<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_enviar_arquivamento') ?>', 'janelaAjudaVariaveisModelo', 800, 600, 'location=0,status=1,resizable=1,scrollbars=1', false);
    }

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<div id="divGeral" class="infraAreaDados" style="height:50em;">

    <form id="frmConcluirArquivar" method="post" onsubmit="return OnSubmitForm();"
          action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParametros) ?>">
              <? PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

        <fieldset class="infraFieldset" style="position:absolute;left:0%;top: 6%;height: 53%;width: 97%;">
            <legend class="infraLegend">Dados do Arquivamento</legend>
            <label id="lblProcedimentos" for="selProcedimentos" class="infraLabelObrigatorio">Processos:</label>
            <select id="selProcedimentos" name="selProcedimentos" size="4" class="infraSelect"
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                        <?= $strItensSelProcedimentos ?>
            </select>

            <label id="lblJustificativa" for="selJustificativa" class="infraLabelObrigatorio">Motivo:</label>
            <select id="selJustificativa" name="selJustificativa"
                    class="infraSelect"
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                        <?= $strItensSelJustificativas ?>


            </select>
            <input type="hidden" id="hdnTotalCondicionantes" name="hdnTotalCondicionantes"
                   value="<?= $numTotalCondicionantes ?>"/>
        </fieldset>
        
        <fieldset class="infraFieldset" style="position:absolute;left:0%;top: 62%;height: 53%;width: 97%;">
            <legend class="infraLegend">Dados da Assinatura</legend>
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
        </fieldset>

        <input type="hidden" id="hdnOrigem" name="hdnOrigem" value="<?= $_GET['acao_origem']; ?>"/>
        <input type="hidden" id="hdnOrgaoAssinatura" name="hdnOrgaoAssinatura" value="" />
        <input type="hidden" id="hdnCargoFuncaoAssinatura" name="hdnCargoFuncaoAssinatura" value="" />
        <input type="hidden" id="hdnSenhaAssinatura" name="hdnSenhaAssinatura" value="" />
        <input type="hidden" id="hdnIdProtocolos" name="hdnIdProtocolos" />
    </form>
</div>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>