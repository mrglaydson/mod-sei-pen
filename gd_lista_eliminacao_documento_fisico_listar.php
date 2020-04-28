<?
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

    $strTitulo = 'Eliminação de Documentos Físicos';

    switch ($_GET['acao']) {

        case 'gd_lista_eliminacao_documentos_fisicos_listar':
            PaginaSEI::getInstance()->salvarCamposPost(array('txtPeriodoEmissaoDe', 'txtPeriodoEmissaoAte', 'txtAnoLimiteDe', 'txtAnoLimiteAte'));

            if (!$_GET['id_listagem_eliminacao']) {
                header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_listar&acao_origem=' . $_GET['acao']));
            }

            // Busca os dados da listagem de eliminação
            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setNumIdListaEliminacao($_GET['id_listagem_eliminacao']);
            $objMdGdListaEliminacaoDTO->retNumAnoLimiteInicio();
            $objMdGdListaEliminacaoDTO->retNumAnoLimiteFim();
            $objMdGdListaEliminacaoDTO->retStrNumero();
            $objMdGdListaEliminacaoDTO->retStrProtocoloProcedimentoEliminacaoFormatado();
            
            $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
            $objMdGdListaEliminacaoDTO = $objMdGdListaEliminacaoRN->consultar($objMdGdListaEliminacaoDTO);
            
            // Busca os processos da listagem de eliminação
            $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
            $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($_GET['id_listagem_eliminacao']);
            $objMdGdListaElimProcedimentoDTO->retDblIdProcedimento();

            $objMdGdListaElimProcedimentoRN = new MdGdListaElimProcedimentoRN();
            $arrObjMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoRN->listar($objMdGdListaElimProcedimentoDTO);
            $arrIdDocumento = [0];

            if ($arrObjMdGdListaElimProcedimentoDTO) {
                $arrIdProcedimento = explode(',', InfraArray::implodeArrInfraDTO($arrObjMdGdListaElimProcedimentoDTO, 'IdProcedimento'));

                $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
                $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($arrIdProcedimento, InfraDTO::$OPER_IN);
                $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();

                $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
                $arrObjRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO);

                if ($arrObjRelProtocoloProtocoloDTO) {
                    $arrIdDocumento = explode(',', InfraArray::implodeArrInfraDTO($arrObjRelProtocoloProtocoloDTO, 'IdProtocolo2'));
                }
            }
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $bolAcaoEliminarDocumentoFisico = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_documentos_fisicos_eliminar');

    $arrComandos = array();

    if ($bolAcaoEliminarDocumentoFisico) {
        $strLinkConfirmarEliminacao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_documentos_fisicos_eliminar&id_listagem_eliminacao=' . $_GET['id_listagem_eliminacao'] . '&acao_origem=' . $_GET['acao']);
        $arrComandos[] = '<button type="button" accesskey="P" id="btnConfirmarEliminacao" value="Confirmar Eliminação" onclick="acaoConfirmarEliminacao(\'' . $strLinkConfirmarEliminacao . '\');" class="infraButton"><span class="infraTeclaAtalho">C</span>onfirmar Eliminação</button>';
    }

    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_listar&acao_origem=' . $_GET['acao']) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

    // Faz a busca de arquivamentos
    $objArquivamentoRN = new ArquivamentoRN();
    $objArquivamentoDTO = new ArquivamentoDTO();
    $objArquivamentoDTO->retDblIdProtocoloDocumento();
    $objArquivamentoDTO->retDblIdProcedimentoDocumento();
    $objArquivamentoDTO->retStrProtocoloFormatadoProcedimento();
    $objArquivamentoDTO->retStrNomeTipoProcedimento();
    $objArquivamentoDTO->retStrProtocoloFormatadoDocumento();
    $objArquivamentoDTO->retStrNomeTipoLocalizador();
    $objArquivamentoDTO->retNumIdLocalizador();
    $objArquivamentoDTO->setDblIdProtocoloDocumento($arrIdDocumento, InfraDTO::$OPER_IN);

    PaginaSEI::getInstance()->prepararPaginacao($objArquivamentoDTO);

    $arrObjArquivamentoDTO = $objArquivamentoRN->listar($objArquivamentoDTO);

    PaginaSEI::getInstance()->processarPaginacao($objArquivamentoDTO);
    $numRegistros = count($arrObjArquivamentoDTO);

    if ($numRegistros > 0) {
        $strResultado = '';

        $strSumarioTabela = 'Lista de Processos/Documentos';
        $strCaptionTabela = 'Lista de Processos/Documentos';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO, 'Processo', 'ProtocoloFormatadoProcedimento', $arrObjArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="19%">' . PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO, 'Tipo de Processo', 'NomeTipoProcedimento', $arrObjArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO, 'Unidade', 'ProtocoloFormatadoDocumento', $arrObjArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Local</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Tipo de Localizador</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Localizador</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Tipo de Suporte</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Status</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        $objLocalizadorRN = new LocalizadorRN();
        $objMdGdDocumentoFisicoElimRN = new MdGdDocumentoFisicoElimRN();

        for ($i = 0; $i < $numRegistros; $i++) {

            // Busca os dados do localizador do arquivamento
            $objLocalizadorDTO = new LocalizadorDTO();
            $objLocalizadorDTO->setNumIdLocalizador($arrObjArquivamentoDTO[$i]->getNumIdLocalizador());
            $objLocalizadorDTO->retNumIdLocalizador();
            $objLocalizadorDTO->retStrNomeLugarLocalizador();
            $objLocalizadorDTO->retStrNomeTipoSuporte();
            $objLocalizadorDTO->retStrIdentificacao();
            $objLocalizadorDTO->retStrStaEstado();
            $objLocalizadorDTO->retStrNomeTipoLocalizador();
            $objLocalizadorDTO->retStrSiglaTipoLocalizador();
            $objLocalizadorDTO->retNumSeqLocalizador();
            $objLocalizadorDTO->retNumIdUnidade();
            $objLocalizadorDTO->retStrSiglaUnidadeLocalizador();

            $objLocalizadorDTO = $objLocalizadorRN->consultarRN0619($objLocalizadorDTO);

            // Verifica se o documento foi eliminado
            $objMdGdDocumentoFisicoElimDTO = new MdGdDocumentoFisicoElimDTO();
            $objMdGdDocumentoFisicoElimDTO->setDblIdDocumento($arrObjArquivamentoDTO[$i]->getDblIdProtocoloDocumento());
            $countDocumentoFisicoEliminado = $objMdGdDocumentoFisicoElimRN->contar($objMdGdDocumentoFisicoElimDTO);

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            if ($countDocumentoFisicoEliminado == 0) {
                $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjArquivamentoDTO[$i]->getDblIdProtocoloDocumento(), $arrObjArquivamentoDTO[$i]->getStrProtocoloFormatadoDocumento()) . '</td>';
            } else {
                $strResultado .= '<td valign="top"></td>';
            }

            $strResultado .= '<td>';
            $strResultado .= '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjArquivamentoDTO[$i]->getDblIdProcedimentoDocumento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjArquivamentoDTO[$i]->getStrProtocoloFormatadoProcedimento() . '</a>';
            $strResultado .= '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrProtocoloFormatadoDocumento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($objLocalizadorDTO->getStrNomeLugarLocalizador()) . '</td>';     
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($objLocalizadorDTO->getStrIdentificacao()) . '</td>';

            //$strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrNomeTipoLocalizador()) . '</td>';
            $strClassLocalizador = '';
            if ($objLocalizadorDTO->getStrStaEstado() == LocalizadorRN::$EA_ABERTO) {
                $strClassLocalizador = ' localizadorAberto';
            } else {
                $strClassLocalizador = ' localizadorFechado';
            }

            if ($objLocalizadorDTO->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual()) {
                $strResultado .= '<td><a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=localizador_protocolos_listar&acao_origem=' . $_GET['acao'] . '&id_localizador=' . $objLocalizadorDTO->getNumIdLocalizador()) . '" target="_blank" class="linkFuncionalidade' . $strClassLocalizador . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '" title="' . PaginaSEI::tratarHTML($objLocalizadorDTO->getStrNomeTipoLocalizador() . ' / ' . $objLocalizadorDTO->getStrNomeLugarLocalizador() . $strTitleLocalizador) . '">'  . LocalizadorINT::montarIdentificacaoRI1132($objLocalizadorDTO->getStrSiglaTipoLocalizador(), $objLocalizadorDTO->getNumSeqLocalizador()) . '</a></td>';
            }else{
                $strResultado .= '<td><a href="javascript:void(0);" onclick="alert(\'Localizador pertence a unidade '.$objLocalizadorDTO->getStrSiglaUnidadeLocalizador().'.\');" class="ancoraSigla" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '" title="' . PaginaSEI::tratarHTML('Localizador pertence a unidade '.$objLocalizadorDTO->getStrSiglaUnidadeLocalizador()) .'">' . LocalizadorINT::montarIdentificacaoRI1132($objLocalizadorDTO->getStrSiglaTipoLocalizador(), $objLocalizadorDTO->getNumSeqLocalizador()) . '</a></td>';
            }
               
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($objLocalizadorDTO->getStrNomeTipoSuporte()) . '</td>';
            $strResultado .= '<td align="center">';
          
            if ($countDocumentoFisicoEliminado) {
                $strResultado .= '<img src="imagens/circulo_verde.png" title="Eliminado" title="Eliminado" class="infraImg" />';
            } else {
                $strResultado .= '<img src="imagens/circulo_vermelho.png" title="Não Eliminado" title="Não Eliminado" class="infraImg" />';
            }


            $strResultado .= '</td></tr>' . "\n";
            $strResultado .= '</tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    //  $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
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


