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

    // Criação da tabela de modelos de documento
    $objBanco->executarSql('CREATE TABLE md_gd_modelo_documento (
            nome  ' . $objMetaBD->tipoTextoVariavel(100) . '  NOT NULL ,
            valor ' . $objMetaBD->tipoTextoGrande() . ' NOT NULL 
        )');

    $objMetaBD->adicionarChavePrimaria('md_gd_modelo_documento', 'pk_md_gd_modelo_documento_nome', array('nome'));

    // Criação da tabela de unidades de arquivamento
    $objBanco->executarSql('CREATE TABLE md_gd_unidade_arquivamento (
          gd_id_unidade_arquivamento ' . $objMetaBD->tipoNumeroGrande() . ' NOT NULL,
          id_unidade_origem ' . $objMetaBD->tipoNumero() . ' NOT NULL,
          id_unidade_destino ' . $objMetaBD->tipoNumero() . ' NOT NULL
    )');

    $objMetaBD->adicionarChavePrimaria('md_gd_unidade_arquivamento', 'pk_md_gd_justificativa_gd_id_unidade_arquivamento', array('gd_id_unidade_arquivamento'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_unidade_arquivamento_origem',  'md_gd_unidade_arquivamento', array('id_unidade_origem'), 'unidade', array('id_unidade'));
    $objMetaBD->adicionarChaveEstrangeira('fk_md_gd_unidade_arquivamento_destino',  'md_gd_unidade_arquivamento', array('id_unidade_destino'), 'unidade', array('id_unidade'));

    $objInfraSequencia = new InfraSequencia($objBanco);

    if (!$objInfraSequencia->verificarSequencia('md_gd_unidade_arquiamento')) {
        $objInfraSequencia->criarSequencia('md_gd_unidade_arquiamento', '1', '1', '9999999999');
    }
    
     // Função anonima para inserção dos parãmetros
    $fnParametroModeloDocumento = function($nome, $valor) use($objBanco){
        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome($nome);
        $objMdGdModeloDocumentoDTO->setStrValor($valor);
        
        $objMdGdModeloDocumentoBD = new MdGdModeloDocumentoBD($objBanco);
        $objMdGdModeloDocumentoBD->cadastrar($objMdGdModeloDocumentoDTO);
    };
    
    $fnParametroModeloDocumento('MODELO_DESPACHO_ARQUIVAMENTO', ' ');
    $fnParametroModeloDocumento('MODELO_DESPACHO_DESARQUIVAMENTO', ' ');
    
    $objBanco->fecharConexao();
    echo "ATUALIZAÇÃO FINALIZADA COM SUCESSO! ";

} catch (Exception $e) {
    echo InfraException::inspecionar($e);
    LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
}
