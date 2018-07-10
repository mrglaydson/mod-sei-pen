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
    $itemMenuDTO->setStrRotulo('Administra��o', InfraDTO::$OPER_LIKE);
    $itemMenuDTO->setNumIdSistema($id_sistema);
    $itemMenuDTO->setNumRegistrosPaginaAtual(1);
    $itemMenuDTO->retNumIdItemMenu();

    $itemMenuRN = new ItemMenuRN();
    $itemMenuDTO = $itemMenuRN->consultar($itemMenuDTO);

    if (!empty($itemMenuDTO)) {
        $id_item_menu_pai = $itemMenuDTO->getNumIdItemMenu();
    }

    //Cria fun��o gen�rica de cadastro de recursos

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


    //Cadastra o recurso de edi��o de par�metros do m�dulo
    $id_recurso_alterar_parametros = $fnCadastrarRecurso($id_sistema, 'gestao_documental_parametros_alterar', 'Altera��o dos par�metros do m�dulo de gest�o documental', 'controlador.php?acao=gd_parametros_alterar', 'S');

    // Cadastra o recurso de listagem de justificativas
    $id_recurso_listar_justificativas = $fnCadastrarRecurso($id_sistema, 'gestao_documental_justificativas_listar', 'Listagem das justificativas de arquivamento e desarquivamento', 'controlador.php?acao=gd_justificativas_listar', 'S');

    // Cadastra o recurso de inclus�o de justificativas
    $id_recurso_incluir_justificativas = $fnCadastrarRecurso($id_sistema, 'gestao_documental_justificativas_incluir', 'Inclus�o das justificativas de arquivamento e desarquivamento', 'controlador.php?acao=gd_justificativas_cadastrar', 'S');

    // Cadastra o recurso de altera��o de justificativa
    $id_recurso_alterar_justificativas = $fnCadastrarRecurso($id_sistema, 'gestao_documental_justificativas_alterar', 'Altera��o das justificativas de arquivamento e desarquivamento', 'controlador.php?acao=gd_justificativas_alterar', 'S');

    // Cadastra o recurso de exclus�o de justificativa
    $id_recurso_excluir_justificativas = $fnCadastrarRecurso($id_sistema, 'gestao_documental_justificativas_excluir', 'Exclus�o das justificativas de arquivamento e desarquivamento', 'controlador.php?acao=gd_justificativas_excluir', 'S');

    // Cadastra o recurso de visualiza��o de justificativa
    $id_recurso_visualizar_justificativas = $fnCadastrarRecurso($id_sistema, 'gestao_documental_justificativas_visualizar', 'Visualiza��o das justificativas de arquivamento e desarquivamento', 'controlador.php?acao=gd_justificativas_visualizar', 'S');

    // Cadastra o recurso de arquivamento de processo
    $id_recurso_arquivar_processo = $fnCadastrarRecurso($id_sistema, 'gestao_documental_arquivar_processo', 'Arquivamento de processos', 'controlador.php?acao=gd_arquivar_procedimento', 'S');

    // Cadastra o recurso de desarquivamento de processo
    $id_recurso_desarquivar_processo = $fnCadastrarRecurso($id_sistema, 'gestao_documental_desarquivar_processo', 'Desarquivamento de processos', 'controlador.php?acao=gd_desarquivar_procedimento', 'S');

    // Cadastra o recurso de pend�ncias de arquivamento
    $id_recurso_pendencias_arquivamento = $fnCadastrarRecurso($id_sistema, 'gestao_documental_pendencias_arquivamento', 'Pend�ncias de Arquivamento', 'controlador.php?acao=gd_pendencias_arquivamento', 'S');

    // Cria a fun��o gen�ria para o cadastramento de menus
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

    // Cria o item de menu gest�o documental
    $id_menu_gestao_documental = $fnItemMenu($id_menu, $id_item_menu_pai, $id_sistema, null, 'Gest�o Documental', 'N', 'S', 10);

    //Cria o item de menu de edi��o de par�metros
    $id_menu_parametros = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_alterar_parametros, 'Configura��es', 'N', 'S', 1);

    //Cria os itens de menu de justificativas
    $id_menu_justificativas = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Justificativas de Arquivamento e Desarquivamento', 'N', 'S', 1);
    $id_menu_listar_justificativas = $fnItemMenu($id_menu, $id_menu_justificativas, $id_sistema, $id_recurso_listar_justificativas, 'Listar', 'N', 'S', 1);
    $id_menu_incluir_justificativas = $fnItemMenu($id_menu, $id_menu_justificativas, $id_sistema, $id_recurso_incluir_justificativas, 'Novo', 'N', 'S', 2 );

    //Cria os itens de menu pend�ncias de arquivamento
    $id_menu_pendencias_arquivamento = $fnItemMenu($id_menu, null, $id_sistema, $id_recurso_pendencias_arquivamento, 'Pend�ncias de arquivamento', 'N', 'S', 131);

    /*
    $relPerfilRecursoDTO = new RelPerfilRecursoDTO();
    $relPerfilRecursoDTO->setNumIdPerfil($id_perfil);
    $relPerfilRecursoDTO->setNumIdRecurso($id_recurso_listar);
    $relPerfilRecursoDTO->setNumIdSistema($id_sistema);
    $relPerfilRecursoRN = new RelPerfilRecursoRN();
    $relPerfilRecursoDTO = $relPerfilRecursoRN->cadastrar($relPerfilRecursoDTO);

    //Cadastra a Relao do perfil com o item menu
    $relPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
    $relPerfilItemMenuDTO->setNumIdPerfil($id_perfil);
    $relPerfilItemMenuDTO->setNumIdSistema($id_sistema);
    $relPerfilItemMenuDTO->setNumIdRecurso($id_recurso_listar);
    $relPerfilItemMenuDTO->setNumIdMenu($id_menu);
    $relPerfilItemMenuDTO->setNumIdItemMenu($itemMenuNovoDTO->getNumIdItemMenu());
    $relPerfilItemMenuRN = new RelPerfilItemMenuRN();
    $relPerfilItemMenuDTO = $relPerfilItemMenuRN->cadastrar($relPerfilItemMenuDTO);
*/

    echo "ATUALIZAO FINALIZADA COM SUCESSO! ";

} catch (Exception $e) {
    echo(InfraException::inspecionar($e));
    try {
        LogSip::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e) {
    }
}