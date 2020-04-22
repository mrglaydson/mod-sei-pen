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
    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(false);
    InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

    SessaoSEI::getInstance()->validarLink();
    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    $objMdGdJustificativaDTO = new MdGdJustificativaDTO();

    $strDesabilitar = '';

    $arrComandos = array();

    switch($_GET['acao']){
        case 'gd_justificativa_cadastrar':
            $strTitulo = 'Nova Justificativa de Arquivamento e Desarquivamento';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarJustificativa" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            $objMdGdJustificativaDTO->setNumIdJustificativa(null);
            $objMdGdJustificativaDTO->setStrNome($_POST['txtNome']);
            $objMdGdJustificativaDTO->setStrStaTipo($_POST['selTipo']);
            $objMdGdJustificativaDTO->setStrDescricao($_POST['txaDescricao']);

            if (isset($_POST['sbmCadastrarJustificativa'])) {
                try{
                    $objMdGdJustificativaRN = new MdGdJustificativaRN();
                    $objMdGdJustificativaDTO = $objMdGdJustificativaRN->cadastrar($objMdGdJustificativaDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Justificativa "'.$objMdGdJustificativaDTO->getStrNome().'" cadastrada com sucesso.');
                    header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_justificativa='.$objMdGdJustificativaDTO->getNumIdJustificativa().PaginaSEI::getInstance()->montarAncora($objMdGdJustificativaDTO->getNumIdJustificativa())));
                    die;
                }catch(Exception $e){
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'gd_justificativa_alterar':
            $strTitulo = 'Alterar Justificativa de Arquivamento e Desarquivamento';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarJustificativa" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $strDesabilitar = 'disabled="disabled"';

            if (isset($_GET['id_justificativa'])){
                $objMdGdJustificativaDTO->setNumIdJustificativa($_GET['id_justificativa']);
                $objMdGdJustificativaDTO->retTodos();

                $objMdGdJustificativaRN = new MdGdJustificativaRN();
                $objMdGdJustificativaDTO = $objMdGdJustificativaRN->consultar($objMdGdJustificativaDTO);
                if ($objMdGdJustificativaDTO==null){
                    throw new InfraException("Registro não encontrado.");
                }
            } else {
                $objMdGdJustificativaDTO->setNumIdJustificativa($_POST['hdnIdJustificativa']);
                $objMdGdJustificativaDTO->setStrNome($_POST['txtNome']);
                $objMdGdJustificativaDTO->setStrStaTipo($_POST['selTipo']);
                $objMdGdJustificativaDTO->setStrDescricao($_POST['txaDescricao']);
            }

            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objMdGdJustificativaDTO->getNumIdJustificativa())).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            if (isset($_POST['sbmAlterarJustificativa'])) {
                try{
                    $objMdGdJustificativaRN = new MdGdJustificativaRN();
                    $objMdGdJustificativaRN->alterar($objMdGdJustificativaDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Justificativa "'.$objMdGdJustificativaDTO->getStrNome().'" alterada com sucesso.');
                    header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objMdGdJustificativaDTO->getNumIdJustificativa())));
                    die;
                }catch(Exception $e){
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'gd_justificativa_consultar':
            $strTitulo = 'Consultar Justificativa de Arquivamento e Desarquivamento';
            $arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($_GET['id_justificativa'])).'\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
            $objMdGdJustificativaDTO->setNumIdJustificativa($_GET['id_justificativa']);
            $objMdGdJustificativaDTO->retTodos();

            $objMdGdJustificativaRN = new MdGdJustificativaRN();
            $objMdGdJustificativaDTO = $objMdGdJustificativaRN->consultar($objMdGdJustificativaDTO);
            if ($objMdGdJustificativaDTO===null){
                throw new InfraException("Registro não encontrado.");
            }
            break;

        default:
            throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
    }


}catch(Exception $e){
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>

    #lblNome {position:absolute;left:0%;top:0%;width:50%;}
    #txtNome {position:absolute;left:0%;top:6%;width:50%;}

    #lblTipo {position:absolute;left:0%;top:16%;width:20%;}
    #selTipo {position:absolute;left:0%;top:22%;width:20%;}

    #lblDescricao {position:absolute;left:0%;top:30%;width:30%;}
    #txaDescricao {position:absolute;left:0%;top:36%;width:40%;height: 20%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
    function inicializar(){
        if ('<?=$_GET['acao']?>'=='gd_justificativa_cadastrar'){
            document.getElementById('txtNome').focus();

        } else if ('<?=$_GET['acao']?>'=='gd_justificativa_consultar'){
            infraDesabilitarCamposAreaDados();

        }else{
            document.getElementById('btnCancelar').focus();
        }
        infraEfeitoTabelas();
    }

    function validarCadastro() {
        if (infraTrim(document.getElementById('txtNome').value)=='') {
            alert('Informe o Nome.');
            document.getElementById('txtNome').focus();
            return false;
        }

        if (infraTrim(document.getElementById('selTipo').value)=='') {
            alert('Informe o Tipo.');
            document.getElementById('selTipo').focus();
            return false;
        }

        if (infraTrim(document.getElementById('txaDescricao').value)=='') {
            alert('Informe a Descrição.');
            document.getElementById('txaDescricao').focus();
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
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
    <form id="frmPaisJustificativa" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
        <?
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        //PaginaSEI::getInstance()->montarAreaValidacao();
        PaginaSEI::getInstance()->abrirAreaDados('30em');
        ?>
        <label id="lblNome" for="txtNome" accesskey="p" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">N</span>ome:</label>
        <input type="text" id="txtNome" name="txtNome" class="infraText" value="<?=PaginaSEI::tratarHTML($objMdGdJustificativaDTO->getStrNome());?>" onkeypress="return infraMascaraTexto(this,event,50);" maxlength="50" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

        <label id="lblTipo" for="selTipo" accesskey="p" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">T</span>ipo:</label>
        <select name="selTipo" id="selTipo" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value="<?= $objMdGdJustificativaDTO->getStrStaTipo(); ?>">
            <option value=""></option>
            <option value="<?= MdGdJustificativaRN::$STA_TIPO_ARQUIVAMENTO ?>"    <?= $objMdGdJustificativaDTO->getStrStaTipo() == MdGdJustificativaRN::$STA_TIPO_ARQUIVAMENTO ? 'selected' : '' ?>>Arquivamento</option>
            <option value="<?= MdGdJustificativaRN::$STA_TIPO_DESARQUIVAMENTO ?>" <?= $objMdGdJustificativaDTO->getStrStaTipo() == MdGdJustificativaRN::$STA_TIPO_DESARQUIVAMENTO ? 'selected' : '' ?>>Desarquivamento</option>
        </select>

        <label id="lblDescricao" for="txaDescricao" accesskey="d" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">D</span>escrição:</label>
        <textarea id="txaDescricao" name="txaDescricao" rows="3"  class="infraTextarea"  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><?= $objMdGdJustificativaDTO->getStrDescricao(); ?></textarea>

        <input type="hidden" id="hdnIdJustificativa" name="hdnIdJustificativa" value="<?=$objMdGdJustificativaDTO->getNumIdJustificativa();?>" />
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