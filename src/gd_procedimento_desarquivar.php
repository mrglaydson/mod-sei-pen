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

    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);


    $arrComandos = array();

    $strParametros = '';

    if (isset($_GET['id_procedimento']) && !empty($_GET['id_procedimento'])) {
        $strParametros .= "&id_procedimento=" . $_GET['id_procedimento'];
    }

    if (isset($_GET['arvore'])) {
        PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
        $strParametros .= '&arvore=' . $_GET['arvore'];
    }

    if(isset($_GET['redirect']) && $_GET['redirect']){
        header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem=' . $_GET['acao'].$strParametros.'&atualizar_arvore=1'));
        die();
    }

    $strTitulo = 'Desarquivar Processo';

    switch ($_GET['acao']) {
        case 'gd_procedimento_desarquivar':

            $arrComandos[] = '<button type="button" accesskey="S" name="sbmSalvar" id="sbmSalvar" value="Salvar" class="infraButton"  onclick="infraAbrirBarraProgresso(this.form,\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']).'\', 600, 250);" ><span class="infraTeclaAtalho">S</span>alvar</button>';

            if ($_GET['acao_origem'] == 'gd_arquivamento_listar') {
                $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivamento_listar&acao_origem=' . $_GET['acao'] . $strParametros) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
            } else {
                $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . $strParametros) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
            }

            if ($_GET['acao_origem'] == 'gd_arquivamento_listar') {
                $arrProtocolosOrigem = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            } else {
                $arrProtocolosOrigem = array($_GET['id_procedimento']);
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_GET['acao_origem'] == 'gd_procedimento_desarquivar') {
                try {
                    $arrProtocolosOrigem = explode(',', $_POST['hdnIdProtocolos']);
                    
                    ini_set('max_execution_time','0');
                    ini_set('memory_limit','2048M');
                    
                    PaginaSEI::getInstance()->prepararBarraProgresso2($strTitulo);
                    $prb = InfraBarraProgresso2::newInstance('DesarquivarProcessos', array('cor_fundo'=>'#5c9ccc','cor_borda'=>'#4297d7'));
                    $prb->setStrRotulo('Desarquivar Processos');
                    $prb->setNumMin(0);
                    $prb->setNumMax(count($arrProtocolosOrigem));

                    $objAssinaturaDTO = new AssinaturaDTO();
                    $objAssinaturaDTO->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
                    $objAssinaturaDTO->setNumIdOrgaoUsuario($_POST['selOrgao']);
                    $objAssinaturaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                    $objAssinaturaDTO->setNumIdContatoUsuario(SessaoSEI::getInstance()->getNumIdContextoUsuario());
                    $objAssinaturaDTO->setStrSenhaUsuario($_POST['pwdSenha']);
                    $objAssinaturaDTO->setStrCargoFuncao($_POST['selCargoFuncao']);

                    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();

                    foreach ($arrProtocolosOrigem as $key => $numIdProcedimento) {
                        $objMdGdDesarquivamentoDTO = new MdGdDesarquivamentoDTO();
                        $objMdGdDesarquivamentoDTO->setDblIdProcedimento($numIdProcedimento);
                        $objMdGdDesarquivamentoDTO->setNumIdJustificativa($_POST['selJustificativa']);
                        $objMdGdDesarquivamentoDTO->setObjAssinaturaDTO($objAssinaturaDTO);

                        $objMdGdArquivamentoRN->desarquivar($objMdGdDesarquivamentoDTO);
                        $prb->setStrRotulo('Desarquivamento '.($key + 1) .' de '.count($arrProtocolosOrigem));
                        $prb->moverProximo();
                    }

                    PaginaSEI::getInstance()->setStrMensagem('Desarquivamento realizado com sucesso!');
                    if(count($arrProtocolosOrigem) > 1){
                        PaginaSEI::getInstance()->finalizarBarraProgresso2(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivamento_listar&acao_origem=' . $_GET['acao']), true);
                    }else{
                        PaginaSEI::getInstance()->finalizarBarraProgresso2(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_desarquivar&acao_origem=' . $_GET['acao'] . '&id_procedimento='.$arrProtocolosOrigem[0].'&arvore=1&redirect=true'), true);
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
    $objMdGdJustificativaDTO->setStrStaTipo(MdGdJustificativaRN::$STA_TIPO_DESARQUIVAMENTO);
    $objMdGdJustificativaDTO->retTodos();

    $objMdGdJustificativaRN = new MdGdJustificativaRN();
    $arrMdGdJustificativaDTO = $objMdGdJustificativaRN->listar($objMdGdJustificativaDTO);

    // Monta as combos de seleção que irão aparecer na tela
    $strItensSelOrgaos = OrgaoINT::montarSelectSiglaRI1358('null', '&nbsp;', SessaoSEI::getInstance()->getNumIdOrgaoUsuario());
    $strItensSelCargoFuncao = AssinanteINT::montarSelectCargoFuncaoUnidadeUsuarioRI1344('null', '&nbsp;', null, SessaoSEI::getInstance()->getNumIdUsuario());
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

#lblProcedimentos {position:absolute;left:1%;top:11%;}
#selProcedimentos {position:absolute;left:1%;top:30%;width:96%; height:55%}

#lblJustificativa {position:absolute;left:1%;top:0%;}
#selJustificativa {position:absolute;left:1%;top:45%;width:96%;}

#divOrgao {position:relative;left:1%;}
#divUsuario {position:relative;left:1%;}
#divCargoFuncao {position:relative;left:1%;}
#divAutenticacao {position:relative;left:1%;}

#fieldsetDadosArquivamento {position: absolute; left: 0%; top: 12%; height: 45%; width: 97%;} 
#fieldsetDadosAssinatura   {position: absolute; left: 0%; top: 60%; height: 37%; width: 97%;}

#lblOrgao {position: absolute; top: 13%;}
#selOrgao {position: absolute; top: 57%; width: 50%;}

#lblUsuario {position: absolute;}
#txtUsuario {position: absolute; top: 50%; width: 41%;}

#lblCargoFuncao {position: absolute;}
#selCargoFuncao {position: absolute; top: 51%; width: 50%;}

#lblSenha {position: absolute;}
#pwdSenha {position: absolute; top: 70%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script>
    var validSenha = false;
    var validConfiguracao = false;

    function inicializar() {
        document.getElementById('sbmSalvar').focus();
        infraEfeitoTabelas();
    }

    function OnSubmitForm() {
        return validarDesarquivar();
    }

    function validarSenhaConfiguracao() {
        // Valida a senha do usuário
        objAjaxVerificacaoAssinatura = new infraAjaxComplementar(null,'<?=SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=gd_arquivamento_validar_senha')?>');
        objAjaxVerificacaoAssinatura.async = false;
        objAjaxVerificacaoAssinatura.prepararExecucao = function(){
            return 'orgao='+ document.getElementById('selOrgao').value + '&senha=' + document.getElementById('pwdSenha').value ;
        };
        objAjaxVerificacaoAssinatura.processarResultado = function(arr){
            if (arr!=null) {
                if(arr.SinValida == 'S'){
                    validSenha = true
                    // Valida as configurações do módulo
                    objAjaxVerificacaoConfiguracao = new infraAjaxComplementar(null,'<?=SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=gd_arquivamento_validar_configuracao')?>');
                    objAjaxVerificacaoConfiguracao.async = false;
                    objAjaxVerificacaoConfiguracao.processarResultado = function(arr){
                        if(arr.SinValida == 'S'){
                            validConfiguracao = true
                        }
                    };
                    objAjaxVerificacaoConfiguracao.executar();

                }
            }
        };
        objAjaxVerificacaoAssinatura.executar();
    }

    function validarDesarquivar() {

        if (document.getElementById('selJustificativa').value == 'null') {
            alert('Informe uma motivo.');
            return false;
        }

        if (document.getElementById('selOrgao').value == 'null') {
            alert('Informe o órgão do assinante.');
            return false;
        }

        if (document.getElementById('selCargoFuncao').value == 'null') {
            alert('Informe o cargo e função.');
            return false;
        }

        if (document.getElementById('pwdSenha').value == '') {
            alert('Informe a senha.');
            return false;
        }
        
        // Valida a senha e configuração do módulo
        if(!validSenha){
            alert('Senha incorreta!');
            return false;
        }

        if(!validConfiguracao){
            alert('O módulo não está corretamente configurado. Verifique a existência de unidades de arquivamento para a sua unidade!');
            return false;
        }

        if(validSenha && validConfiguracao){
            return true
        }


        if (confirm('Atenção! Caso este processo seja desarquivado retornará para a área de trabalho da unidade e seu prazo de guarda será reiniciado. Tem certeza que deseja continuar?')) {
            return true;
        } else {
            return false;
        }

    }

// </script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<div id="divGeral" class="infraAreaDados" style="height:50em;">

    <form id="frmDesarquivar" method="post" onsubmit="return OnSubmitForm();"
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
                <input type="hidden" id="hdnTotalCondicionantes" name="hdnTotalCondicionantes"
                    value="<?= $numTotalCondicionantes ?>"/>
            </div>
        </fieldset>

        <fieldset class="infraFieldset" id="fieldsetDadosAssinatura" style="height:28em;">
            <legend class="infraLegend">Dados da Assinatura</legend>
            <div id="divOrgao" class="infraAreaDados" style="height:4.5em;">
                <label id="lblOrgao" for="selOrgao" accesskey="r" class="infraLabelObrigatorio">Ó<span class="infraTeclaAtalho">r</span>gão do Assinante:</label>
                <select id="selOrgao" name="selOrgao" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                    <?= $strItensSelOrgaos ?>
                </select>
            </div>

            <div id="divUsuario" class="infraAreaDados" style="height:4.5em; top:10%">
                <label id="lblUsuario" for="txtUsuario" accesskey="e" class="infraLabelObrigatorio">Assinant<span class="infraTeclaAtalho">e</span>:</label>
                <input type="text" id="txtUsuario" name="txtUsuario" class="infraText" value="<?= SessaoSEI::getInstance()->getStrNomeUsuario() ?>" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" disabled />
            </div>  

            <div id="divCargoFuncao" class="infraAreaDados" style="height:4.5em; top:15%">
                <label id="lblCargoFuncao" for="selCargoFuncao" accesskey="F" class="infraLabelObrigatorio">Cargo / <span class="infraTeclaAtalho">F</span>unção:</label>
                <select id="selCargoFuncao" name="selCargoFuncao" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                    <?= $strItensSelCargoFuncao ?>
                </select>
            </div>
            <br />
            <div id="divAutenticacao" class="infraAreaDados" style="height:3.5em; top:15%">
                <label id="lblSenha" for="pwdSenha" accesskey="S" class="infraLabelRadio infraLabelObrigatorio" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"><span class="infraTeclaAtalho">S</span>enha:</label>
                <input type="password" id="pwdSenha" name="pwdSenha" autocomplete="off" class="infraText"  value="" onchange="validarSenhaConfiguracao()" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
            </div>
        </fieldset>

        <input type="hidden" id="hdnIdProtocolos" name="hdnIdProtocolos" value="<?= $strIdProtocolos; ?>"/>
    </form>
</div>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>