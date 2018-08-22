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
    
    
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_lista_eliminacao',   'md_gd_arquivamento', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_lista_recolhimento', 'md_gd_arquivamento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));

    $objBanco->fecharConexao();
    echo "ATUALIZAÇÃO FINALIZADA COM SUCESSO! ";
} catch (Exception $e) {
    echo InfraException::inspecionar($e);
    LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
}
