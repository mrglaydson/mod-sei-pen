<?php

require_once dirname(__FILE__) . '/../../web/Sip.php';

class VersaoSipRN extends InfraScriptVersao
{
    const PARAMETRO_VERSAO_MODULO = 'VERSAO_MODULO_GESTAO_DOCUMENTAL';
    const NOME_MODULO = 'Módulo de Gestão Documental';

    private $arrRecurso = array();
    private $arrMenu = array();

  public function __construct()
    {
      parent::__construct();
  }

  protected function inicializarObjInfraIBanco()
    {
      return BancoSip::getInstance();
  }

  protected function verificarVersaoInstaladaControlado()
    {
      $objInfraParametroDTO = new InfraParametroDTO();
      $objInfraParametroDTO->setStrNome(VersaoSipRN::PARAMETRO_VERSAO_MODULO);
      $objInfraParametroDB = new InfraParametroBD(BancoSip::getInstance());
    if ($objInfraParametroDB->contar($objInfraParametroDTO) == 0) {
        $objInfraParametroDTO->setStrValor('0.0.0');
        $objInfraParametroDB->cadastrar($objInfraParametroDTO);
    }
  }

  public function versao_0_0_0($strVersaoAtual)
    {
  }

  public function versao_0_4_0($strVersaoAtual)
    {
  }

  public function versao_0_5_0($strVersaoAtual)
    {
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
          $itemMenuNovoDTO->setStrIcone(null);
          $itemMenuNovoDTO->setStrDescricao(null);
          $itemMenuNovoDTO->setStrSinNovaJanela($nova_janela);
          $itemMenuNovoDTO->setStrSinAtivo($ativo);
          $itemMenuNovoDTO->setNumSequencia($sequencia);
          $itemMenuNovoDTO->setNumIdMenu($id_menu);

          $itemMenuNovoRN = new ItemMenuRN();
          $itemMenuNovoDTO = $itemMenuNovoRN->cadastrar($itemMenuNovoDTO);
          return $itemMenuNovoDTO->getNumIdItemMenu();
      };


      # CONFIGURAÇÕES DO MÓDULO: PARÂMETROS

      $id_recurso_parametro_alterar = $fnCadastrarRecurso($id_sistema, 'gd_parametro_alterar', 'Alteração dos parâmetros do módulo de gestão documental', 'controlador.php?acao=gd_parametro_alterar', 'S');

      # CONFIGURAÇÕES DO MÓDULO: JUSTIFICATIVAS DE ARQUIVAMENTO

      // Gerenciamento das justificativas de arquivamento
      $id_recurso_justificativa_listar = $fnCadastrarRecurso($id_sistema, 'gd_justificativa_listar', 'Listagem das justificativas de arquivamento e desarquivamento de processos', 'controlador.php?acao=gd_justificativa_listar', 'S');
      $id_recurso_justificativa_cadastrar = $fnCadastrarRecurso($id_sistema, 'gd_justificativa_cadastrar', 'Inclusão das justificativas de arquivamento e desarquivamento de processos', 'controlador.php?acao=gd_justificativa_cadastrar', 'S');
      $id_recurso_justificativa_alterar = $fnCadastrarRecurso($id_sistema, 'gd_justificativa_alterar', 'Alteração das justificativas de arquivamento e desarquivamento de processos', 'controlador.php?acao=gd_justificativa_alterar', 'S');
      $id_recurso_justificativa_excluir = $fnCadastrarRecurso($id_sistema, 'gd_justificativa_excluir', 'Exclusão das justificativas de arquivamento e desarquivamento de processos', 'controlador.php?acao=gd_justificativa_excluir', 'S');
      $id_recurso_justificativa_visualizar = $fnCadastrarRecurso($id_sistema, 'gd_justificativa_visualizar', 'Visualização das justificativas de arquivamento e desarquivamento de processos', 'controlador.php?acao=gd_justificativa_visualizar', 'S');

      # CONFIGURAÇÕES DO MÓDULO: UNIDADES DE ARQUIVAMENTO

