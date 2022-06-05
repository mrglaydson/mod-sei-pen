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

    $strTitulo = 'Gestão das Listagens de Eliminação';

    switch ($_GET['acao']) {

        case 'gd_lista_eliminacao_listar':
            PaginaSEI::getInstance()->salvarCamposPost(array('txtPeriodoEmissaoDe', 'txtPeriodoEmissaoAte', 'txtAnoLimiteDe', 'txtAnoLimiteAte'));
        
            break;
        case 'gd_lista_eliminacao_editar':
            PaginaSEI::getInstance()->salvarCamposPost(array('hdnInfraItemId'));
            $numIdListaEliminacao = PaginaSEI::getInstance()->recuperarCampo('hdnInfraItemId');

            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setNumIdListaEliminacao($numIdListaEliminacao);

            $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
            $objMdGdListaEliminacaoRN->editarListaEliminacao($objMdGdListaEliminacaoDTO);

            break;

        case 'gd_lista_eliminacao_edicao_concluir':
            PaginaSEI::getInstance()->salvarCamposPost(array('hdnInfraItemId'));
            $numIdListaEliminacao = PaginaSEI::getInstance()->recuperarCampo('hdnInfraItemId');

            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setNumIdListaEliminacao($numIdListaEliminacao);

            try{
                $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
                $objMdGdListaEliminacaoRN->concluirEdicaoListaEliminacao($objMdGdListaEliminacaoDTO);
            }catch(Exception $ex){
                PaginaSEI::getInstance()->processarExcecao($ex);
            }

            break;
        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $bolAcaoVisualizar = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_visualizar');
    $bolAcaoEliminar = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_eliminar');
    $bolAcaoEliminarDocumentoFisico = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_documentos_fisicos_listar');
    $bolAcaoAdicionarProcessosListagem = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_procedimento_adicionar');
    $bolAcaoRemoverProcessosListagem = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_procedimento_remover');
    $bolAcaoConcluirEdicaoListagem = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_edicao_concluir');
    $bolAcaoEditarListagem = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_editar');

    $strLinkEditarListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_editar&acao_origem=' . $_GET['acao']);
    $strLinkConcluirEdicaoListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_edicao_concluir&acao_origem=' . $_GET['acao']);

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
    $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
    $objMdGdListaEliminacaoDTO->retStrSituacao();
    $objMdGdListaEliminacaoDTO->retStrProtocoloProcedimentoEliminacaoFormatado();
    $objMdGdListaEliminacaoDTO->retDblIdProcedimentoEliminacao();
    $objMdGdListaEliminacaoDTO->retStrNomeUsuario();
    $objMdGdListaEliminacaoDTO->retStrSiglaUsuario();
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

        $strSumarioTabela = 'Listagens de Eliminação';
        $strCaptionTabela = 'Processos de Eliminação';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaEliminacaoDTO, 'Nº da Listagem', 'Numero', $arrObjMdGdListaEliminacaoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaEliminacaoDTO, 'Nº do Processo', 'ProtocoloProcedimentoEliminacaoFormatado', $arrObjMdGdListaEliminacaoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Datas-Limite</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaEliminacaoDTO, 'Data de Emissão', 'EmissaoListagem', $arrObjMdGdListaEliminacaoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaEliminacaoDTO, 'Usuário', 'NomeUsuario', $objMdGdListaEliminacaoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaEliminacaoDTO, 'Quantidade de Processos', 'QtdProcessos', $arrObjMdGdListaEliminacaoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="12%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaEliminacaoDTO, 'Anotação', 'Anotacao', $arrObjMdGdListaEliminacaoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="12%">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();

        for ($i = 0; $i < $numRegistros; $i++) {
            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            $strResultado .= '<td valign="top"  align="center">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao(), $arrObjMdGdListaEliminacaoDTO[$i]->getStrNumero()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getStrNumero()) . '</td>';
            $strResultado .= '<td nowrap="nowrap"><a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjMdGdListaEliminacaoDTO[$i]->getDblIdProcedimentoEliminacao()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjMdGdListaEliminacaoDTO[$i]->getStrProtocoloProcedimentoEliminacaoFormatado() . '</a></td>';
            $strResultado .= '<td align="center">' . PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getNumAnoLimiteInicio() . '-' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumAnoLimiteFim()) . '</td>';
            $strResultado .= '<td align="center">' . PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getDthEmissaoListagem()) . '</td>';
            $strResultado .= '<td align="center"> <a alt="'.PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getStrNomeUsuario()).'" title="'.PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getStrNomeUsuario()).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getStrSiglaUsuario()).'</a> </td>';
            $strResultado .= '<td align="center">' . PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getNumQtdProcessos()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdListaEliminacaoDTO[$i]->getStrAnotacao()) . '</td>';
            $strResultado .= '<td align="center">';

            if ($bolAcaoVisualizar) {
                $strLinkVisualizar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_visualizar&acao_origem=' . $_GET['acao'] . '&id_listagem_eliminacao=' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao());
                $strResultado .= '<a href="' . $strLinkVisualizar . '" ><img src="/infra_css/svg/consultar.svg" title="Visualizar Listagem de Eliminação" alt="Visualizar Listagem de Eliminação" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoEditarListagem && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_GERADA) {
                $strResultado .= '<a href="#ID-' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '" onclick="acaoEditarListagemEliminacao(\'' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="/infra_css/svg/alterar.svg" title="Editar Listagem de Eliminação" alt="Editar Listagem de Eliminação" class="infraImg" /></a>';
            }

            if ($bolAcaoEliminar && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_GERADA) {
                $strLinkEliminar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_eliminar&acao_origem=' . $_GET['acao'] . '&id_listagem_eliminacao=' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao());
                //$strResultado .= '<a href="#" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '" onclick="acaoEliminar(\'' . $strLinkEliminar . '\')"><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/eliminar.png" title="Eliminar Processos" alt="Eliminar Processos" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoEliminarDocumentoFisico && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_GERADA && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSinDocumentosFisicos() == 'S') {
                $strLinkEliminacaoDocumentosFisicos = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_documentos_fisicos_listar&id_listagem_eliminacao=' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '&acao_origem=' . $_GET['acao']);
                $strResultado .= '<a href="' . $strLinkEliminacaoDocumentosFisicos . '" ><img src="imagens/procedimento_desanexado.gif" title="Eliminar Documentos Físicos" alt="Eliminar Documentos Físicos" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoAdicionarProcessosListagem && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_EDICAO) {
                $strLinkAdicionarProcessosListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_procedimento_adicionar&id_listagem_eliminacao=' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '&acao_origem=' . $_GET['acao']);
                $strResultado .= '<a href="' . $strLinkAdicionarProcessosListagem . '" ><img src="' .PaginaSEI::getInstance()->getIconeMais(). '" title="Adicionar Processos" alt="Adicionar Processos" class="infraImg" /></a>&nbsp;';
            }
            
            if ($bolAcaoRemoverProcessosListagem && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_EDICAO) {
                $strLinkRemoverProcessosListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_procedimento_remover&id_listagem_eliminacao=' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '&acao_origem=' . $_GET['acao']);
                $strResultado .= '<a href="' . $strLinkRemoverProcessosListagem . '" ><img src="' . PaginaSEI::getInstance()->getIconeMenos(). '" title="Remover Processos" alt="Remover Processos" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoConcluirEdicaoListagem && $arrObjMdGdListaEliminacaoDTO[$i]->getStrSituacao() == MdGdListaEliminacaoRN::$ST_EDICAO) {
                $strResultado .= '<a href="#ID-' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '" onclick="acaoConcluirEdicaoListagemEliminacao(\'' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . Icone::PUBLICACAO_RELACIONADAS . '" title="Concluir edição da listagem" alt="Concluir edição da listagem" class="infraImg" /></a>';
            }

            $strLinkAnotar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_listar_eliminacao_anotar&acao_origem=' . $_GET['acao'] . '&id_lista_eliminacao=' . $arrObjMdGdListaEliminacaoDTO[$i]->getNumIdListaEliminacao());
            $strResultado .= '<a href="#" onclick="exibirJanelaAnotacao(\'' . $strLinkAnotar . '\');"><img src="' . Icone::ANOTACAO_CADASTRO . '" title="Realizar Anotação" alt="Realizar Anotação" class="infraImg"/></a>&nbsp;';

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


