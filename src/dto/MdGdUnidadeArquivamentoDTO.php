<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdUnidadeArquivamentoDTO extends InfraDTO {

  public function getStrNomeTabela() {
      return 'md_gd_unidade_arquivamento';
  }

  public function montar() {

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUnidadeArquivamento', 'id_unidade_arquivamento');
      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUnidadeOrigem', 'id_unidade_origem');
      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUnidadeDestino', 'id_unidade_destino');
        
      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SiglaUnidadeOrigem', 'u.sigla', 'unidade u');
      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'DescricaoUnidadeOrigem', 'u.descricao', 'unidade u');
        
      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SiglaUnidadeDestino', 'u2.sigla', 'unidade u2');
      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'DescricaoUnidadeDestino', 'u2.descricao', 'unidade u2');
        
      $this->configurarPK('IdUnidadeArquivamento', InfraDTO::$TIPO_PK_SEQUENCIAL);
      $this->configurarFK('IdUnidadeOrigem', 'unidade u', 'u.id_unidade');
      $this->configurarFK('IdUnidadeDestino', 'unidade u2', 'u2.id_unidade');
  }

}
