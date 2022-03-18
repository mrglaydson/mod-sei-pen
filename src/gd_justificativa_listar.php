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
    PaginaSEI::getInstance()->salvarCamposPost(array('txtNomeJustificativaPesquisa', 'txtDescricaoJustificativaPesquisa', 'selTipoJustificativaPesquisa'));

    switch ($_GET['acao']) {
        case 'gd_justificativa_excluir':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();

                $arrObjMdGdJustificativa = array();

                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $objMdGdJustificativaDTO = new MdGdJustificativaDTO();
                    $objMdGdJustificativaDTO->setNumIdJustificativa($arrStrIds[$i]);
                    $arrObjMdGdJustificativa[] = $objMdGdJustificativaDTO;
                }

                $objMdGdJustificativaRN = new MdGdJustificativaRN();
                $objMdGdJustificativaRN->excluir($arrObjMdGdJustificativa);

                PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;


        case 'gd_justificativa_listar':
            $strTitulo = 'Justificativas de Arquivamento e Desarquivamento';
            if ($_GET['acao_origem'] == 'gd_justificativa_cadastrar') {
                if (isset($_GET['id_justificativa'])) {
                    PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_justificativa']);
                }
            }
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    // Ação de cadastro
    $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('gd_justificativa_cadastrar');

    if ($bolAcaoCadastrar) {
        $arrComandos[] = '<button type="button" accesskey="N" id="btnNova" value="Nova" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_justificativa_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }

    $objMdGdJustificativaRN = new MdGdJustificativaRN();
    $objMdGdJustificativaDTO = new MdGdJustificativaDTO();
    $objMdGdJustificativaDTO->retNumIdJustificativa();
    $objMdGdJustificativaDTO->retStrStaTipo();
    $objMdGdJustificativaDTO->retStrNome();
    $objMdGdJustificativaDTO->retStrDescricao();

    // Busca pelo nome
    $strNome = PaginaSEI::getInstance()->recuperarCampo('txtNomeJustificativaPesquisa');

    if ($strNome !== '') {

        $objMdGdJustificativaDTO->setStrNome('%' . $strNome . '%', InfraDTO::$OPER_LIKE);
    }

    // Busca pelo descrição
    $strDescricao = PaginaSEI::getInstance()->recuperarCampo('txtDescricaoJustificativaPesquisa');

    if ($strDescricao !== '') {
        $objMdGdJustificativaDTO->setStrDescricao('%' . $strDescricao . '%', InfraDTO::$OPER_LIKE);
    }

    // Busca pelo tipo de justificativa
    $strTipo = PaginaSEI::getInstance()->recuperarCampo('selTipoJustificativaPesquisa');

    if ($strTipo !== '') {
        $objMdGdJustificativaDTO->setStrStaTipo($strTipo);
    }

    // Valida as permissões das ações
    $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('gd_justificativa_alterar');
    $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('gd_justificativa_excluir');
    $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('gd_justificativa_visualizar');

    // Ação de exclusão
    if ($bolAcaoExcluir) {
        $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
        $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_justificativa_excluir&acao_origem=' . $_GET['acao']);
    }

    // Ação de impressão
    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    

    PaginaSEI::getInstance()->prepararOrdenacao($objMdGdJustificativaDTO, 'Nome', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objMdGdJustificativaDTO);

    $arrMdGdJustificativaDTO = $objMdGdJustificativaRN->listar($objMdGdJustificativaDTO);

    PaginaSEI::getInstance()->processarPaginacao($objMdGdJustificativaDTO);
    $numRegistros = count($arrMdGdJustificativaDTO);

    if ($numRegistros > 0) {
        $strResultado = '';

        $strSumarioTabela = 'Tabela de Justificativas.';
        $strCaptionTabela = 'Justificativas de Arquivamento e Desarquivamento';

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="29%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdJustificativaDTO, 'Nome', 'Nome', $arrMdGdJustificativaDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdJustificativaDTO, 'Tipo', 'StaTipo', $arrMdGdJustificativaDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="40%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdGdJustificativaDTO, 'Base Legal', 'Descricao', $arrMdGdJustificativaDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';
        for ($i = 0; $i < $numRegistros; $i++) {

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrMdGdJustificativaDTO[$i]->getNumIdJustificativa(), $arrMdGdJustificativaDTO[$i]->getStrNome()) . '</td>';
            $strResultado .= '<td>' . $arrMdGdJustificativaDTO[$i]->getStrNome() . '</td>';
            $strResultado .= '<td>' . MdGdJustificativaRN::obterTituloJustificativa(PaginaSEI::tratarHTML($arrMdGdJustificativaDTO[$i]->getStrStaTipo())) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrMdGdJustificativaDTO[$i]->getStrDescricao()) . '</td>';
            $strResultado .= '<td align="center">';

            if ($bolAcaoConsultar) {
                $strResultado .= '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_justificativa_consultar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_justificativa=' . $arrMdGdJustificativaDTO[$i]->getNumIdJustificativa()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="/infra_css/svg/consultar.svg" title="Consultar Justificativa" alt="Consultar Justificativa" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoAlterar) {
                $strResultado .= '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_justificativa_alterar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_justificativa=' . $arrMdGdJustificativaDTO[$i]->getNumIdJustificativa()) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="/infra_css/svg/alterar.svg" title="Alterar Justificativa" alt="Alterar Justificativa" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoExcluir) {
                $strResultado .= '<a href="#ID-' . $arrMdGdJustificativaDTO[$i]->getNumIdJustificativa() . '" onclick="acaoExcluir(\'' . $arrMdGdJustificativaDTO[$i]->getNumIdJustificativa() . '\',\'' . $arrMdGdJustificativaDTO[$i]->getStrNome() . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="/infra_css/svg/excluir.svg" title="Excluir Justificativa" alt="Excluir Justificativa" class="infraImg" /></a>&nbsp;';
            }

            $strResultado .= '</td></tr>' . "\n";
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
    #lblNomeJustificativaPesquisa {position:absolute;left:0%;top:0%;width:30%;}
    #txtNomeJustificativaPesquisa {position:absolute;left:0%;top:40%;width:30%;}

    #lblDescricaoJustificativaPesquisa {position:absolute;left:32%;top:0%;width:30%;}
    #txtDescricaoJustificativaPesquisa {position:absolute;left:32%;top:40%;width:30%;}

    #lblTipoJustificativaPesquisa {position:absolute;left:64%;top:2%;width:15%;}
    #selTipoJustificativaPesquisa {position:absolute;left:64%;top:42%;width:15%;}

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
    function acaoExcluir(id, desc) {
    if (confirm("Confirma exclusão da Justificativa \"" + desc + "\"?")) {
    document.getElementById('hdnInfraItemId').value = id;
    document.getElementById('frmJustificativasLista').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmJustificativasLista').submit();
    }
    }

    function acaoExclusaoMultipla() {
    if (document.getElementById('hdnInfraItensSelecionados').value == '') {
    alert('Nenhuma Justificativa selecionado.');
    return;
    }
    if (confirm("Confirma exclusão das Justificativas selecionadas?")) {
    document.getElementById('hdnInfraItemId').value = '';
    document.getElementById('frmJustificativasLista').action = '<?= $strLinkExcluir ?>';
    document.getElementById('frmJustificativasLista').submit();
    }
    }
