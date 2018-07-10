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

    SessaoSEI::getInstance()->validarPermissao('gestao_documental_desarquivar_processo');


    $arrComandos = array();

    $strParametros = '';

    if (isset($_GET['id_procedimento'])) {
        $strParametros .= "&id_procedimento=" . $_GET['id_procedimento'];
    }

    if (isset($_GET['arvore'])) {
        PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
        $strParametros .= '&arvore=' . $_GET['arvore'];
    }

    $objMdDesarquivamentoRN = new MdGdDesarquivamentoRN();
    $strTitulo = 'Desarquivar Processo';

    switch ($_GET['acao']) {
        case 'gd_desarquivar_procedimento':

            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmSalvar" id="sbmSalvar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . $strParametros) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            if ($_GET['acao_origem'] == 'arvore_visualizar') {
                $arrProtocolosOrigem = array($_GET['id_procedimento']);
            } else if ($_GET['acao_origem'] == 'procedimento_controlar') {
                $arrProtocolosOrigem = array_merge(PaginaSEI::getInstance()->getArrStrItensSelecionados('Gerados'), PaginaSEI::getInstance()->getArrStrItensSelecionados('Recebidos'), PaginaSEI::getInstance()->getArrStrItensSelecionados('Detalhado'));
            }else if (isset($_POST['hdnIdProtocolos'])){
                $arrProtocolosOrigem = explode(',', $_POST['hdnIdProtocolos']);
            }

            if (isset($_POST['sbmSalvar'])) {
                try {
                    foreach ($arrProtocolosOrigem as $numIdProcedimento) {
                        $objMdGdDesarquivamentoDTO = new MdGdDesarquivamentoDTO();
                        $objMdGdDesarquivamentoDTO->setDblIdProcedimento($numIdProcedimento);
                        $objMdGdDesarquivamentoDTO->setNumIdJustificativa($_POST['selJustificativa']);

                        $objMdDesarquivamentoRN->desarquivar($objMdGdDesarquivamentoDTO);
                    }

                    PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&atualizar_arvore=1' . $strParametros));
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
    $objMdGdJustificativaDTO->setStrStaTipo(MdGdJustificativaRN::$STA_TIPO_DESARQUIVAMENTO);
    $objMdGdJustificativaDTO->retTodos();

    $objMdGdJustificativaRN = new MdGdJustificativaRN();
    $arrMdGdJustificativaDTO = $objMdGdJustificativaRN->listar($objMdGdJustificativaDTO);

    // Monta as combos de seleção que irão aparecer na tela
    $strItensSelProcedimentos = ProcedimentoINT::conjuntoCompletoFormatadoRI0903($arrProtocolosOrigem);
    $strItensSelJustificativas = InfraINT::montarSelectArrInfraDTO('null', '', '', $arrMdGdJustificativaDTO, 'IdJustificativa', 'Nome');
    $strIdProtocolos = implode(',', $arrProtocolosOrigem);


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

    #lblProcedimentos {position:absolute;left:0%;top:20%;}
    #selProcedimentos {position:absolute;left:0%;top:30%;width:99%;}

    #lblJustificativa {position:absolute;left:0%;top:66%;}
    #selJustificativa {position:absolute;left:0%;top:77%;width:99%;}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

    function inicializar(){
        document.getElementById('sbmSalvar').focus();
        infraEfeitoTabelas();
    }

    function OnSubmitForm() {
        return validarConcluirArquivar();
    }

    function validarConcluirArquivar(){

        if (document.getElementById('selJustificativa').value == 'null'){
            alert('Informe um motivo.');
            return false;
        }

        if(confirm('Deseja mesmo desarquivar esse processo? Após essa ação o seu tempo de guarda será zerado.')){
            return true;
        }else{
            return false;
        }
    }


<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <div id="divGeral" class="infraAreaDados" style="height:20em;">

        <form id="frmDesarquivar" method="post" onsubmit="return OnSubmitForm();"
              action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParametros) ?>">
            <? PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

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
            <input type="hidden" id="hdnIdProtocolos" name="hdnIdProtocolos" value="<?= $strIdProtocolos; ?>"/>
        </form>
    </div>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>