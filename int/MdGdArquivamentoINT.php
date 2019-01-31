<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Description of ArquivamentoINT
 *
 * @author Eduardo
 */
class MdGdArquivamentoINT extends InfraINT {

    public static function montarSelectSituacoesArquivamento($strItemSelecionado = '') {
        return InfraINT::montarSelectArray('', '', $strItemSelecionado, MdGdArquivamentoRN::obterSituacoesArquivamento());
    }

    public static function montarSelectGuardasArquivamento($strItemSelecionado = '') {
        return InfraINT::montarSelectArray('', '', $strItemSelecionado, MdGdArquivamentoRN::obterGuardasArquivamento());
    }

    public static function montarSelectDestinacoesFinalArquivamento($strItemSelecionado = '') {
        return InfraINT::montarSelectArray('', '', $strItemSelecionado, MdGdArquivamentoRN::obterDestinacoesFinalArquivamento());
    }

    public static function montarSelectCondicionantesArquivamento($strItemSelecionado = '') {
        return InfraINT::montarSelectArray('', '', $strItemSelecionado, ['S' => 'Com Condicionante', 'N' => 'Sem Condicionante']);
    }

    public static function montarSelectUnidadesArquivamento($numIdUnidadeSelecionada = '') {
        // Obtem as unidades de arquivamento mapeadas
        $objMdGdUnidadeArquivamento = new MdGdUnidadeArquivamentoDTO();
        $objMdGdUnidadeArquivamento->retNumIdUnidadeOrigem();
        $objMdGdUnidadeArquivamento->retNumIdUnidadeArquivamento();
        $objMdGdUnidadeArquivamento->setNumIdUnidadeOrigem($numIdUnidadeSelecionada, InfraDTO::$OPER_DIFERENTE);

        $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
        $arrObjMdGdUnidadeArquivamento = $objMdGdUnidadeArquivamentoRN->listar($objMdGdUnidadeArquivamento);

        $arrIdUnidades = InfraArray::mapearArrInfraDTO($arrObjMdGdUnidadeArquivamento, 'IdUnidadeOrigem', 'IdUnidadeOrigem');

        // Realiza os filtros das unidades
        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->retNumIdUnidade();
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->retStrDescricao();
        $objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

        if ($arrIdUnidades) {
            $objUnidadeDTO->setNumIdUnidade($arrIdUnidades, InfraDTO::$OPER_NOT_IN);
        }

        $objUnidadeRN = new UnidadeRN();
        $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);

        foreach ($arrObjUnidadeDTO as $objUnidadeDTO) {
            $objUnidadeDTO->setStrSigla(UnidadeINT::formatarSiglaDescricao($objUnidadeDTO->getStrSigla(), $objUnidadeDTO->getStrDescricao()));
        }

        return InfraINT::montarSelectArrInfraDTO('', '', $numIdUnidadeSelecionada, $arrObjUnidadeDTO, 'IdUnidade', 'Sigla');
    }

    public static function montarSelectAjaxUnidadesArquivamento($strPalavrasPesquisa = '') {
        // Obtem as unidades de arquivamento mapeadas
        $objMdGdUnidadeArquivamento = new MdGdUnidadeArquivamentoDTO();
        $objMdGdUnidadeArquivamento->retNumIdUnidadeOrigem();
        $objMdGdUnidadeArquivamento->retNumIdUnidadeArquivamento();

        $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
        $arrObjMdGdUnidadeArquivamento = $objMdGdUnidadeArquivamentoRN->listar($objMdGdUnidadeArquivamento);

        $arrIdUnidades = InfraArray::mapearArrInfraDTO($arrObjMdGdUnidadeArquivamento, 'IdUnidadeOrigem', 'IdUnidadeOrigem');

        // Realiza os filtros de unidade
        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->retNumIdUnidade();
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->retStrDescricao();
        $objUnidadeDTO->setNumMaxRegistrosRetorno(50);
        $objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

        if ($arrIdUnidades) {
            $objUnidadeDTO->setNumIdUnidade($arrIdUnidades, InfraDTO::$OPER_NOT_IN);
        }

        if ($strPalavrasPesquisa != '') {
            $objUnidadeDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);
        }

        $objUnidadeRN = new UnidadeRN();
        $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);

        foreach ($arrObjUnidadeDTO as $objUnidadeDTO) {
            $objUnidadeDTO->setStrSigla(UnidadeINT::formatarSiglaDescricao($objUnidadeDTO->getStrSigla(), $objUnidadeDTO->getStrDescricao()));
        }

        return $arrObjUnidadeDTO;
    }

}
