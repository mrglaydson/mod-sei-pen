<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdModeloDocumentoDTO extends InfraDTO {

  public function getStrNomeTabela() {
      return 'md_gd_modelo_documento';
  }

  public function montar() {
      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Nome', 'nome');
      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Valor', 'valor');

      $this->configurarPK('Nome', InfraDTO::$TIPO_PK_INFORMADO);
  }

}