      // Gerenciamento das unidades de arquivamento
      $id_recurso_unidade_arquivamento_cadastrar = $fnCadastrarRecurso($id_sistema, 'gd_unidade_arquivamento_cadastrar', 'Cadastro das unidades de arquivamento do módulo de gestão documental', 'controlador.php?acao=gd_unidade_arquivamento_cadastrar', 'S');
      $id_recurso_unidade_arquivamento_alterar = $fnCadastrarRecurso($id_sistema, 'gd_unidade_arquivamento_alterar', 'Alteração das unidades de arquivamento do módulo de gestão documental', 'controlador.php?acao=gd_unidade_arquivamento_alterar', 'S');
      $id_recurso_unidade_arquivamento_listar = $fnCadastrarRecurso($id_sistema, 'gd_unidade_arquivamento_listar', 'Listagem das unidades de arquivamento do módulo de gestão documental', 'controlador.php?acao=gd_unidade_arquivamento_listar', 'S');
      $id_recurso_unidade_arquivamento_excluir = $fnCadastrarRecurso($id_sistema, 'gd_unidade_arquivamento_excluir', 'Exclusão das unidades de arquivamento do módulo de gestão documental', 'controlador.php?acao=gd_unidade_arquivamento_excluir', 'S');
      $id_recurso_unidade_arquivamento_visualizar = $fnCadastrarRecurso($id_sistema, 'gd_unidade_arquivamento_visualizar', 'Exclusão das unidades de arquivamento do módulo de gestão documental', 'controlador.php?acao=gd_unidade_arquivamento_visualizar', 'S');

      # CONFIGURAÇÕES DO MÓDULO: MODELOS DE DOCUMENTO

      //Cadastra o recurso de edição dos modelos de documento
      $id_recurso_modelos_documento_alterar = $fnCadastrarRecurso($id_sistema, 'gd_modelo_documento_alterar', 'Alteração dos modelos de documento do módulo de gestão documental', 'controlador.php?acao=gd_modelo_documento_alterar', 'S');


      # RECURSOS DE ARQUIVAMENTO 

      $id_recurso_procedimento_arquivar = $fnCadastrarRecurso($id_sistema, 'gd_procedimento_arquivar', 'Arquivamento de processos', 'controlador.php?acao=gd_procedimento_arquivar', 'S');

      # RECURSOS DE DESARQUIVAMENTO

      $id_recurso_desarquivar_processo = $fnCadastrarRecurso($id_sistema, 'gd_procedimento_desarquivar', 'Desarquivamento de processos', 'controlador.php?acao=gd_procedimento_desarquivar', 'S');


      # RECURSOS DA PENDÊNCIA DE ARQUIVAMENTO

      $id_recurso_pendencias_arquivamento = $fnCadastrarRecurso($id_sistema, 'gd_pendencia_arquivamento_listar', 'Pendências de Arquivamento', 'controlador.php?acao=gd_pendencia_arquivamento_listar', 'S');
      $id_recurso_pendencias_arquivamento_anotacao = $fnCadastrarRecurso($id_sistema, 'gd_pendencia_arquivamento_anotar', 'Anotações das pendências de arquivamento', 'controlador.php?acao=gd_pendencia_arquivamento_anotar', 'S');

      # RECURSOS DA LISTAGEM DE ARQUIVAMENTOS 

      $id_recurso_arquivamento_listar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_listar', 'Listar arquivo da unidade.', 'controlador.php?acao=gd_arquivamento_listar', 'S');
      $id_recurso_arquivamento_editar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_editar', 'Editar processo arquivado.', 'controlador.php?acao=gd_arquivamento_editar', 'S');
      $id_recurso_arquivamento_edicao_concluir = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_edicao_concluir', 'Concluir edição de processo arquivado.', 'controlador.php?acao=gd_arquivamento_edicao_concluir', 'S');
      $id_recurso_procedimento_reabrir = $fnCadastrarRecurso($id_sistema, 'gd_procedimento_reabrir', 'Reabrir processo no contexto módulo de gestão documental.', 'controlador.php?acao=gd_procedimento_reabrir', 'S');


      # RECUROS DE CONSULTA AO HISTÓRICO DE ARQUIVAMENTO
      $id_recurso_arquivamento_historico_listar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_historico_listar', 'Listar o histórico de arquivamento.', 'controlador.php?acao=gd_arquivamento_historico_listar', 'S');

      # RECURSOS DA AVALIAÇÃO DE PROCESSOS

      $id_recurso_avaliacao_processos_listar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_avaliar', 'Listagem de processos arquivados para avaliação da unidade de arquivo.', 'controlador.php?acao=gd_arquivamento_avaliar', 'S');
      $id_recurso_arquivamento_recolhimento_enviar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_recolhimento_enviar', 'Enviar processo arquivado para recolhimento.', 'controlador.php?acao=gd_arquivamento_recolhimento_enviar', 'S');
      $id_recurso_arquivamento_eliminacao_enviar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_eliminacao_enviar', 'Enviar processo arquivado para eliminação.', 'controlador.php?acao=gd_arquivamento_eliminacao_enviar', 'S');
      $id_recurso_arquivamento_devolver = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_devolver', 'Devolver processos arquivado para unidade que o arquivou.', 'controlador.php?acao=gd_arquivamento_devolver', 'S');


