<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdAnotacaoPendenciaDTO extends InfraDTO
{
  public function getStrNomeTabela()
    {
      return 'md_gd_anotacao_pendencia';
  }

  public function montar()
    {

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
          'IdAnotacaoPendencia',
          'id_anotacao_pendencia');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
          'IdProcedimento',
          'id_procedimento');       
            
      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
          'Anotacao',
          'anotacao');
        
       $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                            'IdProcedimento',
                                            'p.id_procedimento',
                                            'procedimento p');
             
      $this->configurarPK('IdAnotacaoPendencia', InfraDTO::$TIPO_PK_SEQUENCIAL);
      $this->configurarFK('IdProcedimento', 'procedimento p', 'p.id_procedimento');
  }

}
