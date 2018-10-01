<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdRecolhimentoDTO extends InfraDTO
{
    public function getStrNomeTabela()
    {
        return 'md_gd_recolhimento';
    }

    public function montar()
    {
               
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdRecolhimento',
            'id_recolhimento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdUsuario',
            'id_usuario');
          
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdUnidade',
            'id_unidade');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdListaRecolhimento',
            'id_lista_recolhimento');
      
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            'DataRecolhimento',
            'dth_recolhimento');
        
       
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeUsuario',
                                              'u.nome',
                                              'usuario u');
        
        # Assinatura do despacho de arquivamento
        $this->adicionarAtributo(InfraDTO::$PREFIXO_OBJ,'AssinaturaDTO');

        $this->configurarPK('IdRecolhimento', InfraDTO::$TIPO_PK_SEQUENCIAL);
        $this->configurarFK('IdUsuario', 'usuario u', 'u.id_usuario');

    }
    
    
  


}
