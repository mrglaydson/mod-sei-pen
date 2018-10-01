<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaRecolhimentoDTO extends InfraDTO
{
    public function getStrNomeTabela()
    {
        return 'md_gd_lista_recolhimento';
    }

    public function montar()
    {

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdListaRecolhimento',
            'id_lista_recolhimento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'Numero',
            'numero');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            'EmissaoListagem',
            'dth_emissao_listagem');
         
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'AnoLimiteInicio',
            'ano_limite_inicio');
             
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'AnoLimiteFim',
            'ano_limite_fim');
                       
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'QtdProcessos',
            'qtd_processos');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'Situacao',
            'situacao');
                       
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR,'ObjMdGdArquivamentoDTO');

        $this->configurarPK('IdListaRecolhimento', InfraDTO::$TIPO_PK_SEQUENCIAL);

    }

}
