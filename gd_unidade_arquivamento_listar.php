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
    PaginaSEI::getInstance()->salvarCamposPost(array('selUnidadeOrigem', 'selUnidadeDestino'));

    switch ($_GET['acao']) {
        case 'gd_unidade_arquivamento_listar':
            $strTitulo = 'Unidades de Arquivamento';
            break;

        case 'gd_unidade_arquivamento_excluir':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();

                $arrObjMdGdJustificativa = array();

                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $objMdGdUnidadeArquivamentoDTO = new MdGdUnidadeArquivamentoDTO();
                    $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeArquivamento($arrStrIds[$i]);
                    $arrObjMdGdUnidadeArquivamento[] = $objMdGdUnidadeArquivamentoDTO;
                }

                $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
                $objMdGdUnidadeArquivamentoRN->excluir($arrObjMdGdUnidadeArquivamento);

                PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;
        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    // Ação de cadastro
    $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('gd_unidade_arquivamento_cadastrar');

    if ($bolAcaoCadastrar) {
        $arrComandos[] = '<button type="button" accesskey="N" id="btnNova" value="Nova" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_unidade_arquivamento_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }

    $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
    $objMdGdUnidadeArquivamentoDTO = new MdGdUnidadeArquivamentoDTO();

    $objMdGdUnidadeArquivamentoDTO->retNumIdUnidadeArquivamento();
    $objMdGdUnidadeArquivamentoDTO->retStrSiglaUnidadeOrigem();
    $objMdGdUnidadeArquivamentoDTO->retStrSiglaUnidadeDestino();
    $objMdGdUnidadeArquivamentoDTO->retStrDescricaoUnidadeOrigem();
    $objMdGdUnidadeArquivamentoDTO->retStrDescricaoUnidadeDestino();
    
    $selUnidadeOrigem = PaginaSEI::getInstance()->recuperarCampo('selUnidadeOrigem');
    if ($selUnidadeOrigem && $selUnidadeOrigem !== 'null') {
        $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeOrigem($selUnidadeOrigem);
    }

    $selUnidadeDestino = PaginaSEI::getInstance()->recuperarCampo('selUnidadeDestino');
    if ($selUnidadeDestino && $selUnidadeDestino !== 'null') {
        $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeDestino($selUnidadeDestino);
    }

    // Valida as permissões das ações
    $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('gd_unidade_arquivamento_alterar');
    $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('gd_unidade_arquivamento_excluir');
    $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('gd_unidade_arquivamento_visualizar');

    // Ação de exclusão
    if ($bolAcaoExcluir) {
        $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
        $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_unidade_arquivamento_excluir&acao_origem=' . $_GET['acao']);
    }

    // Ação de impressão
    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    
    PaginaSEI::getInstance()->prepararOrdenacao($objMdGdUnidadeArquivamentoDTO, 'SiglaUnidadeOrigem', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objMdGdUnidadeArquivamentoDTO);

    $arrMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoRN->listar($objMdGdUnidadeArquivamentoDTO);
    
    PaginaSEI::getInstance()->processarPaginacao($objMdGdUnidadeArquivamentoDTO);
    $numRegistros = count($arrMdGdUnidadeArquivamentoDTO);

    if ($numRegistros > 0) {
        $strResultado = '';

        $strSumarioTabela = 'Tabela de Unidades de Arquivo.';
        $strCaptionTabela = 'Unidades de Arquivo';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="40%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdUnidadeArquivamentoDTO, 'Unidade de Origem', 'DescricaoUnidadeOrigem', $arrMdGdUnidadeArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="40%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdUnidadeArquivamentoDTO, 'Unidade de Arquivo', 'DescricaoUnidadeDestino', $arrMdGdUnidadeArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        for ($i = 0; $i < $numRegistros; $i++) {

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrMdGdUnidadeArquivamentoDTO[$i]->getNumIdUnidadeArquivamento(), $arrMdGdUnidadeArquivamentoDTO[$i]->getStrSiglaUnidadeOrigem()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrMdGdUnidadeArquivamentoDTO[$i]->getStrSiglaUnidadeOrigem() . ' - ' . $arrMdGdUnidadeArquivamentoDTO[$i]->getStrDescricaoUnidadeOrigem()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrMdGdUnidadeArquivamentoDTO[$i]->getStrSiglaUnidadeDestino() . ' - ' . $arrMdGdUnidadeArquivamentoDTO[$i]->getStrDescricaoUnidadeDestino()) . '</td>';
            $strResultado .= '<td align="center">';

            if ($bolAcaoConsultar) {
                $strResultado .= '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_unidade_arquivamento_visualizar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_unidade_arquivamento=' . $arrMdGdUnidadeArquivamentoDTO[$i]->getNumIdUnidadeArquivamento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="/infra_css/svg/consultar.svg" title="Consultar Unidade de Arquivamento" alt="Consultar Unidade de Arquivamento" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoAlterar) {
                $strResultado .= '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_unidade_arquivamento_alterar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_unidade_arquivamento=' . $arrMdGdUnidadeArquivamentoDTO[$i]->getNumIdUnidadeArquivamento()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="/infra_css/svg/alterar.svg" title="Alterar Unidade de Arquivamento" alt="Alterar Unidade de Arquivamento" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoExcluir) {
                $strResultado .= '<a href="#ID-' . $arrMdGdUnidadeArquivamentoDTO[$i]->getNumIdUnidadeArquivamento() . '" onclick="acaoExcluir(\'' . $arrMdGdUnidadeArquivamentoDTO[$i]->getNumIdUnidadeArquivamento() . '\',\'' . $arrMdGdUnidadeArquivamentoDTO[$i]->getStrSiglaUnidadeOrigem() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="/infra_css/svg/excluir.svg" title="Excluir Unidade de Arquivamento" alt="Excluir Unidade de Arquivamento" class="infraImg" /></a>&nbsp;';
            }

            $strResultado .= '</td></tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    // Busca uma lista de unidades
    $strItensSelUnidadesOrigem = UnidadeINT::montarSelectSiglaDescricao('null', '&nbsp;', $selUnidadeOrigem);
    $strItensSelUnidadesDestino = UnidadeINT::montarSelectSiglaDescricao('null', '&nbsp;', $selUnidadeDestino);
    
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
#lblUnidadeOrigem {position:absolute;left:0%;top:0%;width:30%;}
#selUnidadeOrigem {position:absolute;left:0%;top:40%;width:30%;}

#lblUnidadeDestino {position:absolute;left:32%;top:0%;width:30%;}
#selUnidadeDestino {position:absolute;left:32%;top:40%;width:30%;}


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
    function acaoExcluir(id, unidade_origem, unidade_destino) {
    if (confirm("Confirma exclusão da Unidade de Arquivo \"" + unidade_destino + "\" para a unidade \"" + unidade_origem + "\" ?")) {
    document.getElementById('hdnInfraItemId').value = id;
    document.getElementById('frmUnidadesArquivamentoLista').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmUnidadesArquivamentoLista').submit();
    }
    }

    function acaoExclusaoMultipla() {
    if (document.getElementById('hdnInfraItensSelecionados').value == '') {
    alert('Nenhuma Unidade selecionada.');
    return;
    }
    if (confirm("Confirma exclusão das Unidades de Arquivo selecionadas?")) {
    document.getElementById('hdnInfraItemId').value = '';
    document.getElementById('frmUnidadesArquivamentoLista').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmUnidadesArquivamentoLista').submit();
    }
    }
<? } ?>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmUnidadesArquivamentoLista" method="post"
      action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
          <?
          PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
          PaginaSEI::getInstance()->abrirAreaDados('4.5em');
          ?>
    <label id="lblUnidadeOrigem" for="selUnidadeOrigem" accesskey="" class="infraLabelOpcional">Unidade de Origem:</label>
    <select id="selUnidadeOrigem" name="selUnidadeOrigem" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelUnidadesOrigem ?>
    </select>

    <label id="lblUnidadeDestino" for="selUnidadeDestino" accesskey="" class="infraLabelOpcional">Unidade de Arquivo:</label>
    <select id="selUnidadeDestino" name="selUnidadeDestino" onchange="this.form.submit();" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" >
        <?= $strItensSelUnidadesDestino ?>
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