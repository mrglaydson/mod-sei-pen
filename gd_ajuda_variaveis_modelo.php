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

//  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    $arrVariaveis = array();

    switch ($_GET['acao']) {

        case 'gd_ajuda_variaveis_modelo_arquivamento':

            $strTitulo = 'Vari�veis Dispon�veis no Modelo';
            $arrVariaveis[] = array('@motivo@', 'Motivo do arquivamento');
            $arrVariaveis[] = array('@data_arquivamento@', 'Data do arquivamento');
            $arrVariaveis[] = array('@responsavel_arquivamento@', 'Respons�vel pelo arquivamento');
            break;
        case 'gd_ajuda_variaveis_modelo_desarquivamento':

            $strTitulo = 'Vari�veis Dispon�veis no Modelo';
            $arrVariaveis[] = array('@motivo@', 'Motivo do desarquivamento');
            $arrVariaveis[] = array('@data_desarquivamento@', 'Data do desarquivamento');
            $arrVariaveis[] = array('@responsavel_desarquivamento@', 'Respons�vel pelo desarquivamento');
            break;
        case 'gd_ajuda_variaveis_modelo_listagem_eliminacao':

            $strTitulo = 'Vari�veis Dispon�veis no Modelo';

            $arrVariaveis[] = array('@orgao@', '�rg�o gerador');
            $arrVariaveis[] = array('@unidade@', 'Unidade geradora');
            $arrVariaveis[] = array('@numero_listagem@', 'N�mero da listagem');
            $arrVariaveis[] = array('@folha@', 'N�mero de folhas');
            $arrVariaveis[] = array('@tabela@', 'Tabela de detalhamento da listagem');
            $arrVariaveis[] = array('@mensuracao_total@', 'Mensura��o total');
            $arrVariaveis[] = array('@datas_limites_gerais@', 'Datas limite do arquivamento');
            break;
        case 'gd_ajuda_variaveis_modelo_documento_eliminacao':

            $strTitulo = 'Vari�veis Dispon�veis no Modelo';

            $arrVariaveis[] = array('@orgao@', '�rg�o gerador');
            $arrVariaveis[] = array('@unidade@', 'Unidade geradora');
            $arrVariaveis[] = array('@data_eliminacao@', 'Data da elimina��o');
            $arrVariaveis[] = array('@responsavel_eliminacao@', 'Respons�vel pela elimina��o');
            break;
        case 'gd_ajuda_variaveis_modelo_listagem_recolhimento':

            $strTitulo = 'Vari�veis Dispon�veis no Modelo';

            $arrVariaveis[] = array('@orgao@', '�rg�o gerador');
            $arrVariaveis[] = array('@unidade@', 'Unidade geradora');
            $arrVariaveis[] = array('@numero_listagem@', 'N�mero da listagem');
            $arrVariaveis[] = array('@folha@', 'N�mero de folhas');
            $arrVariaveis[] = array('@tabela@', 'Tabela de detalhamento da listagem');
            $arrVariaveis[] = array('@mensuracao_total@', 'Mensura��o total');
            $arrVariaveis[] = array('@datas_limites_gerais@', 'Datas limite do arquivamento');
            break;
        case 'gd_ajuda_variaveis_modelo_documento_recolhimento':

            $strTitulo = 'Vari�veis Dispon�veis no Modelo';

            $arrVariaveis[] = array('@orgao@', '�rg�o gerador');
            $arrVariaveis[] = array('@unidade@', 'Unidade geradora');
            $arrVariaveis[] = array('@data_recolhimento@', 'Data do recolhimento');
            $arrVariaveis[] = array('@responsavel_recolhimento@', 'Respons�vel pelo recolhimento');
                break;
        default:
            throw new InfraException("A��o '" . $_GET['acao'] . "' n�o reconhecida.");
    }

    $numRegistros = count($arrVariaveis);

    $strResultado = '';
    $strResultado .= '<table width="99%" class="infraTable" summary="Tabela de Vari�veis Dispon�veis">' . "\n"; //80
    $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela('Vari�veis Dispon�veis', $numRegistros) . '</caption>';
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="30%">Vari�vel</th>' . "\n";
    $strResultado .= '<th class="infraTh">Descri��o</th>' . "\n";
    $strResultado .= '</tr>' . "\n";
    $strCssTr = '';
    for ($i = 0; $i < $numRegistros; $i++) {

        $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
        $strResultado .= $strCssTr;

        $strResultado .= '<td><span style="font-family: Courier New">' . PaginaSEI::tratarHTML($arrVariaveis[$i][0]) . '</span></td>';
        $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrVariaveis[$i][1]) . '</td>';

        $strResultado .= '</tr>' . "\n";
    }
    $strResultado .= '</table>';
} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo);
PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros, true);
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>