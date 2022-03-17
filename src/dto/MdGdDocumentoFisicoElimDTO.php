<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdDocumentoFisicoElimDTO extends InfraDTO
{
    public function getStrNomeTabela()
    {
        return 'md_gd_documento_fisico_elim';
    }

    public function montar()
    {

         $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdListaEliminacao',
            'id_lista_eliminacao');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
            'IdDocumento',
            'id_documento');

       
        $this->configurarPK('IdListaEliminacao',InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdDocumento',InfraDTO::$TIPO_PK_INFORMADO);

    }

}
