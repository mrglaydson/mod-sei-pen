<?

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Classe de regras de negуcio das unidades de arquivamento
 */
class MdGdUnidadeArquivamentoRN extends InfraRN {

    /**
     * Construtor
     */
  public function __construct() {
      parent::__construct();
  }

    /**
     * Inicializaзгo da instвncia com o banco de dados do SEI
     *
     * @return BancoSEI
     */
  protected function inicializarObjInfraIBanco() {
      return BancoSEI::getInstance();
  }

    /**
     * Cadastro de unidades de arquivamento
     *
     * @param MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO
     * @return boolean|InfraException
     */
  protected function cadastrarControlado(MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO) {
    try {

        //Valida Permissao
        SessaoSEI::getInstance()->validarAuditarPermissao('gd_unidade_arquivamento_cadastrar', __METHOD__, $objMdGdUnidadeArquivamentoDTO);

        $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());
        $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoBD->cadastrar($objMdGdUnidadeArquivamentoDTO);
        return $objMdGdUnidadeArquivamentoDTO;
    } catch (Exception $e) {
        throw new InfraException('Erro cadastrando a unidade de arquivamento.', $e);
    }
  }

    /**
     * Alteraзгo de unidades de arquivamento
     *
     * @param MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO
     * @return boolean|InfraException
     */
  protected function alterarControlado(MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO) {
    try {

        //Valida Permissao
        SessaoSEI::getInstance()->validarAuditarPermissao('gd_unidade_arquivamento_alterar', __METHOD__, $objMdGdUnidadeArquivamentoDTO);

        $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());
        $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoBD->alterar($objMdGdUnidadeArquivamentoDTO);
        return $objMdGdUnidadeArquivamentoDTO;
    } catch (Exception $e) {
        throw new InfraException('Erro alterando a unidade de arquivamento.', $e);
    }
  }

    /**
     * Exclusгo de unidades de arquivamento
     *
     * @param MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO
     * @return boolean|InfraException
     */
  protected function excluirControlado($arrObjMdGdUnidadeArquivamentoDTO) {
    try {

        //Valida Permissao
        SessaoSEI::getInstance()->validarAuditarPermissao('gd_unidade_arquivamento_excluir', __METHOD__, $arrObjMdGdUnidadeArquivamentoDTO);

        $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());

      foreach ($arrObjMdGdUnidadeArquivamentoDTO as $objMdGdUnidadeArquiamentoDTO) {
        $objMdGdUnidadeArquivamentoBD->excluir($objMdGdUnidadeArquiamentoDTO);
      }
    } catch (Exception $e) {
        throw new InfraException('Erro excluindo a unidade de arquivamento.', $e);
    }
  }

    /**
     * Consulta de unidades de arquivamento
     *
     * @param MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO
     * @return boolean|InfraException
     */
  protected function consultarConectado(MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO) {
    try {
        $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());
        $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoBD->consultar($objMdGdUnidadeArquivamentoDTO);

        return $objMdGdUnidadeArquivamentoDTO;
    } catch (Exception $e) {
        throw new InfraException('Erro consultando a unidade de arquivamento.', $e);
    }
  }

    /**
     * Listagem de unidades de arquivamento
     *
     * @param MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO
     * @return boolean|InfraException
     */
  protected function listarConectado(MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO) {
    try {

        $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());
        $arrObjMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoBD->listar($objMdGdUnidadeArquivamentoDTO);

        return $arrObjMdGdUnidadeArquivamentoDTO;
    } catch (Exception $e) {
        throw new InfraException('Erro listando a unidade de arquivamento.', $e);
    }
  }
    
    /**
     * Contagem de unidades de arquivamento
     *
     * @param MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO
     * @return boolean|InfraException
     */
  protected function contarConectado(MdGdUnidadeArquivamentoDTO $objMdGdArquivamentoDTO) {
    try {

        $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());
        return $objMdGdUnidadeArquivamentoBD->contar($objMdGdArquivamentoDTO);
    } catch (Exception $e) {
        throw new InfraException('Erro ao consultar unidade de arquivamento.', $e);
    }
  }

    /**
     * Obtenзгo a unidade de arquivamento 
     *
     * @param MdGdUnidadeArquivamentoDTO $objMdGdUnidadeArquivamentoDTO
     * @return integer|boolean|InfraException
     */
  protected function getNumIdUnidadeArquivamentoAtualConectado(){
    try {
     
        $objMdGdUnidadeArquivamentoDTO = new MdGdUnidadeArquivamentoDTO();
        $objMdGdUnidadeArquivamentoDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objMdGdUnidadeArquivamentoDTO->retNumIdUnidadeArquivamento();
            
        $objMdGdUnidadeArquivamentoBD = new MdGdUnidadeArquivamentoBD($this->inicializarObjInfraIBanco());
      if($objMdGdUnidadeArquivamentoBD->contar($objMdGdUnidadeArquivamentoDTO) == 1){
        $objMdGdUnidadeArquivamentoDTO = $objMdGdUnidadeArquivamentoBD->consultar($objMdGdUnidadeArquivamentoDTO);
        return $objMdGdUnidadeArquivamentoDTO->getNumIdUnidadeArquivamento();
      }else{
          return false;
      }
    } catch (Exception $e) {
        throw new InfraException('Erro ao consultar unidade de arquivamento.', $e);
    }
  }

}

?>