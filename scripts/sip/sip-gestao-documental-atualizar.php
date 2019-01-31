<?php

/**
 * Migrao para alterao da base de dados do SEI para dar suporte a autenticao via e-cidado.
 *
 * @author Join Tecnologia
 */
try {
    require_once dirname(__FILE__) . '/../web/Sip.php';

    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '-1');

    session_start();

    SessaoSip::getInstance(false);

    $id_sistema = '';
    $id_perfil = '';
    $id_menu = '';
    $id_item_menu_pai = '';

    //Consulta do Sistema
    $sistemaDTO = new SistemaDTO();
    $sistemaDTO->setStrSigla('SEI');
    $sistemaDTO->setNumRegistrosPaginaAtual(1);
    $sistemaDTO->retNumIdSistema();

    $sistemaRN = new SistemaRN();
    $sistemaDTO = $sistemaRN->consultar($sistemaDTO);

    if (!empty($sistemaDTO)) {
        $id_sistema = $sistemaDTO->getNumIdSistema();
    }

    //Consulta do Menu
    $menuDTO = new MenuDTO();
    $menuDTO->setNumIdSistema($id_sistema);
    $menuDTO->setNumRegistrosPaginaAtual(1);
    $menuDTO->retNumIdMenu();

    $menuRN = new MenuRN();
    $menuDTO = $menuRN->consultar($menuDTO);

    if (!empty($menuDTO)) {
        $id_menu = $menuDTO->getNumIdMenu();
    }

    //Consulta do Perfil
    $perfilDTO = new PerfilDTO();
    $perfilDTO->setStrNome('%Administrador%', InfraDTO::$OPER_LIKE);
    $perfilDTO->setNumIdSistema($id_sistema);
    $perfilDTO->setNumRegistrosPaginaAtual(1);
    $perfilDTO->retNumIdPerfil();

    $perfilRN = new PerfilRN();
    $perfilDTO = $perfilRN->consultar($perfilDTO);

    if (!empty($perfilDTO)) {
        $id_perfil = $perfilDTO->getNumIdPerfil();
    }

    //Consulta do Item de menu pai
    $itemMenuDTO = new ItemMenuDTO();
    $itemMenuDTO->setStrRotulo('Administração', InfraDTO::$OPER_LIKE);
    $itemMenuDTO->setNumIdSistema($id_sistema);
    $itemMenuDTO->setNumRegistrosPaginaAtual(1);
    $itemMenuDTO->retNumIdItemMenu();

    $itemMenuRN = new ItemMenuRN();
    $itemMenuDTO = $itemMenuRN->consultar($itemMenuDTO);

    if (!empty($itemMenuDTO)) {
        $id_item_menu_pai = $itemMenuDTO->getNumIdItemMenu();
    }

    //Cria função genérica de cadastro de recursos
    $fnCadastrarRecurso = function ($id_sistema, $nome, $descricao, $caminho, $ativo) {
        $recursoDTO = new RecursoDTO();
        $recursoDTO->setNumIdSistema($id_sistema);
        $recursoDTO->setStrNome($nome);
        $recursoDTO->setStrDescricao($descricao);
        $recursoDTO->setStrCaminho($caminho);
        $recursoDTO->setStrSinAtivo($ativo);

        $recurtoRN = new RecursoRN();
        $recursoDTO = $recurtoRN->cadastrar($recursoDTO);

        return $recursoDTO->getNumIdRecurso();
    };

    // Cria a função genéria para o cadastramento de menus
    $fnItemMenu = function ($id_menu, $id_item_menu_pai, $id_sistema, $id_recurso_listar, $rotulo, $nova_janela, $ativo, $sequencia) {
        $itemMenuNovoDTO = new ItemMenuDTO();
        $itemMenuNovoDTO->setNumIdMenuPai($id_menu);
        $itemMenuNovoDTO->setNumIdItemMenuPai($id_item_menu_pai);
        $itemMenuNovoDTO->setNumIdSistema($id_sistema);
        $itemMenuNovoDTO->setNumIdRecurso($id_recurso_listar);
        $itemMenuNovoDTO->setStrRotulo($rotulo);
        $itemMenuNovoDTO->setStrDescricao(null);
        $itemMenuNovoDTO->setStrSinNovaJanela($nova_janela);
        $itemMenuNovoDTO->setStrSinAtivo($ativo);
        $itemMenuNovoDTO->setNumSequencia($sequencia);
        $itemMenuNovoDTO->setNumIdMenu($id_menu);

        $itemMenuNovoRN = new ItemMenuRN();
        $itemMenuNovoDTO = $itemMenuNovoRN->cadastrar($itemMenuNovoDTO);
        return $itemMenuNovoDTO->getNumIdItemMenu();
    };


    ###########################################################RECURSOS#############################################################################################
    //Cadastra o recurso de edição de parâmetros do módulo
    $id_recurso_alterar_parametros = $fnCadastrarRecurso($id_sistema, 'gestao_documental_parametros_alterar', 'Alteração dos parâmetros do módulo de gestão documental', 'controlador.php?acao=gd_parametros_alterar', 'S');

    // Cadastra o recurso de listagem de justificativas
    $id_recurso_listar_justificativas = $fnCadastrarRecurso($id_sistema, 'gestao_documental_justificativas_listar', 'Listagem das justificativas de arquivamento e desarquivamento', 'controlador.php?acao=gd_justificativas_listar', 'S');

    // Cadastra o recurso de inclusão de justificativas
    $id_recurso_incluir_justificativas = $fnCadastrarRecurso($id_sistema, 'gestao_documental_justificativas_incluir', 'Inclusão das justificativas de arquivamento e desarquivamento', 'controlador.php?acao=gd_justificativas_cadastrar', 'S');

    // Cadastra o recurso de alteração de justificativa
    $id_recurso_alterar_justificativas = $fnCadastrarRecurso($id_sistema, 'gestao_documental_justificativas_alterar', 'Alteração das justificativas de arquivamento e desarquivamento', 'controlador.php?acao=gd_justificativas_alterar', 'S');

    // Cadastra o recurso de exclusão de justificativa
    $id_recurso_excluir_justificativas = $fnCadastrarRecurso($id_sistema, 'gestao_documental_justificativas_excluir', 'Exclusão das justificativas de arquivamento e desarquivamento', 'controlador.php?acao=gd_justificativas_excluir', 'S');

    // Cadastra o recurso de visualização de justificativa
    $id_recurso_visualizar_justificativas = $fnCadastrarRecurso($id_sistema, 'gestao_documental_justificativas_visualizar', 'Visualização das justificativas de arquivamento e desarquivamento', 'controlador.php?acao=gd_justificativas_visualizar', 'S');

    // Cadastra o recurso de arquivamento de processo
    $id_recurso_arquivar_processo = $fnCadastrarRecurso($id_sistema, 'gestao_documental_arquivar_processo', 'Arquivamento de processos', 'controlador.php?acao=gd_arquivar_procedimento', 'S');

    // Cadastra o recurso de desarquivamento de processo
    $id_recurso_desarquivar_processo = $fnCadastrarRecurso($id_sistema, 'gestao_documental_desarquivar_processo', 'Desarquivamento de processos', 'controlador.php?acao=gd_desarquivar_procedimento', 'S');

    // Cadastra o recurso de pendências de arquivamento
    $id_recurso_pendencias_arquivamento = $fnCadastrarRecurso($id_sistema, 'gestao_documental_pendencias_arquivamento', 'Pendências de Arquivamento', 'controlador.php?acao=gd_pendencias_arquivamento', 'S');

    //Cadastra o recurso de edição dos modelos de documento
    $id_recurso_modelos_documento_alterar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_modelo_documento_alterar', 'Alteração dos modelos de documento do módulo de gestão documental', 'controlador.php?acao=gd_modelo_documento_alterar', 'S');

    //Cadastra o recurso de cadastro de unidades de arquivamento
    $id_recurso_unidades_arquivamento_cadastrar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_unidade_arquivamento_cadastrar', 'Cadastro das unidades de arquivamento do módulo de gestão documental', 'controlador.php?acao=gd_unidade_arquivamento_cadastrar', 'S');

    //Cadastra o recurso de alteração de unidades de arquivamento
    $id_recurso_unidades_arquivamento_alterar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_unidade_arquivamento_alterar', 'Alteração das unidades de arquivamento do módulo de gestão documental', 'controlador.php?acao=gd_unidade_arquivamento_alterar', 'S');

    //Cadastra o recurso de listagem de unidades de arquivamento
    $id_recurso_unidades_arquivamento_listar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_unidade_arquivamento_listar', 'Listagem das unidades de arquivamento do módulo de gestão documental', 'controlador.php?acao=gd_unidade_arquivamento_listar', 'S');

    //Cadastra o recurso de exclusão de unidades de arquivamento
    $id_recurso_unidades_arquivamento_excluir = $fnCadastrarRecurso($id_sistema, 'gestao_documental_unidade_arquivamento_excluir', 'Exclusão das unidades de arquivamento do módulo de gestão documental', 'controlador.php?acao=gd_unidade_arquivamento_excluir', 'S');

    //Cadastra o recurso de visualização de unidades de arquivamento
    $id_recurso_unidades_arquivamento_visualizar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_unidade_arquivamento_visualizar', 'Exclusão das unidades de arquivamento do módulo de gestão documental', 'controlador.php?acao=gd_unidade_arquivamento_visualizar', 'S');

    //Cadasta o recurso da listagem de avaliação de processos
    $id_recurso_avaliacao_processos_listar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_avaliacao_processos_listar', 'Listagem das avaliações de processo', 'controlador.php?acao=gd_avaliacao_processos_listar', 'S');

    //Cadasta o recurso de listagem da preparação de listagem de eliminação
    $id_recurso_prep_list_eliminacao_listar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_prep_list_eliminacao_listar', 'Lista de preparação para a listagem de eliminação', 'controlador.php?acao=gd_prep_list_eliminacao_listar', 'S');

    // Cadastra o recurso da exclusão de praparação para a listagem de eliminação
    $id_recurso_prep_list_eliminacao_excluir = $fnCadastrarRecurso($id_sistema, 'gestao_documental_prep_list_eliminacao_excluir', 'Exclusão da preparação para a listagem de eliminação', 'controlador.php?acao=gd_prep_list_eliminacao_excluir', 'S');

    // Cadastra o recurso da observação de preparação para a listagem de eliminação
    $id_recurso_prep_list_eliminacao_observar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_prep_list_eliminacao_observar', 'Observação/Justificativa da preparação para a listagem de eliminação', 'controlador.php?acao=gd_prep_list_eliminacao_observar', 'S');

    // Cadastra o recurso de geração da listagem de eliminação
    $id_recurso_prep_list_eliminacao_gerar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_prep_list_eliminacao_gerar', 'Geração da listagem de eliminação', 'controlador.php?acao=gd_prep_list_eliminacao_gerar', 'S');

    //Cadasta o recurso da preparação de listagem de recolhimento
    $id_recurso_prep_list_recolhimento_listar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_prep_list_recolhimento_listar', 'Lista de preparação para a listagem de recolhimento', 'controlador.php?acao=gd_prep_list_recolhimento_listar', 'S');

    // Cadastra o recurso da exclusão de preparação para a listagem de recolhimento
    $id_recurso_prep_list_recolhimento_excluir = $fnCadastrarRecurso($id_sistema, 'gestao_documental_prep_list_recolhimento_excluir', 'Exclusão da preparação para a listagem de recolhimento', 'controlador.php?acao=gd_prep_list_recolhimento_excluir', 'S');

    // Cadastra o recurso da observação de preparação para a listagem de recolhimento
    $id_recurso_prep_list_recolhimento_observar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_prep_list_recolhimento_observar', 'Observação/Justificativa da preparação para a listagem de recolhimento', 'controlador.php?acao=gd_prep_list_recolhimento_observar', 'S');

    // Cadastra o recurso de geração da listagem de recolhimento
    $id_recurso_prep_list_recolhimento_gerar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_prep_list_recolhimento_gerar', 'Geração da listagem de recolhimento', 'controlador.php?acao=gd_prep_list_recolhimento_gerar', 'S');

    //Cadasta o recurso de gestão da listagem de eliminação
    $id_recurso_gestao_listagem_eliminacao = $fnCadastrarRecurso($id_sistema, 'gestao_documental_gestao_listagem_eliminacao', 'Gestão da listagem de eliminação', 'controlador.php?acao=gd_gestao_listagem_eliminacao', 'S');

    // Cadastra o recurso de geração do pdf da listagem de eliminação
    $id_recurso_geracao_pdf_listagem_eliminacao = $fnCadastrarRecurso($id_sistema, 'gestao_documental_geracao_pdf_listagem_eliminacao', 'Geração de pdf da listagem de eliminação', 'controlador.php?acao=gd_geracao_pdf_listagem_eliminacao', 'S');

    // Cadastra o recurso de visualização da listagem de eliminação
    $id_recurso_visualizacao_listagem_eliminacao = $fnCadastrarRecurso($id_sistema, 'gestao_documental_visualizacao_listagem_eliminacao', 'Visualização da listagem de eliminação', 'controlador.php?acao=gd_visualizacao_listagem_eliminacao', 'S');

    // Cadastra o recurso de listagem documentos fisicos para eliminação
    $id_recurso_listagem_eliminacao_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gestao_documental_list_elim_documentos_fisicos', 'Listagem dos documentos físicos para eliminação', 'controlador.php?acao=gd_listagem_eliminacao_documentos_fisicos', 'S');

    // Cadastra o recurso de registro da eliminação de documentos fisicos
    $id_recurso_eliminacao_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gestao_documental_eliminacao_documentos_fisicos', 'Eliminação dos documentos físicos', 'controlador.php?acao=gd_eliminacao_documentos_fisicos', 'S');

    // Cadastra o recurso de eliminação
    $id_recurso_eliminacao = $fnCadastrarRecurso($id_sistema, 'gestao_documental_eliminacao', 'Eliminação de processos', 'controlador.php?acao=gd_eliminacao', 'S');

    //Cadasta o recurso da gestão de listagem de recolhimento
    $id_recurso_gestao_listagem_recolhimento = $fnCadastrarRecurso($id_sistema, 'gestao_documental_gestao_list_recolhimento', 'Gestão da listagem de recolhimento', 'controlador.php?acao=gd_gestao_listagem_recolhimento', 'S');

    // Cadastra o recurso de geração do pdf da listagem de recolhimento
    $id_recurso_geracao_pdf_listagem_recolhimento = $fnCadastrarRecurso($id_sistema, 'gestao_documental_geracao_pdf_list_recolhimento', 'Geração de pdf da listagem de recolhimento', 'controlador.php?acao=gd_geracao_pdf_listagem_recolhimento', 'S');

    // Cadastra o recurso de visualização da listagem de recolhimento
    $id_recurso_visualizacao_listagem_recolhimento = $fnCadastrarRecurso($id_sistema, 'gestao_documental_visualizacao_list_recolhimento', 'Visualização da listagem de recolhimento', 'controlador.php?acao=gd_visualizacao_listagem_recolhimento', 'S');

    // Cadastra o recurso de listagem documentos fisicos para recolhimento
    $id_recurso_listagem_recolhimento_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gestao_documental_list_recol_documentos_fisicos', 'Listagem dos documentos físicos para recolhimento', 'controlador.php?acao=gd_listagem_recolhimento_documentos_fisicos', 'S');

    // Cadastra o recurso de registro da eliminação de documentos fisicos
    $id_recurso_recolhimento_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gestao_documental_recolhimento_documentos_fisicos', 'Recolhimento dos documentos físicos', 'controlador.php?acao=gd_recolhimento_documentos_fisicos', 'S');

    // Cadastra o recurso de recolhimento
    $id_recurso_recolhimento = $fnCadastrarRecurso($id_sistema, 'gestao_documental_recolhimento', 'Recolhimento de processos', 'controlador.php?acao=gd_recolhimento', 'S');

    // Cadastra o recurso de relatórios
    $id_recurso_relatorio = $fnCadastrarRecurso($id_sistema, 'gestao_documental_relatorio', 'Relatório da gestão documental', 'controlador.php?acao=gd_relatorio', 'S');

    // Cadastro o recurso de listagem de eliminações
    $id_recurso_eliminacao_listar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_eliminacao_listar', 'Listagem de eliminações', 'controlador.php?acao=gd_eliminacao_listar', 'S');

    // gestao_documental_recolhimento_listar
    $id_recurso_recolhimento_listar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_recolhimento_listar', 'Listar recolhimentos', 'controlador.php?acao=gd_recolhimento_listar', 'S');

    // gestao_documental_processos_arquivados
    $id_recurso_arquivamento_listar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_arquivamento_listar', 'Listar arquivamento', 'controlador.php?acao=gd_arquivamento_listar', 'S');

    ###########################################################ITENS DE MENU#############################################################################################
    // Cria o item de menu gestão documental
    $id_menu_gestao_documental = $fnItemMenu($id_menu, $id_item_menu_pai, $id_sistema, null, 'Gestão Documental', 'N', 'S', 10);

    //Cria o item de menu de edição de parâmetros
    $id_menu_parametros = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_alterar_parametros, 'Configurações', 'N', 'S', 1);

    //Cria os itens de menu de justificativas
    $id_menu_justificativas = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Justificativas de Arquivamento e Desarquivamento', 'N', 'S', 1);
    $id_menu_listar_justificativas = $fnItemMenu($id_menu, $id_menu_justificativas, $id_sistema, $id_recurso_listar_justificativas, 'Listar', 'N', 'S', 1);
    $id_menu_incluir_justificativas = $fnItemMenu($id_menu, $id_menu_justificativas, $id_sistema, $id_recurso_incluir_justificativas, 'Novo', 'N', 'S', 2);

    //Cria os itens de menu pendências de arquivamento e processos arquivados
    $id_menu_pendencias_arquivamento = $fnItemMenu($id_menu, null, $id_sistema, $id_recurso_pendencias_arquivamento, 'Pendências de arquivamento', 'N', 'S', 131);
    $id_menu_arquivamento_listar = $fnItemMenu($id_menu, null, $id_sistema, $id_recurso_arquivamento_listar, 'Arquivo da Unidade', 'N', 'S', 132);

    //Cria o item de menu de modelos de documento
    $id_menu_modelos_documento_alterar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_modelos_documento_alterar, 'Modelos de Documento', 'N', 'S', 3);

    //Cria os itens os itens de menu de unidades de arquivamento
    $id_menu_unidades_arquivamento = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Unidades de Arquivamento', 'N', 'S', 2);
    $id_menu_unidades_arquivamento_novo = $fnItemMenu($id_menu, $id_menu_unidades_arquivamento, $id_sistema, $id_recurso_unidades_arquivamento_cadastrar, 'Novo', 'N', 'S', 1);
    $id_menu_unidades_arquivamento_listar = $fnItemMenu($id_menu, $id_menu_unidades_arquivamento, $id_sistema, $id_recurso_unidades_arquivamento_listar, 'Listar', 'N', 'S', 2);

    // Cria o item de menu principal gestão documental
    $id_menu_gestao_documental = $fnItemMenu($id_menu, null, $id_sistema, null, 'Gestão Documental', 'N', 'S', 50);
    $id_menu_avaliacao_processos_listar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_avaliacao_processos_listar, 'Avaliação de Processos', 'N', 'S', 1);

    // Cria os itens de menu gestao documental -> listagem eliminação
    $id_menu_listagem_eliminacao = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Listagens de Eliminação', 'N', 'S', 2);
    $id_menu_prep_list_eliminacao = $fnItemMenu($id_menu, $id_menu_listagem_eliminacao, $id_sistema, $id_recurso_prep_list_eliminacao_listar, 'Preparação da Listagem', 'N', 'S', 1);
    $id_menu_gestao_listagem_eliminacao = $fnItemMenu($id_menu, $id_menu_listagem_eliminacao, $id_sistema, $id_recurso_gestao_listagem_eliminacao, 'Gestão das Listagens', 'N', 'S', 2);
//    $id_menu_eliminacao_listar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_eliminacao_listar, 'Eliminações', 'N', 'S', 3);

    // Cria os itens de menu gestao documental -> listagem recolhimento
    $id_menu_listagem_recolhimento = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Listagens de Recolhimento', 'N', 'S', 3);
    $id_menu_prep_list_recolhimento = $fnItemMenu($id_menu, $id_menu_listagem_recolhimento, $id_sistema, $id_recurso_prep_list_recolhimento_listar, 'Preparação da Listagem', 'N', 'S', 1);
    $id_menu_gestao_listagem_recolhimento = $fnItemMenu($id_menu, $id_menu_listagem_recolhimento, $id_sistema, $id_recurso_gestao_listagem_recolhimento, 'Gestão das Listagens', 'N', 'S', 2);
//    $id_menu_recolhimentos_listar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_recolhimento_listar, 'Recolhimentos', 'N', 'S', 3);

    // Cria o item de menu de relatórios
    $id_menu_relatorio = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_relatorio, 'Relatórios', 'N', 'S', 4);

    echo "ATUALIZAO FINALIZADA COM SUCESSO! ";
} catch (Exception $e) {
    echo(InfraException::inspecionar($e));
    try {
        LogSip::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e) {
        
    }
}