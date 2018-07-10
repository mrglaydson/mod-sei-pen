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