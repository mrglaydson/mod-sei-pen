<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdRecolhimentoRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdRecolhimentoDTO $objMdGdRecolhimentoDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_lista_recolhimento_recolher', __METHOD__, $objMdGdRecolhimentoDTO);

            // Altera a situaчуo do recolhimento para recolhido
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
            $objMdGdArquivamentoDTO->setNumIdListaRecolhimento($objMdGdRecolhimentoDTO->getNumIdListaRecolhimento());
            $objMdGdArquivamentoDTO->retNumIdArquivamento();

            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);
            

            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
                $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_RECOLHIDO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }

            // Altera a situaчуo da listagem para recolhida
            $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
            $objMdGdListaRecolhimentoDTO->setNumIdListaRecolhimento($objMdGdRecolhimentoDTO->getNumIdListaRecolhimento());
            $objMdGdListaRecolhimentoDTO->setStrSituacao(MdGdListaRecolhimentoRN::$ST_RECOLHIDA);
            $objMdGdListaRecolhimentoDTO->retDblIdProcedimentoRecolhimento();

            $objMdGdListaRecolhimentoRN = new MdGdListaRecolhimentoRN();
            $objMdGdListaRecolhimentoRN->alterar($objMdGdListaRecolhimentoDTO);

            // Obtem os parтmetros para criaчуo do processo e documento de eliminaчуo
            $objMdGdParametroRN = new MdGdParametroRN();
            $numIdSerie = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_DOCUMENTO_RECOLHIMENTO);
            $strConteudo = $this->obterConteudoDocumentoRecolhimento();

            // Obtem a listagem de eliminaчуo
            $objMdGdListaRecolhimentoDTO = $objMdGdListaRecolhimentoRN->consultar($objMdGdListaRecolhimentoDTO);

            // Gera o protocolo do despacho de arquivamento
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo(null);
            $objProtocoloDTO->setStrStaProtocolo('G');
            $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
            $objProtocoloDTO->setStrDescricao('Termo de Recolhimento');
            $objProtocoloDTO->setArrObjParticipanteDTO(array());
            $objProtocoloDTO->setArrObjObservacaoDTO(array());

            // Gera o documento do termo de recolhimento
            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento(null);
            $objDocumentoDTO->setDblIdProcedimento($objMdGdListaRecolhimentoDTO->getDblIdProcedimentoRecolhimento());
            $objDocumentoDTO->setNumIdSerie($numIdSerie);
            $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);
            $objDocumentoDTO->setDblIdDocumentoEdoc(null);
            $objDocumentoDTO->setDblIdDocumentoEdocBase(null);
            $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objDocumentoDTO->setNumIdTipoConferencia(null);
            $objDocumentoDTO->setStrNumero('');
            $objDocumentoDTO->setStrConteudo($strConteudo);
            $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);

            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

            // Registra o recolhimento
            $objMdGdRecolhimentoBD = new MdGdRecolhimentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdRecolhimentoBD->cadastrar($objMdGdRecolhimentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao cadatrar recolhimento.', $e);
        }
    }

    protected function consultarConectado(MdGdRecolhimentoDTO $objMdGdRecolhimentoDTO) {
        try {
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_lista_recolhimento_listar', __METHOD__, $objMdGdRecolhimentoDTO);

            $objMdGdRecolhimentoBD = new MdGdRecolhimentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdRecolhimentoBD->consultar($objMdGdRecolhimentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao consultar recolhimento.', $e);
        }
    }

    protected function listarConectado(MdGdRecolhimentoDTO $objMdGdRecolhimentoDTO) {
        try {
            //Valida Permissao

            $objMdGdRecolhimentoBD = new MdGdRecolhimentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdRecolhimentoBD->listar($objMdGdRecolhimentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao listar recolhimentos.', $e);
        }
    }

    /**
     * Obtem o conteњdo do documento de recolhimento
     * 
     * @return string
     */
    public function obterConteudoDocumentoRecolhimento() {
        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->retStrDescricao();
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

        $objUnidadeRN = new UnidadeRN();
        $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);
        
        $arrVariaveisModelo = [
            '@unidade@' => $objUnidadeDTO->getStrSigla()." - ".$objUnidadeDTO->getStrDescricao(),
            '@data_recolhimento@' => date('d/m/Y H:i:s'),
            '@responsavel_recolhimento@' => SessaoSEI::getInstance()->getStrNomeUsuario()
        ];

        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome(MdGdModeloDocumentoRN::MODELO_DOCUMENTO_RECOLHIMENTO);
        $objMdGdModeloDocumentoDTO->retTodos();

        $objMdGdModeloDocumentoRN = new MdGdModeloDocumentoRN();
        $objMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoRN->consultar($objMdGdModeloDocumentoDTO);

        $str = $objMdGdModeloDocumentoDTO->getStrValor();
        $str = strtr($str, $arrVariaveisModelo);
        return $str;
    }

}

?>