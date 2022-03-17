<?

require_once dirname(__FILE__) . '/../../../SEI.php';


/**
 * Regras de negѓcio das anotaчѕes das pendъncias de arquivamento
 */
class MdGdAnotacaoPendenciaRN extends InfraRN {

    /**
     * Construtor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Inicializa instтncia com banco de dados
     *
     * @return BancoSEI
     */
    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    /**
     * Cadastro de anotaчѕes das pendъncias de arquivamento
     *
     * @param MdGdAnotacaoPendenciaDTO $objMdGdAnotacaoPendenciaDTO
     * @return boolean|InfraException
     */
    protected function cadastrarControlado(MdGdAnotacaoPendenciaDTO $objMdGdAnotacaoPendenciaDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_pendencia_arquivamento_anotar', __METHOD__, $objMdGdAnotacaoPendenciaDTO);

            $objMdGdAnotacaoPendenciaBD = new MdGdAnotacaoPendenciaBD($this->inicializarObjInfraIBanco());
            $objMdGdUnidadeArquivamentoDTO = $objMdGdAnotacaoPendenciaBD->cadastrar($objMdGdAnotacaoPendenciaDTO);
            return $objMdGdUnidadeArquivamentoDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro cadastrando a anotaчуo da pendъncia de arquivamento.', $e);
        }
    }

    /**
     * Alteraчѕes de anotaчѕes das pendъncias de arquivamento
     *
     * @param MdGdAnotacaoPendenciaDTO $objMdGdAnotacaoPendenciaDTO
     * @return boolean|InfraException
     */
    protected function alterarControlado(MdGdAnotacaoPendenciaDTO $objMdGdAnotacaoPendenciaDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_pendencia_arquivamento_anotar', __METHOD__, $objMdGdAnotacaoPendenciaDTO);

            $objMdGdAnotacaoPendenciaBD = new MdGdAnotacaoPendenciaBD($this->inicializarObjInfraIBanco());
            $objMdGdAnotacaoPendenciaDTO = $objMdGdAnotacaoPendenciaBD->alterar($objMdGdAnotacaoPendenciaDTO);
            return $objMdGdAnotacaoPendenciaDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro alterando a anotaчуo da pendъncia de arquivamento.', $e);
        }
    }

    /**
     * Consulta de anotaчѕes das pendъncias de arquivamento
     *
     * @param MdGdAnotacaoPendenciaDTO $objMdGdAnotacaoPendenciaDTO
     * @return boolean|InfraException
     */
    protected function consultarConectado(MdGdAnotacaoPendenciaDTO $objMdGdAnotacaoPendenciaDTO) {
        try {
            $objMdGdAnotacaoPendenciaBD = new MdGdAnotacaoPendenciaBD($this->inicializarObjInfraIBanco());
            $objMdGdAnotacaoPendenciaDTO = $objMdGdAnotacaoPendenciaBD->consultar($objMdGdAnotacaoPendenciaDTO);

            return $objMdGdAnotacaoPendenciaDTO;
        } catch (Exception $e) {
            throw new InfraException('Erro consultando a anotaчуo da pendъncia de arquivamento.', $e);
        }
    }

    /**
     * Listagem de anotaчѕes das pendъncias de arquivamento
     *
     * @param MdGdAnotacaoPendenciaDTO $objMdGdAnotacaoPendenciaDTO
     * @return boolean|InfraException
     */
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