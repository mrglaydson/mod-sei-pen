<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdArquivamentoRN extends InfraRN {
    # Situações do arquivamento

    public static $ST_FASE_CORRENTE = 'CO';
    public static $ST_FASE_INTERMEDIARIA = 'IN';
    public static $ST_PREPARACAO_RECOLHIMENTO = 'PR';
    public static $ST_PREPARACAO_ELIMINACAO = 'PE';
    public static $ST_ENVIADO_RECOLHIMENTO = 'ER';
    public static $ST_ENVIADO_ELIMINACAO = 'EE';
    public static $ST_RECOLHIDO = 'RE';
    public static $ST_ELIMINADO = 'EL';

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
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_arquivar_processo', __METHOD__, $objMdGdArquivamentoDTO);

            //TODO: Validar as configurações do módulo
            //TODO: Validação dos parametros obrigatórios do DTO
            // Reabre o processo caso esteja fechado TODO: REVER ESSA IMPLEMENTAÇÃO
            if ($this->reabrir) {
                $objReabrirProcessoDTO = new ReabrirProcessoDTO();
                $objReabrirProcessoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
                $objReabrirProcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $objReabrirProcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());

                $objProcedimentoRN = new ProcedimentoRN();
                $objProcedimentoRN->reabrirRN0966($objReabrirProcessoDTO);
            }

            // Informa a data do arquivamento
            $dtaDataArquivamentoBr = date('d/m/Y H:i:s');
            $dtaDataArquivamentoUs = date('Y-m-d H:i:s');
            $objMdGdArquivamentoDTO->setDthDataArquivamento($dtaDataArquivamentoBr);

            // Cria o despacho e anexa ao arquivamento
            $objDocumentoDTO = $this->gerarDespachoArquivamento($objMdGdArquivamentoDTO);
            $objMdGdArquivamentoDTO->setDblIdDespachoArquivamento($objDocumentoDTO->getDblIdDocumento());

            // Obtem os tempos de guarda corrente e intermediária e adiciona ao arquivamento
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $objRelProtocoloAssuntoDTO->retNumIdAssunto();

            $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
            $arrObjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);

            $objAssuntoRN = new AssuntoRN();
            $numTempoGuardaCorrente = 0;
            $numTempoGuardaIntermediaria = 0;

            foreach ($arrObjRelProtocoloAssuntoDTO as $objRelProtocoloAssuntoDTO) {
                $objAssuntoDTO = new AssuntoDTO();
                $objAssuntoDTO->setNumIdAssunto($objRelProtocoloAssuntoDTO->getNumIdAssunto());
                $objAssuntoDTO->retNumPrazoCorrente();
                $objAssuntoDTO->retNumPrazoIntermediario();

                $objAssuntoDTO = $objAssuntoRN->consultarRN0256($objAssuntoDTO);

                if ($numTempoGuardaCorrente < $objAssuntoDTO->getNumPrazoCorrente()) {
                    $numTempoGuardaCorrente = $objAssuntoDTO->getNumPrazoCorrente();
                }

                if ($numTempoGuardaIntermediaria < $objAssuntoDTO->getNumPrazoIntermediario()) {
                    $numTempoGuardaIntermediaria = $objAssuntoDTO->getNumPrazoIntermediario();
                }
            }
            $dtaGuardaCorrente = date('d/m/Y H:i:s', strtotime("+{$numTempoGuardaCorrente} years", strtotime($dtaDataArquivamentoUs)));
            $dtaGuardaIntermediaria = date('d/m/Y H:i:s', strtotime("+" . ($numTempoGuardaCorrente + $numTempoGuardaIntermediaria) . " years", strtotime($dtaDataArquivamentoUs)));

            $objMdGdArquivamentoDTO->setDthDataGuardaCorrente($dtaGuardaCorrente);
            $objMdGdArquivamentoDTO->setDthDataGuardaIntermediaria($dtaGuardaIntermediaria);
            $objMdGdArquivamentoDTO->setNumGuardaCorrente($numTempoGuardaCorrente);
            $objMdGdArquivamentoDTO->setNumGuardaIntermediaria($numTempoGuardaIntermediaria);

            //Informa os demais parâmetros do arquivamento e realiza seu salvamento
            $objMdGdArquivamentoDTO->setStrSinAtivo('S');
            $objMdGdArquivamentoDTO->setNumIdUnidadeCorrente(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objMdGdArquivamentoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $objMdGdArquivamentoDTO->setStrSituacao(self::$ST_FASE_CORRENTE);
            $objMdGdArquivamentoDTO->setStrStaGuarda(self::$GUARDA_CORRENTE);
            $objMdGdArquivamentoDTO->setStrStaDestinacaoFinal($this->obterDestinacaoFinalProtocolo($objMdGdArquivamentoDTO->getDblIdProcedimento()));

            if ($this->contarCondicionantes($objMdGdArquivamentoDTO->getDblIdProcedimento())) {
                $objMdGdArquivamentoDTO->setStrSinCondicionante('S');
            } else {
                $objMdGdArquivamentoDTO->setStrSinCondicionante('N');
            }

            $objMdGdArquivamentoBD = new MdGdArquivamentoBD($this->inicializarObjInfraIBanco());
            $objMdGdArquivamentoBD->cadastrar($objMdGdArquivamentoDTO);

            // Concluí e bloqueia o processo
            $this->fecharProcedimentoArquivamento($objMdGdArquivamentoDTO);

            return true;
        } catch (Exception $e) {
            throw new InfraException('Erro ao arquivar processo.', $e);
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
     * @param type $numIdJustificativa
     * @param type $dthArquivamento
     * @param type $strResponsavelArquivamento
     * @return type
     */
    public function obterConteudoDespachoArquivamento($numIdJustificativa, $dthArquivamento, $strResponsavelArquivamento) {
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
            return $objMdGdArquivamentoBD->consultar($objMdGdArquivamentoDTO);
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
                $strDestinacaoFinal = $objAssuntoDTO->getStrStaDestinacao() == AssuntoRN::$TD_GUARDA_PERMANENTE ? self::$DF_RECOLHIMENTO : self::$DF_ELIMINACAO;
            }

            return $strDestinacaoFinal;
        } catch (Exception $e) {
            throw new InfraException('Erro ao contar condicionantes.', $e);
        }
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
     * @return null
     * @throws InfraException
     */
    protected function enviarEliminacaoControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {
        try {

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
     * @return null
     * @throws InfraException
     */
    protected function enviarRecolhimentoControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO) {
        try {

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
            self::$ST_ELIMINADO => 'Eliminado'
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

}

?>