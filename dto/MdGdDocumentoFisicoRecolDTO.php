<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdDocumentoFisicoRecolDTO extends InfraDTO
{
    public function getStrNomeTabela()
    {
        return 'md_gd_documento_fisico_recol';
    }

    public function montar()
    {

         $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdListaRecolhimento',
            'id_lista_recolhimento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
            'IdDocumento',
            'id_documento');

       
        $this->configurarPK('IdListaRecolhimento',InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdDocumento',InfraDTO::$TIPO_PK_INFORMADO);

    }

}
