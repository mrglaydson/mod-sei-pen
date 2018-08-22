<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaRecolhimentoRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimento) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_prep_list_recolhimento_gerar', __METHOD__, $objMdGdListaRecolhimento);
            
              // Recupera os arquivamentos
            $arrObjMdGdArquivamentoDTO = $objMdGdListaRecolhimento->getArrObjMdGdArquivamentoDTO();
                        
            // Cria a listagem de eliminaзгo
            $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
            $objMdGdListaRecolhimentoDTO->setStrNumero($this->obterProximaNumeroListagem());
            $objMdGdListaRecolhimentoDTO->setDthEmissaoListagem(date('d/m/Y H:i:s'));
            $objMdGdListaRecolhimentoDTO->setNumAnoLimiteInicio(2004); // TODO NOW
            $objMdGdListaRecolhimentoDTO->setNumAnoLimiteFim(2018); // TODO NOW
            
            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->getObjInfraIBanco());
            $objMdGdListaRecolhimentoDTO = $objMdGdListaRecolhimentoBD->cadastrar($objMdGdListaRecolhimentoDTO);
            
            $objMdGdListRecolProcedimentoBD = new MdGdListaRecolProcedimentoBD($this->getObjInfraIBanco());
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            
            // Cria a relaзгo da listagem de eliminaзгo com os procedimentos
            foreach($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO){
                
                //Cria o vнlculo da lista com o procedimento
                $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
                $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($objMdGdListaRecolhimentoDTO->getNumIdListaRecolhimento());
                $objMdGdListaRecolProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
               
                $objMdGdListRecolProcedimentoBD->cadastrar($objMdGdListaRecolProcedimentoDTO);
                
                // Altera a situaзгo do arquivamento do procedimento
                $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_ENVIADO_RECOLHIMENTO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }
            
            
            // CODE: 
            // -- Criaзгo da listagem de recolhimento
        } catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar a listagem de recolhimento.', $e);
        }
    }

    protected function consultarConectado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimento) {
        try {

            // CODE:
            // -- Consulta a listagem de eliminaзгo
        } catch (Exception $e) {
            throw new InfraException('Erro consultando a listagem de recolhimento.', $e);
        }
    }

    protected function listarConectado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimento) {
        try {

            // CODE
            // -- Listagem das listas de eliminaзгo
        } catch (Exception $e) {
            throw new InfraException('Erro listando as listagens de recolhimento.', $e);
        }
    }

       public function obterProximaNumeroListagem()
    {
        $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
        $objMdGdListaRecolhimentoDTO->setStrNumero('%'.date('Y'), InfraDTO::$OPER_LIKE);
        $objMdGdListaRecolhimentoDTO->retTodos();
        
        $arrObjMdGdListaRecolhimento = $this->listar($objMdGdListaRecolhimentoDTO);
        
        $numeroListagem = count($arrObjMdGdListaRecolhimento) + 1;
        
        return $numeroListagem."/".date('Y');
        
    }
    
}

?>