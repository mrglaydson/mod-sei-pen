<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdArquivamentoDTO extends InfraDTO
{
    public function getStrNomeTabela()
    {
        return 'md_gd_arquivamento';
    }

    public function montar()
    {

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdArquivamento',
            'id_arquivamento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
            'IdProcedimento',
            'id_procedimento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
            'IdDespachoArquivamento',
            'id_despacho_arquivamento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdJustificativa',
            'id_justificativa');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            'DataArquivamento',
            'dta_arquivamento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'GuardaCorrente',
            'guarda_corrente');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'GuardaIntermediaria',
            'guarda_intermediaria');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'StaGuarda',
            'sta_guarda');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'SinAtivo',
            'sin_ativo');


        $this->configurarPK('IdArquivamento', InfraDTO::$TIPO_PK_SEQUENCIAL);
        $this->configurarFK('IdProcedimento', 'procedimento p', 'p.id_procedimento');
        $this->configurarFK('IdDespachoArquivamento', 'documento d', 'd.id_documento');
        $this->configurarFK('IdJustificativa', 'justificativa j', 'j.id_justificativa');
    }

}