      # PREPARAÇÃO DA LISTA DE ELIMINAÇÃO

      //Cadasta o recurso de listagem da preparação de listagem de eliminação
      $id_recurso_lista_eliminacao_preparacao_listar = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_preparacao_listar', 'Lista de preparação para a listagem de eliminação', 'controlador.php?acao=gd_lista_eliminacao_preparacao_listar', 'S');

      // Cadastra o recurso de geração da listagem de eliminação
      $id_recurso_lista_eliminacao_preparacao_gerar = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_preparacao_gerar', 'Geração da listagem de eliminação', 'controlador.php?acao=gd_lista_eliminacao_preparacao_gerar', 'S');

      // Cadastra o recurso da exclusão de praparação para a listagem de eliminação
      $id_recurso_lista_eliminacao_preparacao_excluir = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_preparacao_excluir', 'Exclusão da preparação para a listagem de eliminação', 'controlador.php?acao=gd_lista_eliminacao_preparacao_excluir', 'S');

      // Cadastra o recurso da observação de preparação para a listagem de eliminação
      $id_recurso_lista_eliminacao_preparacao_observar = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_preparacao_observar', 'Observação/Justificativa da preparação para a listagem de eliminação', 'controlador.php?acao=gd_lista_eliminacao_preparacao_observar', 'S');


      # PREPARAÇÃO DA LISTA DE RECOLHIMENTO

      //Cadasta o recurso da preparação de listagem de recolhimento
      $id_recurso_lista_recolhimento_preparacao_listar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_preparacao_listar', 'Lista de preparação para a listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_preparacao_listar', 'S');

      // Cadastra o recurso da exclusão de preparação para a listagem de recolhimento
      $id_recurso_lista_recolhimento_preparacao_excluir = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_preparacao_excluir', 'Exclusão da preparação para a listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_preparacao_excluir', 'S');

      // Cadastra o recurso da observação de preparação para a listagem de recolhimento
      $id_recurso_lista_recolhimento_preparacao_observar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_preparacao_observar', 'Observação/Justificativa da preparação para a listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_preparacao_observar', 'S');

      // Cadastra o recurso de geração da listagem de recolhimento
      $id_recurso_lista_recolhimento_preparacao_gerar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_preparacao_gerar', 'Geração da listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_preparacao_gerar', 'S');

      # GESTÃO DA LISTAGEM DE RECOLHIMENTO

      //Cadasta o recurso da gestão de listagem de recolhimento
      $id_recurso_gestao_listagem_recolhimento = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_listar', 'Gestão da listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_listar', 'S');

      // Cadastra o recurso de visualizar listagem de recolhimento
      $id_recurso_lista_recolhimento_visualizar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_visualizar', 'Visualizar listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_visualizar', 'S');

      // Cadastra o recurso de edição do recolhimento
      $id_recurso_lista_recolhimento_editar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_editar', 'Editar listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_editar', 'S');

      // Cadastra o recurso de geração do pdf da listagem de recolhimento
      $id_recurso_lista_recolhimento_pdf_gerar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_pdf_gerar', 'Geração de pdf da listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_pdf_gerar', 'S');

      // Cadastra o reurso de conclusão da edição do recolhimento
      $id_recurso_lista_recolhimento_edicao_concluir = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_edicao_concluir', 'Concluir edição da listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_edicao_concluir', 'S');

      // Cadastra o recurso de recolhimento
      $id_recurso_recolhimento = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_recolher', 'Recolhimento de processos', 'controlador.php?acao=gd_lista_recolhimento_recolher', 'S');

      // Cadastra o recurso de adição da lista de recolhimento
      $id_recurso_lista_recolhimento_procedimento_adicionar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_procedimento_adicionar', 'Adicionar processo na listagem de eliminação', 'controlador.php?acao=gd_lista_recolhimento_procedimento_adicionar', 'S');

      // Cadastra o recurso de remoção da lista de recolhimento
      $id_recurso_lista_recolhimento_procedimento_remover = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_procedimento_remover', 'Remover processo da listagem de eliminação', 'controlador.php?acao=gd_lista_recolhimento_procedimento_remover', 'S');



      # GESTÃO DA LISTAGEM DE ELIMINAÇÃO

      //Cadasta o recurso de gestão da listagem de eliminação
      $id_recurso_gestao_listagem_eliminacao = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_listar', 'Gestão da listagem de eliminação', 'controlador.php?acao=gd_lista_eliminacao_listar', 'S');

