<?

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Classe de regras de neg�cio das justificativas de arquivamento
 */
class MdGdJustificativaRN extends InfraRN
{

  public static $STA_TIPO_ARQUIVAMENTO = 'A';
  public static $STA_TIPO_DESARQUIVAMENTO = 'D';

    /**
     * Construtor
     */
  public function __construct()
    {
      parent::__construct();
  }

    /**
     * Inicializa��o da inst�ncia com o banco de dados do SEI
     *
     * @return BancoSEI
     */
  protected function inicializarObjInfraIBanco()
    {
      return BancoSEI::getInstance();
  }

    /**
     * Cadastro de justificativa de arquivamento
     *
     * @param MdGdJustificativaDTO $objMdGdJustificativaDTO
     * @return boolean|InfraException
     */
  protected function cadastrarControlado(MdGdJustificativaDTO $objMdGdJustificativaDTO)
    {
    try {

        //Valida Permissao
        SessaoSEI::getInstance()->validarAuditarPermissao('gd_justificativa_cadastrar', __METHOD__, $objMdGdJustificativaDTO);

        //Regras de Negocio
        $objInfraException = new InfraException();

        $objMdGdJustificativaDTO2 = new MdGdJustificativaDTO();
        $objMdGdJustificativaDTO2->setStrNome($objMdGdJustificativaDTO->getStrNome(), InfraDTO::$OPER_IGUAL);
        $objMdGdJustificativaDTO2->setStrStaTipo($objMdGdJustificativaDTO->getStrStaTipo());
            
      if($this->contar($objMdGdJustificativaDTO2) > 0){
        $objInfraException->lancarValidacao('J� existe uma justificativa com esse nome');
      }
            
        $objMdGdJustificativaBD = new MdGdJustificativaBD($this->inicializarObjInfraIBanco());
        $objMdGdJustificativaDTO = $objMdGdJustificativaBD->cadastrar($objMdGdJustificativaDTO);
        return $objMdGdJustificativaDTO;
    } catch (Exception $e) {
        throw new InfraException('Erro cadastrando a justificativa.', $e);
    }
  }

    /**
     * Altera��o de justificativa de arquivamento
     *
     * @param MdGdJustificativaDTO $objMdGdJustificativaDTO
     * @return boolean|InfraException
     */
  protected function alterarControlado(MdGdJustificativaDTO $objMdGdJustificativaDTO)
    {
    try {

        //Valida Permissao
        SessaoSEI::getInstance()->validarAuditarPermissao('gd_justificativa_alterar', __METHOD__, $objMdGdJustificativaDTO);

        //Regras de Negocio
        $objInfraException = new InfraException();

        $objMdGdJustificativaDTO2 = new MdGdJustificativaDTO();
        $objMdGdJustificativaDTO2->setStrNome($objMdGdJustificativaDTO->getStrNome(), InfraDTO::$OPER_IGUAL);
        $objMdGdJustificativaDTO2->setStrStaTipo($objMdGdJustificativaDTO->getStrStaTipo());
        $objMdGdJustificativaDTO2->setNumIdJustificativa($objMdGdJustificativaDTO->getNumIdJustificativa(), InfraDTO::$OPER_DIFERENTE);

      if($this->contar($objMdGdJustificativaDTO2) > 0){
        $objInfraException->lancarValidacao('J� existe uma justificativa com esse nome');
      }

        $objMdGdJustificativaBD = new MdGdJustificativaBD($this->inicializarObjInfraIBanco());
        $objMdGdJustificativaDTO = $objMdGdJustificativaBD->alterar($objMdGdJustificativaDTO);
        return $objMdGdJustificativaDTO;

    } catch (Exception $e) {
        throw new InfraException('Erro cadastrando a justificativa.', $e);
    }
  }


    /**
     * Exclus�o de justificativa de arquivamento
     *
     * @param MdGdJustificativaDTO $objMdGdJustificativaDTO
     * @return boolean|InfraException
     */
  protected function excluirControlado($arrObjMdGdJustificativaDTO)
    {
    try {

        //Valida Permissao
        SessaoSEI::getInstance()->validarAuditarPermissao('gd_justificativa_excluir', __METHOD__, $arrObjMdGdJustificativaDTO);

        $objMdGdJustificativaBD = new MdGdJustificativaBD($this->inicializarObjInfraIBanco());

      foreach ($arrObjMdGdJustificativaDTO as $objMdGdJustificativa) {
        $objMdGdJustificativaBD->excluir($objMdGdJustificativa);
      }

    } catch (Exception $e) {
        throw new InfraException('Erro cadastrando a justificativa.', $e);
    }
  }


    /**
     * Consulta de justificativa de arquivamento
     *
     * @param MdGdJustificativaDTO $objMdGdJustificativaDTO
     * @return boolean|InfraException
     */
  protected function consultarConectado(MdGdJustificativaDTO $objMdGdJustificativaDTO)
    {
    try {

        $objMdGdJustificativaBD = new MdGdJustificativaBD($this->inicializarObjInfraIBanco());
        $objMdGdJustificativaDTO = $objMdGdJustificativaBD->consultar($objMdGdJustificativaDTO);

        return $objMdGdJustificativaDTO;
    } catch (Exception $e) {
        throw new InfraException('Erro cadastrando a justificativa.', $e);
    }
  }


    /**
     * Lista de justificativa de arquivamento
     *
     * @param MdGdJustificativaDTO $objMdGdJustificativaDTO
     * @return boolean|InfraException
     */
  protected function listarConectado(MdGdJustificativaDTO $objMdGdJustificativaDTO)
    {
    try {

        $objMdGdJustificativaBD = new MdGdJustificativaBD($this->inicializarObjInfraIBanco());
        $arrObjMdGdJustificativaDTO = $objMdGdJustificativaBD->listar($objMdGdJustificativaDTO);

        return $arrObjMdGdJustificativaDTO;
    } catch (Exception $e) {
        throw new InfraException('Erro cadastrando a justificativa.', $e);
    }
  }

    /**
     * Constagem de justificativas de arquivamento
     *
     * @param MdGdJustificativaDTO $objMdGdJustificativaDTO
     * @return boolean|InfraException
     */
  protected function contarConectado(MdGdJustificativaDTO $objMdGdJustificativaDTO){
    try {
      $objMdGdJustificativaBD = new MdGdJustificativaBD($this->getObjInfraIBanco());
      $ret = $objMdGdJustificativaBD->contar($objMdGdJustificativaDTO);

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando as justificativas.', $e);
    }
  }

    /**
     * M�todo helper de obten��o do tipo de justificativa
     *
     * @param string $justificativa
     * @return string
     */
  public static function obterTituloJustificativa($justificativa)
    {
    if ($justificativa == self::$STA_TIPO_ARQUIVAMENTO) {
        return 'Arquivamento';
    } else if ($justificativa == self::$STA_TIPO_DESARQUIVAMENTO) {
        return 'Desarquivamento';
    } else {
        return '';
    }
  }
    
    

}

?>