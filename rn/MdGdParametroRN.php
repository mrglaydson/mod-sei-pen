<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdParametroRN extends InfraRN {

    public static $PAR_UNIDADE_ARQUIVAMENTO = 'UNIDADE_ARQUIVAMENTO';
    public static $PAR_DESPACHO_ARQUIVAMENTO = 'DESPACHO_ARQUIVAMENTO';
    public static $PAR_DESPACHO_DESARQUIVAMENTO = 'DESPACHO_DESARQUIVAMENTO';
    public static $PAR_TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO = 'TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO';
    public static $PAR_TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO = 'TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO';
    public static $PAR_TIPO_DOCUMENTO_ELIMINACAO = 'TIPO_DOCUMENTO_ELIMINACAO';
    public static $PAR_TIPO_PROCEDIMENTO_ELIMINACAO = 'TIPO_PROCEDIMENTO_ELIMINACAO';
    
    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdParametroDTO $objMdGdParametroDTO) {
        try {
            $objMdGdParametroBD = new MdGdParametroBD($this->inicializarObjInfraIBanco());
            $objMdGdParametroDTO = $objMdGdParametroBD->cadastrar($objMdGdParametroDTO);

            return $objMdGdParametroDTO;
        } catch (Exception $ex) {
            throw new InfraException('Erro ao inserir parâmetro', $ex);
        }
    }

    protected function alterarControlado(MdGdParametroDTO $objMdGdParametroDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_parametros_alterar', __METHOD__, $objMdGdParametroDTO);

            $objMdGdParametroBD = new MdGdParametroBD($this->inicializarObjInfraIBanco());
            $objMdGdParametroDTO = $objMdGdParametroBD->alterar($objMdGdParametroDTO);
            return $objMdGdParametroDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro alterando o parâmetro.', $e);
        }
    }

    protected function consultarConectado(MdGdParametroDTO $objMdGdParametroDTO) {
        try {

            $objMdGdParametroBD = new MdGdParametroBD($this->inicializarObjInfraIBanco());
            $objMdGdParametroDTO = $objMdGdParametroBD->consultar($objMdGdParametroDTO);

            return $objMdGdParametroDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro consultando o parâmetro.', $e);
        }
    }

    protected function listarConectado(MdGdParametroDTO $objMdGdParametroDTO) {
        try {

            $objMdGdParametroBD = new MdGdParametroBD($this->inicializarObjInfraIBanco());
            $arrObjMdGdParametroDTO = $objMdGdParametroBD->listar($objMdGdParametroDTO);

            return $arrObjMdGdParametroDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro listando o parâmetro.', $e);
        }
    }

    public function obterParametro($strParametro) {
        try {
            $objMdGdParametroDTO = new MdGdParametroDTO();
            $objMdGdParametroDTO->setStrNome($strParametro);
            $objMdGdParametroDTO->retStrValor();

            $objMdGdParametroDTO = $this->consultar($objMdGdParametroDTO);
            return $objMdGdParametroDTO->getStrValor();
        } catch (Exception $e) {
            throw new InfraException('Erro obtendo o parâmetro.', $e);
        }
    }
    

}

?>