      // Cadastra o recurso de visualização da listagem de eliminação
      $id_recurso_visualizacao_listagem_eliminacao = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_visualizar', 'Visualização da listagem de eliminação', 'controlador.php?acao=gd_lista_eliminacao_visualizar', 'S');

      // Cadastra o recurso de geração do pdf da listagem de eliminação
      $id_recurso_geracao_pdf_listagem_eliminacao = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_pdf_gerar', 'Geração de pdf da listagem de eliminação', 'controlador.php?acao=gd_lista_eliminacao_pdf_gerar', 'S');

      // Cadastra o recurso de edição do eliminação
      $id_recurso_lista_eliminacao_editar = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_editar', 'Editar listagem de eliminação', 'controlador.php?acao=gd_lista_eliminacao_editar', 'S');

      // Cadastra o recurso de geração do pdf da listagem de eliminação
      $id_recurso_lista_eliminacao_edicao_concluir = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_edicao_concluir', 'Concluir edição da lista de eliminação', 'controlador.php?acao=gd_lista_eliminacao_edicao_concluir', 'S');

      // Cadastra o recurso de eliminação
      $id_recurso_eliminacao = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_eliminar', 'Eliminação de processos', 'controlador.php?acao=gd_lista_eliminacao_eliminar', 'S');

      // Cadastra o recurso de adição da lista de eliminação
      $id_recurso_lista_eliminacao_procedimento_adicionar = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_procedimento_adicionar', 'Adicionar processo na listagem de eliminação', 'controlador.php?acao=gd_lista_eliminacao_procedimento_adicionar', 'S');

      // Cadastra o recurso de remoção da lista de eliminação
      $id_recurso_lista_eliminacao_procedimento_remover = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_procedimento_remover', 'Remover processo da listagem de eliminação', 'controlador.php?acao=gd_lista_eliminacao_procedimento_remover', 'S');


      // Cadastra o recurso de listagem documentos fisicos para eliminação
      $id_recurso_listagem_eliminacao_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_documentos_fisicos_listar', 'Listagem dos documentos físicos para eliminação', 'controlador.php?acao=gd_lista_eliminacao_documentos_fisicos_listar', 'S');

      // Cadastra o recurso de registro da eliminação de documentos fisicos
      $id_recurso_eliminacao_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_documentos_fisicos_eliminar', 'Eliminação dos documentos físicos', 'controlador.php?acao=gd_lista_eliminacao_documentos_fisicos_eliminar', 'S');


      // Cadastra o recurso de listagem documentos fisicos para recolhimento
      $id_recurso_listagem_recolhimento_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_documentos_fisicos_listar', 'Listagem dos documentos físicos para recolhimento', 'controlador.php?acao=gd_lista_recolhimento_documentos_fisicos_listar', 'S');

      // Cadastra o recurso de registro da eliminação de documentos fisicos
      $id_recurso_recolhimento_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_documentos_fisicos_recolher', 'Recolhimento dos documentos físicos', 'controlador.php?acao=gd_lista_recolhimento_documentos_fisicos_recolher', 'S');


      // Cadastra o recurso de relatórios
      $id_recurso_relatorio = $fnCadastrarRecurso($id_sistema, 'gd_relatorio', 'Relatório da gestão documental', 'controlador.php?acao=gd_relatorio', 'S');

      // Cadastro o recurso de listagem de eliminações
      // $id_recurso_eliminacao_listar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_eliminacao_listar', 'Listagem de eliminações', 'controlador.php?acao=gd_eliminacao_listar', 'S');

      // gestao_documental_recolhimento_listar
      // $id_recurso_recolhimento_listar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_recolhimento_listar', 'Listar recolhimentos', 'controlador.php?acao=gd_recolhimento_listar', 'S');

      ###########################################################ITENS DE MENU#############################################################################################
      // Cria o item de menu gestão documental
      $id_menu_gestao_documental = $fnItemMenu($id_menu, $id_item_menu_pai, $id_sistema, null, 'Gestão Documental', 'N', 'S', 10);

