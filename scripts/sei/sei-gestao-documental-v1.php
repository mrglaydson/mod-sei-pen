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
        id_despacho_arquivamento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        id_justificativa ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
        dta_arquivamento ' . $objMetaBD->tipoDataHora() . ' NOT NULL,
        guarda_corrente ' . $objMetaBD->tipoNumero() . ' NOT NULL,
        guarda_intermediaria ' . $objMetaBD->tipoNumero() . ' NOT NULL,
        sta_guarda ' . $objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
        sin_ativo ' . $objMetaBD->tipoTextoFixo(1) . ' NOT NULL  
    )');

    $objMetaBD->adicionarChavePrimaria('md_gd_arquivamento', 'pk_md_gd_arquivamento_id_arquivamento', array('id_arquivamento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_procedimento',  'md_gd_arquivamento', array('id_procedimento'), 'procedimento', array('id_procedimento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_documento',  'md_gd_arquivamento', array('id_despacho_arquivamento'), 'documento', array('id_documento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_md_gd_justificativa',  'md_gd_arquivamento', array('id_justificativa'), 'md_gd_justificativa', array('id_justificativa'));

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
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_md_gd_arquivamento',  'md_gd_desarquivamento', array('id_arquivamento'), 'md_gd_arquivamento', array('id_arquivamento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_procedimento',  'md_gd_desarquivamento', array('id_procedimento'), 'procedimento', array('id_procedimento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_documento',  'md_gd_desarquivamento', array('id_despacho_desarquivamento'), 'documento', array('id_documento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_md_gd_justificativa_desarquivamento',  'md_gd_desarquivamento', array('id_justificativa_desarquivamento'), 'md_gd_justificativa', array('id_justificativa'));


    $objInfraSequencia = new InfraSequencia($objBanco);

    if (!$objInfraSequencia->verificarSequencia('md_gd_desarquivamento')) {
        $objInfraSequencia->criarSequencia('md_gd_desarquivamento', '1', '1', '9999999999');
    }

    $objBanco->fecharConexao();
    echo "ATUALIZAÇÃO FINALIZADA COM SUCESSO! ";

} catch (Exception $e) {
    echo InfraException::inspecionar($e);
    LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
}
