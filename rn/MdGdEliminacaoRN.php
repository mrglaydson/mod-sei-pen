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

}

?>