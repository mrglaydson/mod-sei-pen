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

    if (isset($_GET['id_procedimento'])) {
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

    $strTitulo = 'Concluir e Arquivar Processo';
    $objMdArquivamentoRN = new MdGdArquivamentoRN();

    switch ($_GET['acao']) {
        case 'gd_procedimento_arquivar':

            $arrComandos[] = '<button type="button" accesskey="S" name="sbmSalvar" id="sbmSalvar" value="Salvar" class="infraButton"  onclick="infraAbrirBarraProgresso(this.form,\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']).'\', 600, 250);" ><span class="infraTeclaAtalho">S</span>alvar</button>';

            if ($_GET['acao_origem'] == 'gd_pendencia_arquivamento_listar') {
                $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_pendencia_arquivamento_listar&acao_origem=' . $_GET['acao'] . $strParametros) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
            } else if($_GET['acao_origem'] == 'procedimento_controlar') {
                $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem=' . $_GET['acao'] . $strParametros) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
            } else {
                $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . $strParametros) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
            }
     
            if ($_GET['acao_origem'] == 'gd_pendencia_arquivamento_listar') {
                $arrProtocolosOrigem = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            } else if($_GET['acao_origem'] == 'procedimento_controlar') {
                $arrProtocolosOrigem = array_merge(PaginaSEI::getInstance()->getArrStrItensSelecionados('Gerados'), PaginaSEI::getInstance()->getArrStrItensSelecionados('Recebidos'), PaginaSEI::getInstance()->getArrStrItensSelecionados('Detalhado'));
            } else {
                $arrProtocolosOrigem = array($_GET['id_procedimento']);
            }
        
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_GET['acao_origem'] == 'gd_procedimento_arquivar') {
                try {
                    $arrProtocolosOrigem = explode(',', $_POST['hdnIdProtocolos']);

                    ini_set('max_execution_time','0');
                    ini_set('memory_limit','2048M');
                    
                    PaginaSEI::getInstance()->prepararBarraProgresso2($strTitulo);
                    $prb = InfraBarraProgresso2::newInstance('ArquivarProcessos', array('cor_fundo'=>'#5c9ccc','cor_borda'=>'#4297d7'));
                    $prb->setStrRotulo('Aguarde o Arquivamento dos Processos');
                    $prb->setNumMin(0);
                    $prb->setNumMax(count($arrProtocolosOrigem));
                    
                    $objAssinaturaDTO = new AssinaturaDTO();
                    $objAssinaturaDTO->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
                    $objAssinaturaDTO->setNumIdOrgaoUsuario($_POST['selOrgao']);
                    $objAssinaturaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                    $objAssinaturaDTO->setNumIdContatoUsuario(SessaoSEI::getInstance()->getNumIdContextoUsuario());
                    $objAssinaturaDTO->setStrSenhaUsuario($_POST['pwdSenha']);
                    $objAssinaturaDTO->setStrCargoFuncao($_POST['selCargoFuncao']);
                    

                    foreach ($arrProtocolosOrigem as $key => $numIdProcedimento) {
                        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                        $objMdGdArquivamentoDTO->setDblIdProcedimento($numIdProcedimento);
                        $objMdGdArquivamentoDTO->setNumIdJustificativa($_POST['selJustificativa']);
                        $objMdGdArquivamentoDTO->setObjAssinaturaDTO($objAssinaturaDTO);

                        if(isset($_POST['txtDataArquivamento'])){
                            $objMdGdArquivamentoDTO->setDthDataArquivamento($_POST['txtDataArquivamento'].' 00:00:00');
                        }
                        
                        if ($_POST['hdnOrigem'] == 'gd_pendencia_arquivamento_listar') {
                            $objMdGdArquivamentoDTO->reabrirProcedimentoGeracao = true;
                        }
                        $objMdArquivamentoRN->arquivar($objMdGdArquivamentoDTO);
                        
                       $prb->setStrRotulo('Arquivando '.($key + 1) .' de '.count($arrProtocolosOrigem));
                       $prb->moverProximo();
                    }

                    PaginaSEI::getInstance()->setStrMensagem('Arquivamento realizado com sucesso!');

                    if ($_POST['hdnOrigem'] == 'gd_pendencia_arquivamento_listar') {
                       PaginaSEI::getInstance()->finalizarBarraProgresso2(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_pendencia_arquivamento_listar&acao_origem=' . $_GET['acao']), true);
                    } else if ($_POST['hdnOrigem'] == 'procedimento_controlar') {
                        PaginaSEI::getInstance()->finalizarBarraProgresso2(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem=' . $_GET['acao']), true);
                    } else {
                      PaginaSEI::getInstance()->finalizarBarraProgresso2(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_arquivar&acao_origem=' . $_GET['acao'] . '&id_procedimento='.$arrProtocolosOrigem[0].'&arvore=1&redirect=true'), true);
                    }
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }

            // Verifica se não existem andamentos abertos em mais de uma unidade nos processos selecionados
            $arrProtocolosAndamentosAbertos = [];
            $strProtocolosAndamentosAbertos = null;

            foreach($arrProtocolosOrigem as $k => $protocoloValidacao) {
                $objAtividadeDTO = new AtividadeDTO();
                $objAtividadeDTO->setDistinct(true);
                $objAtividadeDTO->setDblIdProtocolo($protocoloValidacao);
                $objAtividadeDTO->setDthConclusao(null);
                $objAtividadeDTO->retNumIdUnidade();
                $objAtividadeDTO->retStrProtocoloFormatadoProtocolo();
        
                $objAtividadeRN = new AtividadeRN();
                $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

                if(count($arrObjAtividadeDTO) > 1) {
                    $arrProtocolosAndamentosAbertos[$protocoloValidacao] = $arrObjAtividadeDTO[0]->getStrProtocoloFormatadoProtocolo();
                }
            }

            if($arrProtocolosAndamentosAbertos) {
                $strProtocolosAndamentosAbertos = implode(',', $arrProtocolosAndamentosAbertos);
            }

            // Verifica se algum dos processos não está anexado ou não possuí anexos
            $arrProtocolosAnexos = [];
            $strProtocolosAnexos = null;

            foreach($arrProtocolosOrigem as $k => $protocoloValidacao) {
                $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
                $objRelProtocoloProtocoloDTO->adicionarCriterio(array('IdProtocolo1','IdProtocolo2'),
                                                                    array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
                                                                    array($protocoloValidacao, $protocoloValidacao),
                                                                    InfraDTO::$OPER_LOGICO_OR);
                $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
                $objRelProtocoloProtocoloDTO->retDblIdProtocolo1();
                $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
                $objRelProtocoloProtocoloDTO->retStrProtocoloFormatadoProtocolo1();
                $objRelProtocoloProtocoloDTO->retStrProtocoloFormatadoProtocolo2();
                

                $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
                $arrObjRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO);
                
                if($arrObjAtividadeDTO) {
                    foreach($arrObjRelProtocoloProtocoloDTO as $objRelProtocoloProtocoloDTO) {
                        if($protocoloValidacao == $objRelProtocoloProtocoloDTO->getDblIdProtocolo1()) {
                            $arrProtocolosAnexos[$objRelProtocoloProtocoloDTO->getDblIdProtocolo1()] = $objRelProtocoloProtocoloDTO->getStrProtocoloFormatadoProtocolo1();
                        }
                        
                        if($protocoloValidacao == $objRelProtocoloProtocoloDTO->getDblIdProtocolo2()) {
                            $arrProtocolosAnexos[$objRelProtocoloProtocoloDTO->getDblIdProtocolo2()] = $objRelProtocoloProtocoloDTO->getStrProtocoloFormatadoProtocolo2();
                        }
                    }
                }
            }

            if($arrProtocolosAnexos) {
                $strProtocolosAnexos = implode(', ', $arrProtocolosAnexos);
            }
            
            // Remove os processos que possuem anexos e/ou estão abertos em mais de uma unidade
            // foreach($arrProtocolosAnexos as $k => $protocoloAnexo){
                foreach($arrProtocolosOrigem as $x => $protocoloOrigem){
                    if($protocoloOrigem == $k){
                        unset($arrProtocolosOrigem[$x]);
                    }
                }
            // }


            foreach($arrProtocolosAndamentosAbertos as $k => $protocoloAndamento){
                foreach($arrProtocolosOrigem as $x => $protocoloOrigem){
                    if($protocoloOrigem == $k){
                        unset($arrProtocolosOrigem[$x]);
                    }
                }
            }

            // Verifica o último andamento de cada processo
            $arrIdsProtocolosValidacao = [];
            foreach($arrProtocolosOrigem as $k => $protocoloValidacao) {
                $arrIdsProtocolosValidacao[$k] = 0;

                // Busca os documentos vinculados
                $objDocumentoDTO = new DocumentoDTO();
                $objDocumentoDTO->setDblIdProcedimento($protocoloValidacao);
                $objDocumentoDTO->retDblIdDocumento();

                $objDocumentoRN = new DocumentoRN();
                $arrObjDocumentoDTO = $objDocumentoRN->listarRN0008($objDocumentoDTO);

                $arrIdsProtocolos = [$protocoloValidacao];

                if($arrObjDocumentoDTO){
                    foreach($arrObjDocumentoDTO as $objDocumentoDTO) {
                        $arrIdsProtocolos[] = $objDocumentoDTO->getDblIdDocumento();
                    }
                }

                $objAtividadeDTO = new AtividadeDTO();
                $objAtividadeDTO->setDblIdProtocolo($arrIdsProtocolos, InfraDTO::$OPER_IN);
                $objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_ASC);
                $objAtividadeDTO->retDthAbertura();

                $objAtividadeRN = new AtividadeRN();
                $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

                $dthPrimeiroAndamento = $arrObjAtividadeDTO[0]->getDthAbertura();
                $dthPrimeiroAndamento = explode(' ', $dthPrimeiroAndamento);
                $dthPrimeiroAndamento = explode('/', $dthPrimeiroAndamento[0]);

                $arrIdsProtocolosValidacao[$k] = $dthPrimeiroAndamento[2].$dthPrimeiroAndamento[1].$dthPrimeiroAndamento[0];
            }

            
            arsort($arrIdsProtocolosValidacao);
            $arrIdsProtocolosValidacao = array_values($arrIdsProtocolosValidacao);
            $dataMinima = $arrIdsProtocolosValidacao[0];
            $dataMaxima = date('Ymd');


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

    var validSenha = false;
    var validConfiguracao = false;
    var processosAnexos = '<?php echo $strProtocolosAnexos; ?>';
    var processosAndamentosAbertos = '<?php echo $strProtocolosAndamentosAbertos; ?>';

    // if(processosAnexos) {
    //     alert('Os processos ' + processosAnexos + ' estão anexados ou possuem anexos, portanto não podem ser arquivados.');
    // }

    if(processosAndamentosAbertos) {
        alert('Os processos ' + processosAndamentosAbertos + ' estão com andamentos abertos.');
    }

    function inicializar() {
        document.getElementById('sbmSalvar').focus();
        infraEfeitoTabelas();
    }

    function OnSubmitForm() {
        return validarConcluirArquivar();
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

            var dataMinima = '<?php echo $dataMinima ? $dataMinima : ''; ?>';
            dataMinima = new Number(dataMinima);

            var dataMaxima = <?php echo $dataMaxima; ?>;
            dataMaxima = new Number(dataMaxima);

            var dataArquivamento = document.getElementById('txtDataArquivamento').value ;
            dataArquivamento = dataArquivamento.split('/');
            dataArquivamento = dataArquivamento[2] + dataArquivamento[1] + dataArquivamento[0];
            dataArquivamento = new Number(dataArquivamento);

            if(dataArquivamento < dataMinima) {
                alert('Não existe andamento registrado no(s) processo(s) selecionado(s) na data de arquivamento informada.');
                return false;
            }

            if(dataArquivamento > dataMaxima) {
                alert('Não é permitido informar um arquivamento com uma data futura.');
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

        if (document.getElementById('hdnTotalCondicionantes').value > 0) {
            if (confirm('Este processo possui condicionante de arquivamento. Deseja realizar o arquivamento?')) {
                return true;
            } else {
                return false;
            }
        }
        
   
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
          action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'].$strParametros) ?>">
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
                <img id="imgCalDataArquivamento" title="Selecionar Data de Arquivamento" alt="Selecionar Data de Arquivamento" src="/infra_css/svg/calendario.svg" class="infraImg" onclick="infraCalendario('txtDataArquivamento', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    
            </div>

            <input type="hidden" id="hdnTotalCondicionantes" name="hdnTotalCondicionantes" value="<?= $numTotalCondicionantes ?>"/>
        </fieldset>


        <fieldset class="infraFieldset" id="fieldsetDadosAssinatura" style="height:25em;">
            <legend class="infraLegend">Dados da Assinatura</legend>
            <div style="padding: 1em;"
            <p>Dados para assinatura do despacho de arquivamento</p>
            <div id="divOrgao" class="infraAreaDados" style="height:4.5em;">
                <label id="lblOrgao" for="selOrgao" accesskey="r" class="infraLabelObrigatorio">Ó<span class="infraTeclaAtalho">r</span>gão do Assinante:</label>
                <select id="selOrgao" name="selOrgao" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                    <?= $strItensSelOrgaos ?>
                </select>
            </div>

            <div id="divUsuario" class="infraAreaDados" style="height:4.5em;">
                <label id="lblUsuario" for="txtUsuario" accesskey="e" class="infraLabelObrigatorio">Assinant<span class="infraTeclaAtalho">e</span>:</label>
                <input type="text" style="margin-left: 0.5em;" id="txtUsuario" name="txtUsuario" class="infraText" value="<?= SessaoSEI::getInstance()->getStrNomeUsuario() ?>" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" disabled />
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
                <input type="password" id="pwdSenha" name="pwdSenha" autocomplete="off" class="infraText"  value="" onchange="validarSenhaConfiguracao()" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
            </div>
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