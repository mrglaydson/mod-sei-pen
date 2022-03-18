<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdDocumentoFisicoElimRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdDocumentoFisicoElimDTO $objMdGdDocumentoFisicoElimDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_lista_eliminacao_documentos_fisicos_listar', __METHOD__, $objMdGdDocumentoFisicoElimDTO);

            $objMdGdDocumentoFisicoElimBD = new MdGdDocumentoFisicoElimBD($this->inicializarObjInfraIBanco());
            return $objMdGdDocumentoFisicoElimBD->cadastrar($objMdGdDocumentoFisicoElimDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar elimina��o de documento f�sico.', $e);
        }
    }

    protected function consultarConectado(MdGdDocumentoFisicoElimDTO $objMdGdDocumentoFisicoElimDTO) {
        try {
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_lista_eliminacao_documentos_fisicos_listar', __METHOD__, $objMdGdDocumentoFisicoElimDTO);

            $objMdGdDocumentoFisicoElimBD = new MdGdDocumentoFisicoElimBD($this->inicializarObjInfraIBanco());
            return $objMdGdDocumentoFisicoElimBD->consultar($objMdGdDocumentoFisicoElimDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao consultar elimina��o de documento f�sico.', $e);
        }
    }

    protected function listarConectado(MdGdDocumentoFisicoElimDTO $objMdGdDocumentoFisicoElimDTO) {
        try {
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_lista_eliminacao_documentos_fisicos_listar', __METHOD__, $objMdGdDocumentoFisicoElimDTO);

            $objMdGdDocumentoFisicoElimBD = new MdGdDocumentoFisicoElimBD($this->inicializarObjInfraIBanco());
            return $objMdGdDocumentoFisicoElimBD->listar($objMdGdDocumentoFisicoElimDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao listar elimina��es de documento f�sico.', $e);
        }
    }

    protected function contarConectado(MdGdDocumentoFisicoElimDTO $objMdGdDocumentoFisicoElimDTO) {
        try {
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_lista_eliminacao_documentos_fisicos_listar', __METHOD__, $objMdGdDocumentoFisicoElimDTO);

            $objMdGdDocumentoFisicoElimBD = new MdGdDocumentoFisicoElimBD($this->inicializarObjInfraIBanco());
            return $objMdGdDocumentoFisicoElimBD->contar($objMdGdDocumentoFisicoElimDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao contar elimina��es de documento f�sico.', $e);
        }
    }

}

?>