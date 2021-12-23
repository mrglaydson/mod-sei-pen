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

    $strTitulo = 'Gestão da Listagem de Recolhimento';

    switch ($_GET['acao']) {

        case 'gd_lista_recolhimento_listar':
            PaginaSEI::getInstance()->salvarCamposPost(array('txtPeriodoEmissaoDe', 'txtPeriodoEmissaoAte', 'txtAnoLimiteDe', 'txtAnoLimiteAte'));

            break;

        case 'gd_lista_recolhimento_recolher':
            // Registra a Recolhimento
            $objMdGdRecolhimentoDTO = new MdGdRecolhimentoDTO();
            $objMdGdRecolhimentoDTO->setNumIdListaRecolhimento($_POST['hdnIdListaRecolhimento']);
            $objMdGdRecolhimentoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $objMdGdRecolhimentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objMdGdRecolhimentoDTO->setDthDataRecolhimento(date('d/m/Y'));

            $objMdGdRecolhimentoRN = new MdGdRecolhimentoRN();
            $objMdGdRecolhimentoRN->cadastrar($objMdGdRecolhimentoDTO);

            break;
        case 'gd_lista_recolhimento_editar':
            PaginaSEI::getInstance()->salvarCamposPost(array('hdnInfraItemId'));
            $numIdListaRecolhimento = PaginaSEI::getInstance()->recuperarCampo('hdnInfraItemId');

            $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
            $objMdGdListaRecolhimentoDTO->setNumIdListaRecolhimento($numIdListaRecolhimento);

            $objMdGdListaRecolhimentoRN = new MdGdListaRecolhimentoRN();
            $objMdGdListaRecolhimentoRN->editarListaRecolhimento($objMdGdListaRecolhimentoDTO);

            break;

        case 'gd_lista_recolhimento_edicao_concluir':
            PaginaSEI::getInstance()->salvarCamposPost(array('hdnInfraItemId'));
            $numIdListaRecolhimento = PaginaSEI::getInstance()->recuperarCampo('hdnInfraItemId');

            $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
            $objMdGdListaRecolhimentoDTO->setNumIdListaRecolhimento($numIdListaRecolhimento);

            try{
                $objMdGdListaRecolhimentoRN = new MdGdListaRecolhimentoRN();
                $objMdGdListaRecolhimentoRN->concluirEdicaoListaRecolhimento($objMdGdListaRecolhimentoDTO);
            }catch(Exception $ex){
                PaginaSEI::getInstance()->processarExcecao($ex);
            }

            break;
        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $bolAcaoVisualizar = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_listar');
    $bolAcaoRecolher = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_recolher');
    $bolAcaoRecolherDocumentoFisico = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_documentos_fisicos_listar');
    $bolAcaoAdicionarProcessosListagem = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_procedimento_adicionar');
    $bolAcaoRemoverProcessosListagem = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_procedimento_remover');
    $bolAcaoConcluirEdicaoListagem = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_edicao_concluir'); 
    $bolAcaoEditarListagem = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_editar'); 
    
    $strLinkEditarListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_editar&acao_origem=' . $_GET['acao']);
    $strLinkConcluirEdicaoListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_edicao_concluir&acao_origem=' . $_GET['acao']);


    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';


    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    $objMdGdListaRecolhimentoRN = new MdGdListaRecolhimentoRN();
    $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
    $objMdGdListaRecolhimentoDTO->retStrSituacao();
    $objMdGdListaRecolhimentoDTO->retStrProtocoloProcedimentoRecolhimentoFormatado();
    $objMdGdListaRecolhimentoDTO->retDblIdProcedimentoRecolhimento();
    $objMdGdListaRecolhimentoDTO->retTodos();


    $txtPeriodoEmissaoDe = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoEmissaoDe');
    if ($txtPeriodoEmissaoDe) {
        $objMdGdListaRecolhimentoDTO->setDthEmissaoListagem($txtPeriodoEmissaoDe, InfraDTO::$OPER_MAIOR_IGUAL);
    }

    $txtPeriodoEmissaoAte = PaginaSEI::getInstance()->recuperarCampo('txtPeriodoEmissaoAte');
    if ($txtPeriodoEmissaoAte) {
        $objMdGdListaRecolhimentoDTO->setDthEmissaoListagem($txtPeriodoEmissaoAte, InfraDTO::$OPER_MENOR_IGUAL);
    }


    $txtAnoLimiteDe = PaginaSEI::getInstance()->recuperarCampo('txtAnoLimiteDe');
    if ($txtAnoLimiteDe) {
        $objMdGdListaRecolhimentoDTO->setNumAnoLimiteInicio($txtAnoLimiteDe, InfraDTO::$OPER_MENOR_IGUAL);
    }

    $txtAnoLimiteAte = PaginaSEI::getInstance()->recuperarCampo('txtAnoLimiteAte');
    if ($txtPeriodoEmissaoAte) {
        $objMdGdListaRecolhimentoDTO->setNumAnoLimiteFim($txtAnoLimiteAte, InfraDTO::$OPER_MAIOR_IGUAL);
    }


    PaginaSEI::getInstance()->prepararOrdenacao($objMdGdListaRecolhimentoDTO, 'EmissaoListagem', InfraDTO::$TIPO_ORDENACAO_DESC);
    PaginaSEI::getInstance()->prepararPaginacao($objMdGdListaRecolhimentoDTO);

    $arrObjMdGdListaRecolhimentoDTO = $objMdGdListaRecolhimentoRN->listar($objMdGdListaRecolhimentoDTO);

    PaginaSEI::getInstance()->processarPaginacao($objMdGdListaRecolhimentoDTO);
    $numRegistros = count($arrObjMdGdListaRecolhimentoDTO);

    if ($numRegistros > 0) {
        $strResultado = '';

        $strSumarioTabela = 'Listagens de Recolhimento';
        $strCaptionTabela = 'Listagens de Recolhimento';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="19%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaRecolhimentoDTO, 'Listagem de Recolhimento', 'Numero', $arrObjMdGdListaRecolhimentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaEliminacaoDTO, 'Número de Processo de Eliminação', 'ProtocoloProcedimentoEliminacaoFormatado', $arrObjMdGdListaEliminacaoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="20%">Data Limite</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="25%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaRecolhimentoDTO, 'Data de Emissão da Listagem', 'EmissaoListagem', $arrObjMdGdListaRecolhimentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdListaRecolhimentoDTO, 'Quantidade de Processos', 'QtdProcessos', $arrObjMdGdListaRecolhimentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();

        for ($i = 0; $i < $numRegistros; $i++) {
            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdListaRecolhimentoDTO[$i]->getNumIdListaRecolhimento(), $arrObjMdGdListaRecolhimentoDTO[$i]->getStrNumero()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdListaRecolhimentoDTO[$i]->getStrNumero()) . '</td>';
            $strResultado .= '<td><a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjMdGdListaRecolhimentoDTO[$i]->getDblIdProcedimentoRecolhimento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . ' " target="_blank">' . $arrObjMdGdListaRecolhimentoDTO[$i]->getStrProtocoloProcedimentoRecolhimentoFormatado() . '</a></td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdListaRecolhimentoDTO[$i]->getNumAnoLimiteInicio() . '-' . $arrObjMdGdListaRecolhimentoDTO[$i]->getNumAnoLimiteFim()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdListaRecolhimentoDTO[$i]->getDthEmissaoListagem()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdListaRecolhimentoDTO[$i]->getNumQtdProcessos()) . '</td>';
            $strResultado .= '<td align="center">';


            if ($bolAcaoVisualizar) {
                // gd_lista_eliminacao_visualizar
                $strLinkVisualizar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_visualizar&acao_origem=' . $_GET['acao'] . '&id_listagem_recolhimento=' . $arrObjMdGdListaRecolhimentoDTO[$i]->getNumIdListaRecolhimento());
                $strResultado .= '<a href="' . $strLinkVisualizar . '" ><img src="imagens/consultar.gif" title="Visualizar Listagem de Recolhimento" title="Visualizar Listagem de Recolhimento" class="infraImg" /></a>&nbsp;';
            }
            
            if ($bolAcaoEditarListagem && $arrObjMdGdListaRecolhimentoDTO[$i]->getStrSituacao() == MdGdListaRecolhimentoRN::$ST_GERADA) {
                $strResultado .= '<a href="#ID-' . $arrObjMdGdListaRecolhimentoDTO[$i]->getNumIdListaRecolhimento() . '" onclick="acaoEditarListagemRecolhimento(\'' . $arrObjMdGdListaRecolhimentoDTO[$i]->getNumIdListaRecolhimento() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/editar_listagem.gif" title="Editar Listagem de Recolhimento" title="Editar Listagem de Recolhimento" class="infraImg" /></a>';
            }

            if ($bolAcaoRecolher && $arrObjMdGdListaRecolhimentoDTO[$i]->getStrSituacao() == MdGdListaRecolhimentoRN::$ST_GERADA) {
                // gd_recolhimento
                $strResultado .= '<a href="#ID-' . $arrObjMdGdListaRecolhimentoDTO[$i]->getNumIdListaRecolhimento() . '" onclick="acaoRecolher(\'' . $arrObjMdGdListaRecolhimentoDTO[$i]->getNumIdListaRecolhimento() . '\',\'' . $arrObjMdGdListaRecolhimentoDTO[$i]->getStrNumero() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="imagens/arquivo.png" title="Recolher Processos" alt="Recolher Processos" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoRecolherDocumentoFisico && $arrObjMdGdListaRecolhimentoDTO[$i]->getStrSituacao() == MdGdListaRecolhimentoRN::$ST_GERADA && $arrObjMdGdListaRecolhimentoDTO[$i]->getStrSinDocumentosFisicos() == 'S') {
                $strLinkRecolherDocumentosFisicos = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_documentos_fisicos_listar&id_listagem_recolhimento=' . $arrObjMdGdListaRecolhimentoDTO[$i]->getNumIdListaRecolhimento() . '&acao_origem=' . $_GET['acao']);
                $strResultado .= '<a href="' . $strLinkRecolherDocumentosFisicos . '" ><img src="imagens/procedimento_desanexado.gif" title="Recolher Documentos Físicos" title="Recolher Documentos Físicos" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoAdicionarProcessosListagem && $arrObjMdGdListaRecolhimentoDTO[$i]->getStrSituacao() == MdGdListaRecolhimentoRN::$ST_EDICAO) {
                $strLinkAdicionarProcessosListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_procedimento_adicionar&id_listagem_recolhimento=' . $arrObjMdGdListaRecolhimentoDTO[$i]->getNumIdListaRecolhimento() . '&acao_origem=' . $_GET['acao']);
                $strResultado .= '<a href="' . $strLinkAdicionarProcessosListagem . '" ><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/adicionar_processo_listagem.gif" title="Adicionar Processos" title="Adicionar Processos" class="infraImg" /></a>&nbsp;';
            }
            
            if ($bolAcaoRemoverProcessosListagem && $arrObjMdGdListaRecolhimentoDTO[$i]->getStrSituacao() == MdGdListaRecolhimentoRN::$ST_EDICAO) {
                $strLinkRemoverProcessosListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_procedimento_remover&id_listagem_recolhimento=' . $arrObjMdGdListaRecolhimentoDTO[$i]->getNumIdListaRecolhimento() . '&acao_origem=' . $_GET['acao']);
                $strResultado .= '<a href="' . $strLinkRemoverProcessosListagem . '" ><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/remover_processo_listagem.gif" title="Remover Processos" title="Remover Processos" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoConcluirEdicaoListagem && $arrObjMdGdListaRecolhimentoDTO[$i]->getStrSituacao() == MdGdListaRecolhimentoRN::$ST_EDICAO) {
                $strResultado .= '<a href="#ID-' . $arrObjMdGdListaRecolhimentoDTO[$i]->getNumIdListaRecolhimento() . '" onclick="acaoConcluirEdicaoListagemRecolhimento(\'' . $arrObjMdGdListaRecolhimentoDTO[$i]->getNumIdListaRecolhimento() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/concluir_edicao_listagem.gif" title="Concluir edição da listagem" title="Concluir edição da listagem" class="infraImg" /></a>';
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

    function acaoRecolher(id) {
        if (confirm('Deseja mesmo enviar para recolhimento a listagem selecionada?')) {
            document.getElementById('hdnIdListaRecolhimento').value = id;
            document.getElementById('frmGestaoListagemRecolhimento').action = '<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_recolher&acao_origem=' . $_GET['acao']) ?>';
            document.getElementById('frmGestaoListagemRecolhimento').submit();
        }
    }

     
    function acaoEditarListagemRecolhimento(id_listagem_recolhimento){
        if (confirm("Confirma a edição da listagem de recolhimento?")) {
            document.getElementById('hdnInfraItemId').value = id_listagem_recolhimento;
            document.getElementById('frmGestaoListagemRecolhimento').action = '<?= $strLinkEditarListagem ?>';
            document.getElementById('frmGestaoListagemRecolhimento').submit();
        }
    }

    function acaoConcluirEdicaoListagemRecolhimento(id_listagem_recolhimento){
        if (confirm("Confirma a conclusão da edição na listagem de recolhimento?")) {
            document.getElementById('hdnInfraItemId').value = id_listagem_recolhimento;
            document.getElementById('frmGestaoListagemRecolhimento').action = '<?= $strLinkConcluirEdicaoListagem ?>';
            document.getElementById('frmGestaoListagemRecolhimento').submit();
        }
    }

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmGestaoListagemRecolhimento" method="post"
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

    <input type="hidden" name="hdnIdListaRecolhimento" id="hdnIdListaRecolhimento" value="" />

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