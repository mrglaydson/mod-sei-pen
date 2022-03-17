<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaRecolProcedimentoRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdListaRecolProcedimentoDTO $objMdGdListaRecolProcedimentoDTO) {
        try {

            $objMdGdListaRecolProcedimentoBD = new MdGdListaRecolProcedimentoBD($this->inicializarObjInfraIBanco());
            $objMdGdListaRecolProcedimentoDTO = $objMdGdListaRecolProcedimentoBD->cadastrar($objMdGdListaRecolProcedimentoDTO);
            return $objMdGdListaRecolProcedimentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro cadastrando a unidade de arquivamento.', $e);
        }
    }

    protected function alterarControlado(MdGdListaRecolProcedimentoDTO $objMdGdListaRecolProcedimentoDTO) {
        try {

            $objMdGdListaRecolProcedimentoBD = new MdGdListaRecolProcedimentoBD($this->inicializarObjInfraIBanco());
            $objMdGdListaRecolProcedimentoDTO = $objMdGdListaRecolProcedimentoBD->alterar($objMdGdListaRecolProcedimentoDTO);
            return $objMdGdListaRecolProcedimentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro alterando a unidade de arquivamento.', $e);
        }
    }

    protected function excluirControlado($arrObjMdGdListaRecolProcedimentoDTO) {
        try {
         
            $objMdGdListaRecolProcedimentoBD = new MdGdListaRecolProcedimentoBD($this->inicializarObjInfraIBanco());

            foreach ($arrObjMdGdListaRecolProcedimentoDTO as $objMdGdUnidadeArquiamentoDTO) {
                $objMdGdListaRecolProcedimentoBD->excluir($objMdGdUnidadeArquiamentoDTO);
            }
        } catch (Exception $e) {
            throw new InfraException('Erro excluindo a unidade de arquivamento.', $e);
        }
    }

    protected function consultarConectado(MdGdListaRecolProcedimentoDTO $objMdGdListaRecolProcedimentoDTO) {
        try {
            $objMdGdListaRecolProcedimentoBD = new MdGdListaRecolProcedimentoBD($this->inicializarObjInfraIBanco());
            $objMdGdListaRecolProcedimentoDTO = $objMdGdListaRecolProcedimentoBD->consultar($objMdGdListaRecolProcedimentoDTO);

            return $objMdGdListaRecolProcedimentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro consultando a unidade de arquivamento.', $e);
        }
    }

    protected function listarConectado(MdGdListaRecolProcedimentoDTO $objMdGdListaRecolProcedimentoDTO) {
        try {

            $objMdGdListaRecolProcedimentoBD = new MdGdListaRecolProcedimentoBD($this->inicializarObjInfraIBanco());
            $arrObjMdGdListaRecolProcedimentoDTO = $objMdGdListaRecolProcedimentoBD->listar($objMdGdListaRecolProcedimentoDTO);

            return $arrObjMdGdListaRecolProcedimentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro listando a unidade de arquivamento.', $e);
        }
    }

         /**
     * Método padrão de contagem
     * 
     * @param MdGdListaRecolProcedimentoDTO $objMdGdListaRecolProcedimentoDTO
     * @return integer
     * @throws InfraException
     */
    protected function contarConectado(MdGdListaRecolProcedimentoDTO $objMdGdListaRecolProcedimentoDTO) {
        try {

            $objMdGdListaRecolProcedimentoBD = new MdGdListaRecolProcedimentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdListaRecolProcedimentoBD->contar($objMdGdListaRecolProcedimentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao consultar arquivamento.', $e);
        }
    }

}

?>