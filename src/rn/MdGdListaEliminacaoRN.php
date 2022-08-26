<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaEliminacaoRN extends InfraRN {

    public static $ST_GERADA = 'GE';
    public static $ST_EDICAO = 'ED';
    public static $ST_ELIMINADA = 'EL';

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_lista_eliminacao_preparacao_gerar', __METHOD__, $objMdGdListaEliminacao);

            // Recupera os arquivamentos
            $arrObjMdGdArquivamentoDTO = $objMdGdListaEliminacao->getArrObjMdGdArquivamentoDTO();

            // Recupera os tipos de procedimento e documento que serão criados no arquivamento
            $objMdGdParametroRN = new MdGdParametroRN();
            $numIdTipoProcedimentoArquivamento = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO);
            $numIdTipoDocumentoArquivamento = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO);
            
            $arrIdProtocolo = [];
            foreach($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO){
                $arrIdProtocolo[] = $objMdGdArquivamentoDTO->getDblIdProcedimento();
            }

            //IdProtocolo
            // INCLUI  O PROCESSO
            // INFORMA OS ASSUNTOS
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($arrIdProtocolo, InfraDTO::$OPER_IN);
            $objRelProtocoloAssuntoDTO->retNumIdAssunto();
            $objRelProtocoloAssuntoDTO->retNumSequencia();

            $objRelProtocoloProtocoloRN = new RelProtocoloAssuntoRN();
            $arrayAssuntos = $objRelProtocoloProtocoloRN->listarRN0188($objRelProtocoloAssuntoDTO);

            // INCLUI  OS DEMAIS DADOS DO PROCESSO
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setStrDescricao('Eliminação de documentos');
            $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
            // $objProtocoloDTO->setNumIdHipoteseLegal($hipoteseLegal);
            $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrayAssuntos);
            $objProtocoloDTO->setArrObjParticipanteDTO(array());
            $objProtocoloDTO->setArrObjObservacaoDTO(array());
            // $objProtocoloDTO->setStrStaGrauSigilo($grauSigilo);

            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProcedimentoDTO->setNumIdTipoProcedimento($numIdTipoProcedimentoArquivamento);
            $objProcedimentoDTO->setObjProtocoloDTO($objProtocoloDTO);
            $objProcedimentoDTO->setStrSinGerarPendencia('S');

            // REALIZA A INCLUSÃO DO PROCESSO 
            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoDTO = $objProcedimentoRN->gerarRN0156($objProcedimentoDTO);
            //INCLUSÃO DO DOCUMENTO
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
            $objDocumentoDTO->setStrConteudo($this->obterConteudoDocumentoEliminacao($arrObjMdGdArquivamentoDTO));

            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

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

            // Cria a listagem de eliminação
            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setDblIdProcedimentoEliminacao($objProcedimentoDTO->getDblIdProcedimento());
            $objMdGdListaEliminacaoDTO->setDblIdDocumentoEliminacao($objDocumentoDTO->getDblIdDocumento());
            $objMdGdListaEliminacaoDTO->setStrNumero($this->obterProximaNumeroListagem());
            $objMdGdListaEliminacaoDTO->setDthEmissaoListagem(date('d/m/Y H:i:s'));
            $objMdGdListaEliminacaoDTO->setNumAnoLimiteInicio($numAnoLimiteInicial);
            $objMdGdListaEliminacaoDTO->setNumAnoLimiteFim($numAnoLimiteFinal);
            $objMdGdListaEliminacaoDTO->setNumQtdProcessos(count($arrObjMdGdArquivamentoDTO));
            $objMdGdListaEliminacaoDTO->setStrSituacao(self::$ST_GERADA);
            $objMdGdListaEliminacaoDTO->setStrSinDocumentosFisicos($strSinDocumentosFisicos);
            $objMdGdListaEliminacaoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());

            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            $objMdGdListaEliminacaoDTO = $objMdGdListaEliminacaoBD->cadastrar($objMdGdListaEliminacaoDTO);

            $objMdGdListElimProcedimentoBD = new MdGdListaElimProcedimentoBD($this->getObjInfraIBanco());
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();

            // Cria a relação da listagem de eliminação com os procedimentos
            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {

                //Cria o vílculo da lista com o procedimento
                $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
                $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($objMdGdListaEliminacaoDTO->getNumIdListaEliminacao());
                $objMdGdListaElimProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());

                $objMdGdListElimProcedimentoBD->cadastrar($objMdGdListaElimProcedimentoDTO);

                // Altera a situação do arquivamento do procedimento
                $objMdGdArquivamentoDTO->setNumIdListaEliminacao($objMdGdListaEliminacaoDTO->getNumIdListaEliminacao());
                $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_ENVIADO_ELIMINACAO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }

        } catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar a listagem de eliminação.', $e);
        }
    }

    protected function alterarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            return $objMdGdListaEliminacaoBD->alterar($objMdGdListaEliminacao);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de eliminação.', $e);
        }
    }

    protected function consultarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            return $objMdGdListaEliminacaoBD->consultar($objMdGdListaEliminacao);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de eliminação.', $e);
        }
    }

    protected function listarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            return $objMdGdListaEliminacaoBD->listar($objMdGdListaEliminacao);
        } catch (Exception $e) {
            throw new InfraException('Erro listando as listagens de eliminação.', $e);
        }
    }

    protected function contarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->inicializarObjInfraIBanco());
            return $objMdGdListaEliminacaoBD->contar($objMdGdListaEliminacao);
        } catch (Exception $e) {
            throw new InfraException('Erro ao contar lista de recolhimentos.', $e);
        }
    }

    public function obterProximaNumeroListagem() {
        $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
        $objMdGdListaEliminacaoDTO->setStrNumero('%' . date('Y'), InfraDTO::$OPER_LIKE);
        $objMdGdListaEliminacaoDTO->retTodos();

        $arrObjMdGdListaEliminacao = $this->listar($objMdGdListaEliminacaoDTO);

        $numeroListagem = count($arrObjMdGdListaEliminacao) + 1;

        return $numeroListagem . "/" . date('Y');
    }

    public function obterConteudoDocumentoEliminacao($arrObjMdGdArquivamentoDTO) {
        $objSessaoSEI = SessaoSEI::getInstance();

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

        $objUnidadeDTO=new UnidadeDTO();
        $objUnidadeDTO->setNumIdUnidade($objSessaoSEI->getNumIdUnidadeAtual());
        $objUnidadeDTO->retStrDescricao();
        $objUnidadeRN=new UnidadeRN();
        $objUnidadeDTO=$objUnidadeRN->consultarRN0125($objUnidadeDTO);

        $arrVariaveisModelo = [
            '@orgao@' => $objSessaoSEI->getStrDescricaoOrgaoUsuario(),
            '@unidade@' => $objSessaoSEI->getStrSiglaUnidadeAtual() . ' - ' . $objSessaoSEI->getStrSiglaUnidadeAtual(),
            '@numero_listagem@' => $this->obterProximaNumeroListagem(),
            '@folha@' => '1/1', // Verificar depois
            '@tabela@' => '',
            '@mensuracao_total@' => count($arrObjMdGdArquivamentoDTO) . ' processos',
            '@datas_limites_gerais@' => $numAnoLimiteInicial.'-'.$numAnoLimiteFinal,
            '@descricao_orgao_maiusculas@ ' => strtoupper($objSessaoSEI->getStrDescricaoOrgaoUnidadeAtual()),
            '@sigla_orgao_origem@ ' => strtoupper($objSessaoSEI->getStrSiglaOrgaoSistema()),
            '@descricao_unidade_maiusculas@ ' => strtoupper($objUnidadeDTO->getStrDescricao()),
            '@sigla_unidade@ ' => strtoupper($objSessaoSEI->getStrSiglaUnidadeAtual()),
            '@logo@ ' => '',

        ];

        $strHtmlTabela = '<table border="1" cellpadding="1" cellspacing="1" style="margin-left:auto;margin-right:auto; width: 918px;">';
        $strHtmlTabela .= '<thead><tr>';
        $strHtmlTabela .= '<th rowspan="2"><p class="Tabela_Texto_Centralizado">CÓDIGO REFERENTE A CLASSIFICAÇÃO</p></th>';
        $strHtmlTabela .= '<th rowspan="2"><p class="Tabela_Texto_Centralizado">DESCRITOR DO CÓDIGO</p></th>';
        $strHtmlTabela .= '<th rowspan="2"><p class="Tabela_Texto_Centralizado">DATAS-LIMITE</p></th>';
        $strHtmlTabela .= '<th colspan="2 rowspan="1"><p class="Tabela_Texto_Centralizado">UNIDADE DE ARQUIVAMENTO</p></th>';
        $strHtmlTabela .= '<th rowspan="2"><p class="Tabela_Texto_Centralizado">OBSERVAÇÕES E/OU JUSTIFICATIVAS</p></th>';
        $strHtmlTabela .= '</tr><tr><th><p class="Tabela_Texto_Centralizado">Quantificação</p></th>';
        $strHtmlTabela .= '<th><p class="Tabela_Texto_Centralizado">Especificação</p></th>';

        $strHtmlTabela .= '</thead></tr>';

        $strHtmlTabela .= '<tbody>';


        $arrCodigoClassificacao = [];
        $tamanhoTotal=0;

        foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
            // Obtem os dados do assunto
            $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();

            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($objMdGdArquivamentoDTO->getDblIdProtocoloProcedimento());
            $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();
            $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();
            $objRelProtocoloAssuntoDTO->retNumIdAssunto();

            $arrObjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);

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

            //obtem o código de maior temporalidade
            $objMdGdArquivamentoRN=new MdGdArquivamentoRN();

            $maiorPrazoGuarda=$objMdGdArquivamentoRN->calcularMaiorPrazoGuarda($arrObjRelProtocoloAssuntoDTO);

            $tamanhoProcesso= $this->calcularTamanhoProcesso($objMdGdArquivamentoDTO);

            $codigo=$maiorPrazoGuarda['codigo'];

            if(!array_key_exists($codigo, $arrCodigoClassificacao)){

                $arrCodigoClassificacao[$codigo]=array(
                    "descricao" => $maiorPrazoGuarda['descricao'],
                    "menorAno" => $numAnoLimiteInicial,
                    "maiorAno" => $numAnoLimiteFinal,
                    "quantidade" => 1,
                    "tamanho" => $tamanhoProcesso,
                    // "observacao" => $objMdGdArquivamentoDTO->getStrObservacaoEliminacao(),
                    
                );
            }else{

                $arrCodigoClassificacao[$codigo]['quantidade']+=1;
                if($numAnoLimiteInicial < $arrCodigoClassificacao[$codigo]['menorAno'] ){
                    $arrCodigoClassificacao[$codigo]['menorAno']=$numAnoLimiteInicial;
                }
                if($numAnoLimiteFinal > $arrCodigoClassificacao[$codigo]['maiorAno'] ){
                    $arrCodigoClassificacao[$codigo]['maiorAno']=$numAnoLimiteFinal;
                }

                $arrCodigoClassificacao[$codigo]['tamanho'] += $tamanhoProcesso;

                // $arrCodigoClassificacao[$codigo]['observacao'].= ' - ' . $objMdGdArquivamentoDTO->getStrObservacaoEliminacao();


            }

            $tamanhoTotal += $tamanhoProcesso;

        }

        
        
        foreach ($arrCodigoClassificacao as $key => $assunto) {

            $unidadeTamanho = $this->formataTamanho($assunto['tamanho']);

            $strHtmlTabela .= '<tr>';
            $strHtmlTabela .= '<td style="text-align: center;"><p class="Tabela_Texto_Centralizado">' . $key . '</p></td>';
            $strHtmlTabela .= '<td style="text-align: center;"><p class="Tabela_Texto_Centralizado">' . $assunto['descricao'] . '</p></td>';
            $strHtmlTabela .= '<td style="text-align: center;"><p class="Tabela_Texto_Centralizado">' . $assunto['menorAno'] .'-'. $assunto['maiorAno'] .'</p></td>';
            $strHtmlTabela .= '<td style="text-align: center;"><p class="Tabela_Texto_Centralizado">' . $assunto['quantidade'] .' Processos (' . number_format($unidadeTamanho['valor'],0,",",".") . ' ' . $unidadeTamanho['unidade'] .')</p></td>';
            $strHtmlTabela .= '<td style="text-align: center;"><p class="Tabela_Texto_Centralizado">' . $unidadeTamanho['unidade'] .'</p></td>';
            $strHtmlTabela .= '<td style="text-align: center;"><p class="Tabela_Texto_Centralizado">' . '</p></td>';
            $strHtmlTabela .= '</tr>';
        }

        
                

        $strHtmlTabela .= '</tbody>';
        $strHtmlTabela .= '</table>';

        $arrVariaveisModelo['@tabela@'] = $strHtmlTabela;
        $imagem_logo=file_get_contents(MdGestaoDocumentalIntegracao::getDiretorio() . "/imagens/logo_brasil.png" );
        $imagem_logo=base64_encode($imagem_logo);
        $arrVariaveisModelo['@logo@'] = '<img src="data:image/png;base64,' . $imagem_logo . '" style="width: 130px; height: 73px;" /></a>&nbsp;';


        $unidadeTamanho = $this->formataTamanho($tamanhoTotal);

        $arrVariaveisModelo['@tamanho_total@'] = number_format($unidadeTamanho['valor'],0,",",".") . ' ' . $unidadeTamanho['unidade'];
       

        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome(MdGdModeloDocumentoRN::MODELO_LISTAGEM_ELIMINACAO);
        $objMdGdModeloDocumentoDTO->retTodos();

        $objMdGdModeloDocumentoRN = new MdGdModeloDocumentoRN();
        $objMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoRN->consultar($objMdGdModeloDocumentoDTO);

        $str = $objMdGdModeloDocumentoDTO->getStrValor();
        $str = strtr($str, $arrVariaveisModelo);
        return $str;
    }

    public function calcularTamanhoProcesso($objMdGdArquivamentoDTO){

        // Calcula o tamanho
        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
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
            // $numTamanho += strlen($objDocumentoConteudoDTO->getStrConteudo()) / 8000;

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

        return $numTamanho;
    }

    public function formataTamanho($tamanho){

        switch (true) {

            case $tamanho<1000:
                return [
                    "valor" => $tamanho,
                    "unidade" => 'bytes',
                ];
                break;

            case $tamanho>1000 && $tamanho<1000000:
                return [
                    "valor" => $tamanho/1000,
                    "unidade" => 'Kb',
                ];
                break;

            case $tamanho>1000000 && $tamanho<1000000000:
                return [
                    "valor" => $tamanho/1000000,
                    "unidade" => 'Mb',
                ];
                break;

            case $tamanho>1000000000:
                return [
                    "valor" => $tamanho/1000000000,
                    "unidade" => 'Gb',
                ];
                break;
            
            default:

                break;
        }

    }

    public function gerarPdfConectado($numIdListagem) {
        try{
            $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
            $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($numIdListagem);
            $objSessaoSEI = SessaoSEI::getInstance();
    
            if($_POST['hdnInfraItensSelecionados']){
                $arrIdsProcedimentos = explode(',', $_POST['hdnInfraItensSelecionados']);
                $objMdGdListaElimProcedimentoDTO->setDblIdProcedimento($arrIdsProcedimentos, InfraDTO::$OPER_IN);
            }
            
            $objMdGdListaElimProcedimentoDTO->retDblIdProcedimento();

            $objMdGdListaElimProcedimentoRN = new MdGdListaElimProcedimentoRN();
            $arrObjMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoRN->listar($objMdGdListaElimProcedimentoDTO);

            
            $arrIdsEliminacao = explode(',', InfraArray::implodeArrInfraDTO($arrObjMdGdListaElimProcedimentoDTO, 'IdProcedimento'));

            // Busca todos os arquivamentos dos processos daquela listagem
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

            $objMdGdArquivamentoDTO->retNumIdArquivamento();
            $objMdGdArquivamentoDTO->retDthDataArquivamento();
            $objMdGdArquivamentoDTO->retDthDataGuardaIntermediaria();
            $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
            $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
            $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
            $objMdGdArquivamentoDTO->retStrObservacaoEliminacao();
            $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
            $objMdGdArquivamentoDTO->setStrSinAtivo('S');
            $objMdGdArquivamentoDTO->setDblIdProcedimento($arrIdsEliminacao, InfraDTO::$OPER_IN);
            $objMdGdArquivamentoDTO->retStrNomeJustificativa();
            $objMdGdArquivamentoDTO->retStrDescricaoJustificativa();
            $objMdGdArquivamentoDTO->retStrDescricao();

            $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);
            $numRegistros = count($arrObjMdGdArquivamentoDTO);

            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setNumIdListaEliminacao($numIdListagem);
            $objMdGdListaEliminacaoDTO->retStrNumero();
            $objMdGdListaEliminacaoDTO = $this->consultar($objMdGdListaEliminacaoDTO);

            $objUnidadeDTO = new UnidadeDTO();
            $objUnidadeDTO->retStrTimbreOrgao();
            $objUnidadeDTO->setNumIdUnidade($objSessaoSEI->getNumIdUnidadeAtual());

            $objUnidadeRN = new UnidadeRN();
            $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

            if ($numRegistros > 0) {
                $strResultado = '';

                $strSumarioTabela = 'Lista de Processos';

                $strResultado = '<meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                                <style type="text/css">p.Citacao {font-size:10pt;font-family:Calibri;word-wrap:normal;margin:4pt 0 4pt 160px;text-align:justify;} p.Item_Alinea_Letra {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt 6pt 6pt 120px;counter-increment:letra_minuscula;} p.Item_Alinea_Letra:before {content:counter(letra_minuscula, lower-latin) ") ";display:inline-block;width:5mm;font-weight:normal;} p.Item_Inciso_Romano {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt 6pt 6pt 120px;counter-increment:romano_maiusculo;counter-reset:letra_minuscula;} p.Item_Inciso_Romano:before {content:counter(romano_maiusculo, upper-roman) " - ";display:inline-block;width:15mm;font-weight:normal;} p.Item_Nivel1 {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;counter-increment:item-n1;counter-reset:item-n2 item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel1:before {content:counter(item-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n2;counter-reset:item-n3 item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel2:before {content:counter(item-n1) "." counter(item-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n3;counter-reset:item-n4 romano_maiusculo letra_minuscula;} p.Item_Nivel3:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Item_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:item-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Item_Nivel4:before {content:counter(item-n1) "." counter(item-n2) "." counter(item-n3) "."  counter(item-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel1 {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0mm;margin:6pt;counter-increment:paragrafo-n1;counter-reset:paragrafo-n2 paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel1:before {content:counter(paragrafo-n1) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel2 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n2;counter-reset:paragrafo-n3 paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel2:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel3 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n3;counter-reset:paragrafo-n4 romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel3:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) ".";display:inline-block;width:25mm;font-weight:normal;} p.Paragrafo_Numerado_Nivel4 {font-size:12pt;font-family:Calibri;text-indent:0mm;text-align:justify;word-wrap:normal;margin:6pt;counter-increment:paragrafo-n4;counter-reset:romano_maiusculo letra_minuscula;} p.Paragrafo_Numerado_Nivel4:before {content:counter(paragrafo-n1) "." counter(paragrafo-n2) "." counter(paragrafo-n3) "." counter(paragrafo-n4) ".";display:inline-block;width:25mm;font-weight:normal;} p.Tabela_Texto_8 {font-size:8pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Direita {font-size:11pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Alinhado_Esquerda {font-size:11pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0 3pt 0 3pt;} p.Tabela_Texto_Centralizado {font-size:11pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:0 3pt 0;} p.Texto_Alinhado_Direita {font-size:12pt;font-family:Calibri;text-align:right;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:6pt;} p.Texto_Alinhado_Esquerda_Espacamento_Simples {font-size:12pt;font-family:Calibri;text-align:left;word-wrap:normal;margin:0;} p.Texto_Alinhado_Esquerda_Espacamento_Simples_Maiusc {font-size:12pt;font-family:Calibri;text-align:left;text-transform:uppercase;word-wrap:normal;margin:0;} p.Texto_Centralizado {font-size:12pt;font-family:Calibri;text-align:center;word-wrap:normal;margin:6pt;} p.Texto_Centralizado_Maiusculas {font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Centralizado_Maiusculas_Negrito {font-weight:bold;font-size:13pt;font-family:Calibri;text-align:center;text-transform:uppercase;word-wrap:normal;} p.Texto_Espaco_Duplo_Recuo_Primeira_Linha {letter-spacing:0.2em;font-weight:bold;font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Fundo_Cinza_Maiusculas_Negrito {text-transform:uppercase;font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Fundo_Cinza_Negrito {font-weight:bold;background-color:#e6e6e6;font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;} p.Texto_Justificado_Maiusculas {font-size:12pt;font-family:Calibri;text-align:justify;word-wrap:normal;text-indent:0;margin:6pt;text-transform:uppercase;} p.Texto_Justificado_Recuo_Primeira_Linha {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:6pt;} p.Texto_Justificado_Recuo_Primeira_Linha_Esp_Simples {font-size:12pt;font-family:Calibri;text-indent:25mm;text-align:justify;word-wrap:normal;margin:0 0 0 6pt;}
                                </style>
                                <title></title></div>
                                
                                <div align="center" wfd-id="1">&nbsp;</div>

                                <p class="Texto_Centralizado_Maiusculas"><img alt="Timbre" src="data:image/png;base64,' . $objUnidadeDTO->getStrTimbreOrgao() . '" /></p>
                                
                                <p class="Texto_Centralizado_Maiusculas">'.$objSessaoSEI->getStrDescricaoOrgaoUsuario().'</p>
                                
                                <p class="Texto_Centralizado_Maiusculas">LISTAGEM DE ELIMINAÇÃO DE DOCUMENTOS Nº '.$objMdGdListaEliminacaoDTO->getStrNumero().'</p>
                                
                                <p class="Texto_Centralizado">Relação de Processos</p>';

                $strResultado .= '<table width="99%" class="infraTable" border="1" summary="' . $strSumarioTabela . '">' . "\n";
                $strResultado .= '<tr>';
                $strResultado .= '<th class="infraTh" width="13%">Unidade</th>';
                $strResultado .= '<th class="infraTh" width="10%">Código de Classificação</th>';
                $strResultado .= '<th class="infraTh" width="14%">Nº do Processo</th>';
                $strResultado .= '<th class="infraTh" width="15%">Tipo de Processo</th>';
                $strResultado .= '<th class="infraTh" width="13%">Especificação</th>';
                $strResultado .= '<th class="infraTh" width="13%">Justificativa de Arquivamento</th>';
                $strResultado .= '<th class="infraTh" width="13%">Base Legal</th>';
                $strResultado .= '<th class="infraTh" width="10%">Data de Arquivamento</th>';
                $strResultado .= '<th class="infraTh" width="13%">Data de Destinação</th>';
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

                    $strResultado .= '<td align="center">' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoUnidadeCorrente()) . '</td>';
                    $strResultado .= '<td align="center">' . $strCodigoClassificacao . '</td>';
                    $strResultado .= '<td align="center">' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado()) . '</td>';
                    $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
                    $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricao()) . '</td>';
                    $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeJustificativa()) . '</td>';
                    $strResultado .= '<td align="center">' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoJustificativa()) . '</td>';
                    $strResultado .= '<td align="center">' . PaginaSEI::tratarHTML(substr($arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento(), 0, 10)) . '</td>';
                    $strResultado .= '<td align="center">' . PaginaSEI::tratarHTML(substr($arrObjMdGdArquivamentoDTO[$i]->getDthDataGuardaIntermediaria(), 0, 10)) . '</td>';
                    $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrObservacaoEliminacao()) . '</td>';
                    $strResultado .= '</tr>';
                }
                $strResultado .= '</table>';
            }

            $strCaminhoArquivoHtml = DIR_SEI_TEMP . '/gerar-pdf-listagem-eliminacao-' . date('YmdHis') . '.html';
            $strCaminhoArquivoPdf = DIR_SEI_TEMP . '/gerar-pdf-listagem-eliminacao-' . date('YmdHis') . '.pdf';
            $strCaminhoArquivoPdfRelativo = 'gerar-pdf-listagem-eliminacao-' . date('YmdHis') . '.pdf';
            file_put_contents($strCaminhoArquivoHtml, $strResultado);

            $strComandoGerarPdf = 'wkhtmltopdf --quiet --orientation \'landscape\' --title md_gd_pdf_listagem_eliminacao-' . InfraUtil::retirarFormatacao('1123123', false) . ' ' . $strCaminhoArquivoHtml . '  ' . $strCaminhoArquivoPdf . ' 2>&1';
            shell_exec($strComandoGerarPdf);
            SeiINT::download(null, null, $strCaminhoArquivoPdfRelativo, null,'attachment');
        } catch (Exception $e) {
            throw new InfraException('Erro ao gerar pdf.', $e);
        }
    }

    
    /**
     * Altera a situação da listagem de eliminação para em edição
     *
     * @param MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO
     * @return void
     */
    public function editarListaEliminacaoControlado(MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO){
        try{
            if(!$objMdGdListaEliminacaoDTO->isSetNumIdListaEliminacao()){
                throw new InfraException('Informe o id da lista de eliminação para deixar em modo de edição.');
            }

            $objMdGdListaEliminacaoDTO->retNumIdListaEliminacao();
            $objMdGdListaEliminacaoDTO->retStrSituacao();

            $objMdGdListaEliminacaoDTO = $this->consultar($objMdGdListaEliminacaoDTO);
            
            if($objMdGdListaEliminacaoDTO->getStrSituacao() != self::$ST_GERADA){
                throw new InfraException('A listagem precisa estar na situação gerada.'); 
            }

            $objMdGdListaEliminacaoDTO->setStrSituacao(self::$ST_EDICAO);
            return $this->alterar($objMdGdListaEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de eliminação para o modo de edição.', $e);
        }
    }

    /**
     * Conclui a edição da listagem de eliminação
     *
     * @param MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO
     * @return void
     */
    public function concluirEdicaoListaEliminacaoControlado(MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO){
        try{

            if(!$objMdGdListaEliminacaoDTO->isSetNumIdListaEliminacao()){
                throw new InfraException('Informe o id da lista de eliminação para concluir a edição da listagem.');
            }
            
            // Obtem os dados da lista de eliminação
            $objMdGdListaEliminacaoDTO->retNumIdListaEliminacao();
            $objMdGdListaEliminacaoDTO->retStrSituacao();
            $objMdGdListaEliminacaoDTO->retDblIdProcedimentoEliminacao();

            $objMdGdListaEliminacaoDTO = $this->consultar($objMdGdListaEliminacaoDTO);

            if($objMdGdListaEliminacaoDTO->getStrSituacao() != self::$ST_EDICAO){
                throw new InfraException('A listagem precisa estar em edição para que sua edição seja concluída.');
            }

            // Obtem os processos da listagem de eliminação
            $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
            $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($objMdGdListaEliminacaoDTO->getNumIdListaEliminacao());
            $objMdGdListaElimProcedimentoDTO->retDblIdProcedimento();

            $objMdGdListaElimProcedimentoRN = new MdGdListaElimProcedimentoRN();
            $arrObjMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoRN->listar($objMdGdListaElimProcedimentoDTO);

            // Verifica a existência de processos na lista de eliminação
            if(count($arrObjMdGdListaElimProcedimentoDTO) == 0) {
                $objInfraException = new InfraException();
                $objInfraException->lancarValidacao('Não é possível concluir a edição pois não há processos na listagem.');
            }
            
            $arrIdsProcedimentos = [];
            $arrObjMdGdArquivamentoDTO = [];

            foreach($arrObjMdGdListaElimProcedimentoDTO as $objMdGdListaElimProcedimentoDTO){
                $arrIdsProcedimentos[] = $objMdGdListaElimProcedimentoDTO->getDblIdProcedimento();
            }

            $strSinDocumentosFisicos = 'N';
            
            if($arrIdsProcedimentos){    
                // CONSULTA PARA VERIFICAÇÃO DA EXISTÊNCIA DE DOCUMENTOS FÍSICOS ARQUIVADOS
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
                $objMdGdArquivamentoDTO->retStrObservacaoEliminacao();
                $objMdGdArquivamentoDTO->retDthDataArquivamento();
                $objMdGdArquivamentoDTO->retDthDataGuardaCorrente();
                $objMdGdArquivamentoDTO->retDthDataGuardaIntermediaria();
                $objMdGdArquivamentoDTO->retStrProtocoloFormatado();

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

                $objMdGdListaEliminacaoDTO->setNumAnoLimiteInicio($numAnoLimiteInicial);
                $objMdGdListaEliminacaoDTO->setNumAnoLimiteFim($numAnoLimiteFinal);

            }
            
            // Gera um novo documento atualizado no processo da listagem de eliminação
            $objMdGdParametroRN = new MdGdParametroRN();
            $numIdTipoDocumentoArquivamento = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO);

            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento(null);
            $objDocumentoDTO->setDblIdProcedimento($objMdGdListaEliminacaoDTO->getDblIdProcedimentoEliminacao());

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

            if($arrObjMdGdArquivamentoDTO){
                $objDocumentoDTO->setStrConteudo($this->obterConteudoDocumentoEliminacao($arrObjMdGdArquivamentoDTO));
            }else{
                $objDocumentoDTO->setStrConteudo('');
            }

            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

            // Atualiza a situação da listagem de eliminação
            $objMdGdListaEliminacaoDTO->setDblIdDocumentoEliminacao($objDocumentoDTO->getDblIdDocumento());
            $objMdGdListaEliminacaoDTO->setStrSituacao(self::$ST_GERADA);
            $objMdGdListaEliminacaoDTO->setStrSinDocumentosFisicos($strSinDocumentosFisicos);
            return $this->alterar($objMdGdListaEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de eliminação para o modo de edição.', $e);
        }
    }

     /**
     * Atualiza o número de processos de uma listagem de eliminação
     *
     * @param MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO
     * @return boolean
     */
    public function atualizarNumeroProcessosControlado(MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO){
        try{
            if(!$objMdGdListaEliminacaoDTO->isSetNumIdListaEliminacao()){
                throw new InfraException('Informe o id da lista de eliminação atualizar o número de processos.');
            }

            // Obtem o quantidade de processos
            $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
            $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($objMdGdListaEliminacaoDTO->getNumIdListaEliminacao());

            $objMdGdListaElimProcedimentoRN = new MdGdListaElimProcedimentoRN();
            $numQuantidadeProcessos = $objMdGdListaElimProcedimentoRN->contar($objMdGdListaElimProcedimentoDTO);

            // Atualiza a quantidade de processos
            $objMdGdListaEliminacaoDTO->setNumQtdProcessos($numQuantidadeProcessos);
            return $this->alterar($objMdGdListaEliminacaoDTO);
        }catch(Exception $e){
            throw new InfraException('Erro ao altualizar o número de processos.', $e);
        }
    }


}

?>