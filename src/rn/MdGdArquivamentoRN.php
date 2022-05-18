<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdArquivamentoRN extends InfraRN {
    # Situações do arquivamento

    public static $ST_FASE_CORRENTE = 'CO';
    public static $ST_FASE_INTERMEDIARIA = 'IN';
    public static $ST_FASE_EDICAO = 'ED';
    public static $ST_DEVOLVIDO = 'DE';
    public static $ST_PREPARACAO_RECOLHIMENTO = 'PR';
    public static $ST_PREPARACAO_ELIMINACAO = 'PE';
    public static $ST_ENVIADO_RECOLHIMENTO = 'ER';
    public static $ST_ENVIADO_ELIMINACAO = 'EE';
    public static $ST_RECOLHIDO = 'RE';
    public static $ST_ELIMINADO = 'EL';
    public static $ST_DESARQUIVADO = 'DA';

    # Guarda
    public static $GUARDA_CORRENTE = 'C';
    public static $GUARDA_INTERMEDIARIA = 'I';

    # Destinação Final
    public static $DF_RECOLHIMENTO = 'G';
    public static $DF_ELIMINACAO = 'E';
    

    public $reabrir = false;

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    /**
     * Realiza o arquivamento de um procedimento
     * 
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return boolean
     * @throws InfraException
     */
    protected function arquivarControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {

        //Valida Permissao
        SessaoSEI::getInstance()->validarAuditarPermissao('gd_procedimento_arquivar', __METHOD__, $objMdGdArquivamentoDTO);

        // Validações para arquivamento
        $this->validarArquivamento($objMdGdArquivamentoDTO);

        try {
            // Reabre o processo caso esteja fechado TODO: REVER ESSA IMPLEMENTAÇÃO
            if ($objMdGdArquivamentoDTO->reabrirProcedimentoGeracao) {
                $objReabrirProcessoDTO = new ReabrirProcessoDTO();
                $objReabrirProcessoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
                $objReabrirProcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $objReabrirProcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());

                $objProcedimentoRN = new ProcedimentoRN();
                $objProcedimentoRN->reabrirRN0966($objReabrirProcessoDTO);
            }

            // Realiza o andamento do arquivamento
            $objAtividadeRN = new AtividadeRN();
            $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
            $objPesquisaPendenciaDTO->setDblIdProtocolo($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $objPesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $objPesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $arrObjProcedimentoDTO = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTO);

            $arrObjAtividadeDTO = array();
            foreach($arrObjProcedimentoDTO as $objProcedimentoDTO){
              $arrObjAtividadeDTO = array_merge($arrObjAtividadeDTO,$objProcedimentoDTO->getArrObjAtividadeDTO()); 
            }
            
            $arrStrIdAtividade = implode(',',InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdAtividade'));
            $arrIdProcedimento = [$objMdGdArquivamentoDTO->getDblIdProcedimento()];

            $objAtualizarAndamentoDTO = new AtualizarAndamentoDTO();
            $objAtualizarAndamentoDTO->setStrDescricao('Processo arquivado');
            $objAtualizarAndamentoDTO->setArrObjProtocoloDTO(InfraArray::gerarArrInfraDTO('ProtocoloDTO','IdProtocolo', $arrIdProcedimento));
            $objAtualizarAndamentoDTO->setArrObjAtividadeDTO(InfraArray::gerarArrInfraDTO('AtividadeDTO','IdAtividade',explode(',',$arrStrIdAtividade)));
            
            $objAtividadeRN->atualizarAndamento($objAtualizarAndamentoDTO);
            
            // Informa a data do arquivamento
            if($objMdGdArquivamentoDTO->isSetDthDataArquivamento()){
                $dtaDataArquivamentoBr = $objMdGdArquivamentoDTO->getDthDataArquivamento();
                $arrDtaDataArquivamento = explode('/', str_replace(['00:00:00', ' '], ['', ''], $objMdGdArquivamentoDTO->getDthDataArquivamento()));
                $dtaDataArquivamentoUs = $arrDtaDataArquivamento[2].'-'.$arrDtaDataArquivamento[1].'-'.$arrDtaDataArquivamento[0].' '.date('H:i:s');
                
            }else{
                $dtaDataArquivamentoBr = date('d/m/Y H:i:s');
                $dtaDataArquivamentoUs = date('Y-m-d H:i:s');   
            }
           
            $objMdGdArquivamentoDTO->setDthDataArquivamento($dtaDataArquivamentoBr);
            
            // Cria o despacho e anexa ao arquivamento
            $objDocumentoDTO = $this->gerarDespachoArquivamento($objMdGdArquivamentoDTO);
            $objMdGdArquivamentoDTO->setDblIdDespachoArquivamento($objDocumentoDTO->getDblIdDocumento());

            // Obtem os tempos de guarda corrente e intermediária e adiciona ao arquivamento
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $objRelProtocoloAssuntoDTO->retNumIdAssunto();
            $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();
            $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();

            $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
            $arrObjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);
            
            $maiorPrazoGuarda=$this->calcularMaiorPrazoGuarda($arrObjRelProtocoloAssuntoDTO);

            $guardaTotal = abs($maiorPrazoGuarda['soma']);
            $numTempoGuardaCorrente = $maiorPrazoGuarda['corrente'];
            $numTempoGuardaIntermediaria = $maiorPrazoGuarda['intermediario'];

            
            $dtaGuardaCorrente = date('d/m/Y H:i:s', strtotime("+{$numTempoGuardaCorrente} years", strtotime($dtaDataArquivamentoUs)));
            $dtaGuardaIntermediaria = date('d/m/Y H:i:s', strtotime("+{$guardaTotal} years", strtotime($dtaDataArquivamentoUs)));

            $objMdGdArquivamentoDTO->setDthDataGuardaCorrente($dtaGuardaCorrente);
            $objMdGdArquivamentoDTO->setDthDataGuardaIntermediaria($dtaGuardaIntermediaria);
            $objMdGdArquivamentoDTO->setNumGuardaCorrente($numTempoGuardaCorrente);
            $objMdGdArquivamentoDTO->setNumGuardaIntermediaria($numTempoGuardaIntermediaria);

            //Informa os demais parâmetros do arquivamento e realiza seu salvamento
            $objMdGdArquivamentoDTO->setStrSinAtivo('S');
            $objMdGdArquivamentoDTO->setNumIdUnidadeCorrente(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objMdGdArquivamentoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $objMdGdArquivamentoDTO->setStrStaDestinacaoFinal($this->obterDestinacaoFinalProtocolo($objMdGdArquivamentoDTO->getDblIdProcedimento()));

            if ($this->contarCondicionantes($objMdGdArquivamentoDTO->getDblIdProcedimento())) {
                $objMdGdArquivamentoDTO->setStrSinCondicionante('S');
            } else {
                $objMdGdArquivamentoDTO->setStrSinCondicionante('N');
            }

            $dtaGuardaCorrente = date('YmdHis', strtotime("+{$numTempoGuardaCorrente} years", strtotime($dtaDataArquivamentoUs)));
            $dtaGuardaIntermediaria = date('YmdHis', strtotime("+{$guardaTotal} years", strtotime($dtaDataArquivamentoUs)));

            if($dtaGuardaCorrente >= date('YmdHis')){
                $objMdGdArquivamentoDTO->setStrSituacao(self::$ST_FASE_CORRENTE);
                $objMdGdArquivamentoDTO->setStrStaGuarda(self::$GUARDA_CORRENTE);
            }else{
                $objMdGdArquivamentoDTO->setStrSituacao(self::$ST_FASE_INTERMEDIARIA);
                $objMdGdArquivamentoDTO->setStrStaGuarda(self::$GUARDA_INTERMEDIARIA);

                $objMdGdUnidadeArquivamentoDTO = new MdGdUnidadeArquivamentoDTO();
                $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $objMdGdUnidadeArquivamentoDTO->retNumIdUnidadeDestino();

                $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
                
                if($objMdGdUnidadeArquivamentoRN->contar($objMdGdUnidadeArquivamentoDTO) == 1){
                    $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoRN->consultar($objMdGdUnidadeArquivamentoDTO);
                    $objMdGdArquivamentoDTO->setNumIdUnidadeIntermediaria($objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeDestino());
                }
            }

            $objMdGdArquivamentoBD = new MdGdArquivamentoBD($this->inicializarObjInfraIBanco());
            $objMdGdArquivamentoBD->cadastrar($objMdGdArquivamentoDTO);

            // Concluí e bloqueia o processo
            $this->fecharProcedimentoArquivamento($objMdGdArquivamentoDTO);

            // Registra um histórico do arquivamento
            $this->registrarHistoricoArquivamento($objMdGdArquivamentoDTO->getNumIdArquivamento(), null, self::$ST_FASE_CORRENTE);
            if($objMdGdArquivamentoDTO->getStrSituacao() == self::$ST_FASE_INTERMEDIARIA){
                $strDataHistorico = date("d/m/Y H:i:s", (strtotime(date('Y-m-d H:i:s')) + 1));
                $this->registrarHistoricoArquivamento($objMdGdArquivamentoDTO->getNumIdArquivamento(), self::$ST_FASE_CORRENTE, self::$ST_FASE_INTERMEDIARIA, $strDataHistorico);
            }
            return true;
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar($e->getMessage(),InfraLog::$ERRO);
            throw new InfraException('Erro ao arquivar processo.', $e);
        }
    }

    public function calcularMaiorPrazoGuarda($arrObjRelProtocoloAssuntoDTO){

        $objAssuntoRN = new AssuntoRN();
        $somaPrazos=[];

        foreach ($arrObjRelProtocoloAssuntoDTO as $objRelProtocoloAssuntoDTO) {
            $objAssuntoDTO = new AssuntoDTO();
            $objAssuntoDTO->setNumIdAssunto($objRelProtocoloAssuntoDTO->getNumIdAssunto());
            $objAssuntoDTO->retNumPrazoCorrente();
            $objAssuntoDTO->retNumPrazoIntermediario();
            $objAssuntoDTO->retStrStaDestinacao();

            $objAssuntoDTO = $objAssuntoRN->consultarRN0256($objAssuntoDTO);

            $prazoCorrente=is_null($objAssuntoDTO->getNumPrazoCorrente())?0:$objAssuntoDTO->getNumPrazoCorrente();
            $prazoIntermediario=is_null($objAssuntoDTO->getNumPrazoIntermediario())?0:$objAssuntoDTO->getNumPrazoIntermediario();

            $somaPrazos[]= [
                "soma" => ($objAssuntoDTO->getStrStaDestinacao()==AssuntoRN::$TD_GUARDA_PERMANENTE?1:-1) * ($prazoCorrente+$prazoIntermediario),
                "corrente" => $prazoCorrente,
                "intermediario" => $prazoIntermediario,
                "assuntoId" => $objRelProtocoloAssuntoDTO->getNumIdAssunto(),
                "descricao" => $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto(),
                "codigo" => $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto(),

            ];
            
        }

        uasort($somaPrazos, function($a, $b){
            return $b['soma'] - $a['soma'];
        });

        # Os que tem destinação ELIMINAÇÃO tem soma negativa e portanto se o primeiro item após uasort 
        # é negativo todos são ELIMINAÇÃO e temos que devolver o menor valor negativo da soma.
        # caso o primeiro valor seja positivo há alguma GUARDA PERMANENTE e é essa GUARDA PERMANENTE
        # da maior soma que devemos devolver.
        if($somaPrazos[array_key_first($somaPrazos)]['soma'] > 0){
            return $somaPrazos[array_key_first($somaPrazos)];
        }else{
            return $somaPrazos[array_key_last($somaPrazos)];
        }

        

    }

    /**
     * Valida o arquivamento
     *
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @throws InfraException
     * @return void
     */
    protected function validarArquivamento(MdGdArquivamentoDTO $objMdGdArquivamentoDTO){
        // Validar parâmetros do dto
        if(!$objMdGdArquivamentoDTO->isSetDblIdProcedimento()){
            throw new InfraException('Processo não informado para arquivamento.');
        }

        // Valida a existência de um arquivamento ativo
        $objMdGdArquivamentoDTO2 = new MdGdArquivamentoDTO();
        $objMdGdArquivamentoDTO2->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
        $objMdGdArquivamentoDTO2->setStrSinAtivo('S');

        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        if($objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO2) != 0){
            throw new InfraException('Existe um arquivamento ativo para esse processo.');
        }

        // Validar configuração do módulo
        $objMdGdParametroRN = new MdGdParametroRN();
        if(!$objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_DESPACHO_ARQUIVAMENTO)){
            throw new InfraException('Não foi configurado o tipo de documento para arquivamento!');
        }

        // Validar unidade de arquivamento
        $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
        if(!$objMdGdUnidadeArquivamentoRN->getNumIdUnidadeArquivamentoAtual()){
            throw new InfraException('A unidade atual não possui unidade de arquivamento configurada');
        }
    }
    
    /**
     * Gera o despacho de arquivamento
     * 
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return DocumentoDTO
     */
    protected function gerarDespachoArquivamentoControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {
        //Obtem o tipo de documento e o o seu conteúdo
        $objMdGdParametroRN = new MdGdParametroRN();
        $numIdSerie = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_DESPACHO_ARQUIVAMENTO);
        $strConteudo = $this->obterConteudoDespachoArquivamento($objMdGdArquivamentoDTO->getNumIdJustificativa(), $objMdGdArquivamentoDTO->getDthDataArquivamento(), SessaoSEI::getInstance()->getStrNomeUsuario());

        // Gera o protocolo do despacho de arquivamento
        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->setDblIdProtocolo(null);
        $objProtocoloDTO->setStrStaProtocolo('G');
        $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
        $objProtocoloDTO->setStrDescricao('Despacho de Arquivamento');
        $objProtocoloDTO->setArrObjParticipanteDTO(array());
        $objProtocoloDTO->setArrObjObservacaoDTO(array());

        // Gera o documento de despacho de arquivamento
        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->setDblIdDocumento(null);
        $objDocumentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
        $objDocumentoDTO->setNumIdSerie($numIdSerie);
        $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);
        $objDocumentoDTO->setDblIdDocumentoEdoc(null);
        // $objDocumentoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        $objDocumentoDTO->setDblIdDocumentoEdocBase(null);
        $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objDocumentoDTO->setNumIdTipoConferencia(null);
        $objDocumentoDTO->setStrNumero('');
        $objDocumentoDTO->setStrConteudo($strConteudo);
        $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);

        $objDocumentoRN = new DocumentoRN();
        $objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

        // Assinatura do despacho de arquivamento
        $objAssinaturaDTO = $objMdGdArquivamentoDTO->getObjAssinaturaDTO();
        $objAssinaturaDTO->setArrObjDocumentoDTO([$objDocumentoDTO]);

        $objDocumentoRN = new DocumentoRN();
        $objDocumentoRN->assinar($objAssinaturaDTO);

        return $objDocumentoDTO;
    }

    /**
     * Obtem o conteúdo do despacho de arquivamento
     * 
     * @param integer $numIdJustificativa
     * @param string $dthArquivamento
     * @param string $strResponsavelArquivamento
     * @return string
     */
    protected function obterConteudoDespachoArquivamento($numIdJustificativa, $dthArquivamento, $strResponsavelArquivamento) {
        //TODO: Validação dos parametros obrigatórios do DTO
        // Busca o motivo
        $objMdGdJustificativaDTO = new MdGdJustificativaDTO();
        $objMdGdJustificativaDTO->setNumIdJustificativa($numIdJustificativa);
        $objMdGdJustificativaDTO->retStrNome();

        $objMdGdJustificativaRN = new MdGdJustificativaRN();
        $objMdGdJustificativaDTO = $objMdGdJustificativaRN->consultar($objMdGdJustificativaDTO);

        $arrVariaveisModelo = [
            '@motivo@' => $objMdGdJustificativaDTO->getStrNome(),
            '@data_arquivamento@' => $dthArquivamento,
            '@responsavel_arquivamento@' => $strResponsavelArquivamento
        ];

        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome(MdGdModeloDocumentoRN::MODELO_DESPACHO_ARQUIVAMENTO);
        $objMdGdModeloDocumentoDTO->retTodos();

        $objMdGdModeloDocumentoRN = new MdGdModeloDocumentoRN();
        $objMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoRN->consultar($objMdGdModeloDocumentoDTO);

        $str = $objMdGdModeloDocumentoDTO->getStrValor();
        $str = strtr($str, $arrVariaveisModelo);
        return $str;
    }

    /**
     * Realiza o desarquivamento de um procedimento
     *
     * @param MdGdDesarquivamentoDTO $objMdGdDesarquivamentoDTO
     * @return boolean
     * @throws InfraException
     */
    protected function desarquivarControlado(MdGdDesarquivamentoDTO $objMdGdDesarquivamentoDTO) {
        try {
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_procedimento_desarquivar', __METHOD__, $objMdGdDesarquivamentoDTO);

            // Valida desarquivamento
            $this->validarDesarquivamento($objMdGdDesarquivamentoDTO);
            
            // Inativa os registros de arquivamento
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
            $objMdGdArquivamentoDTO->setDblIdProcedimento($objMdGdDesarquivamentoDTO->getDblIdProcedimento());
            $objMdGdArquivamentoDTO->setStrSinAtivo('S');
            $objMdGdArquivamentoDTO->retNumIdArquivamento();
            $objMdGdArquivamentoDTO->retStrSituacao();

            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);
            $numIdArquivamento = null;

            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
                $this->registrarHistoricoArquivamento($objMdGdArquivamentoDTO->getNumIdArquivamento(), $objMdGdArquivamentoDTO->getStrSituacao(), self::$ST_DESARQUIVADO);

                $objMdGdArquivamentoDTO->setStrSinAtivo('N');
                $objMdGdArquivamentoDTO->setStrSituacao(self::$ST_DESARQUIVADO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
                $numIdArquivamento = $objMdGdArquivamentoDTO->getNumIdArquivamento();
            }

            // Reabre o processo
            $objReabrirProcessoDTO = new ReabrirProcessoDTO();
            $objReabrirProcessoDTO->setDblIdProcedimento($objMdGdDesarquivamentoDTO->getDblIdProcedimento());
            $objReabrirProcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objReabrirProcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());

            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoRN->reabrirRN0966($objReabrirProcessoDTO);

            // Desbloqueia o processo
            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProcedimentoDTO->setDblIdProcedimento($objMdGdDesarquivamentoDTO->getDblIdProcedimento());
            $objProcedimentoDTO->retStrStaEstadoProtocolo();
            $objProcedimentoDTO->retDblIdProcedimento();

            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);

            if ($objProcedimentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO) {
                $objProcedimentoRN->desbloquear([$objProcedimentoDTO]);
            }

            // Realiza o andamento do processo
            $objAtividadeRN = new AtividadeRN();
            $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
            $objPesquisaPendenciaDTO->setDblIdProtocolo($objMdGdDesarquivamentoDTO->getDblIdProcedimento());
            $objPesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $objPesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $arrObjProcedimentoDTO = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTO);

            $arrObjAtividadeDTO = array();
            foreach($arrObjProcedimentoDTO as $objProcedimentoDTO){
                $arrObjAtividadeDTO = array_merge($arrObjAtividadeDTO,$objProcedimentoDTO->getArrObjAtividadeDTO()); 
            }
            
            $arrStrIdAtividade = implode(',',InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdAtividade'));
            $arrIdProcedimento = [$objMdGdDesarquivamentoDTO->getDblIdProcedimento()];

            $objAtualizarAndamentoDTO = new AtualizarAndamentoDTO();
            $objAtualizarAndamentoDTO->setStrDescricao('Processo desarquivado');
            $objAtualizarAndamentoDTO->setArrObjProtocoloDTO(InfraArray::gerarArrInfraDTO('ProtocoloDTO','IdProtocolo', $arrIdProcedimento));
            $objAtualizarAndamentoDTO->setArrObjAtividadeDTO(InfraArray::gerarArrInfraDTO('AtividadeDTO','IdAtividade',explode(',',$arrStrIdAtividade)));
            
            $objAtividadeRN->atualizarAndamento($objAtualizarAndamentoDTO);
            
            // Gera o despacho de arquivamento        
            $objDocumentoDTO = $this->gerarDesapchoDesarquivamento($objMdGdDesarquivamentoDTO);
          
            // Cria o registro de desarquivamento
            $objMdGdDesarquivamentoDTO->setDthDataDesarquivamento(date('d/m/Y H:i:s'));
            $objMdGdDesarquivamentoDTO->setDblIdDespachoDesarquivamento($objDocumentoDTO->getDblIdDocumento());
            $objMdGdDesarquivamentoDTO->setNumIdArquivamento($numIdArquivamento);

            $objMdGdDesarquivamentoBD = new MdGdDesarquivamentoBD($this->inicializarObjInfraIBanco());
            $objMdGdDesarquivamentoBD->cadastrar($objMdGdDesarquivamentoDTO);

            return true;
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar($e->getMessage(),InfraLog::$ERRO);
            throw new InfraException('Erro ao desarquivar processo.', $e);
        }
    }

    /**
     * Validar desarquivamento
     *
     * @param MdGdDesarquivamentoDTO $objMdGdDesarquivamentoDTO
     * @throws InfraException
     * @return void
     */
    protected function validarDesarquivamento(MdGdDesarquivamentoDTO $objMdGdDesarquivamentoDTO){
        // Validar parâmetros do dto
        if(!$objMdGdDesarquivamentoDTO->isSetDblIdProcedimento()){
            throw new InfraException('Processo não informado para desarquivamento.');
        }

        // Valida a existência de um arquivamento ativo
        $objMdGdArquivamentoDTO2 = new MdGdArquivamentoDTO();
        $objMdGdArquivamentoDTO2->setDblIdProcedimento($objMdGdDesarquivamentoDTO->getDblIdProcedimento());
        $objMdGdArquivamentoDTO2->setStrSinAtivo('S');

        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        if($objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO2) == 0){
            throw new InfraException('Não existe um arquivamento ativo para esse processo.');
        }

        // Validar configuração do módulo
        $objMdGdParametroRN = new MdGdParametroRN();
        if(!$objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_DESPACHO_DESARQUIVAMENTO)){
            throw new InfraException('Não foi configurado o tipo de documento para desarquivamento!');
        }
    }

    /**
     * Gera um despacho de arquivamento
     *
     * @param MdGdDesarquivamentoDTO $objMdGdDesarquivamentoDTO
     * @throws InfraException
     * @return void
     */
    public function gerarDesapchoDesarquivamento(MdGdDesarquivamentoDTO $objMdGdDesarquivamentoDTO){
        //Instancia as RN's necessárias
        $objMdGdParametroRN = new MdGdParametroRN();
        $objDocumentoRN = new DocumentoRN();

        // Cria os valores padrões para o arquivamento
        $dtaDesarquivamento = date('d/m/Y H:i:s');
        $numIdSerie = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_DESPACHO_DESARQUIVAMENTO);
        $strConteudo = $this->obterConteudoDespachoDesarquivamento($objMdGdDesarquivamentoDTO->getNumIdJustificativa(), $dtaDesarquivamento, SessaoSEI::getInstance()->getStrNomeUsuario());

        // Cria o despacho de arquivamento
        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->setDblIdProtocolo(null);
        $objProtocoloDTO->setStrStaProtocolo('G');
        $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
        $objProtocoloDTO->setStrDescricao('Despacho de Desarquivamento');
        $objProtocoloDTO->setArrObjParticipanteDTO(array());
        $objProtocoloDTO->setArrObjObservacaoDTO(array());

        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->setDblIdDocumento(null);
        $objDocumentoDTO->setDblIdProcedimento($objMdGdDesarquivamentoDTO->getDblIdProcedimento());
        $objDocumentoDTO->setNumIdSerie($numIdSerie);
        $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);
        $objDocumentoDTO->setDblIdDocumentoEdoc(null);
        $objDocumentoDTO->setDblIdDocumentoEdocBase(null);
        $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objDocumentoDTO->setNumIdTipoConferencia(null);
        $objDocumentoDTO->setStrNumero('');
        $objDocumentoDTO->setStrConteudo($strConteudo);
        $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);

        $objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

        // Assinatura do despacho de desarquivamento
        $objAssinaturaDTO = $objMdGdDesarquivamentoDTO->getObjAssinaturaDTO();
        $objAssinaturaDTO->setArrObjDocumentoDTO([$objDocumentoDTO]);

        $objDocumentoRN = new DocumentoRN();
        $objDocumentoRN->assinar($objAssinaturaDTO);

        return $objDocumentoDTO;
    }
    
    /**
     * Obtem o conteúdo do despacho de desarquivamento
     *
     * @param integer $numIdJustificativa
     * @param string $dthArquivamento
     * @param string $strResponsavelArquivamento
     * @return string
     */
    private function obterConteudoDespachoDesarquivamento($numIdJustificativa, $dthArquivamento, $strResponsavelArquivamento) {
        // Busca o motivo
        $objMdGdJustificativaDTO = new MdGdJustificativaDTO();
        $objMdGdJustificativaDTO->setNumIdJustificativa($numIdJustificativa);
        $objMdGdJustificativaDTO->retStrNome();

        $objMdGdJustificativaRN = new MdGdJustificativaRN();
        $objMdGdJustificativaDTO = $objMdGdJustificativaRN->consultar($objMdGdJustificativaDTO);

        $arrVariaveisModelo = [
            '@motivo@' => $objMdGdJustificativaDTO->getStrNome(),
            '@data_desarquivamento@' => $dthArquivamento,
            '@responsavel_desarquivamento@' => $strResponsavelArquivamento
        ];

        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome(MdGdModeloDocumentoRN::MODELO_DESPACHO_DESARQUIVAMENTO);
        $objMdGdModeloDocumentoDTO->retTodos();

        $objMdGdModeloDocumentoRN = new MdGdModeloDocumentoRN();
        $objMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoRN->consultar($objMdGdModeloDocumentoDTO);

        $str = $objMdGdModeloDocumentoDTO->getStrValor();
        $str = strtr($str, $arrVariaveisModelo);
        return $str;
    }


    /**
     * Concluí e bloqueia o processo arquivado
     * 
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     */
    protected function fecharProcedimentoArquivamentoControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {
        //TODO: Validação dos parametros obrigatórios do DTO
        // Busca o procedimento 
        $objProcedimentoDTO = new ProcedimentoDTO();
        $objProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
        $objProcedimentoDTO->retStrStaEstadoProtocolo();
        $objProcedimentoDTO->retDblIdProcedimento();

        $objProcedimentoRN = new ProcedimentoRN();
        $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);

        // Bloqueia o processo
        if ($objProcedimentoDTO->getStrStaEstadoProtocolo() != ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO) {
            
            $objProcedimentoRN->bloquear([$objProcedimentoDTO]);
        }
        
        // Conclui o processo
        $objProcedimentoRN->concluir([$objProcedimentoDTO]);
    }

    /**
     * Método padrão de alteração
     * 
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return null
     * @throws InfraException
     */
    protected function alterarConectado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {
        try {
            $objMdGdArquivamentoBD = new MdGdArquivamentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdArquivamentoBD->alterar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao atualizar arquivamento de processo', $e);
        }
    }

    /**
     * Método padrão de consulta
     * 
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return MdGdArquivamentoDTO
     * @throws InfraException
     */
    protected function consultarConectado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {
        try {

            $objMdGdArquivamentoBD = new MdGdArquivamentoBD($this->inicializarObjInfraIBanco());
            $objMdGdArquivamentoDTO->setOrdDthDataArquivamento(InfraDTO::$TIPO_ORDENACAO_DESC);
            $ret = $objMdGdArquivamentoBD->listar($objMdGdArquivamentoDTO);

            $r = null;
            if(count($ret)){
              $r = $ret[0];
            }

            return $r;

            // todo: verificar se a implementacao acima esta correta, me parece um erro de modelagem
            // q impede usar o metodo consultar
            // troquei do consultar pelo listar pois em alguns momentos do teste funcional tava dando erro
            //return $objMdGdArquivamentoBD->consultar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao consultar arquivamento.', $e);
        }
    }

    /**
     * Método padrão de listagem
     * 
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return array
     * @throws InfraException
     */
    protected function listarConectado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {
        try {

            $objMdGdArquivamentoBD = new MdGdArquivamentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdArquivamentoBD->listar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao listar arquivamentos.', $e);
        }
    }

    /**
     * Método padrão de contagem
     * 
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return integer
     * @throws InfraException
     */
    protected function contarConectado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {
        try {

            $objMdGdArquivamentoBD = new MdGdArquivamentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdArquivamentoBD->contar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao consultar arquivamento.', $e);
        }
    }

    /**
     * Interface para chamada do método de contagem de condicionantes de um processo
     * 
     * @param integer $numIdProtocolo
     * @return integer
     */
    public function contarCondicionantes($numIdProtocolo) {
        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->setDblIdProtocolo($numIdProtocolo);
        return $this->contarCondicionante($objProtocoloDTO);
    }

    /**
     * Conta as condicionantes do processo
     * 
     * @param ProtocoloDTO $objProtocoloDTO
     * @return int
     * @throws InfraException
     */
    protected function contarCondicionanteConectado(ProtocoloDTO $objProtocoloDTO) {
        try {
            $objAssuntoRN = new AssuntoRN();

            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
            $objRelProtocoloAssuntoDTO->retNumIdAssunto();

            $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
            $arrObjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);
            $numTotalCondicionantes = 0;

            foreach ($arrObjRelProtocoloAssuntoDTO as $objRelProtocoloAssuntoDTO) {
                $objAssuntoDTO = new AssuntoDTO();
                $objAssuntoDTO->setNumIdAssunto($objRelProtocoloAssuntoDTO->getNumIdAssunto());
                $objAssuntoDTO->retStrObservacao();

                $objAssuntoDTO = $objAssuntoRN->consultarRN0256($objAssuntoDTO);

                if (!empty($objAssuntoDTO->getStrObservacao())) {
                    $numTotalCondicionantes++;
                }
            }

            return $numTotalCondicionantes;
        } catch (Exception $e) {
            throw new InfraException('Erro ao contar condicionantes.', $e);
        }
    }

    /**
     * Interface para chamada do método que obtem a destinação final de um protocolo
     * 
     * @param integer $numIdProtocolo
     * @return string
     */
    public function obterDestinacaoFinalProtocolo($numIdProtocolo) {
        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->setDblIdProtocolo($numIdProtocolo);

        return $this->obterDestinacaoFinal($objProtocoloDTO);
    }

    /**
     * Obtem a destianção final de um protocolo
     * 
     * @param ProtocoloDTO $objProtocoloDTO
     * @return string
     * @throws InfraException
     */
    protected function obterDestinacaoFinalConectado(ProtocoloDTO $objProtocoloDTO) {
        try {
            $objAssuntoRN = new AssuntoRN();

            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
            $objRelProtocoloAssuntoDTO->retNumIdAssunto();

            $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
            $arrObjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);
            $numTotalCondicionantes = 0;
            $strDestinacaoFinal = null;

            foreach ($arrObjRelProtocoloAssuntoDTO as $objRelProtocoloAssuntoDTO) {
                $objAssuntoDTO = new AssuntoDTO();
                $objAssuntoDTO->setNumIdAssunto($objRelProtocoloAssuntoDTO->getNumIdAssunto());
                $objAssuntoDTO->retStrStaDestinacao();

                $objAssuntoDTO = $objAssuntoRN->consultarRN0256($objAssuntoDTO);

                if($strDestinacaoFinal !== AssuntoRN::$TD_GUARDA_PERMANENTE){
                    $strDestinacaoFinal = $objAssuntoDTO->getStrStaDestinacao() == AssuntoRN::$TD_GUARDA_PERMANENTE ? self::$DF_RECOLHIMENTO : self::$DF_ELIMINACAO;
                }
            }
            
       

            return $strDestinacaoFinal;
        } catch (Exception $e) {
            throw new InfraException('Erro ao contar condicionantes.', $e);
        }
    }

    /**
     * Obtem os procedimentos pendentes de arquivamento na unidade atual
     *
     * @return array
     */
    protected function obterProcedimentosPendentesConectado(MdGdPesquisarPendenciasArquivamentoDTO $objMdGdPesquisarPendenciasArquivamentoDTO){

        // Varável que receberá o dto hidratado
        $objProcedimentoDTO = null;

        // Obtem os processos em que o último andamento tenha sido de conclusão na unidade 
        $arrTarefas = [
            TarefaRN::$TI_CONCLUSAO_PROCESSO_UNIDADE
        ];

        $condicao = '';

        // Aplicação dos filtros de período
        if($objMdGdPesquisarPendenciasArquivamentoDTO->isSetDthPeriodoInicial()){ 
            $strDthPeriodoInicial = InfraData::formatarDataBanco($objMdGdPesquisarPendenciasArquivamentoDTO->getDthPeriodoInicial());
            $condicao .= " AND atv.dth_abertura >= '".$strDthPeriodoInicial."' ";     
        }

        if($objMdGdPesquisarPendenciasArquivamentoDTO->isSetDthPeriodoFinal()){
            $strDthPeriodoFinal = InfraData::formatarDataBanco($objMdGdPesquisarPendenciasArquivamentoDTO->getDthPeriodoFinal());
            $strDthPeriodoFinal = str_replace(' 00:00:00', ' 23:59:59', $strDthPeriodoFinal);
            $condicao .= " AND atv.dth_abertura <= '".$strDthPeriodoFinal."' ";     
        }

      
        $sql = '  SELECT 
                max(atv.id_atividade) as id_atividade 
            FROM
                atividade atv
            INNER JOIN 
                procedimento pr
                ON pr.id_procedimento  = atv.id_protocolo 
            WHERE
                    1 = 1 
                    '.$condicao.'
            GROUP BY 
                atv.id_protocolo';

        $sql = str_replace(' 00:00:00', '', $sql);
        $arrAtividadesIds = $this->getObjInfraIBanco()->consultarSql($sql);

        if(count($arrAtividadesIds) == 0){
            return [[], []];
        }

        $ids = [];
        foreach($arrAtividadesIds as $atividadeId){
            $ids[] = $atividadeId['id_atividade'];
        }

        $sql = '
                SELECT  
                    atv.id_protocolo, atv.dth_abertura, usr.sigla, usr.nome
                FROM 
                    atividade atv
                INNER JOIN
                    usuario usr on usr.id_usuario = atv.id_usuario_conclusao
                WHERE 
                    atv.id_atividade IN ('.implode(',', $ids).')
                AND 
                    atv.id_tarefa IN ('.implode(',', $arrTarefas).')
                AND 
                    atv.id_unidade = '.SessaoSEI::getInstance()->getNumIdUnidadeAtual().' 
                AND
                    (SELECT count(atv2.id_atividade) FROM atividade atv2 WHERE atv2.dth_conclusao IS NULL AND atv2.id_protocolo = atv.id_protocolo) = 0
                    ';

        $arrProcedimentos = $this->getObjInfraIBanco()->consultarSql($sql);        
        $arrIdProcedimentos = [];
        $arrIdProcedimentoDth = [];
        $arrSiglaUsuarios = [];
        $arrNomeUsuarios = [];

        foreach($arrProcedimentos as $idProcedimento){
            $arrIdProcedimentos[] = $idProcedimento['id_protocolo'];

            if (is_object($idProcedimento['dth_abertura'])){
                $idProcedimento['dth_abertura'] = explode(' ', $idProcedimento['dth_abertura']->date);
            }else{
                $idProcedimento['dth_abertura'] = explode(' ', $idProcedimento['dth_abertura']);
            }
            
            $idProcedimento['dth_abertura'] = explode('-', $idProcedimento['dth_abertura'][0]);

            if(count($idProcedimento['dth_abertura']) > 1){
                $arrIdProcedimentoDth[$idProcedimento['id_protocolo']] = $idProcedimento['dth_abertura'][2].'/'.$idProcedimento['dth_abertura'][1].'/'.$idProcedimento['dth_abertura'][0];
            }else{
                $arrIdProcedimentoDth[$idProcedimento['id_protocolo']] = $idProcedimento['dth_abertura'][0];
            }

            $arrSiglaUsuarios[$idProcedimento['id_protocolo']] = $idProcedimento['sigla'];

            $arrNomeUsuarios[$idProcedimento['id_protocolo']] = $idProcedimento['nome'];
        }
        
        if($arrIdProcedimentos){  
   
            // Retira os procedimentos fechados  
            $objAtividadeDTO = new AtividadeDTO();
            $objAtividadeDTO->setDistinct(true);
            $objAtividadeDTO->retDblIdProtocolo();
            $objAtividadeDTO->setDthConclusao(null);
            $objAtividadeDTO->setDblIdProtocolo($arrIdProcedimentos,InfraDTO::$OPER_IN);

            $objAtividadeRN = new AtividadeRN();
            $arrProcedimentoAbertos = InfraArray::converterArrInfraDTO($objAtividadeRN->listarRN0036($objAtividadeDTO), 'IdProtocolo');
        

            $arrProcedimentoAbertos = array_keys($arrProcedimentoAbertos);
            $arrIdProcedimentos = array_diff($arrIdProcedimentos, $arrProcedimentoAbertos);

            if($arrIdProcedimentos){
                // Retira os procedimentos arquivados
                $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
                $objMdGdArquivamentoDTO->setDblIdProcedimento($arrIdProcedimentos, InfraDTO::$OPER_IN);
                $objMdGdArquivamentoDTO->setStrSinAtivo('S');
                $objMdGdArquivamentoDTO->retDblIdProcedimento();
                
                $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
                $arrProcedimentoArquivados = InfraArray::converterArrInfraDTO($objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO), 'IdProcedimento');            
                $arrIdProcedimentos = array_diff($arrIdProcedimentos, $arrProcedimentoArquivados);
                
                if($arrIdProcedimentos){
                    
                    // Faz o filtro por assunto
                    if($objMdGdPesquisarPendenciasArquivamentoDTO->isSetNumIdProtocoloAssunto()){
                        $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
                        $objRelProtocoloAssuntoDTO->setDblIdProtocolo($arrIdProcedimentos, InfraDTO::$OPER_IN);
                        $objRelProtocoloAssuntoDTO->setNumIdAssunto($objMdGdPesquisarPendenciasArquivamentoDTO->getNumIdProtocoloAssunto());
                        $objRelProtocoloAssuntoDTO->retDblIdProtocolo();
                        
                        $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
                        $arrIdProcedimentos = InfraArray::converterArrInfraDTO($objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO),'IdProtocolo');
                    }

                    // Instância o dto de pesquisa
                    if($arrIdProcedimentos){
                        $objProcedimentoDTO = new ProcedimentoDTO();
                        $objProcedimentoDTO->setDblIdProcedimento($arrIdProcedimentos, InfraDTO::$OPER_IN);
                        $objProcedimentoDTO->retDblIdProcedimento();
                        $objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
                        $objProcedimentoDTO->retStrNomeTipoProcedimento();
                        $objProcedimentoDTO->retObjAnotacaoDTO();
                        $objProcedimentoDTO->retStrStaNivelAcessoGlobalProtocolo();
                        $objProcedimentoDTO->retStrDescricaoProtocolo();
                        $objProcedimentoDTO->setOrdStrDescricaoProtocolo(InfraDTO::$TIPO_ORDENACAO_ASC);

                        if ($objMdGdPesquisarPendenciasArquivamentoDTO->isSetNumIdTipoProcedimento()) {
                            $objProcedimentoDTO->setNumIdTipoProcedimento($objMdGdPesquisarPendenciasArquivamentoDTO->getNumIdTipoProcedimento());
                        }
                    }
                }
            }
        }

        return [$objProcedimentoDTO, $arrIdProcedimentoDth, $arrSiglaUsuarios, $arrNomeUsuarios];

    }
    #############MÉTODOS DE TRAMITAÇÃO DO ARQUIVAMENTO DO PROCESSO################

    // CODE
    public function envairFaseIntermediariaControlado(){
        
    }

    /**
     * Retira um processo arquivado de uma listagem de eliminação
     * 
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return type
     * @throws InfraException
     */
    protected function retirarListaArquivamentoControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {
        try {

            if (!$objMdGdArquivamentoDTO->isSetNumIdArquivamento()) {
                throw new InfraException('Informe o número do arquivamento');
            }
            $objMdGdArquivamentoDTO->setStrObservacaoEliminacao(null);
            $objMdGdArquivamentoDTO->setStrSituacao(self::$ST_FASE_INTERMEDIARIA);
            return $this->alterar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao contar condicionantes.', $e);
        }
    }

    /**
     * Envia um processo para a preparação de listagem de eliminação
     * 
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return boolean
     * @throws InfraException
     */
    protected function enviarEliminacaoControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {
        try {

            // Valida a permissão
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_arquivamento_eliminacao_enviar', __METHOD__, $objMdGdArquivamentoDTO);

            // Valida se o id do arquivamento foi informado
            if (!$objMdGdArquivamentoDTO->isSetNumIdArquivamento()) {
                throw new InfraException('Informe o número do arquivamento');
            }
            
            $objMdGdArquivamentoDTO->retNumIdArquivamento();
            $objMdGdArquivamentoDTO->retStrSituacao();
            $objMdGdArquivamentoDTO->retStrStaDestinacaoFinal();
            $objMdGdArquivamentoDTO = $this->consultar($objMdGdArquivamentoDTO);

            // Valida se o arquivamento encontra-se na situação permitida para mudança
            if ($objMdGdArquivamentoDTO->getStrSituacao() != self::$ST_FASE_INTERMEDIARIA) {
                throw new InfraException('O processo precisa estar em fase intermediária para ser preparado para eliminação');
            }

            // Valida se a destinação final é de eliminação
            if ($objMdGdArquivamentoDTO->getStrStaDestinacaoFinal() != self::$DF_ELIMINACAO) {
                throw new InfraException('A destinação final do processo deve ser de eliminação');
            }

            $objMdGdArquivamentoDTO->setStrObservacaoEliminacao(null);
            $objMdGdArquivamentoDTO->setStrSituacao(self::$ST_PREPARACAO_ELIMINACAO);
            return $this->alterar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao contar condicionantes.', $e);
        }
    }

    /**
     * Envia um processo para a preparação de listagem de recolhimento
     * 
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return boolean
     * @throws InfraException
     */
    protected function enviarRecolhimentoControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {
        try {

            // Valida a permissão
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_arquivamento_recolhimento_enviar', __METHOD__, $objMdGdArquivamentoDTO);
            
            // Valida se o id do arquivamento foi informado
            if (!$objMdGdArquivamentoDTO->isSetNumIdArquivamento()) {
                throw new InfraException('Informe o número do arquivamento');
            }
            
            $objMdGdArquivamentoDTO->retNumIdArquivamento();
            $objMdGdArquivamentoDTO->retStrSituacao();
            $objMdGdArquivamentoDTO->retStrStaDestinacaoFinal();
            $objMdGdArquivamentoDTO = $this->consultar($objMdGdArquivamentoDTO);

            // Valida se o arquivamento encontra-se na situação permitida para mudança
            if ($objMdGdArquivamentoDTO->getStrSituacao() != self::$ST_FASE_INTERMEDIARIA) {
                throw new InfraException('O processo precisa estar em fase intermediária para ser preparado para o recolhimeno.');
            }

            // Valida se a destinação final é de recolhimento
            if ($objMdGdArquivamentoDTO->getStrStaDestinacaoFinal() != self::$DF_RECOLHIMENTO) {
                throw new InfraException('A destinação final do processo deve ser de recolhimento');
            }

            $objMdGdArquivamentoDTO->setStrObservacaoEliminacao(null);
            $objMdGdArquivamentoDTO->setStrSituacao(self::$ST_PREPARACAO_RECOLHIMENTO);
            return $this->alterar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao contar condicionantes.', $e);
        }
    }

    /**
     * Devolve o arquivamento para a unidade corrente para correção
     *
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return boolean
     * @throws InfraException
     */
    protected function devolverArquivamentoControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO){
        try {

            // Valida a permissão
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_arquivamento_devolver', __METHOD__, $objMdGdArquivamentoDTO);

            // Valida se o id do arquivamento foi informado
            if (!$objMdGdArquivamentoDTO->isSetNumIdArquivamento()) {
                throw new InfraException('Informe o número do arquivamento');
            }
            
            $strObservacao = $objMdGdArquivamentoDTO->getStrObservacaoDevolucao();
            $numIdArquivamento = $objMdGdArquivamentoDTO->getNumIdArquivamento();

            // Obtem o objeto de arquivamento
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
            $objMdGdArquivamentoDTO->setNumIdArquivamento($numIdArquivamento);
            $objMdGdArquivamentoDTO->retNumIdArquivamento();
            $objMdGdArquivamentoDTO->retDblIdProcedimento();
            $objMdGdArquivamentoDTO->retStrSituacao();

            $objMdGdArquivamentoDTO = $this->consultar($objMdGdArquivamentoDTO);
            
            // Valida se o arquivamento está em fase intermediária para ser editado
            if($objMdGdArquivamentoDTO->getStrSituacao() != self::$ST_FASE_INTERMEDIARIA){
                throw new InfraException('A devolução do processo só pode ser feita quando o arquivamento estiver em fase intermediária e em avaliação.');
            }
            
            // Atualiza a situação do arquivamento
            $objMdGdArquivamentoDTO->setStrSituacao(self::$ST_DEVOLVIDO);
            $objMdGdArquivamentoDTO->setStrObservacaoDevolucao($strObservacao);
            return $this->alterar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao editar arquivamento.', $e);
        }
    }

    /**
     * Altera a situação do arquivamento para em edição
     *
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return boolean
     * @throws InfraException
     */
    protected function editarArquivamentoControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO){
        try {

            // Valida a permissão
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_arquivamento_editar', __METHOD__, $objMdGdArquivamentoDTO);

            // Valida se o id do arquivamento foi informado
            if (!$objMdGdArquivamentoDTO->isSetNumIdArquivamento()) {
                throw new InfraException('Informe o número do arquivamento');
            }

            // Obtem o objeto de arquivamento
            $objMdGdArquivamentoDTO->retDblIdProcedimento();
            $objMdGdArquivamentoDTO->retNumIdArquivamento();
            $objMdGdArquivamentoDTO->retDblIdProcedimento();
            $objMdGdArquivamentoDTO->retStrSituacao();
            $objMdGdArquivamentoDTO = $this->consultarConectado($objMdGdArquivamentoDTO);
            
            // Valida se o arquivamento está em fase intermediária para ser editado
            if($objMdGdArquivamentoDTO->getStrSituacao() != self::$ST_DEVOLVIDO){
                throw new InfraException('Alteração do processo só pode ser feita quando o arquivamento estiver em fase intermediária e em avaliação.');
            }

            // Reabre o procedimento
            $objReabrirProcedimentoDTO = new ReabrirProcessoDTO();
            $objReabrirProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $objReabrirProcedimentoDTO->setNumIdUnidade($objMdGdArquivamentoDTO->getNumIdUnidadeCorrente());
            $objReabrirProcedimentoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());

            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoRN->reabrirRN0966($objReabrirProcedimentoDTO);

            // Desbloqueia o processo
            // $objProcedimentoDTO = new ProcedimentoDTO();
            // $objProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
            // $objProcedimentoDTO->retStrStaEstadoProtocolo();
            // $objProcedimentoDTO->retDblIdProcedimento();

            // $objProcedimentoRN = new ProcedimentoRN();
            // $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);
            // $objProcedimentoRN->desbloquear([$objProcedimentoDTO]);
            
            // Atualiza a situação do arquivamento
            $objMdGdArquivamentoDTO->setStrSituacao(self::$ST_FASE_EDICAO);
            return $this->alterar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao editar arquivamento.', $e);
        }
    }

    /**
     * Concluí a edição do arquivamento
     *
     * @param MdGdArquivamentoDTO $objMdGdArquivamentoDTO
     * @return boolean
     */
    protected function concluirEdicaoArquivamentoControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO){
        try{

            // Valida a permissão
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_arquivamento_edicao_concluir', __METHOD__, $objMdGdArquivamentoDTO);

            if (!$objMdGdArquivamentoDTO->isSetNumIdArquivamento()) {
                throw new InfraException('Informe o número do arquivamento');
            }

            // Obtem o objeto de arquivamento
            $objMdGdArquivamentoDTO->retDblIdProcedimento();
            $objMdGdArquivamentoDTO->retStrSituacao();
            $objMdGdArquivamentoDTO->retNumIdArquivamento();
            $objMdGdArquivamentoDTO->retDthDataArquivamento();
            $objMdGdArquivamentoDTO = $this->consultarConectado($objMdGdArquivamentoDTO);

            // Valida se o arquivamento está em fase intermediária para ser editado
            if($objMdGdArquivamentoDTO->getStrSituacao() != self::$ST_FASE_EDICAO){
                throw new InfraException('O arquivamento precisa estar em edição.');
            }

            $dtaDataArquivamentoBr = explode(' ', $objMdGdArquivamentoDTO->getDthDataArquivamento());
            $dtaDataArquivamentoBr = explode('/', $dtaDataArquivamentoBr[0]);
            $dtaDataArquivamentoUs = $dtaDataArquivamentoBr[2] . '-' .  $dtaDataArquivamentoBr[1] . '-' . $dtaDataArquivamentoBr[0];
            
            // Obtem os tempos de guarda corrente e intermediária e adiciona ao arquivamento
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $objRelProtocoloAssuntoDTO->retNumIdAssunto();
            $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();
            $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();

            $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
            $arrObjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);

            $maiorPrazoGuarda=$this->calcularMaiorPrazoGuarda($arrObjRelProtocoloAssuntoDTO);

            $guardaTotal = abs($maiorPrazoGuarda['soma']);
            $numTempoGuardaCorrente = $maiorPrazoGuarda['corrente'];
            $numTempoGuardaIntermediaria = $maiorPrazoGuarda['intermediario'];
 
            $guardaTotal = $numTempoGuardaCorrente + $numTempoGuardaIntermediaria;
            
            $dtaGuardaCorrente = date('d/m/Y H:i:s', strtotime("+{$numTempoGuardaCorrente} years", strtotime($dtaDataArquivamentoUs)));
            $dtaGuardaIntermediaria = date('d/m/Y H:i:s', strtotime("+{$guardaTotal} years", strtotime($dtaDataArquivamentoUs)));

            $objMdGdArquivamentoDTO->setDthDataGuardaCorrente($dtaGuardaCorrente);
            $objMdGdArquivamentoDTO->setDthDataGuardaIntermediaria($dtaGuardaIntermediaria);
            $objMdGdArquivamentoDTO->setNumGuardaCorrente($numTempoGuardaCorrente);
            $objMdGdArquivamentoDTO->setNumGuardaIntermediaria($numTempoGuardaIntermediaria);

            //Informa os demais parâmetros do arquivamento e realiza seu salvamento
            $objMdGdArquivamentoDTO->setStrStaDestinacaoFinal($this->obterDestinacaoFinalProtocolo($objMdGdArquivamentoDTO->getDblIdProcedimento()));

             if ($this->contarCondicionantes($objMdGdArquivamentoDTO->getDblIdProcedimento())) {
                 $objMdGdArquivamentoDTO->setStrSinCondicionante('S');
             } else {
                 $objMdGdArquivamentoDTO->setStrSinCondicionante('N');
             }

            $dtaGuardaCorrente = date('YmdHis', strtotime("+{$numTempoGuardaCorrente} years", strtotime($dtaDataArquivamentoUs)));
            $dtaGuardaIntermediaria = date('YmdHis', strtotime("+{$guardaTotal} years", strtotime($dtaDataArquivamentoUs)));

            if($dtaGuardaCorrente >= date('YmdHis')){
                $objMdGdArquivamentoDTO->setStrSituacao(self::$ST_FASE_CORRENTE);
                $objMdGdArquivamentoDTO->setStrStaGuarda(self::$GUARDA_CORRENTE);
            }else{
                $objMdGdArquivamentoDTO->setStrSituacao(self::$ST_FASE_INTERMEDIARIA);
                $objMdGdArquivamentoDTO->setStrStaGuarda(self::$GUARDA_INTERMEDIARIA);

                $objMdGdUnidadeArquivamentoDTO = new MdGdUnidadeArquivamentoDTO();
                $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $objMdGdUnidadeArquivamentoDTO->retNumIdUnidadeDestino();

                $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
                
                if($objMdGdUnidadeArquivamentoRN->contar($objMdGdUnidadeArquivamentoDTO) == 1){
                    $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoRN->consultar($objMdGdUnidadeArquivamentoDTO);
                    $objMdGdArquivamentoDTO->setNumIdUnidadeIntermediaria($objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeDestino());
                }
            }

            // Fecha o procedimento
            $this->fecharProcedimentoArquivamentoControlado($objMdGdArquivamentoDTO);
            
            // Atualiza a situação do arquivamento
            return $this->alterarConectado($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao concluir edição do arquivamento.', $e);
        }
    }

    /**
     * Registra o histórico de arquivamento
     *
     * @param integer $numIdArquivamento
     * @param string $strSituacaoAntiga
     * @param string $strSituacaoAtual
     * @return boolean
     */
    protected function registrarHistoricoArquivamento($numIdArquivamento, $strSituacaoAntiga, $strSituacaoAtual, $strDataHistorico = null){
        $objMdGdArquivamentoHistoricoDTO = new MdGdArquivamentoHistoricoDTO();
        $objMdGdArquivamentoHistoricoDTO->setNumIdArquivamento($numIdArquivamento);
        $objMdGdArquivamentoHistoricoDTO->setStrSituacaoAntiga($strSituacaoAntiga);
        $objMdGdArquivamentoHistoricoDTO->setStrSituacaoAtual($strSituacaoAtual);
        $objMdGdArquivamentoHistoricoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        $objMdGdArquivamentoHistoricoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        
        if($strDataHistorico){
            $objMdGdArquivamentoHistoricoDTO->setDthHistorico($strDataHistorico);
        }else{
            $objMdGdArquivamentoHistoricoDTO->setDthHistorico(date('d/m/Y H:i:s'));
        }
        
        $arrDescricoesHistorico = MdGdArquivamentoHistoricoRN::descricoesHistorico();
        $objMdGdArquivamentoHistoricoDTO->setStrDescricao($arrDescricoesHistorico[$strSituacaoAtual]);

        $objMdGdArquivamentoHistoricoRN = new MdGdArquivamentoHistoricoRN();
        return $objMdGdArquivamentoHistoricoRN->cadastrar($objMdGdArquivamentoHistoricoDTO);
    }

    ######################HELPERS DE ARQUIVAMENTO##############################
    
    

    /**
     * Obtem as labels de situações de um arquivamento
     * 
     * @return type
     */
    public static function obterSituacoesArquivamento() {
        return [
            self::$ST_FASE_CORRENTE => 'Fase Corrente',
            self::$ST_FASE_INTERMEDIARIA => 'Fase Intermediária',
            self::$ST_PREPARACAO_RECOLHIMENTO => 'Pronto para Recolhimento',
            self::$ST_PREPARACAO_ELIMINACAO => 'Pronto para Eliminação',
            self::$ST_ENVIADO_RECOLHIMENTO => 'Enviado para Recolhimento',
            self::$ST_ENVIADO_ELIMINACAO => 'Enviado para Eliminação',
            self::$ST_RECOLHIDO => 'Recolhido',
            self::$ST_ELIMINADO => 'Eliminado',
            self::$ST_DESARQUIVADO => 'Desarquivado'
        ];
    }

    /**
     * Obtem as labels de guarda do arquivamento
     * 
     * @return type
     */
    public static function obterGuardasArquivamento() {
        return [
            self::$GUARDA_CORRENTE => 'Guarda Corrente',
            self::$GUARDA_INTERMEDIARIA => 'Guarda Intermediária'
        ];
    }

    /**
     * Obtem as labels de destinação final dos arquivamentos
     * 
     * @return type
     */
    public static function obterDestinacoesFinalArquivamento() {
        return [
            self::$DF_ELIMINACAO => 'Eliminação',
            self::$DF_RECOLHIMENTO => 'Recolhimento'
        ];
    }

    
    public static function descreverTempoArquivamentoCorrente($strDataArquivamento, $numAnosGuardaCorrente){
        return self::descreverTempoArquivamento($strDataArquivamento, $numAnosGuardaCorrente);
    }

    public static function descreverTempoArquivamentoIntermediario($strDataArquivamento, $numAnosGuardaCorrente, $numAnosGuardaIntermediaria){     
        list($years, $months, $days) = self::obterTempoArquivamento($strDataArquivamento, $numAnosGuardaCorrente);

        if($years == 0 && $months == 0 && $days == 0){
            return self::descreverTempoArquivamento($strDataArquivamento, $numAnosGuardaIntermediaria + $numAnosGuardaCorrente);
        }else{
            return $numAnosGuardaIntermediaria == 1 ? $numAnosGuardaIntermediaria." ano" : $numAnosGuardaIntermediaria." anos" ;
        }
    }

    public static function descreverTempoArquivamento($strDataArquivamento, $numAnosGuarda){
        list($years, $months, $days) = self::obterTempoArquivamento($strDataArquivamento, $numAnosGuarda);


        if($years == 0 && $months == 0 && $days == 0){
            return 'Prazo expirado!';
        }

        $strTemporalidade = '';

        if($years == 1){
            $strTemporalidade .= $years." ano, ";
        }else{
            $strTemporalidade .= $years." anos, ";
        }

        if($months == 1){
            $strTemporalidade .= $months." mês e";
        }else{
            $strTemporalidade .= $months." meses e ";
        }

        if($days == 1){
            $strTemporalidade .= $days." dia.";
        }else{
            $strTemporalidade .= $days." dias.";
        }

        return $strTemporalidade;
    }

    public static function obterTempoArquivamento($strDataArquivamento, $numAnosGuarda)
    {
        $strDataInicial = date('Y-m-d');
        $strDataFinal = date('Y-m-d', strtotime("+{$numAnosGuarda} years", strtotime($strDataArquivamento)));

        $date1 = strtotime($strDataInicial);  
        $date2 = strtotime($strDataFinal);  

        if($date1 >= $date2){
            return array(0, 0, 0);   
        }

        $dtDataInicial = new DateTime(date('Y-m-d'));
        $dtDataFinal = new DateTime($strDataFinal);
        $tempo = $dtDataFinal->diff($dtDataInicial);

        return array($tempo->y, $tempo->m, $tempo->d);      
    }
}

?>