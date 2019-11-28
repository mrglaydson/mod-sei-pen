<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdModeloDocumentoRN extends InfraRN {

    const MODELO_DESPACHO_ARQUIVAMENTO = 'MODELO_DESPACHO_ARQUIVAMENTO';
    const MODELO_DESPACHO_DESARQUIVAMENTO = 'MODELO_DESPACHO_DESARQUIVAMENTO';
    const MODELO_LISTAGEM_ELIMINACAO = 'MODELO_LISTAGEM_ELIMINACAO';
    const MODELO_DOCUMENTO_ELIMINACAO = 'MODELO_DOCUMENTO_ELIMINACAO';

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdModeloDocumentoDTO $objMdGdModeloDocumentoDTO) {
        try {

            $objMdGdModeloDocumentoBD = new MdGdModeloDocumentoBD($this->inicializarObjInfraIBanco());
            $objMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoBD->cadastrar($objMdGdModeloDocumentoDTO);
            return $objMdGdModeloDocumentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro alterando o modelo de documento.', $e);
        }
    }

    protected function alterarControlado(MdGdModeloDocumentoDTO $objMdGdModeloDocumentoDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_modelo_documento_alterar', __METHOD__, $objMdGdModeloDocumentoDTO);

            $objMdGdModeloDocumentoBD = new MdGdModeloDocumentoBD($this->inicializarObjInfraIBanco());
            $objMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoBD->alterar($objMdGdModeloDocumentoDTO);
            return $objMdGdModeloDocumentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro alterando o modelo de documento.', $e);
        }
    }

    protected function consultarConectado(MdGdModeloDocumentoDTO $objMdGdModeloDocumentoDTO) {
        try {
            $objMdGdModeloDocumentoBD = new MdGdModeloDocumentoBD($this->inicializarObjInfraIBanco());
            $objMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoBD->consultar($objMdGdModeloDocumentoDTO);

            return $objMdGdModeloDocumentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro consultando o modelo de documento.', $e);
        }
    }

    protected function listarConectado(MdGdModeloDocumentoDTO $objMdGdModeloDocumentoDTO) {
        try {

            $objMdGdModeloDocumentoBD = new MdGdModeloDocumentoBD($this->inicializarObjInfraIBanco());
            $arrObjMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoBD->listar($objMdGdModeloDocumentoDTO);

            return $arrObjMdGdModeloDocumentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro listando os modelo de documento.', $e);
        }
    }

}

?>