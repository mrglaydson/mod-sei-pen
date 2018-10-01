<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdEliminacaoDTO extends InfraDTO
{
    public function getStrNomeTabela()
    {
        return 'md_gd_eliminacao';
    }

    public function montar()
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdEliminacao',
            'id_eliminacao');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdUsuario',
            'id_usuario');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'Assinante',
            'assinante');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdListaEliminacao',
            'id_lista_eliminacao');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdUnidade',
            'id_unidade');
         
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdVeiculoPublicacao',
            'id_veiculo_publicacao');
          
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdSecaoImprensaNacional',
            'id_secao_imprensa_nacional');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            'DataImprensa',
            'dth_data_imprensa');
        
           
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            'DataEliminacao',
            'dth_eliminacao');
               
       
         $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeUsuario',
                                              'u.nome',
                                              'usuario u');
         
      
         # Assinatura do despacho de arquivamento
        $this->adicionarAtributo(InfraDTO::$PREFIXO_OBJ,'AssinaturaDTO');

        $this->configurarPK('IdEliminacao', InfraDTO::$TIPO_PK_SEQUENCIAL);
        $this->configurarFK('IdUsuario', 'usuario u', 'u.id_usuario');

    }
   
}
