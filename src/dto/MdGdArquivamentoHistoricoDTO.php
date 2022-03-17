<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdArquivamentoHistoricoDTO extends InfraDTO
{
    public function getStrNomeTabela()
    {
        return 'md_gd_arquivamento_historico';
    }

    public function montar()
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdArquivamentoHistorico',
            'id_arquivamento_historico');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdArquivamento',
            'id_arquivamento');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdUsuario',
            'id_usuario');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdUnidade',
            'id_unidade');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'SituacaoAntiga',
            'sta_situacao_antiga');
        
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'SituacaoAtual',
            'sta_situacao_atual');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'Descricao',
            'descricao');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            'Historico',
            'dth_historico');
                              
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeUsuario',
                                              'u.nome',
                                              'usuario u');
        
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaUnidade',
                                              'un.sigla',
                                              'unidade un');    

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                            'DescricaoUnidade',
                                            'un.descricao',
                                            'unidade un');
      

        $this->configurarPK('IdArquivamentoHistorico', InfraDTO::$TIPO_PK_SEQUENCIAL);
        $this->configurarFK('IdArquivamento', 'md_gd_arquivamento a', 'a.id_arquivamento');
        $this->configurarFK('IdUsuario', 'usuario u', 'u.id_usuario');
        $this->configurarFK('IdUnidade', 'unidade un', 'un.id_unidade');


    }
   
}
