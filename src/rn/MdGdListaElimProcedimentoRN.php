<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaElimProcedimentoRN extends InfraRN {

  public function __construct() {
      parent::__construct();
  }

  protected function inicializarObjInfraIBanco() {
      return BancoSEI::getInstance();
  }

  protected function cadastrarControlado(MdGdListaElimProcedimentoDTO $objMdGdListaElimProcedimentoDTO) {
    try {
        // #REVISAR } Adicionar verificaчуo de permissуo OU para criaчуo de listagem de eliminaчуo OU para adiчуo de processos em uma listagem jс existente
        $objMdGdListaElimProcedimentoBD = new MdGdListaElimProcedimentoBD($this->inicializarObjInfraIBanco());
        $objMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoBD->cadastrar($objMdGdListaElimProcedimentoDTO);
        return $objMdGdListaElimProcedimentoDTO;
    } catch (Exception $e) {
        throw new InfraException('Erro cadastrando a unidade de arquivamento.', $e);
    }
  }

  protected function alterarControlado(MdGdListaElimProcedimentoDTO $objMdGdListaElimProcedimentoDTO) {
    try {

        $objMdGdListaElimProcedimentoBD = new MdGdListaElimProcedimentoBD($this->inicializarObjInfraIBanco());
        $objMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoBD->alterar($objMdGdListaElimProcedimentoDTO);
        return $objMdGdListaElimProcedimentoDTO;
    } catch (Exception $e) {
        throw new InfraException('Erro alterando a unidade de arquivamento.', $e);
    }
  }

  protected function excluirControlado($arrObjMdGdListaElimProcedimentoDTO) {
    try {
         
        $objMdGdListaElimProcedimentoBD = new MdGdListaElimProcedimentoBD($this->inicializarObjInfraIBanco());

      foreach ($arrObjMdGdListaElimProcedimentoDTO as $objMdGdUnidadeArquiamentoDTO) {
        $objMdGdListaElimProcedimentoBD->excluir($objMdGdUnidadeArquiamentoDTO);
      }
    } catch (Exception $e) {
        throw new InfraException('Erro excluindo a unidade de arquivamento.', $e);
    }
  }

  protected function consultarConectado(MdGdListaElimProcedimentoDTO $objMdGdListaElimProcedimentoDTO) {
    try {
        $objMdGdListaElimProcedimentoBD = new MdGdListaElimProcedimentoBD($this->inicializarObjInfraIBanco());
        $objMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoBD->consultar($objMdGdListaElimProcedimentoDTO);

        return $objMdGdListaElimProcedimentoDTO;
    } catch (Exception $e) {
        throw new InfraException('Erro consultando a unidade de arquivamento.', $e);
    }
  }

  protected function listarConectado(MdGdListaElimProcedimentoDTO $objMdGdListaElimProcedimentoDTO) {
    try {

        $objMdGdListaElimProcedimentoBD = new MdGdListaElimProcedimentoBD($this->inicializarObjInfraIBanco());
        $arrObjMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoBD->listar($objMdGdListaElimProcedimentoDTO);

        return $arrObjMdGdListaElimProcedimentoDTO;
    } catch (Exception $e) {
        throw new InfraException('Erro listando a unidade de arquivamento.', $e);
    }
  }

  protected function contarConectado(MdGdListaElimProcedimentoDTO $objMdGdListaElimProcedimentoDTO) {
    try {

        $objMdGdListaElimProcedimentoBD = new MdGdListaEliminacaoBD($this->inicializarObjInfraIBanco());
        return $objMdGdListaElimProcedimentoBD->contar($objMdGdListaElimProcedimentoDTO);
    } catch (Exception $e) {
        throw new InfraException('Erro ao contar lista de recolhimentos.', $e);
    }
  }

}

?>