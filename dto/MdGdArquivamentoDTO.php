<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdArquivamentoDTO extends InfraDTO
{
    public function getStrNomeTabela()
    {
        return 'md_gd_arquivamento';
    }

    public function montar()
    {

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdArquivamento',
            'id_arquivamento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
            'IdProcedimento',
            'id_procedimento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
            'IdDespachoArquivamento',
            'id_despacho_arquivamento');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdJustificativa',
            'id_justificativa');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdUsuario',
            'id_usuario');
         
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdUnidadeCorrente',
            'id_unidade_corrente');
          
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdUnidadeIntermediaria',
            'id_unidade_intermediaria');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdListaEliminacao',
            'id_lista_eliminacao');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdListaRecolhimento',
            'id_lista_recolhimento');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            'DataArquivamento',
            'dta_arquivamento');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            'DataGuardaCorrente',
            'dta_guarda_corrente');
                
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            'DataGuardaIntermediaria',
            'dta_guarda_intermediaria');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'GuardaCorrente',
            'guarda_corrente');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'GuardaIntermediaria',
            'guarda_intermediaria');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'StaGuarda',
            'sta_guarda');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'Situacao',
            'sta_situacao');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'StaDestinacaoFinal',
            'sta_destinacao_final');  
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'SinCondicionante',
            'sin_condicionante');
                
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'SinAtivo',
            'sin_ativo');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'ObservacaoEliminacao',
            'observacao_eliminacao');
            
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'ObservacaoRecolhimento',
            'observacao_recolhimento');
        
         $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeUsuario',
                                              'u.nome',
                                              'usuario u');
        
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                     'DescricaoUnidadeCorrente',
                                     'unc.descricao',
                                     'unidade unc');
        
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,
                                              'IdProtocoloProcedimento',
                                              'p.id_procedimento',
                                              'procedimento p');
        
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'ProtocoloFormatado',
                                              'pro.protocolo_formatado',
                                              'protocolo pro');
        
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                            'StaNivelAcessoGlobal',
                                            'pro.sta_nivel_acesso_global',
                                            'protocolo pro');

         $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                              'IdTipoProcedimento',
                                              'p.id_tipo_procedimento',
                                              'procedimento p');
         
         $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeTipoProcedimento',
                                              't.nome',
                                              'tipo_procedimento t');
             
         # Assinatura do despacho de arquivamento
        $this->adicionarAtributo(InfraDTO::$PREFIXO_OBJ,'AssinaturaDTO');

        $this->configurarPK('IdArquivamento', InfraDTO::$TIPO_PK_SEQUENCIAL);
        $this->configurarFK('IdProcedimento', 'procedimento p', 'p.id_procedimento');
        $this->configurarFK('IdProtocoloProcedimento', 'protocolo pro', 'pro.id_protocolo');
        $this->configurarFK('IdTipoProcedimento', 'tipo_procedimento t', 't.id_tipo_procedimento');
        $this->configurarFK('IdUsuario', 'usuario u', 'u.id_usuario');
        $this->configurarFK('IdUnidadeCorrente', 'unidade unc', 'unc.id_unidade');
        $this->configurarFK('IdUnidadeIntermediaria', 'unidade uni', 'uni.id_unidade');
        $this->configurarFK('IdListaEliminacao', 'md_gd_lista_eliminacao le', 'le.id_lista_eliminacao');
        $this->configurarFK('IdListaRecolhimento', 'md_gd_lista_eliminacao lr', 'lr.id_lista_recolhimento');

        $this->configurarFK('IdDespachoArquivamento', 'documento d', 'd.id_documento');
        $this->configurarFK('IdJustificativa', 'justificativa j', 'j.id_justificativa');
    }

}
