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

            // Recupera os tipos de procedimento e documento que ser�o criados no arquivamento
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
            $objProtocoloDTO->setStrDescricao('Elimina��o de documentos');
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

            // REALIZA A INCLUS�O DO PROCESSO 
            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoDTO = $objProcedimentoRN->gerarRN0156($objProcedimentoDTO);
            //INCLUS�O DO DOCUMENTO
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

            // CONSULTA PARA VERIFICA��O DA EXIST�NCIA DE DOCUMENTOS F�SICOS ARQUIVADOS
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

            // Cria a listagem de elimina��o
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

            // Cria a rela��o da listagem de elimina��o com os procedimentos
            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {

                //Cria o v�lculo da lista com o procedimento
                $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
                $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($objMdGdListaEliminacaoDTO->getNumIdListaEliminacao());
                $objMdGdListaElimProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());

                $objMdGdListElimProcedimentoBD->cadastrar($objMdGdListaElimProcedimentoDTO);

                // Altera a situa��o do arquivamento do procedimento
                $objMdGdArquivamentoDTO->setNumIdListaEliminacao($objMdGdListaEliminacaoDTO->getNumIdListaEliminacao());
                $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_ENVIADO_ELIMINACAO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }

        } catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar a listagem de elimina��o.', $e);
        }
    }

    protected function alterarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            return $objMdGdListaEliminacaoBD->alterar($objMdGdListaEliminacao);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de elimina��o.', $e);
        }
    }

    protected function consultarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            return $objMdGdListaEliminacaoBD->consultar($objMdGdListaEliminacao);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de elimina��o.', $e);
        }
    }

    protected function listarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            return $objMdGdListaEliminacaoBD->listar($objMdGdListaEliminacao);
        } catch (Exception $e) {
            throw new InfraException('Erro listando as listagens de elimina��o.', $e);
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
        $strHtmlTabela .= '<th rowspan="2" >C�DIGO REFERENTE A CLASSIFICA��O</th>';
        $strHtmlTabela .= '<th rowspan="2" >DESCRITOR DO C�DIGO</th>';
        $strHtmlTabela .= '<th rowspan="2" >DATAS-LIMITE</th>';
        $strHtmlTabela .= '<th colspan="2" rowspan="1" >UNIDADE DE ARQUIVAMENTO</th>';
        $strHtmlTabela .= '<th rowspan="2" >OBSERVA��ES E/OU JUSTIFICATIVAS</th>';
        $strHtmlTabela .= '</tr><tr><th>Quantifica��o</th>';
        $strHtmlTabela .= '<th>Especifica��o</th>';

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

            //obtem o c�digo de maior temporalidade
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
            $strHtmlTabela .= '<td style="text-align: center;">' . $key . '</td>';
            $strHtmlTabela .= '<td style="text-align: center;">' . $assunto['descricao'] . '</td>';
            $strHtmlTabela .= '<td style="text-align: center;">' . $assunto['menorAno'] .'-'. $assunto['maiorAno'] .'</td>';
            $strHtmlTabela .= '<td style="text-align: center;">' . $assunto['quantidade'] .' Processos (' . number_format($unidadeTamanho['valor'],0,",",".") . ' ' . $unidadeTamanho['unidade'] .')</td>';
            $strHtmlTabela .= '<td style="text-align: center;">' . $unidadeTamanho['unidade'] .'</td>';
            $strHtmlTabela .= '<td style="text-align: center;">' . '</td>';
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
                throw new InfraException('Erro criando arquivo html tempor�rio para cria��o de pdf.');
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
        $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
        $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($numIdListagem);
 
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
        $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
        $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
        $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
        $objMdGdArquivamentoDTO->retStrObservacaoEliminacao();
        $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
        $objMdGdArquivamentoDTO->setStrSinAtivo('S');
        $objMdGdArquivamentoDTO->setDblIdProcedimento($arrIdsEliminacao, InfraDTO::$OPER_IN);

        $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);
        $numRegistros = count($arrObjMdGdArquivamentoDTO);

        if ($numRegistros > 0) {
            $strResultado = '';

            $strSumarioTabela = 'Lista de Processos';
            $strCaptionTabela = 'Processos';

            $strResultado .= '<table width="99%" class="infraTable" border="1">';
            $strResultado .= '<tr>';
            $strResultado .= '<th class="infraTh" width="13%">Descri��o Unidade Corrente</th>';
            $strResultado .= '<th class="infraTh" width="10%">C�digo de Classifica��o</th>';
            $strResultado .= '<th class="infraTh" width="20%">Descritor do C�digo</th>';
            $strResultado .= '<th class="infraTh" width="14%">N� do Processo</th>';
            $strResultado .= '<th class="infraTh" width="15%">Tipo de Processo</th>';
            $strResultado .= '<th class="infraTh" width="10%">Data de arquivamento</th>';
            $strResultado .= '<th class="infraTh" width="10%">Observa��es e/ou Justificativas</th>';
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
    }

    
    /**
     * Altera a situa��o da listagem de elimina��o para em edi��o
     *
     * @param MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO
     * @return void
     */
    public function editarListaEliminacaoControlado(MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO){
        try{
            if(!$objMdGdListaEliminacaoDTO->isSetNumIdListaEliminacao()){
                throw new InfraException('Informe o id da lista de elimina��o para deixar em modo de edi��o.');
            }

            $objMdGdListaEliminacaoDTO->retNumIdListaEliminacao();
            $objMdGdListaEliminacaoDTO->retStrSituacao();

            $objMdGdListaEliminacaoDTO = $this->consultar($objMdGdListaEliminacaoDTO);
            
            if($objMdGdListaEliminacaoDTO->getStrSituacao() != self::$ST_GERADA){
                throw new InfraException('A listagem precisa estar na situa��o gerada.'); 
            }

            $objMdGdListaEliminacaoDTO->setStrSituacao(self::$ST_EDICAO);
            return $this->alterar($objMdGdListaEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de elimina��o para o modo de edi��o.', $e);
        }
    }

    /**
     * Conclui a edi��o da listagem de elimina��o
     *
     * @param MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO
     * @return void
     */
    public function concluirEdicaoListaEliminacaoControlado(MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO){
        try{

            if(!$objMdGdListaEliminacaoDTO->isSetNumIdListaEliminacao()){
                throw new InfraException('Informe o id da lista de elimina��o para concluir a edi��o da listagem.');
            }
            
            // Obtem os dados da lista de elimina��o
            $objMdGdListaEliminacaoDTO->retNumIdListaEliminacao();
            $objMdGdListaEliminacaoDTO->retStrSituacao();
            $objMdGdListaEliminacaoDTO->retDblIdProcedimentoEliminacao();

            $objMdGdListaEliminacaoDTO = $this->consultar($objMdGdListaEliminacaoDTO);

            if($objMdGdListaEliminacaoDTO->getStrSituacao() != self::$ST_EDICAO){
                throw new InfraException('A listagem precisa estar em edi��o para que sua edi��o seja conclu�da.');
            }

            // Obtem os processos da listagem de elimina��o
            $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
            $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($objMdGdListaEliminacaoDTO->getNumIdListaEliminacao());
            $objMdGdListaElimProcedimentoDTO->retDblIdProcedimento();

            $objMdGdListaElimProcedimentoRN = new MdGdListaElimProcedimentoRN();
            $arrObjMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoRN->listar($objMdGdListaElimProcedimentoDTO);

            // Verifica a exist�ncia de processos na lista de elimina��o
            if(count($arrObjMdGdListaElimProcedimentoDTO) == 0) {
                $objInfraException = new InfraException();
                $objInfraException->lancarValidacao('N�o � poss�vel concluir a edi��o pois n�o h� processos na listagem.');
            }
            
            $arrIdsProcedimentos = [];
            $arrObjMdGdArquivamentoDTO = [];

            foreach($arrObjMdGdListaElimProcedimentoDTO as $objMdGdListaElimProcedimentoDTO){
                $arrIdsProcedimentos[] = $objMdGdListaElimProcedimentoDTO->getDblIdProcedimento();
            }

            $strSinDocumentosFisicos = 'N';
            
            if($arrIdsProcedimentos){    
                // CONSULTA PARA VERIFICA��O DA EXIST�NCIA DE DOCUMENTOS F�SICOS ARQUIVADOS
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
            
            // Gera um novo documento atualizado no processo da listagem de elimina��o
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

            // Atualiza a situa��o da listagem de elimina��o
            $objMdGdListaEliminacaoDTO->setDblIdDocumentoEliminacao($objDocumentoDTO->getDblIdDocumento());
            $objMdGdListaEliminacaoDTO->setStrSituacao(self::$ST_GERADA);
            $objMdGdListaEliminacaoDTO->setStrSinDocumentosFisicos($strSinDocumentosFisicos);
            return $this->alterar($objMdGdListaEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de elimina��o para o modo de edi��o.', $e);
        }
    }

     /**
     * Atualiza o n�mero de processos de uma listagem de elimina��o
     *
     * @param MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO
     * @return boolean
     */
    public function atualizarNumeroProcessosControlado(MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO){
        try{
            if(!$objMdGdListaEliminacaoDTO->isSetNumIdListaEliminacao()){
                throw new InfraException('Informe o id da lista de elimina��o atualizar o n�mero de processos.');
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
            throw new InfraException('Erro ao altualizar o n�mero de processos.', $e);
        }
    }


}

?>