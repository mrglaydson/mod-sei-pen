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

    $strTitulo = 'Adicionar Processo na Listagem de Eliminação';
    
    switch ($_GET['acao']) {

        case 'gd_lista_eliminacao_procedimento_adicionar':
            PaginaSEI::getInstance()->salvarCamposPost(array('selOrgao', 'selUnidade', 'hdnInfraItensSelecionados', 'hdnInfraItemId'));

            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                $numIdListaEliminacao  = (int) PaginaSEI::getInstance()->recuperarCampo('hdnInfraItemId');
                $arrNumIdsArquivamento = explode(',', PaginaSEI::getInstance()->recuperarCampo('hdnInfraItensSelecionados'));


                // Obtem os processos do arquivamento
                $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                $objMdGdArquivamentoDTO->setNumIdArquivamento($arrNumIdsArquivamento, InfraDTO::$OPER_IN);
                $objMdGdArquivamentoDTO->retDblIdProcedimento();
                $objMdGdArquivamentoDTO->retNumIdArquivamento();

                $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
                $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

                // Adiciona os processos a listagem de Eliminação
                $objMdGdListaElimProcedimentoRN = new MdGdListaElimProcedimentoRN();

                foreach($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO){
                    // Atualiza a situao do arquivamento
                    $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_ENVIADO_ELIMINACAO);
                    $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);

                    // Insere na listagem
                    $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
                    $objMdGdListaElimProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
                    $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($numIdListaEliminacao);

                    $objMdGdListaElimProcedimentoRN->cadastrar($objMdGdListaElimProcedimentoDTO);
                }

                // Atualiza o total de processos da listagem
                $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
                $objMdGdListaEliminacaoDTO->setNumIdListaEliminacao($numIdListaEliminacao);

                $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
                $objMdGdListaEliminacaoRN->atualizarNumeroProcessos($objMdGdListaEliminacaoDTO);
            
            }

            break;     
        default:
            throw new InfraException("Ao '" . $_GET['acao'] . "' no reconhecida.");
    }

    $bolAcaoObservar = SessaoSEI::getInstance()->verificarPermissao('gd_lista_eliminacao_procedimento_adicionar');

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
    $arrComandos[] = '<button type="button" accesskey="P" id="btnAdicionarListagem" value="Adicionar na Listagem" onclick="acaoAdicionarListagemEliminacao('.$_GET['id_listagem_eliminacao'].');" class="infraButton"><span class="infraTeclaAtalho">A</span>dicionar na Listagem de Eliminação</button>';
    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_listar&acao_origem=' . $_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

    $strLinkAdicionarListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_procedimento_adicionar&acao_origem=' . $_GET['acao'].'&id_listagem_eliminacao=' . $_GET['id_listagem_eliminacao']);

    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

    $objMdGdArquivamentoDTO->retNumIdArquivamento();
    $objMdGdArquivamentoDTO->retDthDataArquivamento();
    $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
    $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
    $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
    $objMdGdArquivamentoDTO->retStrObservacaoEliminacao();
    $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
    $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_PREPARACAO_ELIMINACAO);
    $objMdGdArquivamentoDTO->setStrSinAtivo('S');

    $selUnidade = PaginaSEI::getInstance()->recuperarCampo('selUnidade');
    if ($selUnidade && $selUnidade !== 'null') {
        $objMdGdArquivamentoDTO->setNumIdUnidadeCorrente($selUnidade);
    }

    PaginaSEI::getInstance()->prepararOrdenacao($objMdGdArquivamentoDTO, 'DataArquivamento', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objMdGdArquivamentoDTO);

    $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

    PaginaSEI::getInstance()->processarPaginacao($objMdGdArquivamentoDTO);
    $numRegistros = count($arrObjMdGdArquivamentoDTO);

    if ($numRegistros > 0) {
        $strResultado = '';

        $strSumarioTabela = 'Lista de Processos';
        $strCaptionTabela = 'Lista de Processos';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="13%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Unidade', 'DescricaoUnidadeCorrente', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Código da Classificao</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="20%">Descritor do Código</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="14%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'N do Processo', 'ProtocoloFormatado', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Tipo de Processo', 'NomeTipoProcedimento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Data de arquivamento', 'DataArquivamento', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdArquivamentoDTO, 'Observações e/ou Justificativas', 'ObservacaoEliminacao', $arrObjMdGdArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="12%">Aes</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();

        for ($i = 0; $i < $numRegistros; $i++) {

            // Obtem os dados do assunto
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($arrObjMdGdArquivamentoDTO[$i]->getDblIdProtocoloProcedimento());
            $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();
            $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();

            $arrObjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);

            $strCodigoClassificacao = '';
            $strDescritorCodigo = '';

            foreach ($arrObjRelProtocoloAssuntoDTO as $key => $objRelProtocoloAssuntoDTO) {
                if ($key + 1 == count($arrObjRelProtocoloAssuntoDTO)) {
                    $strCodigoClassificacao .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto();
                    $strDescritorCodigo .= $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto();
                } else {
                    $strCodigoClassificacao .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto() . " / ";
                    $strDescritorCodigo .= $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto() . " / ";
                }
            }

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento(), $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoUnidadeCorrente()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($strCodigoClassificacao) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($strDescritorCodigo) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrObservacaoEliminacao()) . '</td>';
            $strResultado .= '<td align="center">';

            if ($bolAcaoObservar) {
                $strLinkObservar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_eliminacao_preparacao_observar&acao_origem=' . $_GET['acao'] . '&id_arquivamento=' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento());
                $strResultado .= '<a href="#" onclick="exibirJanelaObservacao(\'' . $strLinkObservar . '\');"><img src="imagens/alterar.gif" title="Adicionar Observao e/ou Justificativa" title="Adicionar Observao e/ou Justificativa" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoExcluir) {
                $strResultado .= '<a href="#ID-' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '" onclick="acaoExcluir(\'' . $arrObjMdGdArquivamentoDTO[$i]->getNumIdArquivamento() . '\',\'' . $arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="imagens/excluir.gif" title="Excluir da Preparao para Eliminação" alt="Excluir da Preparao para Eliminação" class="infraImg" /></a>&nbsp;';
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
#lblOrgao {position:absolute;left:0%;top:0%;width:20%;}
#selOrgao {position:absolute;left:0%;top:20%;width:20%;}

#lblUnidade {position:absolute;left:21%;top:0%;width:20%;}
#selUnidade {position:absolute;left:21%;top:20%;width:20%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

    
function inicializar() {
infraEfeitoTabelas();
document.getElementById('btnFechar').focus();
}
<? if ($bolAcaoExcluir) { ?>
    function acaoExcluir(id, protocolo_formatado) {
    if (confirm("Confirma a retirada do processo \"" + protocolo_formatado + "\" da preparao de listagem de Eliminação  ?")) {
    document.getElementById('hdnInfraItemId').value = id;
    document.getElementById('frmPrepararListagemEliminacao').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmPrepararListagemEliminacao').submit();
    }
    }

    function acaoExclusaoMultipla() {
    if (document.getElementById('hdnInfraItensSelecionados').value == '') {
    alert('Nenhum Processo Selecionado.');
    return;
    }
    if (confirm("Confirma a retirada dos processo selecionados da preparao de listagem de Eliminação?")) {
    document.getElementById('hdnInfraItemId').value = '';
    document.getElementById('frmPrepararListagemEliminacao').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmPrepararListagemEliminacao').submit();
    }
    }
<? } ?>

<? if ($bolAcaoObservar) { ?>
    function exibirJanelaObservacao(link) {
    infraAbrirJanela(link, 'janelaObservarPreparacaoListagemEliminacao', 720, 300, 'location=0,status=1,resizable=1,scrollbars=1', false);
    }
<? } ?>

    function acaoAdicionarListagemEliminacao(id_listagem_eliminacao){
        if (document.getElementById('hdnInfraItensSelecionados').value == '') {
            alert('Nenhum Processo Selecionado.');
            return;
        }

        document.getElementById('hdnInfraItemId').value = id_listagem_eliminacao;
        document.getElementById('frmPrepararListagemEliminacao').action = '<?= $strLinkAdicionarListagem ?>';
        document.getElementById('frmPrepararListagemEliminacao').submit();
    }

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmPrepararListagemEliminacao" method="post"
      action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
          <?
          PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
          PaginaSEI::getInstance()->abrirAreaDados('9.5em');
          ?>

    <label id="lblOrgao" for="selOrgao" accesskey="" class="infraLabelOpcional">Órgão:</label>
    <select id="selOrgao" name="selOrgao" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <option><?= SessaoSEI::getInstance()->getStrDescricaoOrgaoSistema() ?></option>
    </select>

    <label id="lblUnidade" for="selUnidade" accesskey="" class="infraLabelOpcional">Unidade:</label>
    <select id="selUnidade" name="selUnidade" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
    <?= $strItensSelUnidade ?>
    </select>
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
