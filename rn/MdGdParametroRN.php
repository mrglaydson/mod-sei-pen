<?

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Classe de regras de neg�cio dos par�metros de arquivamento
 */
class MdGdParametroRN extends InfraRN {

    public static $PAR_UNIDADE_ARQUIVAMENTO = 'UNIDADE_ARQUIVAMENTO';
    public static $PAR_DESPACHO_ARQUIVAMENTO = 'DESPACHO_ARQUIVAMENTO';
    public static $PAR_DESPACHO_DESARQUIVAMENTO = 'DESPACHO_DESARQUIVAMENTO';
    public static $PAR_TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO = 'TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO';
    public static $PAR_TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO = 'TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO';
    public static $PAR_TIPO_DOCUMENTO_ELIMINACAO = 'TIPO_DOCUMENTO_ELIMINACAO';
    public static $PAR_TIPO_PROCEDIMENTO_ELIMINACAO = 'TIPO_PROCEDIMENTO_ELIMINACAO';
    
    /**
     * Construtor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Inicializa��o da conex�o com o banco de dados
     *
     * @return BancoSEI
     */
    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    /**
     * Cadastro de um par�metro
     *
     * @param MdGdParametroDTO $objMdGdParametroDTO
     * @return boolean|InfraException
     */
    protected function cadastrarControlado(MdGdParametroDTO $objMdGdParametroDTO) {
        try {

            $objMdGdParametroBD = new MdGdParametroBD($this->inicializarObjInfraIBanco());
            $objMdGdParametroDTO = $objMdGdParametroBD->cadastrar($objMdGdParametroDTO);
            return $objMdGdParametroDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro alterando o par�metro.', $e);
        }
    }

    /**
     * Altera��o de um par�metro
     *
     * @param MdGdParametroDTO $objMdGdParametroDTO
     * @return boolean|InfraException
     */
    protected function alterarControlado(MdGdParametroDTO $objMdGdParametroDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_parametro_alterar', __METHOD__, $objMdGdParametroDTO);

            $objMdGdParametroBD = new MdGdParametroBD($this->inicializarObjInfraIBanco());
            $objMdGdParametroDTO = $objMdGdParametroBD->alterar($objMdGdParametroDTO);
            return $objMdGdParametroDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro alterando o par�metro.', $e);
        }
    }

    /**
     * Consulta de de um par�metro
     *
     * @param MdGdParametroDTO $objMdGdParametroDTO
     * @return boolean|InfraException
     */
    protected function consultarConectado(MdGdParametroDTO $objMdGdParametroDTO) {
        try {

            $objMdGdParametroBD = new MdGdParametroBD($this->inicializarObjInfraIBanco());
            $objMdGdParametroDTO = $objMdGdParametroBD->consultar($objMdGdParametroDTO);

            return $objMdGdParametroDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro consultando o par�metro.', $e);
        }
    }

    /**
     * Listagem de par�metros
     *
     * @param MdGdParametroDTO $objMdGdParametroDTO
     * @return boolean|InfraException
     */
    protected function listarConectado(MdGdParametroDTO $objMdGdParametroDTO) {
        try {

            $objMdGdParametroBD = new MdGdParametroBD($this->inicializarObjInfraIBanco());
            $arrObjMdGdParametroDTO = $objMdGdParametroBD->listar($objMdGdParametroDTO);

            return $arrObjMdGdParametroDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro listando o par�metro.', $e);
        }
    }

    /**
     * Obten��o de valores de par�metros
     *
     * @param string $strParametro
     * @return string
     */
    public function obterParametro($strParametro) {
        try {
            $objMdGdParametroDTO = new MdGdParametroDTO();
            $objMdGdParametroDTO->setStrNome($strParametro);
            $objMdGdParametroDTO->retStrValor();

            $objMdGdParametroDTO = $this->consultar($objMdGdParametroDTO);
            return $objMdGdParametroDTO->getStrValor();
        } catch (Exception $e) {
            throw new InfraException('Erro obtendo o par�metro.', $e);
        }
    }
    

}

?>