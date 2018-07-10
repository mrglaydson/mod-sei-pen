<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdArquivamentoRN extends InfraRN
{

    public $reabrir = false;

    public function __construct()
    {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }


    protected function arquivarControlado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO)
    {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_arquivar_processo', __METHOD__, $objMdGdArquivamentoDTO);

            //Instancia as RN's necessárias
            $objMdGdParametroRN = new MdGdParametroRN();
            $objDocumentoRN = new DocumentoRN();

            // Cria os valores padrões para o arquivamento
            $dtaArquivamento = date('d/m/Y H:i:s');
            $numIdSerie = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_DESPACHO_ARQUIVAMENTO);

            if ($this->reabrir) {
                // Reabre o processo
                $objReabrirProcessoDTO = new ReabrirProcessoDTO();
                $objReabrirProcessoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
                $objReabrirProcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $objReabrirProcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());

                $objProcedimentoRN = new ProcedimentoRN();
                $objProcedimentoRN->reabrirRN0966($objReabrirProcessoDTO);
            }
            // Cria o despacho de arquivamento
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo(null);
            $objProtocoloDTO->setStrStaProtocolo('G');
            $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
            $objProtocoloDTO->setStrDescricao('Despacho de Arquivamento');
            $objProtocoloDTO->setArrObjParticipanteDTO(array());
            $objProtocoloDTO->setArrObjObservacaoDTO(array());

            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento(null);
            $objDocumentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $objDocumentoDTO->setNumIdSerie($numIdSerie);
            $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);
            $objDocumentoDTO->setDblIdDocumentoEdoc(null);
            $objDocumentoDTO->setDblIdDocumentoEdocBase(null);
            $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objDocumentoDTO->setNumIdTipoConferencia(null);
            $objDocumentoDTO->setStrNumero('');
            $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);

            $objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

            //Busca os assuntos
            $arrRelProtocoloAssunto = array();
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

            // Cria o registro de arquivamento
            $objMdGdArquivamentoDTO->setDthDataArquivamento($dtaArquivamento);
            $objMdGdArquivamentoDTO->setDblIdDespachoArquivamento($objDocumentoDTO->getDblIdDocumento());
            $objMdGdArquivamentoDTO->setStrStaGuarda('C');
            $objMdGdArquivamentoDTO->setStrSinAtivo('S');
            $objMdGdArquivamentoDTO->setNumGuardaCorrente($numTempoGuardaCorrente); // BOTAR A GUARDA CORRETA VINDA DOS ASSUNTOS!!!
            $objMdGdArquivamentoDTO->setNumGuardaIntermediaria($numTempoGuardaIntermediaria); // BOTAR A GUARDA CORRETA VINDA DOS ASSUNTOS!!!

            $objMdGdArquivamentoBD = new MdGdArquivamentoBD($this->inicializarObjInfraIBanco());
            $objMdGdArquivamentoBD->cadastrar($objMdGdArquivamentoDTO);

            // Concluí o processo
            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $arrProcedimentoDTO[] = $objProcedimentoDTO;

            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoRN->concluir($arrProcedimentoDTO);
            return true;
        } catch (Exception $e) {
            throw new InfraException('Erro ao arquivar processo.', $e);
        }
    }

    protected function alterarConectado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO)
    {
        try {
            $objMdGdArquivamentoBD = new MdGdArquivamentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdArquivamentoBD->alterar($objMdGdArquivamentoDTO);

        } catch (Exception $e) {
            throw new InfraException('Erro ao atualizar arquivamento de processo', $e);
        }

    }

    protected function consultarConectado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO)
    {
        try {

            $objMdGdArquivamentoBD = new MdGdArquivamentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdArquivamentoBD->consultar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao consultar arquivamento.', $e);
        }
    }

    protected function listarConectado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO)
    {
        try {

            $objMdGdArquivamentoBD = new MdGdArquivamentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdArquivamentoBD->listar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao listar arquivamentos.', $e);
        }
    }


    protected function contarConectado(MdGdArquivamentoDTO $objMdGdArquivamentoDTO)
    {
        try {

            $objMdGdArquivamentoBD = new MdGdArquivamentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdArquivamentoBD->contar($objMdGdArquivamentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao consultar arquivamento.', $e);
        }
    }

    public function contarCondicionantes($numIdProtocolo)
    {
        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->setDblIdProtocolo($numIdProtocolo);
        return $this->contarCondicionante($objProtocoloDTO);

    }

    protected function contarCondicionanteConectado(ProtocoloDTO $objProtocoloDTO)
    {
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


}

?>