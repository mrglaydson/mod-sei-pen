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

    $strTitulo = 'Gestão da Listagem de Eliminação';

    switch ($_GET['acao']) {

        case 'gd_gestao_listagem_eliminacao':
            SessaoSEI::getInstance()->validarPermissao('gestao_documental_gestao_listagem_eliminacao');
            PaginaSEI::getInstance()->salvarCamposPost(array('txtPeriodoEmissaoDe', 'txtPeriodoEmissaoAte', 'txtAnoLimiteDe', 'txtAnoLimiteAte'));
        
            break;
        case 'gd_editar_listagem_eliminacao':
            PaginaSEI::getInstance()->salvarCamposPost(array('hdnInfraItemId'));
            $numIdListaEliminacao = PaginaSEI::getInstance()->recuperarCampo('hdnInfraItemId');

            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setNumIdListaEliminacao($numIdListaEliminacao);

            $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
            $objMdGdListaEliminacaoRN->editarListaEliminacao($objMdGdListaEliminacaoDTO);

            break;

        case 'gd_concluir_edicao_listagem_elim':
            PaginaSEI::getInstance()->salvarCamposPost(array('hdnInfraItemId'));
            $numIdListaEliminacao = PaginaSEI::getInstance()->recuperarCampo('hdnInfraItemId');

            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setNumIdListaEliminacao($numIdListaEliminacao);

            $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
            $objMdGdListaEliminacaoRN->concluirEdicaoListaEliminacao($objMdGdListaEliminacaoDTO);

            break;
        case 'gd_prep_list_eliminacao_gerar':
            SessaoSEI::getInstance()->validarPermissao('gestao_documental_prep_list_eliminacao_gerar');
            $arrNumIdsArquivamento = PaginaSEI::getInstance()->getArrStrItensSelecionados();

            //Busca os arquivamentos dos processos que serão enviados para a listagem de eliminação
            /*   $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
              $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

              $objMdGdArquivamentoDTO->setNumIdArquivamento($arrNumIdsArquivamento, InfraDTO::$OPER_IN);
              $objMdGdArquivamentoDTO->retNumIdArquivamento();
              $objMdGdArquivamentoDTO->retDblIdProcedimento();
              $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimentoEliminacaoFormatado();
              $objMdGdArquivamentoDTO->retStrObservacaoEliminacao();

              $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

              //Envia os processos para a listagem de eliminação
              $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
              $objMdGdListaEliminacaoDTO->setArrObjMdGdArquivamentoDTO($arrObjMdGdArquivamentoDTO);

              $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
              $objMdGdListaEliminacaoRN->cadastrar($objMdGdListaEliminacaoDTO);

              header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'])); */
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $bolAcaoVisualizar = SessaoSEI::getInstance()->verificarPermissao('gestao_documental_visualizacao_listagem_eliminacao');
    $bolAcaoEliminar = SessaoSEI::getInstance()->verificarPermissao('gestao_documental_eliminacao');
    $bolAcaoEliminarDocumentoFisico = SessaoSEI::getInstance()->verificarPermissao('gestao_documental_list_elim_documentos_fisicos');
    $bolAcaoAdicionarProcessosListagem = true;
    $bolAcaoRemoverProcessosListagem = true;
    $bolAcaoConcluirEdicaoListagem = true;
    $bolAcaoEditarListagem = true;

    $strLinkEditarListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_editar_listagem_eliminacao&acao_origem=' . $_GET['acao']);
    $strLinkConcluirEdicaoListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_concluir_edicao_listagem_elim&acao_origem=' . $_GET['acao']);

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
    $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
    $objMdGdListaEliminacaoDTO->retStrSituacao();
    $objMdGdListaEliminacaoDTO->retStrProtocoloProcedimentoEliminacaoFormatado();
    $objMdGdListaEliminacaoDTO->retTodos();


    $txtPeriodoEmissaoDe = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoEmissaoDe');
    if ($txtPeriodoEmissaoDe) {
        $objMdGdListaEliminacaoDTO->setDthEmissaoListagem($txtPeriodoEmissaoDe, InfraDTO::$OPER_MAIOR_IGUAL);
    }

    $txtPeriodoEmissaoAte = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoEmissaoAte');
    if ($txtPeriodoEmissaoAte) {
        $objMdGdListaEliminacaoDTO->setDthEmissaoListagem($txtPeriodoEmissaoAte, InfraDTO::$OPER_MENOR_IGUAL);
    }


    $txtAnoLimiteDe = PaginaSEI::getInstance()->recuperarCampo('txtAnoLimiteDe');
    if ($txtAnoLimiteDe) {
        $objMdGdListaEliminacaoDTO->setNumAnoLimiteInicio($txtAnoLimiteDe, InfraDTO::$OPER_MENOR_IGUAL);
    }

    $txtAnoLimiteAte = PaginaSEI::getInstance()->recuperarCampo('txtAnoLimiteAte');
    if ($txtPeriodoEmissaoAte) {
        $objMdGdListaEliminacaoDTO->setNumAnoLimiteFim($txtAnoLimiteAte, InfraDTO::$OPER_MAIOR_IGUAL);
    }


    PaginaSEI::getInstance()->prepararOrdenacao($objMdGdListaEliminacaoDTO, 'EmissaoListagem', InfraDTO::$TIPO_ORDENACAO_DESC);
    PaginaSEI::getInstance()->prepararPaginacao($objMdGdListaEliminacaoDTO);

    $arrObjMdGdListaEliminacaoDTO = $objMdGdListaEliminacaoRN->listar($objMdGdListaEliminacaoDTO);

    PaginaSEI::getInstance()->processarPaginacao($objMdGdListaEliminacaoDTO);
    $numRegistros = count($arrObjMdGdListaEliminacaoDTO);

    if ($numRegistros > 0) {
        $strResultado = '';

        $strSumarioTabela = 'Listagens de Eliminacao';
        $strCaptionTabela = 'Listagens de Eliminacao';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="19%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaEliminacaoDTO, 'Listagem de Eliminacao', 'Numero', $arrObjMdGdListaEliminacaoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="20%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaEliminacaoDTO, 'Número de Processo de Eliminação', 'ProtocoloProcedimentoEliminacaoFormatado', $arrObjMdGdListaEliminacaoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="20%">Data Limite</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="25%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaEliminacaoDTO, 'Data de Emissão da Listagem', 'EmissaoListagem', $arrObjMdGdListaEliminacaoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();

        for ($i = 0; $i < $numRegistros; $i++) {
            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao(), $arrObjMdGdListaEliminacaoDTO[$i]->getStrNumero()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getStrNumero()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getStrProtocoloProcedimentoEliminacaoFormatado()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getNumAnoLimiteInicio() . '-' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumAnoLimiteFim()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getDthEmissaoListagem()) . '</td>';
            $strResultado .= '<td align="center">';

            if ($bolAcaoEditarListagem && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_GERADA) {
                $strResultado .= '<a href="#ID-' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '" onclick="acaoEditarListagemEliminacao(\'' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="modulos/sei-mod-gestao-documental/imagens/editar_listagem.gif" title="Editar Listagem de Eliminação" title="Editar Listagem de Eliminação" class="infraImg" /></a>';
            }

            if ($bolAcaoVisualizar) {
                $strLinkVisualizar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_visualizacao_listagem_eliminacao&acao_origem=' . $_GET['acao'] . '&id_listagem_eliminacao=' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao());
                $strResultado .= '<a href="' . $strLinkVisualizar . '" ><img src="imagens/consultar.gif" title="Visualizar Listagem de Eliminacao" title="Visualizar Listagem de Eliminacao" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoEliminar && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_GERADA) {
                $strLinkEliminar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_listagem_eliminacao_eliminar&acao_origem=' . $_GET['acao'] . '&id_listagem_eliminacao=' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao());
                $strResultado .= '<a href="' .$strLinkEliminar . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="imagens/arquivo.png" title="Eliminar Processos" alt="Eliminar Processos" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoEliminarDocumentoFisico && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_GERADA) {
                $strLinkEliminacaoDocumentosFisicos = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_eliminacao_documentos_fisicos&id_listagem_eliminacao=' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '&acao_origem=' . $_GET['acao']);
                $strResultado .= '<a href="' . $strLinkEliminacaoDocumentosFisicos . '" ><img src="imagens/procedimento_desanexado.gif" title="Eliminar Documentos Físicos" title="Eliminar Documentos Físicos" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoAdicionarProcessosListagem && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_EDICAO) {
                $strLinkAdicionarProcessosListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_adicionar_processo_listagem_elim&id_listagem_eliminacao=' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '&acao_origem=' . $_GET['acao']);
                $strResultado .= '<a href="' . $strLinkAdicionarProcessosListagem . '" ><img src="modulos/sei-mod-gestao-documental/imagens/adicionar_processo_listagem.gif" title="Adicionar Processos" title="Adicionar Processos" class="infraImg" /></a>&nbsp;';
            }
            
            if ($bolAcaoRemoverProcessosListagem && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_EDICAO) {
                $strLinkRemoverProcessosListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_remover_processo_listagem_elim&id_listagem_eliminacao=' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '&acao_origem=' . $_GET['acao']);
                $strResultado .= '<a href="' . $strLinkRemoverProcessosListagem . '" ><img src="modulos/sei-mod-gestao-documental/imagens/remover_processo_listagem.gif" title="Remover Processos" title="Remover Processos" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoConcluirEdicaoListagem && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_EDICAO) {
                $strResultado .= '<a href="#ID-' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '" onclick="acaoConcluirEdicaoListagemEliminacao(\'' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="modulos/sei-mod-gestao-documental/imagens/concluir_edicao_listagem.gif" title="Concluir edição da listagem" title="Concluir edição da listagem" class="infraImg" /></a>';
            }

            $strResultado .= '</td></tr>' . "\n";
            $strResultado .= '</tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    // Busca uma lista de unidades
    $strItensSelUnidade = UnidadeINT::montarSelectSiglaDescricao('null', '&nbsp;', $selUnidade);
    $strItensSelTipoProcedimento = TipoProcedimentoINT::montarSelectNome('null', 'Todos', $selTipoProcedimento);

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


#lblAnoLimiteDe {position:absolute;left:0%;top:55%;width:20%;}
#txtAnoLimiteDe {position:absolute;left:0%;top:70%;width:17%;}

#lblAnoLimiteAte {position:absolute;left:19%;top:55%;width:20%;}
#txtAnoLimiteAte {position:absolute;left:19%;top:70%;width:17%;}

#lblPeriodoEmissaoDe {position:absolute;left:38%;top:55%;width:20%;}
#txtPeriodoEmissaoDe {position:absolute;left:38%;top:70%;width:17%;}
#imgCalPeriodoEmissaoDe {position:absolute;left:56%;top:72%;}

#lblPeriodoEmissaoAte {position:absolute;left:59%;top:55%;width:20%;}
#txtPeriodoEmissaoAte {position:absolute;left:59%;top:70%;width:17%;}
#imgCalPeriodoEmissaoAte {position:absolute;left:77%;top:72%;}


<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script>

    function inicializar() {
        infraEfeitoTabelas();
        document.getElementById('btnFechar').focus();
    }

    
    function acaoEditarListagemEliminacao(id_listagem_eliminacao){
        if (confirm("Confirma a edição da listagem de eliminação?")) {
            document.getElementById('hdnInfraItemId').value = id_listagem_eliminacao;
            document.getElementById('frmAvaliacaoProcessoLista').action = '<?= $strLinkEditarListagem ?>';
            document.getElementById('frmAvaliacaoProcessoLista').submit();
        }
    }

    function acaoConcluirEdicaoListagemEliminacao(id_listagem_eliminacao){
        if (confirm("Confirma a conclusão edição na listagem de eliminação?")) {
            document.getElementById('hdnInfraItemId').value = id_listagem_eliminacao;
            document.getElementById('frmAvaliacaoProcessoLista').action = '<?= $strLinkConcluirEdicaoListagem ?>';
            document.getElementById('frmAvaliacaoProcessoLista').submit();
        }
    }

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmAvaliacaoProcessoLista" method="post"
      action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
          <?
          PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
          PaginaSEI::getInstance()->abrirAreaDados('9.5em');
          ?>

    <label id="lblAnoLimiteDe" for="txtAnoLimiteDe" accesskey="" class="infraLabelOpcional">Datas-limite:</label>
    <input type="text" id="txtAnoLimiteDe" value="<?= $txtAnoLimiteDe ?>" name="txtAnoLimiteDe" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoEmissaoDe) ?>" onkeypress="return infraMascaraNumero(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />

    <label id="lblAnoLimiteAte" for="txtAnoLimiteAte" accesskey="" class="infraLabelOpcional">Até</label>
    <input type="text" id="txtAnoLimiteAte" value="<?= $txtAnoLimiteAte ?>" name="txtAnoLimiteAte" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoEmissaoA) ?>" onkeypress="return infraMascaraNumero(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />

    <label id="lblPeriodoEmissaoDe" for="txtPeriodoEmissaoDe" accesskey="" class="infraLabelOpcional">Emissão da listagem de:</label>
    <input type="text" id="txtPeriodoEmissaoDe" value="<?= $txtPeriodoEmissaoDe ?>" name="txtPeriodoEmissaoDe" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoEmissaoDe) ?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
    <img id="imgCalPeriodoEmissaoDe" title="Selecionar Data Inicial" alt="Selecionar Data Inicial" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/calendario.gif" class="infraImg" onclick="infraCalendario('txtPeriodoEmissaoDe', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    

    <label id="lblPeriodoEmissaoAte" for="txtPeriodoEmissaoAte" accesskey="" class="infraLabelOpcional">Até</label>
    <input type="text" id="txtPeriodoEmissaoAte" value="<?= $txtPeriodoEmissaoAte ?>" name="txtPeriodoEmissaoAte" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoEmissaoAte) ?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
    <img id="imgCalPeriodoEmissaoAte" title="Selecionar Data Final" alt="Selecionar Data Final" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/calendario.gif" class="infraImg" onclick="infraCalendario('txtPeriodoEmissaoAte', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    
    <input type='hidden' name='hdnIdListagemEliminacao' id='hdnListagensEliminacao' value='' />

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