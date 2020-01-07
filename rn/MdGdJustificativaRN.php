<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdJustificativaRN extends InfraRN
{

    public static $STA_TIPO_ARQUIVAMENTO = 'A';
    public static $STA_TIPO_DESARQUIVAMENTO = 'D';

    public function __construct()
    {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdJustificativaDTO $objMdGdJustificativaDTO)
    {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_justificativas_incluir', __METHOD__, $objMdGdJustificativaDTO);

            //Regras de Negocio
            $objInfraException = new InfraException();

            $objMdGdJustificativaDTO2 = new MdGdJustificativaDTO();
            $objMdGdJustificativaDTO2->setStrNome($objMdGdJustificativaDTO->getStrNome(), InfraDTO::$OPER_IGUAL);
            $objMdGdJustificativaDTO2->setStrStaTipo($objMdGdJustificativaDTO->getStrStaTipo());
            
            if($this->contar($objMdGdJustificativaDTO2) > 0){
                $objInfraException->lancarValidacao('Já existe uma justificativa com esse nome');
            }
            
            $objMdGdJustificativaBD = new MdGdJustificativaBD($this->inicializarObjInfraIBanco());
            $objMdGdJustificativaDTO = $objMdGdJustificativaBD->cadastrar($objMdGdJustificativaDTO);
            return $objMdGdJustificativaDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro cadastrando a justificativa.', $e);
        }
    }

    protected function alterarControlado(MdGdJustificativaDTO $objMdGdJustificativaDTO)
    {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_justificativas_alterar', __METHOD__, $objMdGdJustificativaDTO);

            //Regras de Negocio
            $objInfraException = new InfraException();

            $objMdGdJustificativaDTO2 = new MdGdJustificativaDTO();
            $objMdGdJustificativaDTO2->setStrNome($objMdGdJustificativaDTO->getStrNome(), InfraDTO::$OPER_IGUAL);
            $objMdGdJustificativaDTO2->setStrStaTipo($objMdGdJustificativaDTO->getStrStaTipo());
            $objMdGdJustificativaDTO2->setNumIdJustificativa($objMdGdJustificativaDTO->getNumIdJustificativa(), InfraDTO::$OPER_DIFERENTE);

            if($this->contar($objMdGdJustificativaDTO2) > 0){
                $objInfraException->lancarValidacao('Já existe uma justificativa com esse nome');
            }

            $objMdGdJustificativaBD = new MdGdJustificativaBD($this->inicializarObjInfraIBanco());
            $objMdGdJustificativaDTO = $objMdGdJustificativaBD->alterar($objMdGdJustificativaDTO);
            return $objMdGdJustificativaDTO;

        } catch (Exception $e) {
            throw new InfraException('Erro cadastrando a justificativa.', $e);
        }
    }

    protected function excluirControlado($arrObjMdGdJustificativaDTO)
    {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_justificativas_excluir', __METHOD__, $arrObjMdGdJustificativaDTO);

            $objMdGdJustificativaBD = new MdGdJustificativaBD($this->inicializarObjInfraIBanco());

            foreach ($arrObjMdGdJustificativaDTO as $objMdGdJustificativa) {
                $objMdGdJustificativaBD->excluir($objMdGdJustificativa);
            }

        } catch (Exception $e) {
            throw new InfraException('Erro cadastrando a justificativa.', $e);
        }
    }

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

    
  protected function contarConectado(MdGdJustificativaDTO $objMdGdJustificativaDTO){
    try {
      $objMdGdJustificativaBD = new MdGdJustificativaBD($this->getObjInfraIBanco());
      $ret = $objMdGdJustificativaBD->contar($objMdGdJustificativaDTO);

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando as justificativas.',$e);
    }
  }

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