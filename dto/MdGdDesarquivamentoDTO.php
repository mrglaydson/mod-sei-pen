<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdDesarquivamentoDTO extends InfraDTO
{
    public function getStrNomeTabela()
    {
        return 'md_gd_desarquivamento';
    }

    public function montar()
    {

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdDesarquivamento',
            'id_desarquivamento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdArquivamento',
            'id_arquivamento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
            'IdProcedimento',
            'id_procedimento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
            'IdDespachoDesarquivamento',
            'id_despacho_desarquivamento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdJustificativa',
            'id_justificativa_desarquivamento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            'DataDesarquivamento',
            'dta_desarquivamento');


        $this->configurarPK('IdDesarquivamento', InfraDTO::$TIPO_PK_SEQUENCIAL);
        $this->configurarFK('IdArquivamento', 'arquivamento a', 'a.id_arquivamento');
        $this->configurarFK('IdProcedimento', 'procedimento p', 'p.id_procedimento');
        $this->configurarFK('IdDespachoArquivamento', 'documento d', 'd.id_documento');
        $this->configurarFK('IdJustificativa', 'justificativa j', 'j.id_justificativa');
    }

}
