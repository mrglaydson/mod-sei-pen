<?php

/**
 * Migrao para alterao da base de dados do SEI para dar suporte a autenticao via e-cidado.
 *
 * @author Join Tecnologia
 */
try {

    require_once dirname(__FILE__) . '/../web/SEI.php';

    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '-1');

    session_start();

    SessaoSEI::getInstance(false);

    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(false);
    InfraDebug::getInstance()->setBolEcho(true);
    InfraDebug::getInstance()->limpar();

    $objBanco = BancoSEI::getInstance();
    $objBanco->abrirConexao();

    $objMetaBD = new InfraMetaBD($objBanco);

    // Criação da tabela de parâmetros
    $objBanco->executarSql('CREATE TABLE md_gd_parametro (
            nome  ' . $objMetaBD->tipoTextoVariavel(100) . '  NOT NULL ,
            valor ' . $objMetaBD->tipoTextoVariavel(50) . ' NOT NULL 
        )');

    $objMetaBD->adicionarChavePrimaria('md_gd_parametro', 'pk_md_gd_parametro_nome', array('nome'));

    // Criação da tabela de justificativas arquivamento e desarquivamento
    $objBanco->executarSql('CREATE TABLE md_gd_justificativa (
          id_justificativa ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
          sta_tipo ' . $objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
          nome ' . $objMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
          descricao ' . $objMetaBD->tipoTextoGrande() . ' NOT NULL
    )');

    $objMetaBD->adicionarChavePrimaria('md_gd_justificativa', 'pk_md_gd_justificativa_id_justificativa', array('id_justificativa'));

    $objInfraSequencia = new InfraSequencia($objBanco);

  if (!$objInfraSequencia->verificarSequencia('md_gd_justificativa')) {
      $objInfraSequencia->criarSequencia('md_gd_justificativa', '1', '1', '9999999999');
  }

    // Criação da tabela de arquivamentos
    $objBanco->executarSql(' CREATE TABLE md_gd_arquivamento (
        id_arquivamento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_procedimento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_usuario ' . $objMetaBD->tipoNumero() . ' NOT NULL,
        id_unidade_corrente ' . $objMetaBD->tipoNumero() . ' NOT NULL,
        id_unidade_intermediaria ' . $objMetaBD->tipoNumero() . ' ,
        id_despacho_arquivamento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_justificativa ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_lista_eliminacao ' . $objMetaBD->tipoNumeroGrande() . ' ,
        id_lista_recolhimento ' . $objMetaBD->tipoNumeroGrande() . ' ,
        dta_arquivamento ' . $objMetaBD->tipoDataHora() . ' NOT NULL,
        guarda_corrente ' . $objMetaBD->tipoNumero() . ' NOT NULL,
        guarda_intermediaria ' . $objMetaBD->tipoNumero() . ' NOT NULL,
        sta_guarda ' . $objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
        sin_ativo ' . $objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
        situacao ' . $objMetaBD->tipoTextoFixo(2) . ' NOT NULL,
        observacao_eliminacao ' . $objMetaBD->tipoTextoGrande() . ',
        observacao_recolhimento ' . $objMetaBD->tipoTextoGrande() . '
    )');

    $objMetaBD->adicionarChavePrimaria('md_gd_arquivamento', 'pk_md_gd_arquivamento_id_arquivamento', array('id_arquivamento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_procedimento', 'md_gd_arquivamento', array('id_procedimento'), 'procedimento', array('id_procedimento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_documento', 'md_gd_arquivamento', array('id_despacho_arquivamento'), 'documento', array('id_documento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_md_gd_justificativa', 'md_gd_arquivamento', array('id_justificativa'), 'md_gd_justificativa', array('id_justificativa'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_usuario', 'md_gd_arquivamento', array('id_usuario'), 'usuario', array('id_usuario'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_unidade_corrente', 'md_gd_arquivamento', array('id_unidade_corrente'), 'unidade', array('id_unidade'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_unidade_intermediaria', 'md_gd_arquivamento', array('id_unidade_intermediaria'), 'unidade', array('id_unidade'));

    $objInfraSequencia = new InfraSequencia($objBanco);

  if (!$objInfraSequencia->verificarSequencia('md_gd_arquivamento')) {
      $objInfraSequencia->criarSequencia('md_gd_arquivamento', '1', '1', '9999999999');
  }

    // Criação da tabela de desarquivamento
    $objBanco->executarSql(' CREATE TABLE md_gd_desarquivamento (
        id_desarquivamento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_arquivamento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_procedimento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_despacho_desarquivamento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_justificativa_desarquivamento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        dta_desarquivamento ' . $objMetaBD->tipoDataHora() . ' NOT NULL
    )');

    $objMetaBD->adicionarChavePrimaria('md_gd_desarquivamento', 'pk_gd_desarquivamento_id_arquivamento', array('id_desarquivamento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_md_gd_arquivamento', 'md_gd_desarquivamento', array('id_arquivamento'), 'md_gd_arquivamento', array('id_arquivamento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_procedimento', 'md_gd_desarquivamento', array('id_procedimento'), 'procedimento', array('id_procedimento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_documento', 'md_gd_desarquivamento', array('id_despacho_desarquivamento'), 'documento', array('id_documento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_md_gd_justificativa_desarquivamento', 'md_gd_desarquivamento', array('id_justificativa_desarquivamento'), 'md_gd_justificativa', array('id_justificativa'));


    $objInfraSequencia = new InfraSequencia($objBanco);

  if (!$objInfraSequencia->verificarSequencia('md_gd_desarquivamento')) {
      $objInfraSequencia->criarSequencia('md_gd_desarquivamento', '1', '1', '9999999999');
  }

    // Função anonima para inserção dos parãmetros
    $fnParametroIncluir = function($nome, $valor) use($objBanco) {
        $objMdGdParametroDTO = new MdGdParametroDTO();
        $objMdGdParametroDTO->setStrNome($nome);
        $objMdGdParametroDTO->setStrValor($valor);

        $objMdGdParametroBD = new MdGdParametroBD($objBanco);
        $objMdGdParametroBD->cadastrar($objMdGdParametroDTO);
    };

    $fnParametroIncluir('UNIDADE_ARQUIVAMENTO', ' ');
    $fnParametroIncluir('DESPACHO_ARQUIVAMENTO', ' ');
    $fnParametroIncluir('DESPACHO_DESARQUIVAMENTO', ' ');
    $fnParametroIncluir('LISTAGEM_ELIMINACAO', ' ');

    // Criação da tabela de modelos de documento
    $objBanco->executarSql('CREATE TABLE md_gd_modelo_documento (
            nome  ' . $objMetaBD->tipoTextoVariavel(100) . '  NOT NULL ,
            valor ' . $objMetaBD->tipoTextoGrande() . ' NOT NULL 
        )');

    $objMetaBD->adicionarChavePrimaria('md_gd_modelo_documento', 'pk_md_gd_modelo_documento_nome', array('nome'));

    // Criação da tabela de unidades de arquivamento
    $objBanco->executarSql('CREATE TABLE md_gd_unidade_arquivamento (
          id_unidade_arquivamento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
          id_unidade_origem ' . $objMetaBD->tipoNumero() . ' NOT NULL,
          id_unidade_destino ' . $objMetaBD->tipoNumero() . ' NOT NULL
    )');

    $objMetaBD->adicionarChavePrimaria('md_gd_unidade_arquivamento', 'pk_md_gd_justificativa_id_unidade_arquivamento', array('id_unidade_arquivamento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_unidade_arquivamento_origem', 'md_gd_unidade_arquivamento', array('id_unidade_origem'), 'unidade', array('id_unidade'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_unidade_arquivamento_destino', 'md_gd_unidade_arquivamento', array('id_unidade_destino'), 'unidade', array('id_unidade'));

    $objInfraSequencia = new InfraSequencia($objBanco);

  if (!$objInfraSequencia->verificarSequencia('md_gd_unidade_arquivamento')) {
      $objInfraSequencia->criarSequencia('md_gd_unidade_arquivamento', '1', '1', '9999999999');
  }

    // Função anonima para inserção dos parãmetros
    $fnParametroModeloDocumento = function($nome, $valor) use($objBanco) {
        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome($nome);
        $objMdGdModeloDocumentoDTO->setStrValor($valor);

        $objMdGdModeloDocumentoBD = new MdGdModeloDocumentoBD($objBanco);
        $objMdGdModeloDocumentoBD->cadastrar($objMdGdModeloDocumentoDTO);
    };

    $fnParametroModeloDocumento('MODELO_DESPACHO_ARQUIVAMENTO', ' ');
    $fnParametroModeloDocumento('MODELO_DESPACHO_DESARQUIVAMENTO', ' ');
    $fnParametroModeloDocumento('MODELO_LISTAGEM_ELIMINACAO', ' ');

    // Criação da tabela da listagem de eliminação
    $objBanco->executarSql('CREATE TABLE md_gd_lista_eliminacao (
        id_lista_eliminacao ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_procedimento_eliminacao ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_documento_eliminacao ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        numero ' . $objMetaBD->tipoTextoVariavel(50) . ' NOT NULL,
        dth_emissao_listagem ' . $objMetaBD->tipoDataHora() . ' NOT NULL,
        ano_limite_inicio ' . $objMetaBD->tipoNumero() . ' NOT NULL,
        ano_limite_fim ' . $objMetaBD->tipoNumero() . ' NOT NULL
    )');

    $objMetaBD->adicionarChavePrimaria('md_gd_lista_eliminacao', 'pk_md_gd_lista_eliminacao_id_lista_eliminacao', array('id_lista_eliminacao'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_lista_eliminacao_procedimento', 'md_gd_lista_eliminacao', array('id_procedimento_eliminacao'), 'procedimento', array('id_procedimento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_lista_eliminacao_documento', 'md_gd_lista_eliminacao', array('id_documento_eliminacao'), 'documento', array('id_documento'));

    $objInfraSequencia = new InfraSequencia($objBanco);

  if (!$objInfraSequencia->verificarSequencia('md_gd_lista_eliminacao')) {
      $objInfraSequencia->criarSequencia('md_gd_lista_eliminacao', '1', '1', '9999999999');
  }

    // Cria a tabela ternária entre a listagem de eliminação e os procedimentos
    $objBanco->executarSql('CREATE TABLE md_gd_lista_elim_procedimento (
        id_lista_eliminacao ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_procedimento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL
    )');

    $objMetaBD->adicionarChavePrimaria('md_gd_lista_elim_procedimento', 'pk_md_gd_lista_elim_procedimento', array('id_lista_eliminacao', 'id_procedimento'));
    $objMetaBD->adicionarChaveEstrangeira('md_gd_lista_elim_procedimento_lista_eliminacao', 'md_gd_lista_elim_procedimento', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));
    $objMetaBD->adicionarChaveEstrangeira('md_gd_lista_elim_procedimento_procedimento', 'md_gd_lista_elim_procedimento', array('id_procedimento'), 'procedimento', array('id_procedimento'));


    // Criação da tabela da listagem de recolhimento
    $objBanco->executarSql('CREATE TABLE md_gd_lista_recolhimento (
        id_lista_recolhimento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        numero ' . $objMetaBD->tipoTextoVariavel(50) . ' NOT NULL,
        dth_emissao_listagem ' . $objMetaBD->tipoDataHora() . ' NOT NULL,
        ano_limite_inicio ' . $objMetaBD->tipoNumero() . ' NOT NULL,
        ano_limite_fim ' . $objMetaBD->tipoNumero() . ' NOT NULL,
        qtd_processos ' . $objMetaBD->tipoNumero() . ' NOT NULL 
    )');

    $objMetaBD->adicionarChavePrimaria('md_gd_lista_recolhimento', 'pk_md_gd_lista_recolhimento_id_lista_recolhimento', array('id_lista_recolhimento'));

    $objInfraSequencia = new InfraSequencia($objBanco);

  if (!$objInfraSequencia->verificarSequencia('md_gd_lista_recolhimento')) {
      $objInfraSequencia->criarSequencia('md_gd_lista_recolhimento', '1', '1', '9999999999');
  }

    // Cria a tabela ternária entre a listagem de recolhimento
    $objBanco->executarSql('CREATE TABLE md_gd_lista_recol_procedimento (
        id_lista_recolhimento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_procedimento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL
    )');

    $objMetaBD->adicionarChavePrimaria('md_gd_lista_recol_procedimento', 'pk_md_gd_lista_recol_procedimento', array('id_lista_recolhimento', 'id_procedimento'));
    $objMetaBD->adicionarChaveEstrangeira('md_gd_lista_recol_procedimento_lista_recolhimento', 'md_gd_lista_recol_procedimento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));
    $objMetaBD->adicionarChaveEstrangeira('md_gd_lista_recol_procedimento_procedimento', 'md_gd_lista_recol_procedimento', array('id_procedimento'), 'procedimento', array('id_procedimento'));


    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_lista_eliminacao', 'md_gd_arquivamento', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_lista_recolhimento', 'md_gd_arquivamento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));

    $objBanco->fecharConexao();

    $objBanco->fecharConexao();
    echo "ATUALIZAÇÃO FINALIZADA COM SUCESSO! ";
} catch (Exception $e) {
    echo InfraException::inspecionar($e);
    LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
}
