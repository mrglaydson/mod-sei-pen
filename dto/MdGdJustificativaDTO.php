<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdJustificativaDTO extends InfraDTO
{
    public function getStrNomeTabela()
    {
        return 'md_gd_justificativa';
    }

    public function montar()
    {

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdJustificativa',
            'id_justificativa');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'StaTipo',
            'sta_tipo');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'Nome',
            'nome');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'Descricao',
            'descricao');

        $this->configurarPK('IdJustificativa', InfraDTO::$TIPO_PK_SEQUENCIAL);

    }

}