<? } ?>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <form id="frmJustificativasLista" method="post"
          action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
        <?
        //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        PaginaSEI::getInstance()->abrirAreaDados('4.5em');
        ?>

        <label id="lblNomeJustificativaPesquisa" for="txtNomeJustificativaPesquisa" accesskey=""
               class="infraLabelOpcional">Nome:</label>
        <input type="text" id="txtNomeJustificativaPesquisa" name="txtNomeJustificativaPesquisa" value="<?= $strNome ?>"
               class="infraText" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>


        <label id="lblDescricaoJustificativaPesquisa" for="txtDescricaoJustificativaPesquisa" accesskey=""
               class="infraLabelOpcional">Base Legal:</label>
        <input type="text" id="txtDescricaoJustificativaPesquisa" name="txtDescricaoJustificativaPesquisa"
               value="<?= $strDescricao ?>"
               class="infraText" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

        <label id="lblTipoJustificativaPesquisa" for="selTipoJustificativaPesquisa" accesskey=""
               class="infraLabelOpcional">Tipo:</label>
        <select id="selTipoJustificativaPesquisa" name="selTipoJustificativaPesquisa" onchange="this.form.submit();"
                class="infraSelect"
                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value="<?= $strTipo; ?>">
            <option value=""></option>
            <option value="<?= MdGdJustificativaRN::$STA_TIPO_ARQUIVAMENTO ?>" <?= $strTipo== MdGdJustificativaRN::$STA_TIPO_ARQUIVAMENTO ? 'selected' : '' ?>>
                Arquivamento
            </option>
            <option value="<?= MdGdJustificativaRN::$STA_TIPO_DESARQUIVAMENTO ?>" <?= $strTipo == MdGdJustificativaRN::$STA_TIPO_DESARQUIVAMENTO ? 'selected' : '' ?>>
                Desarquivamento
            </option>
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