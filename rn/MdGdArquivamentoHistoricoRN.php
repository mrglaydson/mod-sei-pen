<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdArquivamentoHistoricoRN extends InfraRN {
    
    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    public function cadastrarControlado(MdGdArquivamentoHistoricoDTO $objMdGdArquivamentoHistoricoDTO){
        try{    
            
            $objMdGdArquivamentoHistoricoBD = new MdGdArquivamentoHistoricoBD($this->inicializarObjInfraIBanco());
            return $objMdGdArquivamentoHistoricoBD->cadastrar($objMdGdArquivamentoHistoricoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar histуrico de arquivamento.', $e);
        }
    }
    
    public function listarControlado(MdGdArquivamentoHistoricoDTO $objMdGdArquivamentoHistoricoDTO){
        try{    
            
            $objMdGdArquivamentoHistoricoBD = new MdGdArquivamentoHistoricoBD($this->inicializarObjInfraIBanco());
            return $objMdGdArquivamentoHistoricoBD->listar($objMdGdArquivamentoHistoricoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao listar histуrico de arquivamento.', $e);
        }
    }

    public static function descricoesHistorico(){
        return array(
            MdGdArquivamentoRN::$ST_FASE_CORRENTE => 'O processo foi arquivado e encontra-se em fase corrente de arquivamento.',
            MdGdArquivamentoRN::$ST_FASE_INTERMEDIARIA => 'O processo encontra-se em fase intermediбria de arquivamento.',
            MdGdArquivamentoRN::$ST_FASE_EDICAO => 'O processo arquivado foi aberto para ediзгo.',
            MdGdArquivamentoRN::$ST_DEVOLVIDO => 'O processo arquivado foi devolvido.',
            MdGdArquivamentoRN::$ST_PREPARACAO_RECOLHIMENTO => 'O processo arquivado foi preparado para recolhimento.',
            MdGdArquivamentoRN::$ST_PREPARACAO_ELIMINACAO => 'O processo arquivado foi preparado para eliminaзгo.',
            MdGdArquivamentoRN::$ST_ENVIADO_RECOLHIMENTO => 'O processo arquivado foi preparado para recolhimento.',
            MdGdArquivamentoRN::$ST_ENVIADO_ELIMINACAO => 'O processo arquivado foi enviado para eliminaзгo.',
            MdGdArquivamentoRN::$ST_RECOLHIDO => 'O processo arquivado foi recolhido.',
            MdGdArquivamentoRN::$ST_ELIMINADO => 'O processo arquivado foi eliminado.'
        );
    }

}

?>