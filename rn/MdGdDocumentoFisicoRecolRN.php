<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdDocumentoFisicoRecolRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdDocumentoFisicoRecolDTO $objMdGdDocumentoFisicoRecolDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_recolhimento_documentos_fisicos', __METHOD__, $objMdGdDocumentoFisicoRecolDTO);

            $objMdGdDocumentoFisicoRecolBD = new MdGdDocumentoFisicoRecolBD($this->inicializarObjInfraIBanco());
            return $objMdGdDocumentoFisicoRecolBD->cadastrar($objMdGdDocumentoFisicoRecolDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar recolhimento de documento físico.', $e);
        }
    }

    protected function consultarConectado(MdGdDocumentoFisicoRecolDTO $objMdGdDocumentoFisicoRecolDTO) {
        try {
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_list_recol_documentos_fisicos', __METHOD__, $objMdGdDocumentoFisicoRecolDTO);

            $objMdGdDocumentoFisicoRecolBD = new MdGdDocumentoFisicoRecolBD($this->inicializarObjInfraIBanco());
            return $objMdGdDocumentoFisicoRecolBD->consultar($objMdGdDocumentoFisicoRecolDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao consultar recolhimento de documento físico.', $e);
        }
    }

    protected function listarConectado(MdGdDocumentoFisicoRecolDTO $objMdGdDocumentoFisicoRecolDTO) {
        try {
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_list_recol_documentos_fisicos', __METHOD__, $objMdGdDocumentoFisicoRecolDTO);

            $objMdGdDocumentoFisicoRecolBD = new MdGdDocumentoFisicoRecolBD($this->inicializarObjInfraIBanco());
            return $objMdGdDocumentoFisicoRecolBD->listar($objMdGdDocumentoFisicoRecolDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao listar recolhimentos de documento físico.', $e);
        }
    }

    protected function contarConectado(MdGdDocumentoFisicoRecolDTO $objMdGdDocumentoFisicoRecolDTO) {
        try {
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_list_recol_documentos_fisicos', __METHOD__, $objMdGdDocumentoFisicoRecolDTO);

            $objMdGdDocumentoFisicoRecolBD = new MdGdDocumentoFisicoRecolBD($this->inicializarObjInfraIBanco());
            return $objMdGdDocumentoFisicoRecolBD->contar($objMdGdDocumentoFisicoRecolDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao contar recolhimentos de documento físico.', $e);
        }
    }

}

?>