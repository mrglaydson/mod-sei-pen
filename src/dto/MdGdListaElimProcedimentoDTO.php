<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaElimProcedimentoDTO extends InfraDTO
{
  public function getStrNomeTabela()
    {
      return 'md_gd_lista_elim_procedimento';
  }

  public function montar()
    {

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
          'IdListaEliminacao',
          'id_lista_eliminacao');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
          'IdProcedimento',
          'id_procedimento');

       
      $this->configurarPK('IdListaEliminacao', InfraDTO::$TIPO_PK_INFORMADO);
      $this->configurarPK('IdProcedimento', InfraDTO::$TIPO_PK_INFORMADO);

  }

}
