<?
/**
 * Join Tecnologia
 */
try {
    require_once dirname(__FILE__) . '/../../SEI.php';
    
    session_start();
    
    define('PEN_RECURSO_ATUAL', 'pen_parametros_configuracao');
    define('PEN_PAGINA_TITULO', 'Par�metros de Configura��o do M�dulo de Tramita��es PEN');

    $objPagina = PaginaSEI::getInstance();
    $objBanco = BancoSEI::getInstance();
    $objSessao = SessaoSEI::getInstance();
    
    $objSessao->validarPermissao('pen_parametros_configuracao');
    
    $objPENParametroDTO = new PenParametroDTO();
    $objPENParametroDTO->retTodos();
    
    $objPENParametroRN = new PENParametroRN();
    $retParametros = $objPENParametroRN->listar($objPENParametroDTO);
    
    if ($objPENParametroDTO===null){
        throw new PENException("Registros n�o encontrados.");
    }
    
    switch ($_GET['acao']) {
        case 'pen_parametros_configuracao_salvar':
            try {
                $objPENParametroRN = new PENParametroRN();
            
                if (!empty(count($_POST['parametro']))) {
                    foreach ($_POST['parametro'] as $nome => $valor) {
                        $objPENParametroDTO = new PENParametroDTO();
                        $objPENParametroDTO->setStrNome($nome);
                        $objPENParametroDTO->retStrNome();
                        
                        if($objPENParametroRN->contar($objPENParametroDTO) > 0) {
                            $objPENParametroDTO->setStrValor($valor);
                            $objPENParametroRN->alterar($objPENParametroDTO);
                        }
                    }
                }
            
            } catch (Exception $e) {
                $objPagina->processarExcecao($e);
            }
            header('Location: ' . $objSessao->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;

        case 'pen_parametros_configuracao':
            $strTitulo = 'Par�metros de Configura��o do M�dulo de Tramita��es PEN';
            break;

        default:
            throw new PENException("A��o '" . $_GET['acao'] . "' n�o reconhecida.");
    }

} catch (Exception $e) {
    $objPagina->processarExcecao($e);
}

//Monta os bot�es do topo
if ($objSessao->verificarPermissao('pen_parametros_configuracao_alterar')) {
    $arrComandos[] = '<button type="submit" id="btnSalvar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
}
$arrComandos[] = '<button type="button" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . $objPagina->formatarXHTML($objSessao->assinarLink('controlador.php?acao=pen_parametros_configuracao&acao_origem=' . $_GET['acao'])) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';   

$objPagina->montarDocType();
$objPagina->abrirHtml();
$objPagina->abrirHead();
$objPagina->montarMeta();
$objPagina->montarTitle($objPagina->getStrNomeSistema() . ' - ' . $strTitulo);
$objPagina->montarStyle();
$objPagina->abrirStyle();
?>
<?
$objPagina->fecharStyle();
$objPagina->montarJavaScript();
$objPagina->abrirJavaScript();
?>

function inicializar(){
    if ('<?= $_GET['acao'] ?>'=='pen_parametros_configuracao_selecionar'){
        infraReceberSelecao();
        document.getElementById('btnFecharSelecao').focus();
    }else{
        document.getElementById('btnFechar').focus();
    }
    infraEfeitoImagens();
    infraEfeitoTabelas();
}

<?
$objPagina->fecharJavaScript();
$objPagina->fecharHead();
$objPagina->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<style>
    .input-field-input {
        width: 35%;
        margin-bottom: 8px;
        margin-top: 2px;
    }
    .input-field {
        margin-bottom: 8px;
        margin-top: 2px;
    }
</style>

<form id="frmInfraParametroCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=$objSessao->assinarLink('controlador.php?acao='.$_GET['acao'].'_salvar&acao_origem='.$_GET['acao'])?>">
    <?
    $objPagina->montarBarraComandosSuperior($arrComandos);
    foreach ($retParametros as $parametro) {
        
        //Constroi o label
        ?> <label id="lbl<?php echo $parametro->getStrNome(); ?>" for="txt<?php echo $parametro->getStrNome(); ?>" accesskey="N" class="infraLabelObrigatorio"><?php echo $parametro->getStrDescricao(); ?>:</label><br> <?php
        
        //Constroi o campo de valor
        switch ($parametro->getStrNome()) {
            case 'PEN_SENHA_CERTIFICADO_DIGITAL':
                echo '<input type="password" id="PARAMETRO_'.$parametro->getStrNome().'" name="parametro['.$parametro->getStrNome().']" class="infraText input-field-input" value="'.$objPagina->tratarHTML($parametro->getStrValor()).'" onkeypress="return infraMascaraTexto(this,event);" tabindex="'.$objPagina->getProxTabDados().'" maxlength="100" /><br>';
                break;
            
            case 'PEN_ENVIA_EMAIL_NOTIFICACAO_RECEBIMENTO':
                echo '<select id="PARAMETRO_PEN_ENVIA_EMAIL_NOTIFICACAO_RECEBIMENTO" name="parametro[PEN_ENVIA_EMAIL_NOTIFICACAO_RECEBIMENTO]" class="input-field" >';
                echo '    <option value="S" ' . ($parametro->getStrValor() == 'S' ? 'selected="selected"' : '') . '>Sim</option>';
                echo '    <option value="N" ' . ($parametro->getStrValor() == 'N' ? 'selected="selected"' : '') . '>N�o</option>';
                echo '<select>';
                break;
            
//            case 'PEN_TIPO_PROCESSO_EXTERNO':
//                echo '<select name="PEN_TIPO_PROCESSO_EXTERNO" class="input-field" >';
//                
//                echo '<select>';
//                break;
//                
//            case 'PEN_UNIDADE_GERADORA_DOCUMENTO_RECEBIDO':
//                echo '<select name="PEN_UNIDADE_GERADORA_DOCUMENTO_RECEBIDO" class="input-field" >';
//                
//                echo '<select>';
//                break;

            default:
                echo '<input type="text" id="PARAMETRO_'.$parametro->getStrNome().'" name="parametro['.$parametro->getStrNome().']" class="infraText input-field-input" value="'.$objPagina->tratarHTML($parametro->getStrValor()).'" onkeypress="return infraMascaraTexto(this,event);" tabindex="'.$objPagina->getProxTabDados().'" maxlength="100" /><br>';
                break;
        }
        echo '<br>';
    }
    ?>
</form>

<?
$objPagina->fecharBody();
$objPagina->fecharHtml();
?>