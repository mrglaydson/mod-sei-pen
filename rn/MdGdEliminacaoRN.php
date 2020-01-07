<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdEliminacaoRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdEliminacaoDTO $objMdGdEliminacaoDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_eliminacao', __METHOD__, $objMdGdEliminacaoDTO);

            // Altera a situaчуo da eliminaчуo para eliminado
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
            $objMdGdArquivamentoDTO->setNumIdListaEliminacao($objMdGdEliminacaoDTO->getNumIdListaEliminacao());
            $objMdGdArquivamentoDTO->retNumIdArquivamento();

            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
                $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_ELIMINADO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }

            // Altera a situaчуo da listagem para eliminada
            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setNumIdListaEliminacao($objMdGdEliminacaoDTO->getNumIdListaEliminacao());
            $objMdGdListaEliminacaoDTO->setStrSituacao(MdGdListaEliminacaoRN::$ST_ELIMINADA);

            $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
            $objMdGdListaEliminacaoRN->alterar($objMdGdListaEliminacaoDTO);

            // Obtem os parтmetros para criaчуo do processo e documento de eliminaчуo
            $objMdGdParametroRN = new MdGdParametroRN();
            $numIdSerie = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_DOCUMENTO_ELIMINACAO);
            $numIdTipoProcedimento = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_PROCEDIMENTO_ELIMINACAO);  
            $strConteudo = $this->obterConteudoDocumentoEliminacao();

            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setNumIdAssunto(340);
            $objRelProtocoloAssuntoDTO->setNumSequencia(1);

            // Gera o processo
            $objProtocoloProcedimentoDTO = new ProtocoloDTO();
            $objProtocoloProcedimentoDTO->setStrStaProtocolo('G');
            $objProtocoloProcedimentoDTO->setStrDescricao('Processo de registro de eliminaчуo');
            $objProtocoloProcedimentoDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
            $objProtocoloProcedimentoDTO->setArrObjParticipanteDTO(array());
            $objProtocoloProcedimentoDTO->setArrObjObservacaoDTO(array());
            $objProtocoloProcedimentoDTO->setArrObjRelProtocoloAssuntoDTO(array($objRelProtocoloAssuntoDTO));
            
            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProcedimentoDTO->setStrSinGerarPendencia('S');
            $objProcedimentoDTO->setNumIdTipoProcedimento($numIdTipoProcedimento);
            $objProcedimentoDTO->setObjProtocoloDTO($objProtocoloProcedimentoDTO);

            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoDTO = $objProcedimentoRN->gerarRN0156($objProcedimentoDTO);

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
            $objDocumentoDTO->setDblIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());
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
            // $objAssinaturaDTO = $objMdGdArquivamentoDTO->getObjAssinaturaDTO();
            // $objAssinaturaDTO->setArrObjDocumentoDTO([$objDocumentoDTO]);

         //   $objDocumentoRN = new DocumentoRN();
           // $objDocumentoRN->assinar($objAssinaturaDTO);

            
            // Registra a eliminaчуo
            $objMdGdEliminacaoBD = new MdGdEliminacaoBD($this->inicializarObjInfraIBanco());
            return $objMdGdEliminacaoBD->cadastrar($objMdGdEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao cadatrar eliminaчуo.', $e);
        }
    }

    protected function consultarConectado(MdGdEliminacaoDTO $objMdGdEliminacaoDTO) {
        try {
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_eliminacao_listar', __METHOD__, $objMdGdEliminacaoDTO);

            $objMdGdEliminacaoBD = new MdGdEliminacaoBD($this->inicializarObjInfraIBanco());
            return $objMdGdEliminacaoBD->consultar($objMdGdEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao consultar eliminaчуo.', $e);
        }
    }

    protected function listarConectado(MdGdEliminacaoDTO $objMdGdEliminacaoDTO) {
        try {
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_eliminacao_listar', __METHOD__, $objMdGdEliminacaoDTO);

            $objMdGdEliminacaoBD = new MdGdEliminacaoBD($this->inicializarObjInfraIBanco());
            return $objMdGdEliminacaoBD->listar($objMdGdEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao listar eliminaчѕes.', $e);
        }
    }

    /**
     * Obtem o conteњdo do documento de eliminaчуo
     * 
     * @param string $strNumeroListagem
     * @return string
     */
    public function obterConteudoDocumentoEliminacao() {
        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->retStrDescricao();
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

        $objUnidadeRN = new UnidadeRN();
        $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);
        
        $arrVariaveisModelo = [
            '@unidade@' => $objUnidadeDTO->getStrSigla()." - ".$objUnidadeDTO->getStrDescricao(),
            '@data_eliminacao@' => date('d/m/Y H:i:s'),
            '@responsavel_eliminacao@' => SessaoSEI::getInstance()->getStrNomeUsuario()

        ];

        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome(MdGdModeloDocumentoRN::MODELO_DOCUMENTO_ELIMINACAO);
        $objMdGdModeloDocumentoDTO->retTodos();

        $objMdGdModeloDocumentoRN = new MdGdModeloDocumentoRN();
        $objMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoRN->consultar($objMdGdModeloDocumentoDTO);

        $str = $objMdGdModeloDocumentoDTO->getStrValor();
        $str = strtr($str, $arrVariaveisModelo);
        return $str;
    }

}

?>