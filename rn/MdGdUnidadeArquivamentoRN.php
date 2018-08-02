<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdUnidadeArquivamentoRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_unidade_arquivamento_cadastrar', __METHOD__, $objMdGdUnidadeArquivamentoDTO);

            $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());
            $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoBD->cadastrar($objMdGdUnidadeArquivamentoDTO);
            return $objMdGdUnidadeArquivamentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro cadastrando a unidade de arquivamento.', $e);
        }
    }

    protected function alterarControlado(MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_unidade_arquivamento_alterar', __METHOD__, $objMdGdUnidadeArquivamentoDTO);

            $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());
            $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoBD->alterar($objMdGdUnidadeArquivamentoDTO);
            return $objMdGdUnidadeArquivamentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro alterando a unidade de arquivamento.', $e);
        }
    }

    protected function excluirControlado($arrObjMdGdUnidadeArquivamentoDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_unidade_arquivamento_excluir', __METHOD__, $arrObjMdGdUnidadeArquivamentoDTO);

            $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());

            foreach ($arrObjMdGdUnidadeArquivamentoDTO as $objMdGdUnidadeArquiamentoDTO) {
                $objMdGdUnidadeArquivamentoBD->excluir($objMdGdUnidadeArquiamentoDTO);
            }
        } catch (Exception $e) {
            throw new InfraException('Erro excluindo a unidade de arquivamento.', $e);
        }
    }

    protected function consultarConectado(MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO) {
        try {
            $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());
            $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoBD->consultar($objMdGdUnidadeArquivamentoDTO);

            return $objMdGdUnidadeArquivamentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro consultando a unidade de arquivamento.', $e);
        }
    }

    protected function listarConectado(MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO) {
        try {

            $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());
            $arrObjMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoBD->listar($objMdGdUnidadeArquivamentoDTO);

            return $arrObjMdGdUnidadeArquivamentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro listando a unidade de arquivamento.', $e);
        }
    }

}

?>