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


      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
          'IdProcedimentoRecolhimento',
          'id_procedimento_recolhimento');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
          'IdDocumentoRecolhimento',
          'id_documento_recolhimento');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
          'IdUsuario',
          'id_usuario');

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
          'SinDocumentosFisicos',
          'sin_documentos_fisicos');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
          'Situacao',
          'situacao');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
          'Anotacao',
          'anotacao');
                       
      $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjMdGdArquivamentoDTO');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,
                                          'IdProtocoloProcedimentoRecolhimento',
                                          'p.id_procedimento',
                                          'procedimento p');


      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                            'ProtocoloProcedimentoRecolhimentoFormatado',
                                            'pro.protocolo_formatado',
                                            'protocolo pro');
                                              
      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                            'SiglaUsuario',
                                            'sigla',
                                            'usuario');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                            'NomeUsuario',
                                            'nome',
                                            'usuario');

      $this->configurarPK('IdListaRecolhimento', InfraDTO::$TIPO_PK_SEQUENCIAL);
      $this->configurarFK('IdProcedimentoRecolhimento', 'procedimento p', 'p.id_procedimento');
      $this->configurarFK('IdDocumentoRecolhimento', 'documento d', 'd.id_documento');

      $this->configurarFK('IdProtocoloProcedimentoRecolhimento', 'protocolo pro', 'pro.id_protocolo');
      $this->configurarFK('IdUsuario', 'usuario', 'id_usuario');

  }

}
