<?php

require_once DIR_SEI_WEB.'/SEI.php';

/**
 * Regra de neg�cio para o par�metros do m�dulo PEN
 */
class TramitaEmBlocoProtocoloRN extends InfraRN
{
    /**
     * Inicializa o obj do banco da Infra
     * @return obj
     */
    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    protected function listarProtocolosBlocoConectado(TramitaEmBlocoProtocoloDTO $parObjTramitaEmBlocoProtocoloDTO)
    {
        try {

            $ret = array();

            //Valida Permissao
            // SessaoSEI::getInstance()->validarAuditarPermissao('rel_bloco_protocolo_listar', __METHOD__, $parObjRelBlocoProtocoloDTO);

            $parObjRelBlocoProtocoloDTO = InfraString::prepararPesquisaDTO($parObjTramitaEmBlocoProtocoloDTO, "PalavrasPesquisa", "IdxRelBlocoProtocolo");
            $parObjRelBlocoProtocoloDTO->setStrStaNivelAcessoGlobalProtocolo(ProtocoloRN::$NA_SIGILOSO, InfraDTO::$OPER_DIFERENTE);
            $arrObjRelProtocoloBlocoDTO = $this->listar($parObjRelBlocoProtocoloDTO);

            foreach ($arrObjRelProtocoloBlocoDTO as $dto) {

                $objPenLoteProcedimentoDTO = new PenLoteProcedimentoDTO();
                $objPenLoteProcedimentoDTO->retNumIdLote();
                $objPenLoteProcedimentoDTO->retDblIdProcedimento();
                $objPenLoteProcedimentoDTO->retStrProcedimentoFormatado();
                $objPenLoteProcedimentoDTO->retNumIdAndamento();
                $objPenLoteProcedimentoDTO->retStrUnidadeDestino();
                $objPenLoteProcedimentoDTO->retStrNomeUsuario();
                $objPenLoteProcedimentoDTO->retDthRegistro();
                $objPenLoteProcedimentoDTO->setNumMaxRegistrosRetorno(1);
                $objPenLoteProcedimentoDTO->setOrdNumIdLote(InfraDTO::$TIPO_ORDENACAO_DESC);
                $objPenLoteProcedimentoDTO->setDblIdProcedimento($dto->getDblIdProtocolo());

                $objPenLoteProcedimentoRN = new PenLoteProcedimentoRN();
                $objPenLoteProcedimentoDTO = $objPenLoteProcedimentoRN->consultarLoteProcedimento($objPenLoteProcedimentoDTO);

                $dto->setObjPenLoteProcedimentoDTO($objPenLoteProcedimentoDTO);
                $ret[] = $dto;
            }

            return $ret;
        } catch (Exception $e) {
            throw new InfraException('Erro listando protocolos do bloco.', $e);
        }
    }

    /**
     * M�todo utilizado para exclus�o de dados.
     * @param TramitaEmBlocoProtocoloDTO $objDTO
     * @return array
     * @throws InfraException
     */
    protected function listarControlado(TramitaEmBlocoProtocoloDTO $objDTO)
    {
        try {
            //Valida Permiss�o
            //SessaoSEI::getInstance()->validarAuditarPermissao('pen_expedir_lote', __METHOD__, $objDTO);

            $objTramitaEmBlocoProtocoloBD = new TramitaEmBlocoProtocoloBD($this->getObjInfraIBanco());
            $arrTramitaEmBlocoProtocoloDTO = $objTramitaEmBlocoProtocoloBD->listar($objDTO);

            return $arrTramitaEmBlocoProtocoloDTO;
        } catch (\Exception $e) {
            throw new InfraException('Falha na listagem de pend�ncias de tr�mite de processos em lote.', $e);
        }
    }

    /**
     * M�todo utilizado para exclus�o de dados.
     * @param array $arrayObjDTO
     * @return array
     * @throws InfraException
     */
    protected function excluirControlado(array $arrayObjDTO)
    {
        try {
            $arrayExcluido = array();
            foreach ($arrayObjDTO as $objDTO) {
                $objBD = new TramitaEmBlocoProtocoloBD(BancoSEI::getInstance());
                $arrayExcluido[] = $objBD->excluir($objDTO);
            }
            return $arrayExcluido;
        } catch (Exception $e) {
            throw new InfraException('Erro excluindo Bloco.', $e);
        }
    }
}
