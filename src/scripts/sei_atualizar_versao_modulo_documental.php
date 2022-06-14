<?php

require_once dirname(__FILE__) . '/../../web/SEI.php';

class VersaoSeiRN extends InfraScriptVersao
{
    const PARAMETRO_VERSAO_MODULO = 'VERSAO_MODULO_GESTAO_DOCUMENTAL';
    const NOME_MODULO = 'Módulo de Gestão Documental';

    protected $objInfraBanco;
    protected $objMetaBD;
    protected $objInfraSequencia;
    protected $objInfraParametro;

    public function __construct()
    {
        parent::__construct();
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');

        SessaoSEI::getInstance(false);

        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(true);
        InfraDebug::getInstance()->limpar();
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    protected function verificarVersaoInstaladaControlado()
    {
        $objInfraParametroDTO = new InfraParametroDTO();
        $objInfraParametroDTO->setStrNome(VersaoSeiRN::PARAMETRO_VERSAO_MODULO);
        $objInfraParametroBD = new InfraParametroBD(BancoSEI::getInstance());
        if ($objInfraParametroBD->contar($objInfraParametroDTO) == 0) {
            $objInfraParametroDTO->setStrValor('0.0.0');
            $objInfraParametroBD->cadastrar($objInfraParametroDTO);
        }
    }

    /**
     * Cadastra os parâmetros de configurao padres do módulo
     *
     * @return void
     */
    protected function cadastrarParametros()
    {
        $despachoArquivamento = $this->cadastrarTipoDocumento(1, 'Termo de Arquivamento', 'Termo automático para arquivamento de processos no sistema', SerieRN::$TA_INTERNO, 34, SerieRN::$TN_SEQUENCIAL_UNIDADE);
        $despachoDesarquivamento = $this->cadastrarTipoDocumento(1, 'Termo de Desarquivamento', 'Termo automático para desarquivamento de processos no sistema', SerieRN::$TA_INTERNO, 34, SerieRN::$TN_SEQUENCIAL_UNIDADE);

        $tipoProcedimentoEliminacao = $this->cadastrarTipoProcedimento('Processo de eliminação', 'Processo de eliminação');
        $tipoDocumentoListagemEliminacao = $this->cadastrarTipoDocumento(1, 'Listagem de eliminação de documentos', 'Termo automático para listagem de eliminação de processos no sistema', SerieRN::$TA_INTERNO, 34, SerieRN::$TN_SEQUENCIAL_UNIDADE);
        $tipoDocumentoEliminacao = $this->cadastrarTipoDocumento(1, 'Termo de Eliminação', 'Termo automático para eliminação de processos no sistema', SerieRN::$TA_INTERNO, 34, SerieRN::$TN_SEQUENCIAL_UNIDADE);;

        $tipoProcedimentoListagemRecolhimento = $this->cadastrarTipoProcedimento('Processo de listagem de recolhimento', 'Processo de listagem de recolhimento');
        $tipoDocumentoListagemRecolhimento = $this->cadastrarTipoDocumento(1, 'Listagem de recolhimento de documentos', 'Termo automático para listagem de recolhimento de processos no sistema', SerieRN::$TA_INTERNO, 34, SerieRN::$TN_SEQUENCIAL_UNIDADE);
        $tipoDocumentoRecolhimento = $this->cadastrarTipoDocumento(1, 'Termo de Recolhimento', 'Termo automático para recolhimento de processos no sistema', SerieRN::$TA_INTERNO, 34, SerieRN::$TN_SEQUENCIAL_UNIDADE);;

        $this->cadastrarParametro('DESPACHO_ARQUIVAMENTO', $despachoArquivamento);
        $this->cadastrarParametro('DESPACHO_DESARQUIVAMENTO', $despachoDesarquivamento);

        $this->cadastrarParametro('TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO', $tipoProcedimentoEliminacao);
        $this->cadastrarParametro('TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO', $tipoDocumentoListagemEliminacao);
        $this->cadastrarParametro('TIPO_DOCUMENTO_ELIMINACAO', $tipoDocumentoEliminacao);

        $this->cadastrarParametro('TIPO_PROCEDIMENTO_LISTAGEM_RECOLHIMENTO', $tipoProcedimentoListagemRecolhimento);
        $this->cadastrarParametro('TIPO_DOCUMENTO_LISTAGEM_RECOLHIMENTO', $tipoDocumentoListagemRecolhimento);
        $this->cadastrarParametro('TIPO_DOCUMENTO_RECOLHIMENTO', $tipoDocumentoRecolhimento);
    }

    /**
     * Cadastra um parâmetro no mdulo
     *
     * @param string $strNome
     * @param string $strValor
     * @return void
     */
    protected function cadastrarParametro($strNome, $strValor)
    {
        $objMdGdParametroDTO = new MdGdParametroDTO();
        $objMdGdParametroDTO->setStrNome($strNome);
        $objMdGdParametroDTO->setStrValor($strValor);

        $objMdGdParametroRN = new MdGdParametroRN();
        $objMdGdParametroRN->cadastrar($objMdGdParametroDTO);
    }


    /**
     * Cadastra os modelos de documento padres do mdulo
     *
     * @return void
     */
    public function cadastrarModelos()
    {
        $strModeloDespachoArquivamento = '<p class="Texto_Alinhado_Esquerda">&nbsp;</p>
        <p class="Texto_Justificado">O processo n&ordm; @processo@ foi arquivado eletronicamente conforme as informa&ccedil;&otilde;es abaixo descritas:</p>
        
        <p class="Texto_Alinhado_Esquerda">&nbsp;</p>
        
        <table summary="Tabela de Variáveis Disponíveis">
            <tbody>
                <tr>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">Local</p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">@cidade_unidade@, @sigla_uf_unidade@</p>
                    </td>
                </tr>
                <tr>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">Data</p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">@data_arquivamento@</p>
                    </td>
                </tr>
                <tr>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">Usu&aacute;rio</p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">@responsavel_arquivamento@</p>
                    </td>
                </tr>
                <tr>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">Justificativa</p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">@motivo@</p>
                    </td>
                </tr>
            </tbody>
        </table>
        ';

        $strModeloDespachoDesarquivamento = '<p class="Texto_Alinhado_Esquerda">&nbsp;</p>
        <p class="Texto_Alinhado_Esquerda">O processo n&ordm; @processo@ foi desarquivado eletronicamente conforme as informa&ccedil;&otilde;es abaixo descritas:</p>
        
        <p class="Texto_Alinhado_Esquerda">&nbsp;</p>
        
        <table summary="Tabela de Variáveis Disponíveis">
            <tbody>
                <tr>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">Local</p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">@cidade_unidade@, @sigla_uf_unidade@</p>
                    </td>
                </tr>
                <tr>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">Data</p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">@data_desarquivamento@</p>
                    </td>
                </tr>
                <tr>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">Usu&aacute;rio</p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">@responsavel_desarquivamento@</p>
                    </td>
                </tr>
                <tr>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">Justificativa</p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Alinhado_Esquerda">@motivo@</p>
                    </td>
                </tr>
            </tbody>
        </table>
        ';

        $strModeloListagemEliminacao = '<meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <style type="text/css">p.Citacao {font-size:10pt;font-family:Calibri;word-wrap:normal;margin:4pt 0 4pt 160px;text-align:justify;} p.Item_Alinea_Letra {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt 6pt 6pt 120px;counter-increment:letra_minuscula;} p.Item_Alinea_Letra:before {content:counter(letra_minuscula, lower-latin) ") ";display:inline-block;width:5mm;font-weight:normal;} p.Item_Inciso_Romano {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt 6pt 6pt 120px;counter-increment:romano_maiusculo;counter-reset:letra_minuscula;} p.Item_Inciso_Romano:before {content:counter(romano_maiusculo, upper-roman) " - ";display:inline-block;width:15mm;font-weight:normal;} p.Item_Nivel1 {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;counter-increment:item-n1;counter-reset:item-n2 item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel1:before {content:counter(item-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n2;counter-reset:item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel2:before {content:counter(item-n1) "." counter(item-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n3;counter-reset:item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel3:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Item_Nivel4:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) "."  counter(item-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel1 {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt;counter-increment:paragrafo-n1;counter-reset:paragrafo-n2 paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel1:before {content:counter(paragrafo-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n2;counter-reset:paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel2:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n3;counter-reset:paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel3:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel4:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) "." counter(paragrafo-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Tabela_Texto_8 {font-size:8pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Direita {font-size:11pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Esquerda {font-size:11pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Centralizado {font-size:11pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:0 3pt 0;} p.Texto_Alinhado_Direita {font-size:12pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda_Espacamento_Simples {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0;} p.Texto_Alinhado_Esquerda_Espacamento_Simples_Maiusc {font-size:12pt;font-family:Calibri;text-align:left;text-transform:uppercase;word-wrap:normal;margin:0;} p.Texto_Centralizado {font-size:12pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:6pt;} p.Texto_Centralizado_Maiusculas {font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Centralizado_Maiusculas_Negrito {font-weight:bold;font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Espaco_Duplo_Recuo_Primeira_Linha {letter-spacing:0.2em;font-weight:bold;font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Fundo_Cinza_Maiusculas_Negrito {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Fundo_Cinza_Negrito {font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado_Maiusculas {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;text-transform:uppercase;} p.Texto_Justificado_Recuo_Primeira_Linha {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Justificado_Recuo_Primeira_Linha_Esp_Simples {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:0 0 0 6pt;}
        </style>
        <title></title>
        <p class="Texto_Centralizado">&nbsp;</p>
        
        <p class="Texto_Centralizado"><!--%3Cmeta%20http-equiv%3D%22Pragma%22%20content%3D%22no-cache%22%20%2F%3E--><!--%3Cmeta%20http-equiv%3D%22Content-Type%22%20content%3D%22text%2Fhtml%3B%20charset%3Diso-8859-1%22%3E-->
        <style type="text/css">p.Citacao {font-size:10pt;font-family:Calibri;word-wrap:normal;margin:4pt 0 4pt 160px;text-align:justify;} p.Item_Alinea_Letra {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt 6pt 6pt 120px;counter-increment:letra_minuscula;} p.Item_Alinea_Letra:before {content:counter(letra_minuscula, lower-latin) ") ";display:inline-block;width:5mm;font-weight:normal;} p.Item_Inciso_Romano {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt 6pt 6pt 120px;counter-increment:romano_maiusculo;counter-reset:letra_minuscula;} p.Item_Inciso_Romano:before {content:counter(romano_maiusculo, upper-roman) " - ";display:inline-block;width:15mm;font-weight:normal;} p.Item_Nivel1 {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;counter-increment:item-n1;counter-reset:item-n2 item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel1:before {content:counter(item-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n2;counter-reset:item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel2:before {content:counter(item-n1) "." counter(item-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n3;counter-reset:item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel3:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Item_Nivel4:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) "."  counter(item-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel1 {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt;counter-increment:paragrafo-n1;counter-reset:paragrafo-n2 paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel1:before {content:counter(paragrafo-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n2;counter-reset:paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel2:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n3;counter-reset:paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel3:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel4:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) "." counter(paragrafo-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Tabela_Texto_8 {font-size:8pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Direita {font-size:11pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Esquerda {font-size:11pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Centralizado {font-size:11pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:0 3pt 0;} p.Texto_Alinhado_Direita {font-size:12pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda_Espacamento_Simples {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0;} p.Texto_Alinhado_Esquerda_Espacamento_Simples_Maiusc {font-size:12pt;font-family:Calibri;text-align:left;text-transform:uppercase;word-wrap:normal;margin:0;} p.Texto_Centralizado {font-size:12pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:6pt;} p.Texto_Centralizado_Maiusculas {font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Centralizado_Maiusculas_Negrito {font-weight:bold;font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Espaco_Duplo_Recuo_Primeira_Linha {letter-spacing:0.2em;font-weight:bold;font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Fundo_Cinza_Maiusculas_Negrito {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Fundo_Cinza_Negrito {font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado_Maiusculas {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;text-transform:uppercase;} p.Texto_Justificado_Recuo_Primeira_Linha {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Justificado_Recuo_Primeira_Linha_Esp_Simples {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:0 0 0 6pt;}
        </style>
        </p>
        
        <table border="1" cellpadding="1" cellspacing="1" style="margin-left: auto; margin-right: auto; width: 918.333px;">
            <tbody>
                <tr>
                    <td style="width: 129px;">
                    <p class="Tabela_Texto_Centralizado">@logo@</p>
                    </td>
                    <td colspan="4" rowspan="1" style="width: 474px;">
                    <p class="Tabela_Texto_Alinhado_Esquerda"><strong>LISTAGEM DE ELIMINA&Ccedil;&Atilde;O DE DOCUMENTOS</strong></p>
        
                    <p class="Tabela_Texto_Alinhado_Esquerda"><strong>@descricao_orgao_maiusculas@ - @sigla_orgao_origem@</strong></p>
        
                    <p class="Tabela_Texto_Alinhado_Esquerda"><strong>@descricao_unidade_maiusculas@ - @sigla_unidade@</strong></p>
        
                    <p class="Tabela_Texto_Alinhado_Esquerda">&nbsp;</p>
                    </td>
                    <td style="width: 314px;">
                    <p class="Tabela_Texto_Alinhado_Esquerda"><strong>@sigla_orgao_origem@/@sigla_unidade@</strong></p>
        
                    <p class="Tabela_Texto_Alinhado_Esquerda"><strong>Listagem n&ordm; @numero_listagem@</strong></p>
        
                    <p class="Tabela_Texto_Alinhado_Esquerda"><strong>Folha:1/1</strong></p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p>@tabela@</p>
        
        <table border="1" cellpadding="1" cellspacing="1" style="margin-left: auto; margin-right: auto; width: 918.333px;">
            <tbody>
                <tr>
                    <td colspan="6" style="width: 910px;">
                    <p class="Tabela_Texto_Alinhado_Esquerda"><strong>MENSURA&Ccedil;&Atilde;O TOTAL:&nbsp;</strong>@mensuracao_total@ , @tamanho_total@</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="6" style="width: 910px;">
                    <p class="Tabela_Texto_Alinhado_Esquerda"><strong>DATAS-LIMITE GERAIS:&nbsp;</strong>@datas_limites_gerais@</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p>&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Centralizado">O quadro abaixo somente dever&aacute; ser preenchido se os documentos a serem eliminados necessitarem de comprova&ccedil;&atilde;o de aprova&ccedil;&atilde;o das contas pelos Tribunais de Contas.</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <table border="1" cellpadding="1" cellspacing="1" style="margin-left:auto;margin-right:auto;width:870px;">
            <tbody>
                <tr>
                    <td style="width: 225px;">
                    <p class="Tabela_Texto_Centralizado"><strong>Conta(s) do(s) exerc&iacute;cio(s) de:</strong></p>
                    </td>
                    <td style="width: 305px;">
                    <p class="Tabela_Texto_Centralizado"><strong>Conta(s) aprovada(s) pelo Tribunal de Contas em:</strong></p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Centralizado"><strong>Publica&ccedil;&atilde;o no Di&aacute;rio Oficial </strong>(data, se&ccedil;&atilde;o, p&aacute;gina)</p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 225px;">
                    <p class="Tabela_Texto_Centralizado">[Informar o ano]</p>
                    </td>
                    <td style="width: 305px;">
                    <p class="Tabela_Texto_Centralizado">[Informar o ano]</p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <table border="1" cellpadding="1" cellspacing="1" style="margin-left: auto; margin-right: auto; width: 924.333px;">
            <tbody>
                <tr>
                    <td style="width: 301px;">
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
        
                    <p class="Tabela_Texto_Centralizado">[nome]</p>
        
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
        
                    <p class="Tabela_Texto_Centralizado">Respons&aacute;vel pela sele&ccedil;&atilde;o</p>
                    </td>
                    <td style="width: 300px;">
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
        
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
        
                    <p class="Tabela_Texto_Centralizado">[nome]</p>
        
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
        
                    <p class="Tabela_Texto_Centralizado">Presidente da Comiss&atilde;o Permanente de Avalia&ccedil;&atilde;o de Documentos</p>
                    </td>
                    <td style="width: 305px;">
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
        
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
        
                    <p class="Tabela_Texto_Centralizado">[nome]</p>
        
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
        
                    <p class="Tabela_Texto_Centralizado">Autoridade do &oacute;rg&atilde;o/entidade a quem compete aprovar</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="width: 301px;">
                    <p class="Tabela_Texto_Alinhado_Esquerda">&nbsp;</p>
        
                    <p class="Tabela_Texto_Alinhado_Esquerda">&nbsp;</p>
        
                    <p class="Tabela_Texto_Alinhado_Esquerda">AUTORIZO:</p>
        
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
        
                    <p class="Tabela_Texto_Centralizado">[nome]</p>
        
                    <p class="Tabela_Texto_Centralizado">Titular do &oacute;rg&atilde;o/entidade</p>
        
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p>&nbsp;</p>
        ';

        $strModeloListagemRecolhimento = '<meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <style type="text/css">p.Citacao {font-size:10pt;font-family:Calibri;word-wrap:normal;margin:4pt 0 4pt 160px;text-align:justify;} p.Item_Alinea_Letra {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt 6pt 6pt 120px;counter-increment:letra_minuscula;} p.Item_Alinea_Letra:before {content:counter(letra_minuscula, lower-latin) ") ";display:inline-block;width:5mm;font-weight:normal;} p.Item_Inciso_Romano {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt 6pt 6pt 120px;counter-increment:romano_maiusculo;counter-reset:letra_minuscula;} p.Item_Inciso_Romano:before {content:counter(romano_maiusculo, upper-roman) " - ";display:inline-block;width:15mm;font-weight:normal;} p.Item_Nivel1 {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;counter-increment:item-n1;counter-reset:item-n2 item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel1:before {content:counter(item-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n2;counter-reset:item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel2:before {content:counter(item-n1) "." counter(item-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n3;counter-reset:item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel3:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Item_Nivel4:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) "."  counter(item-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel1 {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt;counter-increment:paragrafo-n1;counter-reset:paragrafo-n2 paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel1:before {content:counter(paragrafo-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n2;counter-reset:paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel2:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n3;counter-reset:paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel3:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel4:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) "." counter(paragrafo-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Tabela_Texto_8 {font-size:8pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Direita {font-size:11pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Esquerda {font-size:11pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Centralizado {font-size:11pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:0 3pt 0;} p.Texto_Alinhado_Direita {font-size:12pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda_Espacamento_Simples {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0;} p.Texto_Alinhado_Esquerda_Espacamento_Simples_Maiusc {font-size:12pt;font-family:Calibri;text-align:left;text-transform:uppercase;word-wrap:normal;margin:0;} p.Texto_Centralizado {font-size:12pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:6pt;} p.Texto_Centralizado_Maiusculas {font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Centralizado_Maiusculas_Negrito {font-weight:bold;font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Espaco_Duplo_Recuo_Primeira_Linha {letter-spacing:0.2em;font-weight:bold;font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Fundo_Cinza_Maiusculas_Negrito {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Fundo_Cinza_Negrito {font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado_Maiusculas {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;text-transform:uppercase;} p.Texto_Justificado_Recuo_Primeira_Linha {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Justificado_Recuo_Primeira_Linha_Esp_Simples {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:0 0 0 6pt;}
        </style>
        <title></title>
        <p>@orgao@</p>
        
        <div align="center" wfd-id="1">&nbsp;</div>
        
        <p class="Texto_Centralizado_Maiusculas_Negrito">LISTAGEM DE&nbsp;RECOLHIMENTO&nbsp;DE DOCUMENTOS</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <table border="1" cellpadding="1" cellspacing="1" style="margin-left: auto; margin-right: auto; width: 873px;">
            <tbody>
                <tr>
                    <td colspan="2" rowspan="1">
                    <p class="Tabela_Texto_Alinhado_Esquerda">@orgao@</p>
        
                    <p class="Tabela_Texto_Alinhado_Esquerda">@unidade@</p>
                    </td>
                    <td colspan="3" rowspan="1" style="width: 381px;">
                    <p class="Tabela_Texto_Alinhado_Esquerda">@orgao@</p>
        
                    <p class="Tabela_Texto_Alinhado_Esquerda">Listagem n&ordm; @numero_listagem@</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; @tabela@</p>
        
        <p>&nbsp;</p>
        
        <table border="1" cellpadding="1" cellspacing="1" style="margin-left: auto; margin-right: auto; width: 873px;">
            <tbody>
                <tr>
                    <td style="width: 105px;">
                    <p class="Tabela_Texto_Centralizado"><strong>MENSURA&Ccedil;&Atilde;O</strong></p>
                    </td>
                    <td style="width: 722px;">@mensuracao_total@</td>
                </tr>
                <tr>
                    <td style="width: 105px;">
                    <p class="Tabela_Texto_Centralizado"><strong>DATAS-LIMITES</strong></p>
        
                    <p class="Tabela_Texto_Centralizado"><strong>GERAIS</strong></p>
                    </td>
                    <td style="width: 722px;">@datas_limites_gerais@</td>
                </tr>
                <tr>
                    <td style="width: 105px;">
                    <p class="Tabela_Texto_Centralizado"><strong>VOLUME /</strong></p>
        
                    <p class="Tabela_Texto_Centralizado"><strong>QUANTIFICA&Ccedil;&Atilde;O</strong></p>
                    </td>
                    <td style="width: 722px;">@folha@ bytes</td>
                </tr>
            </tbody>
        </table>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Justificado">O quadro abaixo somente dever&aacute; ser preenchido se os documentos a serem eliminados necessitarem de comprova&ccedil;&atilde;o de aprova&ccedil;&atilde;o das contas pelos Tribunais de Contas.</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <table border="1" cellpadding="1" cellspacing="1" style="margin-left:auto;margin-right:auto;width:870px;">
            <tbody>
                <tr>
                    <td>
                    <p class="Tabela_Texto_Centralizado"><strong>EXERC&Iacute;CIO</strong></p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Centralizado"><strong>APROVA&Ccedil;&Atilde;O PELO TRIBUNAL DE CONTAS</strong></p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Centralizado"><strong>Publica&ccedil;&atilde;o no Di&aacute;rio Oficial </strong>(data, se&ccedil;&atilde;o, p&aacute;gina)</p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 198px;">
                    <p class="Tabela_Texto_Centralizado">[Informar o ano]</p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Centralizado">[Informar a data]</p>
                    </td>
                    <td>
                    <p class="Tabela_Texto_Centralizado">&nbsp;</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Justificado">[Cidade], 28 de janeiro&nbsp;de 2021.</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Centralizado">[NOME]</p>
        
        <p class="Texto_Centralizado">Respons&aacute;vel pela sele&ccedil;&atilde;o</p>
        
        <p class="Texto_Centralizado">&nbsp;</p>
        
        <p class="Texto_Centralizado">[NOME]</p>
        
        <p class="Texto_Centralizado">Presidente da Comiss&atilde;o Permanente de Avalia&ccedil;&atilde;o de Documentos</p>
        
        <p class="Texto_Centralizado">&nbsp;</p>
        
        <p class="Texto_Centralizado">[NOME DO TITULAR DO &Oacute;RG&Atilde;O/ENTIDADE PRODUTOR/ACUMULADOR DO ARQUIVO]</p>
        
        <p class="Texto_Centralizado">[Cargo]</p>
        
        <p class="Texto_Centralizado">&nbsp;</p>
        
        <hr style="border:none; padding:0; margin:5px 2px 0 2px; border-top:medium double #333" />
        <table border="0" cellpadding="2" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <td align="left" style="font-family:Calibri;font-size:9pt;border:0;" width="50%"><strong>Refer&ecirc;ncia:</strong> Processo n&ordm; 99990.000006/2020-91</td>
                    <td align="right" style="font-family:Calibri;font-size:9pt;border:0;" width="50%">SEI n&ordm; 0000045</td>
                </tr>
            </tbody>
        </table>        
        ';

        $strModeloDocumentoEliminacao = '<meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <style type="text/css">p.Citacao {font-size:10pt;font-family:Calibri;word-wrap:normal;margin:4pt 0 4pt 160px;text-align:justify;} p.Item_Alinea_Letra {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt 6pt 6pt 120px;counter-increment:letra_minuscula;} p.Item_Alinea_Letra:before {content:counter(letra_minuscula, lower-latin) ") ";display:inline-block;width:5mm;font-weight:normal;} p.Item_Inciso_Romano {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt 6pt 6pt 120px;counter-increment:romano_maiusculo;counter-reset:letra_minuscula;} p.Item_Inciso_Romano:before {content:counter(romano_maiusculo, upper-roman) " - ";display:inline-block;width:15mm;font-weight:normal;} p.Item_Nivel1 {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;counter-increment:item-n1;counter-reset:item-n2 item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel1:before {content:counter(item-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n2;counter-reset:item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel2:before {content:counter(item-n1) "." counter(item-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n3;counter-reset:item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel3:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Item_Nivel4:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) "."  counter(item-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel1 {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt;counter-increment:paragrafo-n1;counter-reset:paragrafo-n2 paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel1:before {content:counter(paragrafo-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n2;counter-reset:paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel2:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n3;counter-reset:paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel3:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel4:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) "." counter(paragrafo-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Tabela_Texto_8 {font-size:8pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Direita {font-size:11pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Esquerda {font-size:11pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Centralizado {font-size:11pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:0 3pt 0;} p.Texto_Alinhado_Direita {font-size:12pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda_Espacamento_Simples {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0;} p.Texto_Alinhado_Esquerda_Espacamento_Simples_Maiusc {font-size:12pt;font-family:Calibri;text-align:left;text-transform:uppercase;word-wrap:normal;margin:0;} p.Texto_Centralizado {font-size:12pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:6pt;} p.Texto_Centralizado_Maiusculas {font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Centralizado_Maiusculas_Negrito {font-weight:bold;font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Espaco_Duplo_Recuo_Primeira_Linha {letter-spacing:0.2em;font-weight:bold;font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Fundo_Cinza_Maiusculas_Negrito {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Fundo_Cinza_Negrito {font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado_Maiusculas {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;text-transform:uppercase;} p.Texto_Justificado_Recuo_Primeira_Linha {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Justificado_Recuo_Primeira_Linha_Esp_Simples {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:0 0 0 6pt;}
        </style>
        <title></title>
        @orgao@</div>
        
        <div align="center" wfd-id="1">&nbsp;</div>
        
        <p class="Texto_Centralizado_Maiusculas_Negrito">TERMO DE ELIMINA&Ccedil;&Atilde;O DE DOCUMENTOS</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Justificado">Aos 28 dias do m&ecirc;s de janeiro do ano de 2021, o(a) ME, de acordo com o que consta da Listagem de Elimina&ccedil;&atilde;o de Documentos n&ordm; (indicar o n&ordm; / ano da listagem), aprovada pelo(a) titular do(a) ME&nbsp;e respectivo Edital de Ci&ecirc;ncia de Elimina&ccedil;&atilde;o de Documentos n&ordm; (indicar o n&ordm; / ano do edital), publicado no (indicar o nome do peri&oacute;dico oficial ou, na aus&ecirc;ncia dele, o do ve&iacute;culo de divulga&ccedil;&atilde;o local), de (indicar a data de publica&ccedil;&atilde;o do edital), procedeu &agrave; elimina&ccedil;&atilde;o de (indicar a mensura&ccedil;&atilde;o total) dos documentos relativos a (indicar as refer&ecirc;ncias gerais dos descritores dos c&oacute;digos de classifica&ccedil;&atilde;o dos documentos a serem eliminados), do per&iacute;odo de (indicar as datas-limite gerais), do(a) (indicar o nome do(a) &oacute;rg&atilde;o/entidade produtor(a) ou acumulador(a) dos documentos que foram eliminados).</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Justificado">[Cidade], 28 de janeiro&nbsp;de 2021.</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Centralizado">[NOME DO RESPONS&Aacute;VEL DESIGNADO PARA SUPERVISIONAR E ACOMPANHAR A ELIMINA&Ccedil;&Atilde;O]</p>
        
        <p class="Texto_Centralizado">Cargo</p>
        
        <p class="Texto_Centralizado">&nbsp;</p>
        
        <p class="Texto_Centralizado">[NOME]</p>
        
        <p class="Texto_Centralizado">Presidente da Comiss&atilde;o Permanente de Avalia&ccedil;&atilde;o de Documentos</p>
        ';

        $strModeloDocumentoRecolhimento = '<meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <style type="text/css">p.Citacao {font-size:10pt;font-family:Calibri;word-wrap:normal;margin:4pt 0 4pt 160px;text-align:justify;} p.Item_Alinea_Letra {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt 6pt 6pt 120px;counter-increment:letra_minuscula;} p.Item_Alinea_Letra:before {content:counter(letra_minuscula, lower-latin) ") ";display:inline-block;width:5mm;font-weight:normal;} p.Item_Inciso_Romano {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt 6pt 6pt 120px;counter-increment:romano_maiusculo;counter-reset:letra_minuscula;} p.Item_Inciso_Romano:before {content:counter(romano_maiusculo, upper-roman) " - ";display:inline-block;width:15mm;font-weight:normal;} p.Item_Nivel1 {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;counter-increment:item-n1;counter-reset:item-n2 item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel1:before {content:counter(item-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n2;counter-reset:item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel2:before {content:counter(item-n1) "." counter(item-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n3;counter-reset:item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel3:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Item_Nivel4:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) "."  counter(item-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel1 {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt;counter-increment:paragrafo-n1;counter-reset:paragrafo-n2 paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel1:before {content:counter(paragrafo-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n2;counter-reset:paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel2:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n3;counter-reset:paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel3:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel4:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) "." counter(paragrafo-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Tabela_Texto_8 {font-size:8pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Direita {font-size:11pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Esquerda {font-size:11pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Centralizado {font-size:11pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:0 3pt 0;} p.Texto_Alinhado_Direita {font-size:12pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda_Espacamento_Simples {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0;} p.Texto_Alinhado_Esquerda_Espacamento_Simples_Maiusc {font-size:12pt;font-family:Calibri;text-align:left;text-transform:uppercase;word-wrap:normal;margin:0;} p.Texto_Centralizado {font-size:12pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:6pt;} p.Texto_Centralizado_Maiusculas {font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Centralizado_Maiusculas_Negrito {font-weight:bold;font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Espaco_Duplo_Recuo_Primeira_Linha {letter-spacing:0.2em;font-weight:bold;font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Fundo_Cinza_Maiusculas_Negrito {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Fundo_Cinza_Negrito {font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado_Maiusculas {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;text-transform:uppercase;} p.Texto_Justificado_Recuo_Primeira_Linha {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Justificado_Recuo_Primeira_Linha_Esp_Simples {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:0 0 0 6pt;}
        </style>
        <title></title>
        @orgao@</div>
        
        <div align="center" wfd-id="1">&nbsp;</div>
        
        <p class="Texto_Centralizado_Maiusculas_Negrito">TERMO DE RECOLHIMENTO&nbsp;DE DOCUMENTOS</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Justificado">[DIGITE AQUI O TEXTO]</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Justificado">[Cidade], 28 de janeiro&nbsp;de 2021.</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Justificado">&nbsp;</p>
        
        <p class="Texto_Centralizado">[NOME DO RESPONS&Aacute;VEL DESIGNADO PARA SUPERVISIONAR E ACOMPANHAR A ELIMINA&Ccedil;&Atilde;O]</p>
        
        <p class="Texto_Centralizado">Cargo</p>
        
        <p class="Texto_Centralizado">&nbsp;</p>
        
        <p class="Texto_Centralizado">[NOME]</p>
        
        <p class="Texto_Centralizado">Presidente da Comiss&atilde;o Permanente de Avalia&ccedil;&atilde;o de Documentos</p>
        ';

        $this->cadastrarModeloDocumento('MODELO_DESPACHO_ARQUIVAMENTO', $strModeloDespachoArquivamento);
        $this->cadastrarModeloDocumento('MODELO_DESPACHO_DESARQUIVAMENTO', $strModeloDespachoDesarquivamento);
        $this->cadastrarModeloDocumento('MODELO_LISTAGEM_ELIMINACAO', $strModeloListagemEliminacao);
        $this->cadastrarModeloDocumento('MODELO_LISTAGEM_RECOLHIMENTO', $strModeloListagemRecolhimento);
        $this->cadastrarModeloDocumento('MODELO_DOCUMENTO_ELIMINACAO', $strModeloDocumentoEliminacao);
        $this->cadastrarModeloDocumento('MODELO_DOCUMENTO_RECOLHIMENTO', $strModeloDocumentoRecolhimento);
    }

    /**
     * Cadastra um modelo de documento
     *
     * @param string $strNome
     * @param string $strValor
     * @return void
     */
    protected function cadastrarModeloDocumento($strNome, $strValor)
    {
        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome($strNome);
        $objMdGdModeloDocumentoDTO->setStrValor($strValor);

        $objMdGdModeloDocumentoRN = new MdGdModeloDocumentoRN();
        $objMdGdModeloDocumentoRN->cadastrar($objMdGdModeloDocumentoDTO);
    }

    /**
     * Mtodo simplificado para cadastro de um tipo de documento
     *
     * @param integer $numIdGrupoSerie
     * @param string $strNome
     * @param string $strDescricao
     * @param string $strStaAplicabilidade
     * @param integer $numIdModelo
     * @param string $strStaNumeracao
     * @return integer
     */
    protected function cadastrarTipoDocumento($numIdGrupoSerie, $strNome, $strDescricao, $strStaAplicabilidade, $numIdModelo, $strStaNumeracao)
    {
        $objSerieDTO = new SerieDTO();
        $objSerieDTO->setNumIdSerie(null);
        $objSerieDTO->setNumIdGrupoSerie($numIdGrupoSerie);
        $objSerieDTO->setStrNome($strNome);
        $objSerieDTO->setStrDescricao($strDescricao);
        $objSerieDTO->setStrStaAplicabilidade($strStaAplicabilidade);
        $objSerieDTO->setNumIdModelo($numIdModelo);
        $objSerieDTO->setNumIdModeloEdoc(null);
        $objSerieDTO->setNumIdTipoFormulario(null);
        $objSerieDTO->setStrStaNumeracao($strStaNumeracao);
        $objSerieDTO->setStrSinAssinaturaPublicacao('S');
        $objSerieDTO->setStrSinInteressado('N');
        $objSerieDTO->setStrSinDestinatario('N');
        $objSerieDTO->setStrSinInterno('N');
        $objSerieDTO->setStrSinAtivo('S');
        $objSerieDTO->setStrSinUsuarioExterno('N');
        $objSerieDTO->setArrObjRelSerieAssuntoDTO(array());
        $objSerieDTO->setArrObjSerieRestricaoDTO(array());
        $objSerieDTO->setArrObjRelSerieVeiculoPublicacaoDTO(array());

        $objSerieRN = new SerieRN();
        $objSerieDTO = $objSerieRN->cadastrarRN0642($objSerieDTO);
        return $objSerieDTO->getNumIdSerie();
    }


    /**
     * Mtodo simplificado para cadastro de um tipo de procedimento
     *
     * @param string $strNome
     * @param string $strDescricao
     * @return integer
     */
    protected function cadastrarTipoProcedimento($strNome, $strDescricao)
    {
        $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
        $objTipoProcedimentoDTO->setArrObjRelTipoProcedimentoAssuntoDTO(array());
        $objTipoProcedimentoDTO->setArrObjTipoProcedRestricaoDTO(array());
        $objTipoProcedimentoDTO->setNumIdTipoProcedimento(null);
        $objTipoProcedimentoDTO->setStrNome($strNome);
        $objTipoProcedimentoDTO->setStrDescricao($strDescricao);
        $objTipoProcedimentoDTO->setStrStaGrauSigiloSugestao(null);
        $objTipoProcedimentoDTO->setNumIdHipoteseLegalSugestao(null);
        $objTipoProcedimentoDTO->setStrSinInterno('N');
        $objTipoProcedimentoDTO->setStrSinOuvidoria('N');
        $objTipoProcedimentoDTO->setStrSinIndividual('N');

        // Nveis de acesso permitidos
        $arrObjNivelAcessoPermitidoDTO = array();

        $objNivelAcessoPermitidoDTO = new NivelAcessoPermitidoDTO();
        $objNivelAcessoPermitidoDTO->setStrStaNivelAcesso(ProtocoloRN::$NA_PUBLICO);
        $arrObjNivelAcessoPermitidoDTO[] = $objNivelAcessoPermitidoDTO;

        $objNivelAcessoPermitidoDTO = new NivelAcessoPermitidoDTO();
        $objNivelAcessoPermitidoDTO->setStrStaNivelAcesso(ProtocoloRN::$NA_RESTRITO);
        $arrObjNivelAcessoPermitidoDTO[] = $objNivelAcessoPermitidoDTO;

        $objTipoProcedimentoDTO->setArrObjNivelAcessoPermitidoDTO($arrObjNivelAcessoPermitidoDTO);

        $objTipoProcedimentoDTO->setStrStaNivelAcessoSugestao(ProtocoloRN::$NA_PUBLICO);
        $objTipoProcedimentoDTO->setStrSinAtivo('S');

        $objTipoProcedimentoRN = new TipoProcedimentoRN();
        $objTipoProcedimentoDTO = $objTipoProcedimentoRN->cadastrarRN0265($objTipoProcedimentoDTO);
        return $objTipoProcedimentoDTO->getNumIdTipoProcedimento();
    }

    public function versao_0_0_0($strVersaoAtual)
    {
    }

    public function versao_0_4_0($strVersaoAtual)
    {
    }

    public function versao_0_5_0($strVersaoAtual)
    {
        $this->objInfraBanco = BancoSEI::getInstance();
        $this->objMetaBD = new InfraMetaBD($this->objInfraBanco);
        $this->objInfraSequencia = new InfraSequencia($this->objInfraBanco);
        $this->objInfraParametro = new InfraParametro($this->objInfraBanco);

        try {
            // Criação da tabela de parâmetros
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_parametro (
                nome  ' . $this->objMetaBD->tipoTextoVariavel(100) . '  NOT NULL ,
                valor ' . $this->objMetaBD->tipoTextoVariavel(50) . '  NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_parametro', 'pk_md_gd_parametro_nome', array('nome'));

            // Cadastra os parâmetros
            $this->cadastrarParametros();

            // Criação da tabela de justificativas arquivamento e desarquivamento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_justificativa (
                id_justificativa ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                sta_tipo ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                nome ' . $this->objMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                descricao ' . $this->objMetaBD->tipoTextoGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_justificativa', 'pk_md_gd_id_justificativa', array('id_justificativa'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_justificativa')) {
                $this->objInfraSequencia->criarSequencia('md_gd_justificativa', '1', '1', '9999999999');
            }

            // Criação da tabela de arquivamentos
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_arquivamento (
                id_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade_corrente ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade_intermediaria ' . $this->objMetaBD->tipoNumero() . ' NULL ,
                id_despacho_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_justificativa ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NULL ,
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NULL,
                dta_arquivamento ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                dta_guarda_corrente ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                dta_guarda_intermediaria ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                guarda_corrente ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                guarda_intermediaria ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                sta_guarda ' . $this->objMetaBD->tipoTextoFixo(2) . ' NOT NULL,
                sta_situacao ' . $this->objMetaBD->tipoTextoFixo(2) . ' NOT NULL,
                sta_destinacao_final ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                sin_condicionante ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                sin_ativo ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                observacao_devolucao ' . $this->objMetaBD->tipoTextoGrande() . ' NULL,
                observacao_eliminacao ' . $this->objMetaBD->tipoTextoGrande() . ' NULL,
                observacao_recolhimento ' . $this->objMetaBD->tipoTextoGrande() . ' NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_arquivamento', 'pk_md_gd_id_arquivamento', array('id_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_procedimento', 'md_gd_arquivamento', array('id_procedimento'), 'procedimento', array('id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_documento', 'md_gd_arquivamento', array('id_despacho_arquivamento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_justificativa', 'md_gd_arquivamento', array('id_justificativa'), 'md_gd_justificativa', array('id_justificativa'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_usuario', 'md_gd_arquivamento', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_un_corrente', 'md_gd_arquivamento', array('id_unidade_corrente'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_un_intermediaria', 'md_gd_arquivamento', array('id_unidade_intermediaria'), 'unidade', array('id_unidade'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_arquivamento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_arquivamento', '1', '1', '9999999999');
            }

            // Cria a tabela de histórico do arquivamento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_arquivamento_historico (
                id_arquivamento_historico ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                sta_situacao_antiga ' . $this->objMetaBD->tipoTextoFixo(2) . ' NULL,
                sta_situacao_atual ' . $this->objMetaBD->tipoTextoFixo(2) . ' NOT NULL,
                descricao ' . $this->objMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                dth_historico ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_arquivamento_historico', 'pk_md_gd_id_arq_historico', array('id_arquivamento_historico'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_hist_arquivamento', 'md_gd_arquivamento_historico', array('id_arquivamento'), 'md_gd_arquivamento', array('id_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_hist_usuario', 'md_gd_arquivamento_historico', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_hist_unidade', 'md_gd_arquivamento_historico', array('id_unidade'), 'unidade', array('id_unidade'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_arquivamento_historico')) {
                $this->objInfraSequencia->criarSequencia('md_gd_arquivamento_historico', '1', '1', '9999999999');
            }

            // Cria a tabela de anotaes das pendências de arquivamento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_anotacao_pendencia (
                id_anotacao_pendencia ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                anotacao ' . $this->objMetaBD->tipoTextoGrande() . ' NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_anotacao_pendencia', 'pk_md_gd_id_anotacao_pendencia', array('id_anotacao_pendencia'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_an_id_procedimento', 'md_gd_anotacao_pendencia', array('id_procedimento'), 'procedimento', array('id_procedimento'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_anotacao_pendencia')) {
                $this->objInfraSequencia->criarSequencia('md_gd_anotacao_pendencia', '1', '1', '9999999999');
            }

            // Criação da tabela de desarquivamento
            $this->objInfraBanco->executarSql(' CREATE TABLE md_gd_desarquivamento (
                id_desarquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_despacho_desarquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_justificativa ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                dta_desarquivamento ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_desarquivamento', 'pk_gd_id_desarquivamento', array('id_desarquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarq_arquivamento', 'md_gd_desarquivamento', array('id_arquivamento'), 'md_gd_arquivamento', array('id_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarq_procedimento', 'md_gd_desarquivamento', array('id_procedimento'), 'procedimento', array('id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarq_documento', 'md_gd_desarquivamento', array('id_despacho_desarquivamento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarq_justificativa', 'md_gd_desarquivamento', array('id_justificativa'), 'md_gd_justificativa', array('id_justificativa'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_desarquivamento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_desarquivamento', '1', '1', '9999999999');
            }

            // Criação da tabela de modelos de documento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_modelo_documento (
                nome  ' . $this->objMetaBD->tipoTextoVariavel(100) . '  NOT NULL ,
                valor ' . $this->objMetaBD->tipoTextoGrande() . ' NOT NULL 
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_modelo_documento', 'pk_md_gd_modelo_documento_nome', array('nome'));

            // Cadastra os modelos padres
            $this->cadastrarModelos();

            // Criação da tabela de unidades de arquivamento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_unidade_arquivamento (
                    id_unidade_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                    id_unidade_origem ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                    id_unidade_destino ' . $this->objMetaBD->tipoNumero() . ' NOT NULL
              )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_unidade_arquivamento', 'pk_md_gd_id_unidade_arq', array('id_unidade_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_unidade_origem', 'md_gd_unidade_arquivamento', array('id_unidade_origem'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_unidade_destino', 'md_gd_unidade_arquivamento', array('id_unidade_destino'), 'unidade', array('id_unidade'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_unidade_arquivamento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_unidade_arquivamento', '1', '1', '9999999999');
            }

            // Criação da tabela da listagem de Eliminação
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_eliminacao (
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_documento_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                numero ' . $this->objMetaBD->tipoTextoVariavel(50) . ' NOT NULL,
                qtd_processos ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                sin_documentos_fisicos ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                dth_emissao_listagem ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                ano_limite_inicio ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                ano_limite_fim ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                situacao ' . $this->objMetaBD->tipoTextoFixo(2) . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_eliminacao', 'pk_md_gd_id_lista_eliminacao', array('id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_list_eli_procedimento', 'md_gd_lista_eliminacao', array('id_procedimento_eliminacao'), 'procedimento', array('id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_list_eli_documento', 'md_gd_lista_eliminacao', array('id_documento_eliminacao'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_arquivamento_gd_list_eli', 'md_gd_arquivamento', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_lista_eliminacao')) {
                $this->objInfraSequencia->criarSequencia('md_gd_lista_eliminacao', '1', '1', '9999999999');
            }

            // Cria a tabela ternária entre a listagem de Eliminação e os procedimentos
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_elim_procedimento (
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_elim_procedimento', 'pk_md_gd_list_eli_procedimento', array('id_lista_eliminacao', 'id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_list_eli_proc_lista_elim', 'md_gd_lista_elim_procedimento', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_list_eli_proc_proc', 'md_gd_lista_elim_procedimento', array('id_procedimento'), 'procedimento', array('id_procedimento'));

            // Cria a tabela que relaciona os documentos físicos eliminados
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_documento_fisico_elim (
                id_documento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_documento_fisico_elim', 'pk_md_gd_documento_fisico_elim', array('id_documento', 'id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_doc_fisico_elim_doc', 'md_gd_documento_fisico_elim', array('id_documento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_doc_fisico_elim_list_eli', 'md_gd_documento_fisico_elim', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));


            // Criação da tabela da listagem de recolhimento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_recolhimento (
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_documento_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                numero ' . $this->objMetaBD->tipoTextoVariavel(50) . ' NOT NULL,
                dth_emissao_listagem ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                ano_limite_inicio ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                ano_limite_fim ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                qtd_processos ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                sin_documentos_fisicos ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                situacao ' . $this->objMetaBD->tipoTextoFixo(2) . ' NOT NULL

            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_recolhimento', 'pk_md_gd_id_lista_recolhimento', array('id_lista_recolhimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_list_rec_procedimento', 'md_gd_lista_recolhimento', array('id_procedimento_recolhimento'), 'procedimento', array('id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_list_rec_documento', 'md_gd_lista_recolhimento', array('id_documento_recolhimento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_list_rec', 'md_gd_arquivamento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_lista_recolhimento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_lista_recolhimento', '1', '1', '9999999999');
            }

            // Cria a tabela ternária entre a listagem de recolhimento e procedimento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_recol_procedimento (
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_recol_procedimento', 'pk_md_gd_list_rec_procedimento', array('id_lista_recolhimento', 'id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_list_rec_proc_list_rec', 'md_gd_lista_recol_procedimento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_list_rec_proc_proc', 'md_gd_lista_recol_procedimento', array('id_procedimento'), 'procedimento', array('id_procedimento'));

            // Cria a tabela de relacionamento de documentos físicos recolhidos
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_documento_fisico_recol (
                id_documento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_documento_fisico_recol', 'pk_md_gd_documento_fisico_rec', array('id_documento', 'id_lista_recolhimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_doc_fisico_rec_documento', 'md_gd_documento_fisico_recol', array('id_documento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_doc_fisico_rec_list_rec', 'md_gd_documento_fisico_recol', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));

            // Cria a tabela que armazena as eliminaes
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_eliminacao (
                id_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                assinante ' . $this->objMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_unidade ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_veiculo_publicacao ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_secao_imprensa_nacional ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                pagina ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                dth_data_imprensa ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                dth_eliminacao ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_eliminacao', 'pk_md_gd_eliminacao', array('id_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_elim_usuario', 'md_gd_eliminacao', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_elim_unidade', 'md_gd_eliminacao', array('id_unidade'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_elim_lista_eliminacao', 'md_gd_eliminacao', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_elim_veiculo', 'md_gd_eliminacao', array('id_veiculo_publicacao'), 'veiculo_publicacao', array('id_veiculo_publicacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_elim_secao', 'md_gd_eliminacao', array('id_secao_imprensa_nacional'), 'secao_imprensa_nacional', array('id_secao_imprensa_nacional'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_eliminacao')) {
                $this->objInfraSequencia->criarSequencia('md_gd_eliminacao', '1', '1', '9999999999');
            }

            // Cria a tabela que armazena os recolhimentos
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_recolhimento (
                id_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                dth_recolhimento ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL
            )');

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_recolhimento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_recolhimento', '1', '1', '9999999999');
            }

            $this->objMetaBD->adicionarChavePrimaria('md_gd_recolhimento', 'pk_md_gd_recolhimento', array('id_recolhimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_rec_usuario', 'md_gd_recolhimento', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_rec_unidade', 'md_gd_recolhimento', array('id_unidade'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_rec_lista_recolhimento', 'md_gd_recolhimento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));

            // Adiciona o agendamento
            $objInfraAgendamentoDTO = new InfraAgendamentoTarefaDTO();
            $objInfraAgendamentoDTO->setStrDescricao('Arquivamento em fase intermediária');
            $objInfraAgendamentoDTO->setStrComando('MdGdAgendamentoRN::verificarTempoGuarda');
            $objInfraAgendamentoDTO->setStrStaPeriodicidadeExecucao('D');
            $objInfraAgendamentoDTO->setStrPeriodicidadeComplemento('0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23');
            $objInfraAgendamentoDTO->setStrSinAtivo('S');
            $objInfraAgendamentoDTO->setStrSinSucesso('S');

            $objAtividadeBD = new  AgendamentoBD(BancoSEI::getInstance());
            $objAtividadeBD->cadastrar($objInfraAgendamentoDTO);


            //----------------------------------------------------------------------
            // Tarefas
            //----------------------------------------------------------------------
            $objDTO = new TarefaDTO();
            $objBD = new TarefaBD(BancoSEI::getInstance());

            $fnCadastrar = function ($strNome = '', $strHistoricoResumido = 'N', $strHistoricoCompleto = 'N', $strFecharAndamentosAbertos = 'N', $strLancarAndamentoFechado = 'N', $strPermiteProcessoFechado = 'N', $strIdTarefaModulo = '') use ($objDTO, $objBD) {

                $objDTO->unSetTodos();
                $objDTO->setStrIdTarefaModulo($strIdTarefaModulo);

                if ($objBD->contar($objDTO) == 0) {

                    $objUltimaTarefaDTO = new TarefaDTO();
                    $objUltimaTarefaDTO->retNumIdTarefa();
                    $objUltimaTarefaDTO->setNumMaxRegistrosRetorno(1);
                    $objUltimaTarefaDTO->setOrd('IdTarefa', InfraDTO::$TIPO_ORDENACAO_DESC);
                    $objUltimaTarefaDTO = $objBD->consultar($objUltimaTarefaDTO);

                    $objDTO->setNumIdTarefa($objUltimaTarefaDTO->getNumIdTarefa() + 1);
                    $objDTO->setStrNome($strNome);
                    $objDTO->setStrSinHistoricoResumido($strHistoricoResumido);
                    $objDTO->setStrSinHistoricoCompleto($strHistoricoCompleto);
                    $objDTO->setStrSinFecharAndamentosAbertos($strFecharAndamentosAbertos);
                    $objDTO->setStrSinLancarAndamentoFechado($strLancarAndamentoFechado);
                    $objDTO->setStrSinPermiteProcessoFechado($strPermiteProcessoFechado);
                    $objDTO->setStrIdTarefaModulo($strIdTarefaModulo);
                    $objBD->cadastrar($objDTO);
                }
            };

            $fnCadastrar('O processo teve os assuntos atualizados de @ASSUNTOS_ANTIGOS@ para @ASSUNTOS_NOVOS@', 'S', 'S', 'N', 'N', 'S', 'MOD_GESTAO_ATUALIZAR_ASSUNTO');
        } catch (Exception $ex) {
            throw new InfraException('Erro ao atualizar a versão 1.0.0 do mdulo de gestão documental', $ex);
        }
    }

    public function versao_0_5_1($strVersaoAtual)
    {
    }

    public function versao_0_5_2($strVersaoAtual)
    {
    }

    public function versao_1_2_0($strVersaoAtual)
    {
        $this->objInfraBanco = BancoSEI::getInstance();
        $this->objMetaBD = new InfraMetaBD($this->objInfraBanco);
        $this->objInfraSequencia = new InfraSequencia($this->objInfraBanco);
        $this->objInfraParametro = new InfraParametro($this->objInfraBanco);

        try {
            if (BancoSEI::getInstance() instanceof InfraSqlServer) {
                // Alteração da tabela da listagem de recolhimento
                $this->objInfraBanco->executarSql('ALTER TABLE md_gd_lista_recolhimento
                ADD id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                    anotacao ' . $this->objMetaBD->tipoTextoGrande() . ' NULL'

                );

                $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_rec_usuario', 'md_gd_lista_recolhimento', array('id_usuario'), 'usuario', array('id_usuario'));

                // Alteração da tabela da listagem de eliminação
                $this->objInfraBanco->executarSql('ALTER TABLE md_gd_lista_eliminacao
                    ADD id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                        anotacao ' . $this->objMetaBD->tipoTextoGrande() . ' NULL'

                );

            }else{
                // Alteração da tabela da listagem de recolhimento
                $this->objInfraBanco->executarSql('ALTER TABLE md_gd_lista_recolhimento
                    ADD (id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                        anotacao ' . $this->objMetaBD->tipoTextoGrande() . ' NULL)'

                );

                $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_rec_usuario', 'md_gd_lista_recolhimento', array('id_usuario'), 'usuario', array('id_usuario'));

                // Alteração da tabela da listagem de eliminação
                $this->objInfraBanco->executarSql('ALTER TABLE md_gd_lista_eliminacao
                    ADD (id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                        anotacao ' . $this->objMetaBD->tipoTextoGrande() . ' NULL)'

                );
            }

            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_eli_usuario', 'md_gd_lista_eliminacao', array('id_usuario'), 'usuario', array('id_usuario'));


        } catch (Exception $ex) {
            throw new InfraException('Erro ao atualizar a versão 1.2.0 do mdulo de gestão documental', $ex);
        }
    }

    public function versao_1_2_1($strVersaoAtual)
    {
    }
}

try {
    session_start();

    SessaoSEI::getInstance(false);
    BancoSEI::getInstance()->setBolScript(true);

    $objVersaoSeiRN = new VersaoSeiRN();
    $objVersaoSeiRN->verificarVersaoInstalada();
    $objVersaoSeiRN->setStrNome(VersaoSeiRN::NOME_MODULO);
    $objVersaoSeiRN->setStrVersaoAtual(MdGestaoDocumentalIntegracao::VERSAO_MODULO);
    $objVersaoSeiRN->setStrParametroVersao(VersaoSeiRN::PARAMETRO_VERSAO_MODULO);
    $objVersaoSeiRN->setArrVersoes(
        array(
            '0.0.0' => 'versao_0_0_0',
            '0.4.0' => 'versao_0_4_0',
            '0.5.0' => 'versao_0_5_0',
            '0.5.1' => 'versao_0_5_1',
            '0.5.2' => 'versao_0_5_2',
            '1.2.0' => 'versao_1_2_0',
            '1.2.1' => 'versao_1_2_1',
        )
    );

    $objVersaoSeiRN->setStrVersaoAtual(array_key_last($objVersaoSeiRN->getArrVersoes()));
    $objVersaoSeiRN->setStrVersaoInfra('1.595.1');
    $objVersaoSeiRN->setBolMySql(true);
    $objVersaoSeiRN->setBolOracle(true);
    $objVersaoSeiRN->setBolSqlServer(true);
    $objVersaoSeiRN->setBolPostgreSql(true);
    $objVersaoSeiRN->setBolErroVersaoInexistente(true);
    $objVersaoSeiRN->atualizarVersao();
} catch (Exception $e) {
    echo (InfraException::inspecionar($e));
    try {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e) {
    }
    exit(1);
}
