<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdAgendamentoRN extends InfraRN
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    public function verificarTempoGuarda()
    {
    
        $objSeiRN = new SeiRN();

        // Obtem as unidades de destino
        $objMdGdUnidadeArquivamentoDTO = new MdGdUnidadeArquivamentoDTO();
        $objMdGdUnidadeArquivamentoDTO->retNumIdUnidadeOrigem();
        $objMdGdUnidadeArquivamentoDTO->retNumIdUnidadeDestino();
        
        $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
        $arrObjMdGdUnidadeArquivamentoDTO = InfraArray::converterArrInfraDTO($objMdGdUnidadeArquivamentoRN->listar($objMdGdUnidadeArquivamentoDTO), 'IdUnidadeDestino', 'IdUnidadeOrigem');
     
        // Obtem os arquivamentos vencidos
        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
        $objMdGdArquivamentoDTO->retNumIdArquivamento();
        $objMdGdArquivamentoDTO->retDblIdProcedimento();
        $objMdGdArquivamentoDTO->retNumIdUnidadeCorrente();
        $objMdGdArquivamentoDTO->retNumIdUnidadeIntermediaria();
        $objMdGdArquivamentoDTO->setDthDataGuardaCorrente(date('d/m/Y H:i:s'), InfraDTO::$OPER_MENOR);
        $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_FASE_CORRENTE);
        
        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);  
  
        foreach($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO){
            
            // Faz login na unidade corrente
            SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SEI, null, null, $objMdGdArquivamentoDTO->getNumIdUnidadeCorrente());

            // Verifica a existência de unidade arquivamento para a unidade do processo arquivado
            if(!isset($arrObjMdGdUnidadeArquivamentoDTO[$objMdGdArquivamentoDTO->getNumIdUnidadeCorrente()])){
                continue;
            }

            // Reabre o processo na unidade corrente
           /* $objEntradaReabrirProcessoAPI = new EntradaReabrirProcessoAPI();
            $objEntradaReabrirProcessoAPI->setIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $objEntradaReabrirProcessoAPI->setProtocoloProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $objSeiRN->reabrirProcesso($objEntradaReabrirProcessoAPI);

            // Desbloqueia o processo
            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $objProcedimentoDTO->retStrStaEstadoProtocolo();
            $objProcedimentoDTO->retDblIdProcedimento();

            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);
            $objProcedimentoRN->desbloquear([$objProcedimentoDTO]);

            // Envia o processo para a unidade destino
            $objEntradaEnviarProcessoAPI = new EntradaEnviarProcessoAPI();
            $objEntradaEnviarProcessoAPI->setIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $objEntradaEnviarProcessoAPI->setProtocoloProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
            $objEntradaEnviarProcessoAPI->setUnidadesDestino([$arrObjMdGdUnidadeArquivamentoDTO[$objMdGdArquivamentoDTO->getNumIdUnidadeCorrente()]]);
            $objSeiRN->enviarProcesso($objEntradaEnviarProcessoAPI);
*/
            // Atualiza a situação e a guarda do arquivamento
            $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_FASE_INTERMEDIARIA);
            $objMdGdArquivamentoDTO->setStrStaGuarda(MdGdArquivamentoRN::$ST_FASE_INTERMEDIARIA);
            $objMdGdArquivamentoDTO->setNumIdUnidadeIntermediaria($arrObjMdGdUnidadeArquivamentoDTO[$objMdGdArquivamentoDTO->getNumIdUnidadeCorrente()]);
            
            $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);

           /* // Faz o login na unidade intermediária
            SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SEI, null, null, 110000002);
             echo SessaoSEI::getInstance()->getNumIdUnidadeAtual();
            die('praxedes');*/

            // Encerra o processo na unidade intermediaria
            // $objMdGdArquivamentoRN->fecharProcedimentoArquivamento($objMdGdArquivamentoDTO);

        }

        LogSEI::getInstance()->gravar('Verificação de periodicidade realizada com sucesso!');
    }
}