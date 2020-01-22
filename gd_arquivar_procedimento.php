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
            } else if ($_GET['acao_origem'] == 'gd_pendencias_arquivamento') {
                $arrProtocolosOrigem = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            } else {
                $arrProtocolosOrigem = array($_GET['id_procedimento']);
            }

            if (isset($_POST['sbmSalvar'])) {
                try {
                    $arrProtocolosOrigem = explode(',', $_POST['hdnIdProtocolos']);
                    
                    $objAssinaturaDTO = new AssinaturaDTO();
                    $objAssinaturaDTO->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
                    $objAssinaturaDTO->setNumIdOrgaoUsuario($_POST['selOrgao']);
                    $objAssinaturaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                    $objAssinaturaDTO->setNumIdContextoUsuario(SessaoSEI::getInstance()->getNumIdContextoUsuario());
                    $objAssinaturaDTO->setStrSenhaUsuario($_POST['pwdSenha']);
                    $objAssinaturaDTO->setStrCargoFuncao($_POST['selCargoFuncao']);
                    
                    foreach ($arrProtocolosOrigem as $numIdProcedimento) {
                        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                        $objMdGdArquivamentoDTO->setDblIdProcedimento($numIdProcedimento);
                        $objMdGdArquivamentoDTO->setNumIdJustificativa($_POST['selJustificativa']);
                        $objMdGdArquivamentoDTO->setObjAssinaturaDTO($objAssinaturaDTO);

                        if(isset($_POST['txtDataArquivamento'])){
                            $objMdGdArquivamentoDTO->setDthDataArquivamento($_POST['txtDataArquivamento']);
                        }
                        
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
    $strItensSelOrgaos = OrgaoINT::montarSelectSiglaRI1358('null', '&nbsp;', SessaoSEI::getInstance()->getNumIdOrgaoUsuario());
    $strItensSelCargoFuncao = AssinanteINT::montarSelectCargoFuncaoUnidadeUsuarioRI1344('null', '&nbsp;', null, SessaoSEI::getInstance()->getNumIdUsuario());
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

#lblProcedimentos {position:absolute;left:1%;top:11%;}
#selProcedimentos {position:absolute;left:1%;top:30%;width:96%;}

#lblJustificativa {position:absolute;left:1%;top:0%;}
#selJustificativa {position:absolute;left:1%;top:45%;width:96%;}

/*#chkSinLegado { position:absolute;top:10%; }*/

#lblDataArquivamento { position:absolute;left:1%;top:0%; }
#txtDataArquivamento { position:absolute;left:1%;top:40%;width: 17%; } 
#imgCalDataArquivamento { position: absolute; left: 18.5%; top: 42%; }

#fieldsetDadosArquivamento {position: absolute; left: 0%; top: 12%; height: 38%; width: 97%;} 
#fieldsetDadosAssinatura   {position: absolute; left: 0%; top: 57%; height: 37%; width: 97%;}

#lblOrgao {position: absolute; top: 13%; left: 0%;}
#selOrgao {position: absolute; top: 57%; width: 50%;}

#lblUsuario {position: absolute; top: 36%;}
#txtUsuario {position: absolute; left: 8%; top: 35%; width: 41%;}

#lblCargoFuncao {position: absolute;}
#selCargoFuncao {position: absolute; top: 51%; width: 50%;}
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
        if (document.getElementById('selJustificativa').value == 'null' || document.getElementById('selJustificativa').value == '') {
            alert('Informe um motivo.');
            return false;
        }

        if(document.getElementById('chkSinLegado').checked){
            if (document.getElementById('txtDataArquivamento').value == '') {
                alert('Informe a data do arquivamento.');
                return false;
            }

            if (!infraValidarData(document.getElementById('txtDataArquivamento'))) {
                return false;
            }
        }

        if (document.getElementById('selOrgao').value == 'null' || document.getElementById('selOrgao').value == '') {
            alert('Informe o órgão do assinante.');
            return false;
        }

        if (document.getElementById('selCargoFuncao').value == 'null' || document.getElementById('selCargoFuncao').value == '') {
            alert('Informe o cargo e função.');
            return false;
        }

        if (document.getElementById('pwdSenha').value == '') {
            alert('Informe a senha.');
            return false;
        }

        if (document.getElementById('hdnTotalCondicionantes').value > 0) {
            if (confirm('Este processo possui condicionante de arquivamento. Deseja realizar o arquivamento?')) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    function ativarLegado(){
        if(document.getElementById('chkSinLegado').checked){
            document.getElementById('divDataArquivamento').style.display = "block";
            document.getElementById('txtDataArquivamento').disabled = false;
        }else{
            document.getElementById('divDataArquivamento').style.display = "none";
            document.getElementById('txtDataArquivamento').disabled = true;
        }
    }

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<div id="divGeral" class="infraAreaDados" style="height:60em;">

    <form id="frmConcluirArquivar" method="post" onsubmit="return OnSubmitForm();"
          action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParametros) ?>">
              <? PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

        <fieldset class="infraFieldset" id="fieldsetDadosArquivamento">
            <legend class="infraLegend">Dados do Arquivamento</legend>

            <div id="divProcedimentos" class="infraAreaDados" style="height:10em;">
                <label id="lblProcedimentos" for="selProcedimentos" class="infraLabelObrigatorio">Processos:</label>
                <select id="selProcedimentos" name="selProcedimentos" size="4" class="infraSelect"
                        tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelProcedimentos ?>
                </select>
            </div>

            <div id="divJustificativa" class="infraAreaDados" style="height:4.5em;">
                <label id="lblJustificativa" for="selJustificativa" class="infraLabelObrigatorio">Motivo:</label>
                <select id="selJustificativa" name="selJustificativa"
                        class="infraSelect"
                        tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelJustificativas ?>
                </select>
            </div>

            <div id="divSinLegado" class="infraAreaDados" style="height:2.5em; padding-left: 0.6em">
                <input type="checkbox" id="chkSinLegado" name="chkSinLegado" class="infraCheckbox" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" onclick="ativarLegado()"/>
                <label id="lblSinLegado" for="chkSinLegado" class="infraLabelCheckbox">Arquivamento legado?</label>
            </div>

            <div id="divDataArquivamento" class="infraAreaDados" style="height:4em; display:none;">
                <label id="lblDataArquivamento" for="txtDataArquivamento" accesskey="e">Data do arquivamento:</label>
                <input type="text" id="txtDataArquivamento" name="txtDataArquivamento" class="infraText" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" onkeypress="return infraMascara(this, event,'##/##/####')" disabled />
                <img id="imgCalDataArquivamento" title="Selecionar Data de Arquivamento" alt="Selecionar Data de Arquivamento" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/calendario.gif" class="infraImg" onclick="infraCalendario('txtDataArquivamento', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    
            </div>

            <input type="hidden" id="hdnTotalCondicionantes" name="hdnTotalCondicionantes" value="<?= $numTotalCondicionantes ?>"/>
        </fieldset>


        <fieldset class="infraFieldset" id="fieldsetDadosAssinatura">
            <legend class="infraLegend">Dados da Assinatura</legend>
            <p>Dados para assinatura do despacho de arquivamento</p>
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
        <input type="hidden" id="hdnIdProtocolos" name="hdnIdProtocolos" value='<?= $strIdProtocolos ?>'/>
    </form>
</div>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>