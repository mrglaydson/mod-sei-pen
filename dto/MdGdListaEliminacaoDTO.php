<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaEliminacaoDTO extends InfraDTO
{
    public function getStrNomeTabela()
    {
        return 'md_gd_lista_eliminacao';
    }

    public function montar()
    {

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdListaEliminacao',
            'id_lista_eliminacao');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
            'IdProcedimentoEliminacao',
            'id_procedimento_eliminacao');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
            'IdDocumentoEliminacao',
            'id_documento_eliminacao');

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
                       
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'Situacao',
            'situacao');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'QtdProcessos',
            'qtd_processos');
                
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,
                                              'IdProtocoloProcedimentoEliminacao',
                                              'p.id_procedimento',
                                              'procedimento p');
        
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'ProtocoloProcedimentoEliminacaoFormatado',
                                              'pro.protocolo_formatado',
                                              'protocolo pro');
        
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR,'ObjMdGdArquivamentoDTO');

        $this->configurarPK('IdListaEliminacao', InfraDTO::$TIPO_PK_SEQUENCIAL);
        $this->configurarFK('IdProcedimentoEliminacao', 'procedimento p', 'p.id_procedimento');
        $this->configurarFK('IdDocumentoEliminacao', 'documento d', 'd.id_documento');
       
        $this->configurarFK('IdProtocoloProcedimentoEliminacao', 'protocolo pro', 'pro.id_protocolo');

    }

}
