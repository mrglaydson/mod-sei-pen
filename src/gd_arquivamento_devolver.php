<?
try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    //////////////////////////////////////////////////////////////////////////////
    //InfraDebug::getInstance()->setBolLigado(false);
    //InfraDebug::getInstance()->setBolDebugInfra(true);
    //InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

    SessaoSEI::getInstance()->validarLink();
    PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);
    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    switch ($_GET['acao']) {

        case 'gd_arquivamento_devolver':
            $strTitulo = 'Devolver Arquivamento';

            if($_POST['sbmDevolver']){
                $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                $objMdGdArquivamentoDTO->setNumIdArquivamento((int) $_REQUEST['id_arquivamento']);
                $objMdGdArquivamentoDTO->setStrObservacaoDevolucao($_REQUEST['txtObservacaoDevolucao']);
                $objMdGdArquivamentoDTO->retDblIdProcedimento();
                $objMdGdArquivamentoDTO->retNumIdUnidadeCorrente();
                
                // Muda a situação do arquivamento para editado
                $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
                $objMdGdArquivamentoRN->devolverArquivamento($objMdGdArquivamentoDTO);
                
                //reabir processo
                
                // vamos desabilitar a busca por esse campo, caso contrario da erro no Oracle
                $objMdGdArquivamentoDTO->unSetStrObservacaoDevolucao();
                
                $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
                $objMdGdArquivamentoRN->editarArquivamento($objMdGdArquivamentoDTO);
                
                // try {

                //     $objMdGdArquivamentoDTO=$objMdGdArquivamentoRN->consultar($objMdGdArquivamentoDTO);
                    
                //     SessaoSEI::getInstance()->validarPermissao('procedimento_reabrir');
    
                //     $idProcedimento = $objMdGdArquivamentoDTO->getDblIdProcedimento();
                    
                //     $objReabrirProcedimentoDTO = new ReabrirProcessoDTO();
                //     $objReabrirProcedimentoDTO->setDblIdProcedimento($idProcedimento);
                //     $objReabrirProcedimentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                //     $objReabrirProcedimentoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());

                //     $objProcedimentoRN = new ProcedimentoRN();
                //     $objProcedimentoRN->reabrirRN0966($objReabrirProcedimentoDTO);
            
                // } catch (Exception $e) {
                //     throw new InfraException("Erro ao reabrir processo");
                // }



            }
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }
} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

$arrComandos[] = '<button type="submit" accesskey="S" name="sbmDevolver" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
$arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="window.opener.location.reload(); window.close();" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';


PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->montarJavaScript(); 
PaginaSEI::getInstance()->abrirJavaScript(); ?>

<? if($_POST['sbmDevolver']){ ?>
    window.opener.location.reload(); 
    window.close();
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript(); 
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo);
?>
<form id="frmDevolver" method="post" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . '&id_arquivamento=' . $_GET['id_arquivamento']) ?>">
    <?
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    PaginaSEI::getInstance()->abrirAreaDados('20em');
    ?>
    <textarea id="txtObservacaoDevolucao" style="width: 657px;" rows="10" name="txtObservacaoDevolucao" rows="<?= PaginaSEI::getInstance()->isBolNavegadorFirefox() ? '2' : '3' ?>" class="infraTextArea" onkeypress="return infraLimitarTexto(this, event, 500);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"></textarea>
    <?
    PaginaSEI::getInstance()->fecharAreaDados();
    ?>
</form>

<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>