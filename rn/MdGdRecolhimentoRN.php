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
                $objMdGdArquivamentoDTO->setStrStaSituacao(MdGdArquivamentoRN::$ST_ELIMINADO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }

            // Altera a situaчуo da listagem para recolhida
            $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
            $objMdGdListaRecolhimentoDTO->setNumIdListaRecolhimento($objMdGdRecolhimentoDTO->getNumIdListaRecolhimento());
            $objMdGdListaRecolhimentoDTO->setStrSituacao(MdGdListaRecolhimentoRN::$ST_RECOLHIDA);

            $objMdGdListaRecolhimentoRN = new MdGdListaRecolhimentoRN();
            $objMdGdListaRecolhimentoRN->alterar($objMdGdListaRecolhimentoDTO);

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

}

?>