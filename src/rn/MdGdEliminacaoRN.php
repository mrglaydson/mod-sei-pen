<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdEliminacaoRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    /**
     * Undocumented function
     *
     * @param MdGdEliminacaoDTO $objMdGdEliminacaoDTO
     * @return void
     */
    protected function cadastrarControlado(MdGdEliminacaoDTO $objMdGdEliminacaoDTO) {
        try {

            //Valida Permiss�o
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_procedimento_arquivar', __METHOD__, $objMdGdEliminacaoDTO);

            // Altera a situa��o da elimina��o para eliminado
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
            $objMdGdArquivamentoDTO->setNumIdListaEliminacao($objMdGdEliminacaoDTO->getNumIdListaEliminacao());
            $objMdGdArquivamentoDTO->retNumIdArquivamento();

            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);

            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
                $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_ELIMINADO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }

            // Altera a situa��o da listagem para eliminada
            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setNumIdListaEliminacao($objMdGdEliminacaoDTO->getNumIdListaEliminacao());
            $objMdGdListaEliminacaoDTO->setStrSituacao(MdGdListaEliminacaoRN::$ST_ELIMINADA);
            $objMdGdListaEliminacaoDTO->retDblIdProcedimentoEliminacao();

            $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();
            $objMdGdListaEliminacaoRN->alterar($objMdGdListaEliminacaoDTO);

            // Obtem os par�metros para cria��o do processo e documento de elimina��o
            $objMdGdParametroRN = new MdGdParametroRN();
            $numIdSerie = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_DOCUMENTO_ELIMINACAO);
            $strConteudo = $this->obterConteudoDocumentoEliminacao();

            // Obtem a listagem de elimina��o
            $objMdGdListaEliminacaoDTO = $objMdGdListaEliminacaoRN->consultar($objMdGdListaEliminacaoDTO);

            // Gera o protocolo do despacho de arquivamento
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo(null);
            $objProtocoloDTO->setStrStaProtocolo('G');
            $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
            $objProtocoloDTO->setStrDescricao('Termo de Elimina��o');
            $objProtocoloDTO->setArrObjParticipanteDTO(array());
            $objProtocoloDTO->setArrObjObservacaoDTO(array());

            // Gera o documento do termo de elimina��o
            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento(null);
            $objDocumentoDTO->setDblIdProcedimento($objMdGdListaEliminacaoDTO->getDblIdProcedimentoEliminacao());
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

            // Assinatura do documento termo de elimina��o
            /*$objAssinaturaDTO = $objMdGdEliminacaoDTO->getObjAssinaturaDTO();
            $objAssinaturaDTO->setArrObjDocumentoDTO([$objDocumentoDTO]);
            $objDocumentoRN->assinar($objAssinaturaDTO);*/
            
            // Registra a elimina��o
            $objMdGdEliminacaoBD = new MdGdEliminacaoBD($this->inicializarObjInfraIBanco());
            return $objMdGdEliminacaoBD->cadastrar($objMdGdEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao cadatrar elimina��o.', $e);
        }
    }

    protected function consultarConectado(MdGdEliminacaoDTO $objMdGdEliminacaoDTO) {
        try {
            //Valida Permissao

            $objMdGdEliminacaoBD = new MdGdEliminacaoBD($this->inicializarObjInfraIBanco());
            return $objMdGdEliminacaoBD->consultar($objMdGdEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao consultar elimina��o.', $e);
        }
    }

    protected function listarConectado(MdGdEliminacaoDTO $objMdGdEliminacaoDTO) {
        try {
            //Valida Permissao

            $objMdGdEliminacaoBD = new MdGdEliminacaoBD($this->inicializarObjInfraIBanco());
            return $objMdGdEliminacaoBD->listar($objMdGdEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao listar elimina��es.', $e);
        }
    }

    /**
     * Obtem o conte�do do documento de elimina��o
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