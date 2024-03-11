<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 20/12/2007 - criado por marcio_db
*
* Versão do Gerador de Código: 1.12.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdPesquisarPendenciasArquivamentoDTO extends InfraDTO {

  public function getStrNomeTabela() {
     return null;
  }

  public function montar() {
    $this->adicionarAtributo(InfraDTO::$PREFIXO_DTH, 'PeriodoInicial');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_DTH, 'PeriodoFinal');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdTipoProcedimento');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdProtocoloAssunto');
    
  }
}
?>