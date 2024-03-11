<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaRecolProcedimentoDTO extends InfraDTO
{
  public function getStrNomeTabela()
    {
      return 'md_gd_lista_recol_procedimento';
  }

  public function montar()
    {

       $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
          'IdListaRecolhimento',
          'id_lista_recolhimento');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
          'IdProcedimento',
          'id_procedimento');

       
      $this->configurarPK('IdListaRecolhimento', InfraDTO::$TIPO_PK_INFORMADO);
      $this->configurarPK('IdProcedimento', InfraDTO::$TIPO_PK_INFORMADO);

  }

}
