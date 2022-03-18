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

    $strTitulo = 'Recolhimento de Documentos Físicos';

    switch ($_GET['acao']) {

        case 'gd_lista_recolhimento_documentos_fisicos_listar':

            PaginaSEI::getInstance()->salvarCamposPost(array('txtPeriodoEmissaoDe', 'txtPeriodoEmissaoAte', 'txtAnoLimiteDe', 'txtAnoLimiteAte'));

            if (!$_GET['id_listagem_recolhimento']) {
                header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_listar&acao_origem=' . $_GET['acao']));
            }

            // Busca os dados da listagem de recolhimento
            $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
            $objMdGdListaRecolhimentoDTO->setNumIdListaRecolhimento($_GET['id_listagem_recolhimento']);
            $objMdGdListaRecolhimentoDTO->retNumAnoLimiteInicio();
            $objMdGdListaRecolhimentoDTO->retNumAnoLimiteFim();
            $objMdGdListaRecolhimentoDTO->retStrNumero();
            
            $objMdGdListaRecolhimentoRN = new MdGdListaRecolhimentoRN();
            $objMdGdListaRecolhimentoDTO = $objMdGdListaRecolhimentoRN->consultar($objMdGdListaRecolhimentoDTO);
            
            // Busca os processos da listagem de recolhimento
            $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
            $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($_GET['id_listagem_recolhimento']);
            $objMdGdListaRecolProcedimentoDTO->retDblIdProcedimento();

            $objMdGdListaRecolProcedimentoRN = new MdGdListaRecolProcedimentoRN();
            $arrObjMdGdListaRecolProcedimentoDTO = $objMdGdListaRecolProcedimentoRN->listar($objMdGdListaRecolProcedimentoDTO);
            $arrIdDocumento = [0];

            if ($arrObjMdGdListaRecolProcedimentoDTO) {
                $arrIdProcedimento = explode(',', InfraArray::implodeArrInfraDTO($arrObjMdGdListaRecolProcedimentoDTO, 'IdProcedimento'));

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

    $arrComandos = array();
    $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    $bolAcaoRecolherDocumentoFisico = SessaoSEI::getInstance()->verificarPermissao('gd_lista_recolhimento_documentos_fisicos_recolher');
    if ($bolAcaoRecolherDocumentoFisico) {
        $strLinkConfirmarRecolhimento = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_documentos_fisicos_recolher&id_listagem_recolhimento=' . $_GET['id_listagem_recolhimento'] . '&acao_origem=' . $_GET['acao']);
        $arrComandos[] = '<button type="button" accesskey="P" id="btnConfirmarRecolhimento" value="Confirmar Recolhimento" onclick="acaoConfirmarRecolhimento(\'' . $strLinkConfirmarRecolhimento . '\');" class="infraButton"><span class="infraTeclaAtalho">C</span>onfirmar Recolhimento</button>';
    }

    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_lista_recolhimento_listar&acao_origem=' . $_GET['acao']) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

    // Faz a busca de arquivamentos
    $objArquivamentoRN = new ArquivamentoRN();
    $objArquivamentoDTO = new ArquivamentoDTO();
    $objArquivamentoDTO->retDblIdProtocoloDocumento();
    $objArquivamentoDTO->retStrSiglaUnidadeArquivamento();
    $objArquivamentoDTO->retStrProtocoloFormatadoProcedimento();
    $objArquivamentoDTO->retStrNomeTipoProcedimento();
    $objArquivamentoDTO->retStrProtocoloFormatadoDocumento();
    $objArquivamentoDTO->retStrNomeTipoLocalizador();
    $objArquivamentoDTO->retNumIdLocalizador();
    $objArquivamentoDTO->setDblIdProtocoloDocumento($arrIdDocumento, InfraDTO::$OPER_IN);

    $selUnidade = PaginaSEI::getInstance()->recuperarCampo('selUnidade');
    if ($selUnidade) {
        $objArquivamentoDTO->setNumIdUnidadeArquivamento($selUnidade);
    }

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
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO, 'Unidade', 'SiglaUnidadeArquivamento', $arrObjArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO, 'Processo', 'ProtocoloFormatadoProcedimento', $arrObjArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="19%">' . PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO, 'Tipo de Processo', 'NomeTipoProcedimento', $arrObjArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">' . PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO, 'Unidade', 'ProtocoloFormatadoDocumento', $arrObjArquivamentoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Local</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Tipo de Localizador</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Localizador</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Tipo de Suporte</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="10%">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';

        $objLocalizadorRN = new LocalizadorRN();
        $objMdGdDocumentoFisicoRecolRN = new MdGdDocumentoFisicoRecolRN();

        for ($i = 0; $i < $numRegistros; $i++) {

            // Busca os dados do localizador do arquivamento
            $objLocalizadorDTO = new LocalizadorDTO();
            $objLocalizadorDTO->setNumIdLocalizador($arrObjArquivamentoDTO[$i]->getNumIdLocalizador());
            $objLocalizadorDTO->retStrNomeLugarLocalizador();
            $objLocalizadorDTO->retStrNomeTipoSuporte();
            $objLocalizadorDTO->retStrIdentificacao();

            $objLocalizadorDTO = $objLocalizadorRN->consultarRN0619($objLocalizadorDTO);
          
            // Verifica se o documento foi eliminado
            $objMdGdDocumentoFisicoRecolDTO = new MdGdDocumentoFisicoRecolDTO();
            $objMdGdDocumentoFisicoRecolDTO->setDblIdDocumento($arrObjArquivamentoDTO[$i]->getDblIdProtocoloDocumento());
            $countDocumentoFisicoRecolinado = $objMdGdDocumentoFisicoRecolRN->contar($objMdGdDocumentoFisicoRecolDTO);

            $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            $strResultado .= $strCssTr;

            if ($countDocumentoFisicoRecolinado == 0) {
                $strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjArquivamentoDTO[$i]->getDblIdProtocoloDocumento(), $arrObjArquivamentoDTO[$i]->getStrProtocoloFormatadoDocumento()) . '</td>';
            } else {
                $strResultado .= '<td valign="top"></td>';
            }

            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrSiglaUnidadeArquivamento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrProtocoloFormatadoProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrProtocoloFormatadoDocumento()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($objLocalizadorDTO ? $objLocalizadorDTO->getStrNomeLugarLocalizador() : '') . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrNomeTipoLocalizador()) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($objLocalizadorDTO ? $objLocalizadorDTO->getStrIdentificacao() : '') . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($objLocalizadorDTO ? $objLocalizadorDTO->getStrNomeTipoSuporte() : '') . '</td>';
            $strResultado .= '<td align="center">';

            if ($countDocumentoFisicoRecolinado) {
                $strResultado .= '<img src="imagens/circulo_verde.png" title="Eliminado" title="Eliminado" class="infraImg" />';
            } else {
                $strResultado .= '<img src="imagens/circulo_vermelho.png" title="Não Eliminado" title="Não Eliminado" class="infraImg" />';
            }


            $strResultado .= '</td></tr>' . "\n";
            $strResultado .= '</tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    // Busca uma lista de unidades
    $strItensSelUnidade = UnidadeINT::montarSelectSiglaDescricao('null', '&nbsp;', $selUnidade);

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

#lblListagemRecolhimento { position: absolute; top: 45px; left: 0px; }
#lblDatasLimite { position: absolute; top: 45px; left: 191px; }
#lblProcessoSei { position: absolute; top: 45px; left: 329px; }

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>


function inicializar() {
infraEfeitoTabelas();
document.getElementById('btnFechar').focus();
}

<? if ($bolAcaoRecolherDocumentoFisico) { ?>
    function acaoConfirmarRecolhimento(link) {
        if (document.getElementById('hdnInfraItensSelecionados').value == '') {
            alert('Nenhum Documento Selecionado.');
            return;
        }

        infraAbrirJanela(link, 'janelaObservarPreparacaoListagemRecolhimento', 720, 300, 'location=0,status=1,resizable=1,scrollbars=1', false);
    }
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmPrepararListagemRecolhimento" method="post"
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

    <p id="lblListagemRecolhimento">
        <label class="infraLabelObrigatorio">Listagem de Recolhimento: </label><?= $objMdGdListaRecolhimentoDTO->getStrNumero(); ?>
    </p>
    <p id="lblDatasLimite">
        <label class="infraLabelObrigatorio">Datas-Limite: </label><?= $objMdGdListaRecolhimentoDTO->getNumAnoLimiteInicio(). ' - '.$objMdGdListaRecolhimentoDTO->getNumAnoLimiteFim(); ?>
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