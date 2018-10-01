<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdDesarquivamentoRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function desarquivarControlado(MdGdDesarquivamentoDTO $objMdGdDesarquivamentoDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_desarquivar_processo', __METHOD__, $objMdGdDesarquivamentoDTO);

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

            // Inativa os registros de arquivamento
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
            $objMdGdArquivamentoDTO->setDblIdProcedimento($objMdGdDesarquivamentoDTO->getDblIdProcedimento());
            $objMdGdArquivamentoDTO->retNumIdArquivamento();

            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);
            $numIdArquivamento = null;

            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
                $objMdGdArquivamentoDTO->setStrSinAtivo('N');
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
                $numIdArquivamento = $objMdGdArquivamentoDTO->getNumIdArquivamento();
            }


            // Cria o registro de desarquivamento
            $objMdGdDesarquivamentoDTO->setDthDataDesarquivamento($dtaDesarquivamento);
            $objMdGdDesarquivamentoDTO->setDblIdDespachoDesarquivamento($objDocumentoDTO->getDblIdDocumento());
            $objMdGdDesarquivamentoDTO->setNumIdArquivamento($numIdArquivamento);

            $objMdGdDesarquivamentoBD = new MdGdDesarquivamentoBD($this->inicializarObjInfraIBanco());
            $objMdGdDesarquivamentoBD->cadastrar($objMdGdDesarquivamentoDTO);



            return true;
        } catch (Exception $e) {
            throw new InfraException('Erro ao arquivar processo.', $e);
        }
    }

    public function obterConteudoDespachoDesarquivamento($numIdJustificativa, $dthArquivamento, $strResponsavelArquivamento) {
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

}

?>