      //Cria o item de menu de edição de parâmetros
      $id_menu_parametros = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_parametro_alterar, 'Configurações', 'N', 'S', 1);

      //Cria os itens de menu de justificativas
      $id_menu_justificativas = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Justificativas de Arquivamento / Desarquivamento', 'N', 'S', 1);
      $id_menu_listar_justificativas = $fnItemMenu($id_menu, $id_menu_justificativas, $id_sistema, $id_recurso_justificativa_listar, 'Listar', 'N', 'S', 1);
      $id_menu_incluir_justificativas = $fnItemMenu($id_menu, $id_menu_justificativas, $id_sistema, $id_recurso_justificativa_cadastrar, 'Novo', 'N', 'S', 2);

      //Cria os itens de menu pendências de arquivamento e processos arquivados
      $id_menu_pendencias_arquivamento = $fnItemMenu($id_menu, null, $id_sistema, $id_recurso_pendencias_arquivamento, 'Pendências de Arquivamento', 'N', 'S', 133);
      $id_menu_arquivamento_listar = $fnItemMenu($id_menu, null, $id_sistema, $id_recurso_arquivamento_listar, 'Arquivo da Unidade', 'N', 'S', 131);



      //Cria o item de menu de modelos de documento
      $id_menu_modelos_documento_alterar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_modelos_documento_alterar, 'Modelos de Documentos', 'N', 'S', 2);

      //Cria os itens os itens de menu de unidades de arquivamento
      $id_menu_unidades_arquivamento = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Unidades de Arquivamento', 'N', 'S', 3);
      $id_menu_unidades_arquivamento_novo = $fnItemMenu($id_menu, $id_menu_unidades_arquivamento, $id_sistema, $id_recurso_unidade_arquivamento_cadastrar, 'Novo', 'N', 'S', 1);
      $id_menu_unidades_arquivamento_listar = $fnItemMenu($id_menu, $id_menu_unidades_arquivamento, $id_sistema, $id_recurso_unidade_arquivamento_listar, 'Listar', 'N', 'S', 2);

      // Cria o item de menu principal gestão documental
      $id_menu_gestao_documental = $fnItemMenu($id_menu, null, $id_sistema, null, 'Gestão Documental', 'N', 'S', 132);
      $id_menu_avaliacao_processos_listar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_avaliacao_processos_listar, 'Avaliação de Processos', 'N', 'S', 1);

      // Cria os itens de menu gestao documental -> listagem eliminação
      $id_menu_listagem_eliminacao = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Listagens de Eliminação', 'N', 'S', 2);
      $id_menu_prep_list_eliminacao = $fnItemMenu($id_menu, $id_menu_listagem_eliminacao, $id_sistema, $id_recurso_lista_eliminacao_preparacao_listar, 'Preparação da Listagem', 'N', 'S', 1);
      $id_menu_gestao_listagem_eliminacao = $fnItemMenu($id_menu, $id_menu_listagem_eliminacao, $id_sistema, $id_recurso_gestao_listagem_eliminacao, 'Gestão das Listagens', 'N', 'S', 2);
      //    $id_menu_eliminacao_listar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_eliminacao_listar, 'Eliminações', 'N', 'S', 3);

      // Cria os itens de menu gestao documental -> listagem recolhimento
      $id_menu_listagem_recolhimento = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Listagens de Recolhimento', 'N', 'S', 3);
      $id_menu_prep_list_recolhimento = $fnItemMenu($id_menu, $id_menu_listagem_recolhimento, $id_sistema, $id_recurso_lista_recolhimento_preparacao_listar, 'Preparação da Listagem', 'N', 'S', 1);
      $id_menu_gestao_listagem_recolhimento = $fnItemMenu($id_menu, $id_menu_listagem_recolhimento, $id_sistema, $id_recurso_gestao_listagem_recolhimento, 'Gestão das Listagens', 'N', 'S', 2);
      //    $id_menu_recolhimentos_listar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_recolhimento_listar, 'Recolhimentos', 'N', 'S', 3);

      // Cria o item de menu de relatórios
      $id_menu_relatorio = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_relatorio, 'Relatórios', 'N', 'S', 4);
  }

  public function addRecursosToPerfil($numIdPerfil, $numIdSistema) {

    if (!empty($this->arrRecurso)) {

        $objDTO = new RelPerfilRecursoDTO();
        $objBD = new RelPerfilRecursoBD(BancoSip::getInstance());

      foreach ($this->arrRecurso as $numIdRecurso) {

        $objDTO->setNumIdSistema($numIdSistema);
        $objDTO->setNumIdPerfil($numIdPerfil);
        $objDTO->setNumIdRecurso($numIdRecurso);

        if ($objBD->contar($objDTO) == 0) {
            $objBD->cadastrar($objDTO);
        }
      }
    }
  }

  public function addMenusToPerfil($numIdPerfil, $numIdSistema) {

    if (!empty($this->arrMenu)) {

        $objDTO = new RelPerfilItemMenuDTO();
        $objBD = new RelPerfilItemMenuBD(BancoSip::getInstance());

      foreach ($this->arrMenu as $array) {

        list($numIdItemMenu, $numIdMenu, $numIdRecurso) = $array;

        $objDTO->setNumIdPerfil($numIdPerfil);
        $objDTO->setNumIdSistema($numIdSistema);
        $objDTO->setNumIdRecurso($numIdRecurso);
        $objDTO->setNumIdMenu($numIdMenu);
        $objDTO->setNumIdItemMenu($numIdItemMenu);

        if ($objBD->contar($objDTO) == 0) {
            $objBD->cadastrar($objDTO);
        }
      }
    }
  }

  protected function consultarRecurso($numIdSistema, $strNomeRecurso)
    {
      $objRecursoDTO = new RecursoDTO();
      $objRecursoDTO->setBolExclusaoLogica(false);
      $objRecursoDTO->setNumIdSistema($numIdSistema);
      $objRecursoDTO->setStrNome($strNomeRecurso);
      $objRecursoDTO->retNumIdRecurso();

      $objRecursoRN = new RecursoRN();
      $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

    if ($objRecursoDTO == null){
        throw new InfraException("Recurso com nome {$strNomeRecurso} não pode ser localizado.");
    }

      return $objRecursoDTO->getNumIdRecurso();
  }

  protected function consultarItemMenu($numIdSistema, $strNomeRecurso)
    {
      $numIdRecurso = $this->consultarRecurso($numIdSistema, $strNomeRecurso);

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->setBolExclusaoLogica(false);
      $objItemMenuDTO->setNumIdSistema($numIdSistema);
      $objItemMenuDTO->setNumIdRecurso($numIdRecurso);
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->retNumIdItemMenu();

      $objItemMenuRN = new ItemMenuRN();
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

    if ($objItemMenuDTO == null){
        throw new InfraException("Item de menu não pode ser localizado.");
    }

      return array($objItemMenuDTO->getNumIdItemMenu(), $objItemMenuDTO->getNumIdMenu(), $numIdRecurso);
  }

  public function atribuirPerfil($numIdSistema, $numIdPerfil) {
      $objRN = $this;

      // Vincula a um perfil os recursos e menus adicionados
      $fnCadastrar = function($numIdSistema, $numIdPerfil) use($objRN) {
          $objRN->addRecursosToPerfil($numIdPerfil, $numIdSistema);
          $objRN->addMenusToPerfil($numIdPerfil, $numIdSistema);
      };

      $fnCadastrar($numIdSistema, $numIdPerfil);
  }

  public function versao_0_5_1($strVersaoAtual)
    {
  }

  public function versao_0_5_2($strVersaoAtual)
    {
  }

  public function versao_1_2_0($strVersaoAtual)
    {
      session_start();

      SessaoSip::getInstance(false);

      $id_sistema = '';

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

      # PREPARAÇÃO DA LISTA DE RECOLHIMENTO

      //Cadasta o recurso da preparação de listagem de recolhimento
      $id_recurso_listagem_recolhimento_anotacao = $fnCadastrarRecurso($id_sistema, 'gd_listar_recolhimento_anotar', 'Anotações da listagem de recolhimento', 'controlador.php?acao=gd_listar_recolhimento_anotar', 'S');
      $id_recurso_listagem_eliminacao_anotacao = $fnCadastrarRecurso($id_sistema, 'gd_listar_eliminacao_anotar', 'Anotações da listagem de eliminação', 'controlador.php?acao=gd_listar_eliminacao_anotar', 'S');
        
  }

  public function versao_1_2_1($strVersaoAtual)
    {
  }

  public function versao_1_2_2($strVersaoAtual)
    {
  }

  public function versao_1_2_3($strVersaoAtual)
    {
      session_start();

      SessaoSip::getInstance(false);

      $numIdSistema = '';

      //Consulta do Sistema
      $sistemaDTO = new SistemaDTO();
      $sistemaDTO->setStrSigla('SEI');
      $sistemaDTO->setNumRegistrosPaginaAtual(1);
      $sistemaDTO->retNumIdSistema();

      $sistemaRN = new SistemaRN();
      $sistemaDTO = $sistemaRN->consultar($sistemaDTO);

    if (!empty($sistemaDTO)) {
        $numIdSistema = $sistemaDTO->getNumIdSistema();
    }

      //Cria função genérica de cadastro de perfil
      $fnCadastrarPerfil = function ($numIdSistema, $nome, $descricao, $coordenado, $ativo) {
          $objPerfilDTO = new PerfilDTO();
          $objPerfilDTO->setNumIdSistema($numIdSistema);
          $objPerfilDTO->setStrNome($nome);
          $objPerfilDTO->setStrDescricao($descricao);
          $objPerfilDTO->setStrSinCoordenado($coordenado);
          $objPerfilDTO->setStrSinAtivo($ativo);

          $objPerfilRN = new PerfilRN();
          $objPerfilDTO = $objPerfilRN->cadastrar($objPerfilDTO);

          return $objPerfilDTO->getNumIdPerfil();
      };

      //Cadastrar os perfis
      $id_perfil_arquivamento = $fnCadastrarPerfil($numIdSistema, 'GD Arquivamento', 'Acesso aos recursos de arquivamento e desarquivamento de processos, Pendências de Arquivamento e Arquivo da Unidade. Também possibilita consultar a lista de Unidades de Arquivamento e as Justificativas de Arquivamento e Desarquivamento.', 'N', 'S');
      $id_perfil_avaliacao = $fnCadastrarPerfil($numIdSistema, 'GD Avaliação', 'Acesso aos recursos de avaliação documental: devolução para correção, preparação e gestão das listagens de eliminação e recolhimento.', 'N', 'S');
        
      $this->arrRecurso = [];
      $this->arrRecurso = array_merge($this->arrRecurso, array(
          $this->consultarRecurso($numIdSistema, "gd_arquivamento_edicao_concluir"),
          $this->consultarRecurso($numIdSistema, "gd_arquivamento_editar"),
          $this->consultarRecurso($numIdSistema, "gd_arquivamento_historico_listar"),
          $this->consultarRecurso($numIdSistema, "gd_arquivamento_listar"),
          $this->consultarRecurso($numIdSistema, "gd_justificativa_listar"),
          $this->consultarRecurso($numIdSistema, "gd_pendencia_arquivamento_anotar"),
          $this->consultarRecurso($numIdSistema, "gd_pendencia_arquivamento_listar"),
          $this->consultarRecurso($numIdSistema, "gd_procedimento_arquivar"),
          $this->consultarRecurso($numIdSistema, "gd_procedimento_desarquivar"),
          $this->consultarRecurso($numIdSistema, "gd_procedimento_reabrir"),
          $this->consultarRecurso($numIdSistema, "gd_unidade_arquivamento_listar")
          ));

      $this->arrMenu = [];
      $this->arrMenu = array_merge($this->arrMenu, array(
          $this->consultarItemMenu($numIdSistema, "gd_arquivamento_listar"),
          $this->consultarItemMenu($numIdSistema, "gd_justificativa_listar"),
          $this->consultarItemMenu($numIdSistema, "gd_pendencia_arquivamento_listar"),
          $this->consultarItemMenu($numIdSistema, "gd_unidade_arquivamento_listar")
          ));

      //Atribui as permissões aos recursos e menus
      $this->atribuirPerfil($numIdSistema, $id_perfil_arquivamento);

      $this->arrRecurso = [];
      $this->arrRecurso = array_merge($this->arrRecurso, array(
          $this->consultarRecurso($numIdSistema, "gd_arquivamento_avaliar"),
          $this->consultarRecurso($numIdSistema, "gd_arquivamento_devolver"),
          $this->consultarRecurso($numIdSistema, "gd_arquivamento_edicao_concluir"),
          $this->consultarRecurso($numIdSistema, "gd_arquivamento_editar"),
          $this->consultarRecurso($numIdSistema, "gd_arquivamento_eliminacao_enviar"),
          $this->consultarRecurso($numIdSistema, "gd_arquivamento_historico_listar"),
          $this->consultarRecurso($numIdSistema, "gd_arquivamento_recolhimento_enviar"),
          $this->consultarRecurso($numIdSistema, "gd_justificativa_alterar"),
          $this->consultarRecurso($numIdSistema, "gd_justificativa_cadastrar"),
          $this->consultarRecurso($numIdSistema, "gd_justificativa_excluir"),
          $this->consultarRecurso($numIdSistema, "gd_justificativa_listar"),
          $this->consultarRecurso($numIdSistema, "gd_justificativa_visualizar"),
          $this->consultarRecurso($numIdSistema, "gd_listar_recolhimento_anotar"),
          $this->consultarRecurso($numIdSistema, "gd_listar_eliminacao_anotar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_documentos_fisicos_eliminar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_documentos_fisicos_listar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_edicao_concluir"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_editar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_eliminar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_listar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_pdf_gerar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_preparacao_excluir"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_preparacao_gerar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_preparacao_listar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_preparacao_observar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_procedimento_remover"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_visualizar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_documentos_fisicos_listar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_documentos_fisicos_recolher"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_edicao_concluir"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_editar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_listar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_pdf_gerar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_preparacao_excluir"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_preparacao_gerar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_preparacao_listar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_preparacao_observar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_procedimento_adicionar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_procedimento_remover"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_recolher"),
          $this->consultarRecurso($numIdSistema, "gd_lista_recolhimento_visualizar"),
          $this->consultarRecurso($numIdSistema, "gd_modelo_documento_alterar"),
          $this->consultarRecurso($numIdSistema, "gd_parametro_alterar"),
          $this->consultarRecurso($numIdSistema, "gd_relatorio"),
          $this->consultarRecurso($numIdSistema, "gd_unidade_arquivamento_alterar"),
          $this->consultarRecurso($numIdSistema, "gd_unidade_arquivamento_cadastrar"),
          $this->consultarRecurso($numIdSistema, "gd_unidade_arquivamento_excluir"),
          $this->consultarRecurso($numIdSistema, "gd_unidade_arquivamento_listar"),
          $this->consultarRecurso($numIdSistema, "gd_unidade_arquivamento_visualizar"),
          $this->consultarRecurso($numIdSistema, "gd_lista_eliminacao_procedimento_adicionar")
          ));

      $this->arrMenu = [];
      $this->arrMenu = array_merge($this->arrMenu, array(
          $this->consultarItemMenu($numIdSistema, "gd_arquivamento_avaliar"),
          $this->consultarItemMenu($numIdSistema, "gd_justificativa_cadastrar"),
          $this->consultarItemMenu($numIdSistema, "gd_justificativa_listar"),
          $this->consultarItemMenu($numIdSistema, "gd_lista_eliminacao_listar"),
          $this->consultarItemMenu($numIdSistema, "gd_lista_eliminacao_preparacao_listar"),
          $this->consultarItemMenu($numIdSistema, "gd_lista_recolhimento_listar"),
          $this->consultarItemMenu($numIdSistema, "gd_lista_recolhimento_preparacao_listar"),
          $this->consultarItemMenu($numIdSistema, "gd_modelo_documento_alterar"),
          $this->consultarItemMenu($numIdSistema, "gd_parametro_alterar"),
          $this->consultarItemMenu($numIdSistema, "gd_relatorio"),
          $this->consultarItemMenu($numIdSistema, "gd_unidade_arquivamento_cadastrar"),
          $this->consultarItemMenu($numIdSistema, "gd_unidade_arquivamento_listar")
          ));

      //Atribui as permissões aos recursos e menus
      $this->atribuirPerfil($numIdSistema, $id_perfil_avaliacao);
  }

  public function versao_1_2_4($strVersaoAtual)
    {
  }

  public function versao_1_2_5($strVersaoAtual)
    {
  }

  public function versao_1_2_6($strVersaoAtual)
    {
  }

  public function versao_1_2_7($strVersaoAtual)
    {
  }
}

