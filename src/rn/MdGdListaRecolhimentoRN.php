<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaRecolhimentoRN extends InfraRN {

    public static $ST_GERADA = 'GE';
    public static $ST_EDICAO = 'ED';
    public static $ST_RECOLHIDA = 'RE';

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimento) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_lista_recolhimento_preparacao_gerar', __METHOD__, $objMdGdListaRecolhimento);

            // Recupera os arquivamentos
            $arrObjMdGdArquivamentoDTO = $objMdGdListaRecolhimento->getArrObjMdGdArquivamentoDTO();

            // CONSULTA PARA VERIFICAÇÃO DA EXISTÊNCIA DE DOCUMENTOS FÍSICOS ARQUIVADOS
            $strSinDocumentosFisicos = 'N';
            
            // Obtem os ids dos procedimentos vinculdados e os anos limite inicial e final
            $arrIdsProcedimentos = array();

            // Calcula as datas limite
            $numAnoLimiteInicial = 0;
            $numAnoLimiteFinal = 0;

            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
                $numAnoFinal = (int) substr($objMdGdArquivamentoDTO->getDthDataArquivamento(), 6, 4);
                $numAnoLimiteFinal = $numAnoLimiteFinal < $numAnoFinal ? $numAnoFinal : $numAnoLimiteFinal;
                $arrIdsProcedimentos[] = $objMdGdArquivamentoDTO->getDblIdProcedimento();
            }

            $objAtividadeDTO = new AtividadeDTO();
            $objAtividadeDTO->setDblIdProtocolo($arrIdsProcedimentos, InfraDTO::$OPER_IN);
            $objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_ASC);
            $objAtividadeDTO->retDthAbertura();

            $objAtividadeRN = new AtividadeRN();
            $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

            $dthPrimeiroAndamento = $arrObjAtividadeDTO[0]->getDthAbertura();
            $dthPrimeiroAndamento = explode(' ', $dthPrimeiroAndamento);
            $dthPrimeiroAndamento = explode('/', $dthPrimeiroAndamento[0]);

            $numAnoLimiteInicial = $dthPrimeiroAndamento[2];


            // Obtem os documentos vinculados aos processos da listagem
            $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
            $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($arrIdsProcedimentos, InfraDTO::$OPER_IN);
            $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();

            $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
            $arrObjRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO);
            
            $arrIdsDocumentos = array();
            if ($arrObjRelProtocoloProtocoloDTO) {
                $arrIdsDocumentos = explode(',', InfraArray::implodeArrInfraDTO($arrObjRelProtocoloProtocoloDTO, 'IdProtocolo2'));
            }
            
            // Obtem todos os arquivamentos fisicos registrados para os documentos do processo
            $objArquivamentoDTO = new ArquivamentoDTO();
            $objArquivamentoDTO->retDblIdProtocoloDocumento();
            $objArquivamentoDTO->setDblIdProtocoloDocumento($arrIdsDocumentos, InfraDTO::$OPER_IN);

            $objArquivamentoRN = new ArquivamentoRN();
            $strSinDocumentosFisicos = $objArquivamentoRN->contar($objArquivamentoDTO) == 0 ? 'N' : 'S';

            // Recupera os tipos de procedimento e documento que serão criados no arquivamento
            $objMdGdParametroRN = new MdGdParametroRN();
            $numIdTipoProcedimentoArquivamento = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_PROCEDIMENTO_LISTAGEM_RECOLHIMENTO);
            $numIdTipoDocumentoArquivamento = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_DOCUMENTO_LISTAGEM_RECOLHIMENTO);
            
            $arrIdProtocolo = [];
            foreach($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO){
                $arrIdProtocolo[] = $objMdGdArquivamentoDTO->getDblIdProcedimento();
            }

            // Busca os assuntos que serão inseridos no processo
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($arrIdProtocolo, InfraDTO::$OPER_IN);
            $objRelProtocoloAssuntoDTO->retNumIdAssunto();
            $objRelProtocoloAssuntoDTO->retNumSequencia();

            $objRelProtocoloProtocoloRN = new RelProtocoloAssuntoRN();
            $arrayAssuntos = $objRelProtocoloProtocoloRN->listarRN0188($objRelProtocoloAssuntoDTO);

            // Cria o processo
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setStrDescricao('Recolhimento de documentos');
            $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
            $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrayAssuntos);
            $objProtocoloDTO->setArrObjParticipanteDTO(array());
            $objProtocoloDTO->setArrObjObservacaoDTO(array());

            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProcedimentoDTO->setNumIdTipoProcedimento($numIdTipoProcedimentoArquivamento);
            $objProcedimentoDTO->setObjProtocoloDTO($objProtocoloDTO);
            $objProcedimentoDTO->setStrSinGerarPendencia('S');

            // Cadastra o processo
            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoDTO = $objProcedimentoRN->gerarRN0156($objProcedimentoDTO);

            // Cadastra o doumento
            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento(null);
            $objDocumentoDTO->setDblIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());

            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo(null);
            $objProtocoloDTO->setStrStaProtocolo('G');
            $objProtocoloDTO->setStrDescricao('');

            $objDocumentoDTO->setNumIdSerie($numIdTipoDocumentoArquivamento);
            $objDocumentoDTO->setDblIdDocumentoEdoc(null);
            $objDocumentoDTO->setDblIdDocumentoEdocBase(null);
            $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objDocumentoDTO->setNumIdTipoConferencia(null);
            $objDocumentoDTO->setStrNumero('');

            $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
            $objProtocoloDTO->setArrObjParticipanteDTO(array());
            $objProtocoloDTO->setArrObjObservacaoDTO(array());
            $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);
            $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);
            $objDocumentoDTO->setStrConteudo($this->obterConteudoDocumentoRecolhimento($arrObjMdGdArquivamentoDTO));

            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);
            
            // Cria a listagem de recolhimento
            $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
            $objMdGdListaRecolhimentoDTO->setStrNumero($this->obterProximaNumeroListagem());
            $objMdGdListaRecolhimentoDTO->setDthEmissaoListagem(date('d/m/Y H:i:s'));
            $objMdGdListaRecolhimentoDTO->setNumAnoLimiteInicio($numAnoLimiteInicial);
            $objMdGdListaRecolhimentoDTO->setNumAnoLimiteFim($numAnoLimiteFinal);
            $objMdGdListaRecolhimentoDTO->setNumQtdProcessos(count($arrObjMdGdArquivamentoDTO));
            $objMdGdListaRecolhimentoDTO->setStrSituacao(self::$ST_GERADA);
            $objMdGdListaRecolhimentoDTO->setStrSinDocumentosFisicos($strSinDocumentosFisicos);
            $objMdGdListaRecolhimentoDTO->setDblIdProcedimentoRecolhimento($objProcedimentoDTO->getDblIdProcedimento());
            $objMdGdListaRecolhimentoDTO->setDblIdDocumentoRecolhimento($objDocumentoDTO->getDblIdDocumento());
            $objMdGdListaRecolhimentoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->getObjInfraIBanco());
            $objMdGdListaRecolhimentoDTO = $objMdGdListaRecolhimentoBD->cadastrar($objMdGdListaRecolhimentoDTO);

            $objMdGdListRecolProcedimentoBD = new MdGdListaRecolProcedimentoBD($this->getObjInfraIBanco());
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();

            // Cria a relação da listagem de recolhimento com os procedimentos
            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {

                //Cria o vílculo da lista com o procedimento
                $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
                $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($objMdGdListaRecolhimentoDTO->getNumIdListaRecolhimento());
                $objMdGdListaRecolProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());

                $objMdGdListRecolProcedimentoBD->cadastrar($objMdGdListaRecolProcedimentoDTO);

                // Altera a situação do arquivamento do procedimento
                $objMdGdArquivamentoDTO->setNumIdListaRecolhimento($objMdGdListaRecolhimentoDTO->getNumIdListaRecolhimento());
                $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_ENVIADO_RECOLHIMENTO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }

        } catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar a listagem de recolhimento.', $e);
        }
    }
    
    
    protected function alterarConectado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO) {
        try {

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->getObjInfraIBanco());
            return $objMdGdListaRecolhimentoBD->alterar($objMdGdListaRecolhimentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro consultando a listagem de recolhimento.', $e);
        }
    }
    
    protected function consultarConectado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimento) {
        try {

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->getObjInfraIBanco());
            return $objMdGdListaRecolhimentoBD->consultar($objMdGdListaRecolhimento);
        } catch (Exception $e) {
            throw new InfraException('Erro consultando a listagem de recolhimento.', $e);
        }
    }

    protected function listarConectado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimento) {
        try {

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->getObjInfraIBanco());
            return $objMdGdListaRecolhimentoBD->listar($objMdGdListaRecolhimento);
        } catch (Exception $e) {
            throw new InfraException('Erro listando as listagens de recolhimento.', $e);
        }
    }

    protected function contarConectado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimento) {
        try {

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdListaRecolhimentoBD->contar($objMdGdListaRecolhimento);
        } catch (Exception $e) {
            throw new InfraException('Erro ao contar lista de recolhimentos.', $e);
        }
    }

    public function obterProximaNumeroListagem() {
        $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
        $objMdGdListaRecolhimentoDTO->setStrNumero('%' . date('Y'), InfraDTO::$OPER_LIKE);
        $objMdGdListaRecolhimentoDTO->retTodos();

        $arrObjMdGdListaRecolhimento = $this->listar($objMdGdListaRecolhimentoDTO);

        $numeroListagem = count($arrObjMdGdListaRecolhimento) + 1;

        return $numeroListagem . "/" . date('Y');
    }

    public function obterConteudoDocumentoRecolhimento($arrObjMdGdArquivamentoDTO) {
        $objSessaoSEI = SessaoSEI::getInstance();

        // Calcula as datas limite
        $numAnoLimiteInicial = 0;
        $numAnoLimiteFinal = 0;
        $idsProcedimento = [];

        foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
            $numAnoFinal = (int) substr($objMdGdArquivamentoDTO->getDthDataArquivamento(), 6, 4);
            $numAnoLimiteFinal = $numAnoLimiteFinal < $numAnoFinal ? $numAnoFinal : $numAnoLimiteFinal;
            $idsProcedimento[] = $objMdGdArquivamentoDTO->getDblIdProcedimento();
        }

        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDblIdProtocolo($idsProcedimento, InfraDTO::$OPER_IN);
        $objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_ASC);
        $objAtividadeDTO->retDthAbertura();

        $objAtividadeRN = new AtividadeRN();
        $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

        $dthPrimeiroAndamento = $arrObjAtividadeDTO[0]->getDthAbertura();
        $dthPrimeiroAndamento = explode(' ', $dthPrimeiroAndamento);
        $dthPrimeiroAndamento = explode('/', $dthPrimeiroAndamento[0]);

        $numAnoLimiteInicial = $dthPrimeiroAndamento[2];

        $arrVariaveisModelo = [
            '@orgao@' => $objSessaoSEI->getStrDescricaoOrgaoUsuario(),
            '@unidade@' => $objSessaoSEI->getStrSiglaUnidadeAtual() . ' - ' . $objSessaoSEI->getStrSiglaUnidadeAtual(),
            '@numero_listagem@' => $this->obterProximaNumeroListagem(),
            '@folha@' => '1/1', // Verificar depois
            '@tabela@' => '',
            '@mensuracao_total@' => count($arrObjMdGdArquivamentoDTO) . ' processos',
            '@datas_limites_gerais@' => $numAnoLimiteInicial.'-'.$numAnoLimiteFinal
        ];

        $strHtmlTabela = '<table border="1" cellpadding="1" cellspacing="1" style="margin-left:auto;margin-right:auto; width: 873px;">';
        $strHtmlTabela .= '<thead><tr>';
        $strHtmlTabela .= '<th><p class="Tabela_Texto_Centralizado">CÓDIGO DE CLASSIFICAÇÃO</p></th>';
        $strHtmlTabela .= '<th><p class="Tabela_Texto_Centralizado">DESCRITOR DO CÓDIGO</p></th>';
        $strHtmlTabela .= '<th><p class="Tabela_Texto_Centralizado">DATAS-LIMITE</p></th>';
        $strHtmlTabela .= '<th><p class="Tabela_Texto_Centralizado">PROCESSO Nº</p></th>';
        $strHtmlTabela .= '<th><p class="Tabela_Texto_Centralizado">OBSERVAÇÕES E/OU JUSTIFICATIVAS</p></th>';
        $strHtmlTabela .= '</thead></tr>';

        $strHtmlTabela .= '<tbody>';

        foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
            // Obtem os dados do assunto
            $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();

            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($objMdGdArquivamentoDTO->getDblIdProtocoloProcedimento());
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
                    $strCodigoClassificacao .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto() . "  <br><br>  ";
                    $strDescritorCodigo .= $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto() . "  <br><br>  ";
                }
            }


            $objAtividadeDTO = new AtividadeDTO();
            $objAtividadeDTO->setDblIdProtocolo($objMdGdArquivamentoDTO->getDblIdProtocoloProcedimento());
            $objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_ASC);
            $objAtividadeDTO->retDthAbertura();
    
            $objAtividadeRN = new AtividadeRN();
            $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);
    
            $dthPrimeiroAndamento = $arrObjAtividadeDTO[0]->getDthAbertura();
            $dthPrimeiroAndamento = explode(' ', $dthPrimeiroAndamento);
            $dthPrimeiroAndamento = explode('/', $dthPrimeiroAndamento[0]);
    
            $numAnoLimiteInicial = $dthPrimeiroAndamento[2];
            $numAnoLimiteFinal = (int) substr($objMdGdArquivamentoDTO->getDthDataArquivamento(), 6, 4);
    

            $strHtmlTabela .= '<tr>';
            $strHtmlTabela .= '<td><p class="Tabela_Texto_Centralizado">' . $strCodigoClassificacao . '</p></td>';
            $strHtmlTabela .= '<td><p class="Tabela_Texto_Centralizado">' . $strDescritorCodigo . '</p></td>';
            $strHtmlTabela .= '<td><p class="Tabela_Texto_Centralizado">' . $numAnoLimiteInicial.'-'.$numAnoLimiteFinal.'</p></td>';
            $strHtmlTabela .= '<td><p class="Tabela_Texto_Centralizado">' . $objMdGdArquivamentoDTO->getStrProtocoloFormatado().'</p></td>';
            $strHtmlTabela .= '<td><p class="Tabela_Texto_Centralizado">' . $objMdGdArquivamentoDTO->getStrObservacaoRecolhimento() . '</p></td>';
            $strHtmlTabela .= '</tr>';
        }

        $strHtmlTabela .= '</tbody>';
        $strHtmlTabela .= '</table>';

        $arrVariaveisModelo['@tabela@'] = $strHtmlTabela;

        // Calcula o tamanho
        $arrIdProcedimentos = [];
        foreach($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO){
            $arrIdProcedimentos[] = $objMdGdArquivamentoDTO->getDblIdProcedimento();
        }

        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->setDblIdProcedimento($arrIdProcedimentos, InfraDTO::$OPER_IN);
        $objDocumentoDTO->retDblIdDocumento();

        $objDocumentoRN = new DocumentoRN();
        $arrObjDocumentoDTO = $objDocumentoRN->listarRN0008($objDocumentoDTO);

        $arrIdDocumentos = [];
        foreach($arrObjDocumentoDTO as $objDocumentoDTO){
            $arrIdDocumentos[] = $objDocumentoDTO->getDblIdDocumento();
        }

        // Calcula o tamanho dos anexos
        $objAnexoDTO = new AnexoDTO();
        $objAnexoDTO->setDblIdProtocolo($arrIdDocumentos, InfraDTO::$OPER_IN);
        $objAnexoDTO->retNumTamanho();

        $objAnexoRN = new AnexoRN();
        $arrAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);

        $numTamanho = 0;
        foreach($arrAnexoDTO as $objAnexoDTO) {
            $numTamanho += $objAnexoDTO->getNumTamanho();
        }

        // Calcula o tamanho dos documentos nato digital
        $objDocumentoConteudoDTO = new DocumentoConteudoDTO();
        $objDocumentoConteudoDTO->setDblIdDocumento($arrIdDocumentos, InfraDTO::$OPER_IN);
        $objDocumentoConteudoDTO->retStrConteudo();
        $objDocumentoConteudoDTO->retDblIdDocumento();

        $objDocumentoConteudoRN = new DocumentoConteudoBD($this->getObjInfraIBanco());
        $arrObjDocumentoConteudoDTO = $objDocumentoConteudoRN->listar($objDocumentoConteudoDTO);

        foreach($arrObjDocumentoConteudoDTO as $objDocumentoConteudoDTO){
            //$numTamanho += strlen($objDocumentoConteudoDTO->getStrConteudo()) / 8000;

            $objAnexoRN = new AnexoRN();
            
            $objEditorDTO = new EditorDTO();
            $objEditorDTO->setDblIdDocumento($objDocumentoConteudoDTO->getDblIdDocumento());
            $objEditorDTO->setNumIdBaseConhecimento(null);
            $objEditorDTO->setStrSinCabecalho('S');
            $objEditorDTO->setStrSinRodape('S');
            $objEditorDTO->setStrSinCarimboPublicacao('S');
            $objEditorDTO->setStrSinIdentificacaoVersao('N');

            $objEditorRN = new EditorRN();
            $strResultado = $objEditorRN->consultarHtmlVersao($objEditorDTO);

            $strArquivoHtmlTemp = DIR_SEI_TEMP.'/'.$objAnexoRN->gerarNomeArquivoTemporario('.html');

            if (file_put_contents($strArquivoHtmlTemp,$strResultado) === false){
                throw new InfraException('Erro criando arquivo html temporário para criação de pdf.');
            }
              
            $numTamanho +=  filesize($strArquivoHtmlTemp);

            unlink($strArquivoHtmlTemp);
            
        }
        $arrVariaveisModelo['@folha@'] = $numTamanho;

        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome(MdGdModeloDocumentoRN::MODELO_LISTAGEM_ELIMINACAO);
        $objMdGdModeloDocumentoDTO->retTodos();

        $objMdGdModeloDocumentoRN = new MdGdModeloDocumentoRN();
        $objMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoRN->consultar($objMdGdModeloDocumentoDTO);

        $str = $objMdGdModeloDocumentoDTO->getStrValor();
        $str = strtr($str, $arrVariaveisModelo);
        return $str;
    }

    public function gerarPdfConectado($numIdListagem) {
        $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
        $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($_GET['id_listagem_recolhimento']);

        if($_POST['hdnInfraItensSelecionados']){
            $arrIdsProcedimentos = explode(',', $_POST['hdnInfraItensSelecionados']);
            $objMdGdListaRecolProcedimentoDTO->setDblIdProcedimento($arrIdsProcedimentos, InfraDTO::$OPER_IN);
        }
        
        $objMdGdListaRecolProcedimentoDTO->retDblIdProcedimento();

        $objMdGdListaRecolProcedimentoRN = new MdGdListaRecolProcedimentoRN();
        $arrObjMdGdListaRecolProcedimentoDTO = $objMdGdListaRecolProcedimentoRN->listar($objMdGdListaRecolProcedimentoDTO);

        $arrIdsRecolhimento = explode(',', InfraArray::implodeArrInfraDTO($arrObjMdGdListaRecolProcedimentoDTO, 'IdProcedimento'));

        // Busca todos os arquivamentos dos processos daquela listagem
        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

        $objMdGdArquivamentoDTO->retNumIdArquivamento();
        $objMdGdArquivamentoDTO->retDthDataArquivamento();
        $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
        $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
        $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
        $objMdGdArquivamentoDTO->retStrObservacaoRecolhimento();
        $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
        $objMdGdArquivamentoDTO->setStrSinAtivo('S');
        $objMdGdArquivamentoDTO->setDblIdProcedimento($arrIdsRecolhimento, InfraDTO::$OPER_IN);

        $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);
        $numRegistros = count($arrObjMdGdArquivamentoDTO);

        if ($numRegistros > 0) {
            $strResultado = '';

            $strResultado .= '<table width="99%" class="infraTable" border="1">';
            $strResultado .= '<tr>';
            $strResultado .= '<th class="infraTh" width="13%">Descrição Unidade Corrente</th>';
            $strResultado .= '<th class="infraTh" width="10%">Código de Classificação</th>';
            $strResultado .= '<th class="infraTh" width="20%">Descritor do Código</th>';
            $strResultado .= '<th class="infraTh" width="14%">Nº do Processo</th>';
            $strResultado .= '<th class="infraTh" width="15%">Tipo de Processo</th>';
            $strResultado .= '<th class="infraTh" width="10%">Data de arquivamento</th>';
            $strResultado .= '<th class="infraTh" width="10%">Observações e/ou Justificativas</th>';
            $strResultado .= '</tr>';
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
                        $strCodigoClassificacao .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto() . " <br><br> ";
                        $strDescritorCodigo .= $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto() . " <br><br> ";
                    }
                }

                $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
                $strResultado .= $strCssTr;

                $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoUnidadeCorrente()) . '</td>';
                $strResultado .= '<td>' . $strCodigoClassificacao . '</td>';
                $strResultado .= '<td>' . $strDescritorCodigo . '</td>';
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado()) . '</td>';
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento()) . '</td>';
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrObservacaoRecolhimento()) . '</td>';
                $strResultado .= '</tr>';
            }
            $strResultado .= '</table>';
        }

        $strCaminhoArquivoHtml = DIR_SEI_TEMP . '/gerar-pdf-listagem-recolhimento-' . date('YmdHis') . '.html';
        $strCaminhoArquivoPdf = DIR_SEI_TEMP . '/gerar-pdf-listagem-recolhimento-' . date('YmdHis') . '.pdf';
        file_put_contents($strCaminhoArquivoHtml, $strResultado);

        $strComandoGerarPdf = DIR_SEI_BIN . '/wkhtmltopdf-amd64 --quiet --orientation \'landscape\' --title md_gd_pdf_listagem_recolhimento-' . InfraUtil::retirarFormatacao('1123123', false) . ' ' . $strCaminhoArquivoHtml . '  ' . $strCaminhoArquivoPdf . ' 2>&1';
        shell_exec($strComandoGerarPdf);
        SeiINT::download(null, $strCaminhoArquivoPdf, 'listagem_recolhimento.pdf', 'attachment', true);
    }

     /**
     * Altera a situação da listagem de recolhimento para em edição
     *
     * @param MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO
     * @return void
     */
    public function editarListaRecolhimentoControlado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO){
        try{
            if(!$objMdGdListaRecolhimentoDTO->isSetNumIdListaRecolhimento()){
                throw new InfraException('Informe o id da lista de recolhimento para deixar em modo de edição.');
            }

            $objMdGdListaRecolhimentoDTO->retNumIdListaRecolhimento();
            $objMdGdListaRecolhimentoDTO->retStrSituacao();

            $objMdGdListaRecolhimentoDTO = $this->consultar($objMdGdListaRecolhimentoDTO);
            
            if($objMdGdListaRecolhimentoDTO->getStrSituacao() != self::$ST_GERADA){
                throw new InfraException('A listagem precisa estar na situação gerada.'); 
            }

            $objMdGdListaRecolhimentoDTO->setStrSituacao(self::$ST_EDICAO);
            return $this->alterar($objMdGdListaRecolhimentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de recolhimento para o modo de edição.', $e);
        }
    }

    /**
     * Conclui a edição da listagem de recolhimento
     *
     * @param MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO
     * @return void
     */
    public function concluirEdicaoListaRecolhimentoControlado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO){
        try{
            if(!$objMdGdListaRecolhimentoDTO->isSetNumIdListaRecolhimento()){
                throw new InfraException('Informe o id da lista de recolhimento para concluir a edição.');
            }

            $objMdGdListaRecolhimentoDTO->retDblIdProcedimentoRecolhimento();
            $objMdGdListaRecolhimentoDTO->retNumIdListaRecolhimento();
            $objMdGdListaRecolhimentoDTO->retStrSituacao();

            $objMdGdListaRecolhimentoDTO = $this->consultar($objMdGdListaRecolhimentoDTO);
            
            if($objMdGdListaRecolhimentoDTO->getStrSituacao() != self::$ST_EDICAO){
                throw new InfraException('A listagem precisa estar na situação gerada.'); 
            }

            // Obtem os processos da listagem de recolhimento
            $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
            $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($objMdGdListaRecolhimentoDTO->getNumIdListaRecolhimento());
            $objMdGdListaRecolProcedimentoDTO->retDblIdProcedimento();

            $objMdGdListaRecolProcedimentoRN = new MdGdListaRecolProcedimentoRN();
            $arrObjMdGdListaRecolProcedimentoDTO = $objMdGdListaRecolProcedimentoRN->listar($objMdGdListaRecolProcedimentoDTO);
            $totalProcessos = count($arrObjMdGdListaRecolProcedimentoDTO);

            if($totalProcessos == 0) {
                $objInfraException = new InfraException();
                $objInfraException->lancarValidacao('Não é possível concluir a edição pois não há processos na listagem.');
            }

            // Obtem os documentos vinculados aos processos da listagem
            $arrIdsProcedimentos = [];
            $arrObjMdGdArquivamentoDTO = [];

            foreach($arrObjMdGdListaRecolProcedimentoDTO as $objMdGdListaRecolProcedimentoDTO){
                $arrIdsProcedimentos[] = $objMdGdListaRecolProcedimentoDTO->getDblIdProcedimento();
            }
            
            if($arrIdsProcedimentos){
                $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
                $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($arrIdsProcedimentos, InfraDTO::$OPER_IN);
                $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();

                $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
                $arrObjRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO);
                
                $arrIdsDocumentos = array();
                if ($arrObjRelProtocoloProtocoloDTO) {
                    $arrIdsDocumentos = explode(',', InfraArray::implodeArrInfraDTO($arrObjRelProtocoloProtocoloDTO, 'IdProtocolo2'));
                }

                // Obtem todos os arquivamentos fisicos registrados para os documentos do processo
                $objArquivamentoDTO = new ArquivamentoDTO();
                $objArquivamentoDTO->retDblIdProtocoloDocumento();
                $objArquivamentoDTO->setDblIdProtocoloDocumento($arrIdsDocumentos, InfraDTO::$OPER_IN);

                $objArquivamentoRN = new ArquivamentoRN();
                $strSinDocumentosFisicos = $objArquivamentoRN->contar($objArquivamentoDTO) == 0 ? 'N' : 'S';

                // Obtem os arquivamentos dos processos
                $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                $objMdGdArquivamentoDTO->setDblIdProcedimento($arrIdsProcedimentos, InfraDTO::$OPER_IN);
                $objMdGdArquivamentoDTO->setStrSinAtivo('S');
                $objMdGdArquivamentoDTO->retDblIdProcedimento();
                $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
                $objMdGdArquivamentoDTO->retDthDataArquivamento();
                $objMdGdArquivamentoDTO->retDthDataGuardaCorrente();
                $objMdGdArquivamentoDTO->retDthDataGuardaIntermediaria();
                $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
                $objMdGdArquivamentoDTO->retStrObservacaoRecolhimento();

                $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
                $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

                // Calcula as datas limite
                $numAnoLimiteInicial = 0;
                $numAnoLimiteFinal = 0;
                $idsProcedimento = [];

                foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
                    $numAnoFinal = (int) substr($objMdGdArquivamentoDTO->getDthDataArquivamento(), 6, 4);
                    $numAnoLimiteFinal = $numAnoLimiteFinal < $numAnoFinal ? $numAnoFinal : $numAnoLimiteFinal;
                    $idsProcedimento[] = $objMdGdArquivamentoDTO->getDblIdProcedimento();
                }

                $objAtividadeDTO = new AtividadeDTO();
                $objAtividadeDTO->setDblIdProtocolo($idsProcedimento, InfraDTO::$OPER_IN);
                $objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_ASC);
                $objAtividadeDTO->retDthAbertura();

                $objAtividadeRN = new AtividadeRN();
                $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

                $dthPrimeiroAndamento = $arrObjAtividadeDTO[0]->getDthAbertura();
                $dthPrimeiroAndamento = explode(' ', $dthPrimeiroAndamento);
                $dthPrimeiroAndamento = explode('/', $dthPrimeiroAndamento[0]);

                $numAnoLimiteInicial = $dthPrimeiroAndamento[2];

                $objMdGdListaRecolhimentoDTO->setNumAnoLimiteInicio($numAnoLimiteInicial);
                $objMdGdListaRecolhimentoDTO->setNumAnoLimiteFim($numAnoLimiteFinal);
            }

            $objMdGdParametroRN = new MdGdParametroRN();
            $numIdTipoDocumento = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_DOCUMENTO_LISTAGEM_RECOLHIMENTO);

            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento(null);
            $objDocumentoDTO->setDblIdProcedimento($objMdGdListaRecolhimentoDTO->getDblIdProcedimentoRecolhimento());

            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo(null);
            $objProtocoloDTO->setStrStaProtocolo('G');
            $objProtocoloDTO->setStrDescricao('');

            $objDocumentoDTO->setNumIdSerie($numIdTipoDocumento);
            $objDocumentoDTO->setDblIdDocumentoEdoc(null);
            $objDocumentoDTO->setDblIdDocumentoEdocBase(null);
            $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objDocumentoDTO->setNumIdTipoConferencia(null);
            $objDocumentoDTO->setStrNumero('');

            $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
            $objProtocoloDTO->setArrObjParticipanteDTO(array());
            $objProtocoloDTO->setArrObjObservacaoDTO(array());

            $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);
            $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);

            if($arrObjMdGdArquivamentoDTO){
                $objDocumentoDTO->setStrConteudo($this->obterConteudoDocumentoRecolhimento($arrObjMdGdArquivamentoDTO));
            }else{
                $objDocumentoDTO->setStrConteudo('');
            }

            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

            $objMdGdListaRecolhimentoDTO->setDblIdDocumentoRecolhimento($objDocumentoDTO->getDblIdDocumento());
            $objMdGdListaRecolhimentoDTO->setStrSituacao(self::$ST_GERADA);
            $objMdGdListaRecolhimentoDTO->setNumQtdProcessos($totalProcessos);
            $objMdGdListaRecolhimentoDTO->setStrSinDocumentosFisicos($strSinDocumentosFisicos);
            return $this->alterar($objMdGdListaRecolhimentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de recolhimento para o modo de edição.', $e);
        }
    }

    /**
     * Atualiza o número de processos de uma listagem de recolhimento
     *
     * @param MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO
     * @return boolean
     */
    public function atualizarNumeroProcessosControlado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO){
        try{
            if(!$objMdGdListaRecolhimentoDTO->isSetNumIdListaRecolhimento()){
                throw new InfraException('Informe o id da lista de recolhimento atualizar o número de processos.');
            }

            // Obtem o quantidade de processos
            $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
            $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($objMdGdListaRecolhimentoDTO->getNumIdListaRecolhimento());

            $objMdGdListaRecolProcedimentoRN = new MdGdListaRecolProcedimentoRN();
            $numQuantidadeProcessos = $objMdGdListaRecolProcedimentoRN->contar($objMdGdListaRecolProcedimentoDTO);

            // Atualiza a quantidade de processos
            $objMdGdListaRecolhimentoDTO->setNumQtdProcessos($numQuantidadeProcessos);
            return $this->alterar($objMdGdListaRecolhimentoDTO);
        }catch(Exception $e){
            throw new InfraException('Erro ao altualizar o número de processos.', $e);

        }
    }


}

?>