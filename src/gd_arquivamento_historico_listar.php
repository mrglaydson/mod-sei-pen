<?
try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    //////////////////////////////////////////////////////////////////////////////
    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(false);
    InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    SessaoSEI::getInstance()->validarLink();
    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  switch ($_GET['acao']) {
    case 'gd_arquivamento_historico_listar':
        $strTitulo = 'Histórico de arquivamento';
        break;

    default:
        throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
  }


    // Busca os arquivamentos dos processos
    $arrIdArquivamento = [];
    $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
    $objMdGdArquivamentoDTO->setDblIdProcedimento($_GET['id_procedimento']);
    $objMdGdArquivamentoDTO->retNumIdArquivamento();

    $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
    $arrObjMdGdArquivamentoDTO = InfraArray::indexarArrInfraDTO($objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO), 'IdArquivamento');
    $arrIdArquivamentos = array_keys($arrObjMdGdArquivamentoDTO);

  if ($arrIdArquivamentos) {
      // Lista o histórico de arquivamento do processo
      $objMdGdHistoricoArquivamentoDTO = new MdGdArquivamentoHistoricoDTO();
      $objMdGdHistoricoArquivamentoDTO->setNumIdArquivamento($arrIdArquivamentos, InfraDTO::$OPER_IN);
      $objMdGdHistoricoArquivamentoDTO->retStrNomeUsuario();
      $objMdGdHistoricoArquivamentoDTO->retStrSiglaUnidade();
      $objMdGdHistoricoArquivamentoDTO->retStrDescricaoUnidade();
      $objMdGdHistoricoArquivamentoDTO->retStrSituacaoAntiga();
      $objMdGdHistoricoArquivamentoDTO->retStrSituacaoAtual();
      $objMdGdHistoricoArquivamentoDTO->retStrDescricao();
      $objMdGdHistoricoArquivamentoDTO->retDthHistorico();
      $objMdGdHistoricoArquivamentoDTO->setOrdDthHistorico(InfraDTO::$TIPO_ORDENACAO_DESC);

      $objMdGdHistoricoArquivamentoRN = new MdGdArquivamentoHistoricoRN();
      $arrMdGdHistoricoArquivamentoDTO = $objMdGdHistoricoArquivamentoRN->listar($objMdGdHistoricoArquivamentoDTO);
      $numRegistros = count($arrMdGdHistoricoArquivamentoDTO);
        
      $strResultado = '';

      $strSumarioTabela = 'Histórico de Arquivamento.';
      $strCaptionTabela = 'Histórico de Arquivamento do Processo';

      $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
      $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
      $strResultado .= '<tr>';
      $strResultado .= '<th class="infraTh" width="25%">Descrição do Histórico</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="10%">Situação Antiga</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="10%">Situação Atual</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="15%">Unidade</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="15%">Usuário Responsável</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="15%">Data e Hora</th>' . "\n"; 
      $strResultado .= '</tr>' . "\n";
        
      $strCssTr = '';
    for ($i = 0; $i < $numRegistros; $i++) {

        $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
        $strResultado .= $strCssTr;
        $strResultado .= '<td>' . $arrMdGdHistoricoArquivamentoDTO[$i]->getStrDescricao() . '</td>';
        $strResultado .= '<td>' . MdGdArquivamentoRN::obterSituacoesArquivamento()[$arrMdGdHistoricoArquivamentoDTO[$i]->getStrSituacaoAntiga()] . '</td>';
        $strResultado .= '<td>' . MdGdArquivamentoRN::obterSituacoesArquivamento()[$arrMdGdHistoricoArquivamentoDTO[$i]->getStrSituacaoAtual()] . '</td>';
        $strResultado .= '<td>' . $arrMdGdHistoricoArquivamentoDTO[$i]->getStrSiglaUnidade() . ' - ' . $arrMdGdHistoricoArquivamentoDTO[$i]->getStrDescricaoUnidade() . '</td>';
        $strResultado .= '<td>' . $arrMdGdHistoricoArquivamentoDTO[$i]->getStrNomeUsuario() . '</td>';
        $strResultado .= '<td>' . substr($arrMdGdHistoricoArquivamentoDTO[$i]->getDthHistorico(), 0, 16) . '</td>';
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
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <div id="divGeral" class="infraAreaDados" style="height:60em;">
        <?
        //PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros);
        PaginaSEI::getInstance()->montarAreaDebug();
        ?>
    </div>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>