#lblAnoLimiteDe {position:absolute;left:0%;top:0%;width:20%;}
#txtAnoLimiteDe {position:absolute;left:0%;top:22%;width:17%;}

#lblAnoLimiteAte {position:absolute;left:19%;top:0%;width:20%;}
#txtAnoLimiteAte {position:absolute;left:19%;top:22%;width:17%;}

#lblPeriodoEmissaoDe {position:absolute;left:38%;top:0%;width:20%;}
#txtPeriodoEmissaoDe {position:absolute;left:38%;top:22%;width:17%;}
#imgCalPeriodoEmissaoDe {position:absolute;left:56%;top:22%;}

#lblPeriodoEmissaoAte {position:absolute;left:59%;top:0%;width:20%;}
#txtPeriodoEmissaoAte {position:absolute;left:59%;top:22%;width:17%;}
#imgCalPeriodoEmissaoAte {position:absolute;left:77%;top:22%;}


<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script>

    function exibirJanelaAnotacao(link) {
        infraAbrirJanela(link, 'janelaAnotacao', 720, 300, 'location=0,status=1,resizable=1,scrollbars=1', false);
    }

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
        if (confirm("Confirma a conclusão da edição na listagem de eliminação?")) {
            document.getElementById('hdnInfraItemId').value = id_listagem_eliminacao;
            document.getElementById('frmAvaliacaoProcessoLista').action = '<?= $strLinkConcluirEdicaoListagem ?>';
            document.getElementById('frmAvaliacaoProcessoLista').submit();
        }
    }

    <? if ($bolAcaoEliminar) { ?>
    function acaoEliminar(link) {
        infraAbrirJanela(link, 'janelaObservarPreparacaoListagemEliminacao', 750, 500, 'location=0,status=1,resizable=1,scrollbars=1', false);
    }
<? } ?>

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

    <label id="lblAnoLimiteDe" for="txtAnoLimiteDe" accesskey="" class="infraLabelOpcional">Datas-limite de:</label>
    <input type="text" id="txtAnoLimiteDe" value="<?= $txtAnoLimiteDe ?>" name="txtAnoLimiteDe" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoEmissaoDe) ?>" onkeypress="return infraMascaraNumero(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />

    <label id="lblAnoLimiteAte" for="txtAnoLimiteAte" accesskey="" class="infraLabelOpcional">Até:</label>
    <input type="text" id="txtAnoLimiteAte" value="<?= $txtAnoLimiteAte ?>" name="txtAnoLimiteAte" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoEmissaoA) ?>" onkeypress="return infraMascaraNumero(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />

    <label id="lblPeriodoEmissaoDe" for="txtPeriodoEmissaoDe" accesskey="" class="infraLabelOpcional">Data de Emissão de:</label>
    <input type="text" id="txtPeriodoEmissaoDe" value="<?= $txtPeriodoEmissaoDe ?>" name="txtPeriodoEmissaoDe" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoEmissaoDe) ?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
    <img id="imgCalPeriodoEmissaoDe" title="Selecionar Data Inicial" alt="Selecionar Data Inicial" src="/infra_css/svg/calendario.svg" class="infraImg" onclick="infraCalendario('txtPeriodoEmissaoDe', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    

    <label id="lblPeriodoEmissaoAte" for="txtPeriodoEmissaoAte" accesskey="" class="infraLabelOpcional">Até:</label>
    <input type="text" id="txtPeriodoEmissaoAte" value="<?= $txtPeriodoEmissaoAte ?>" name="txtPeriodoEmissaoAte" class="infraText" value="<?= PaginaSEI::tratarHTML($dtaPeriodoEmissaoAte) ?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
    <img id="imgCalPeriodoEmissaoAte" title="Selecionar Data Final" alt="Selecionar Data Final" src="/infra_css/svg/calendario.svg" class="infraImg" onclick="infraCalendario('txtPeriodoEmissaoAte', this);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />    
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