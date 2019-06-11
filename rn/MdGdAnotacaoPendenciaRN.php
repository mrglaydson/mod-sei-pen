<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdAnotacaoPendenciaRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdAnotacaoPendenciaDTO $objMdGdAnotacaoPendenciaDTO) {
        try {

            //Valida Permissao
            // SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_unidade_arquivamento_cadastrar', __METHOD__, $objMdGdAnotacaoPendenciaDTO);

            $objMdGdAnotacaoPendenciaBD = new MdGdAnotacaoPendenciaBD($this->inicializarObjInfraIBanco());
            $objMdGdUnidadeArquivamentoDTO = $objMdGdAnotacaoPendenciaBD->cadastrar($objMdGdAnotacaoPendenciaDTO);
            return $objMdGdUnidadeArquivamentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro cadastrando a anotaчуo da pendъncia de arquivamento.', $e);
        }
    }

    protected function alterarControlado(MdGdAnotacaoPendenciaDTO $objMdGdAnotacaoPendenciaDTO) {
        try {

            //Valida Permissao
        //    SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_unidade_arquivamento_alterar', __METHOD__, $objMdGdUnidadeArquivamentoDTO);

            $objMdGdAnotacaoPendenciaBD = new MdGdAnotacaoPendenciaBD($this->inicializarObjInfraIBanco());
            $objMdGdAnotacaoPendenciaDTO = $objMdGdAnotacaoPendenciaBD->alterar($objMdGdAnotacaoPendenciaDTO);
            return $objMdGdAnotacaoPendenciaDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro alterando a anotaчуo da pendъncia de arquivamento.', $e);
        }
    }

    protected function excluirControlado($arrObjMdGdAnotacaoPendenciaDTO) {
        try {

            //Valida Permissao
            // SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_unidade_arquivamento_excluir', __METHOD__, $arrObjMdGdUnidadeArquivamentoDTO);

            $objMdGdAnotacaoPendenciaBD = new MdGdAnotacaoPendenciaBD($this->inicializarObjInfraIBanco());

            foreach ($arrObjMdGdAnotacaoPendenciaDTO as $objMdGdAnotacaoPendenciaDTO) {
                $objMdGdAnotacaoPendenciaBD->excluir($objMdGdAnotacaoPendenciaDTO);
            }
        } catch (Exception $e) {
            throw new InfraException('Erro excluindo a anotaчуo da pendъncia de arquivamento.', $e);
        }
    }

    protected function consultarConectado(MdGdAnotacaoPendenciaDTO $objMdGdAnotacaoPendenciaDTO) {
        try {
            $objMdGdAnotacaoPendenciaBD = new MdGdAnotacaoPendenciaBD($this->inicializarObjInfraIBanco());
            $objMdGdAnotacaoPendenciaDTO = $objMdGdAnotacaoPendenciaBD->consultar($objMdGdAnotacaoPendenciaDTO);

            return $objMdGdAnotacaoPendenciaDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro consultando a anotaчуo da pendъncia de arquivamento.', $e);
        }
    }

    protected function listarConectado(MdGdAnotacaoPendenciaDTO $objMdGdAnotacaoPendenciaDTO) {
        try {

            $objMdGdAnotacaoPendenciaBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());
            $arrObjMdGdAnotacaoPendenciaDTO = $objMdGdAnotacaoPendenciaBD->listar($objMdGdAnotacaoPendenciaDTO);

            return $arrObjMdGdAnotacaoPendenciaDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro listando a anotaчуo da pendъncia de arquivamento.', $e);
        }
    }

}

?>