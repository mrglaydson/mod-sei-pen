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

}