#lblListagemEliminacao { position: absolute; top: 0px; left: 0px; }
#lblDatasLimite { position: absolute; top: 0px; left: 191px; }
#lblProcessoSei { position: absolute; top: 0px; left: 329px; }

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>


function inicializar() {
infraEfeitoTabelas();
document.getElementById('btnFechar').focus();
}

<? if ($bolAcaoEliminarDocumentoFisico) { ?>
    function acaoConfirmarEliminacao(link) {
        if (document.getElementById('hdnInfraItensSelecionados').value == '') {
            alert('Nenhum Documento Selecionado.');
            return;
        }

        infraAbrirJanela(link, 'janelaObservarPreparacaoListagemEliminacao', 720, 300, 'location=0,status=1,resizable=1,scrollbars=1', false);
    }
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmPrepararListagemEliminacao" method="post"
      action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
          <?
          PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
          PaginaSEI::getInstance()->abrirAreaDados('3.5em');
          ?>

    <p id="lblListagemEliminacao">
        <label class="infraLabelObrigatorio">Listagem de Eliminação: </label><?= $objMdGdListaEliminacaoDTO->getStrNumero(); ?>
    </p>
    <p id="lblDatasLimite">
        <label class="infraLabelObrigatorio">Datas-Limite: </label><?= $objMdGdListaEliminacaoDTO->getNumAnoLimiteInicio(). ' - '.$objMdGdListaEliminacaoDTO->getNumAnoLimiteFim(); ?>
    </p>
    <p id="lblProcessoSei">
        <label class="infraLabelObrigatorio">Processo no SEI: </label><?= $objMdGdListaEliminacaoDTO->getStrProtocoloProcedimentoEliminacaoFormatado(); ?>
    </p>

    <?
    PaginaSEI::getInstance()->fecharAreaDados();
    PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros);
    PaginaSEI::getInstance()->montarAreaDebug();
    PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
    ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>