try {
    session_start();

    SessaoSip::getInstance(false);
    BancoSip::getInstance()->setBolScript(true);

    $objVersaoSipRN = new VersaoSipRN();
    $objVersaoSipRN->verificarVersaoInstalada();
    $objVersaoSipRN->setStrNome(VersaoSipRN::NOME_MODULO);
    $objVersaoSipRN->setStrParametroVersao(VersaoSipRN::PARAMETRO_VERSAO_MODULO);
    $objVersaoSipRN->setArrVersoes(
        array(
            '0.0.0' => 'versao_0_0_0',
            '0.4.0' => 'versao_0_4_0',
            '0.5.0' => 'versao_0_5_0',
            '0.5.1' => 'versao_0_5_1',
            '0.5.2' => 'versao_0_5_2',
            '1.2.0' => 'versao_1_2_0',
            '1.2.1' => 'versao_1_2_1',
            '1.2.2' => 'versao_1_2_2',
            '1.2.3' => 'versao_1_2_3',
            '1.2.4' => 'versao_1_2_4',
            '1.2.5' => 'versao_1_2_5',
            '1.2.6' => 'versao_1_2_6',
            '1.2.7' => 'versao_1_2_7',
        )
    );

    $objVersaoSipRN->setStrVersaoAtual(array_key_last($objVersaoSipRN->getArrVersoes()));
    $objVersaoSipRN->setStrVersaoInfra('1.595.1');
    $objVersaoSipRN->setBolMySql(true);
    $objVersaoSipRN->setBolOracle(true);
    $objVersaoSipRN->setBolSqlServer(true);
    $objVersaoSipRN->setBolPostgreSql(true);
    $objVersaoSipRN->setBolErroVersaoInexistente(true);

    $objVersaoSipRN->atualizarVersao();
} catch (Exception $e) {
    echo (InfraException::inspecionar($e));
  try {
      LogSip::getInstance()->gravar(InfraException::inspecionar($e));
  } catch (Exception $e) {
  }
    exit(1);
}
