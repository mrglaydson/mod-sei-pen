<?php

require_once dirname(__FILE__) . '/../../web/Sip.php';

class VersaoSipRN extends InfraScriptVersao
{
    const PARAMETRO_VERSAO_MODULO = 'VERSAO_MODULO_GESTAO_DOCUMENTAL';
    const NOME_MODULO = 'M�dulo de Gest�o Documental';

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

      // Cria a fun��o gen�ria para o cadastramento de menus
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


      # CONFIGURA��ES DO M�DULO: PAR�METROS

      $id_recurso_parametro_alterar = $fnCadastrarRecurso($id_sistema, 'gd_parametro_alterar', 'Altera��o dos par�metros do m�dulo de gest�o documental', 'controlador.php?acao=gd_parametro_alterar', 'S');

      # CONFIGURA��ES DO M�DULO: JUSTIFICATIVAS DE ARQUIVAMENTO

      // Gerenciamento das justificativas de arquivamento
      $id_recurso_justificativa_listar = $fnCadastrarRecurso($id_sistema, 'gd_justificativa_listar', 'Listagem das justificativas de arquivamento e desarquivamento de processos', 'controlador.php?acao=gd_justificativa_listar', 'S');
      $id_recurso_justificativa_cadastrar = $fnCadastrarRecurso($id_sistema, 'gd_justificativa_cadastrar', 'Inclus�o das justificativas de arquivamento e desarquivamento de processos', 'controlador.php?acao=gd_justificativa_cadastrar', 'S');
      $id_recurso_justificativa_alterar = $fnCadastrarRecurso($id_sistema, 'gd_justificativa_alterar', 'Altera��o das justificativas de arquivamento e desarquivamento de processos', 'controlador.php?acao=gd_justificativa_alterar', 'S');
      $id_recurso_justificativa_excluir = $fnCadastrarRecurso($id_sistema, 'gd_justificativa_excluir', 'Exclus�o das justificativas de arquivamento e desarquivamento de processos', 'controlador.php?acao=gd_justificativa_excluir', 'S');
      $id_recurso_justificativa_visualizar = $fnCadastrarRecurso($id_sistema, 'gd_justificativa_visualizar', 'Visualiza��o das justificativas de arquivamento e desarquivamento de processos', 'controlador.php?acao=gd_justificativa_visualizar', 'S');

      # CONFIGURA��ES DO M�DULO: UNIDADES DE ARQUIVAMENTO

      // Gerenciamento das unidades de arquivamento
      $id_recurso_unidade_arquivamento_cadastrar = $fnCadastrarRecurso($id_sistema, 'gd_unidade_arquivamento_cadastrar', 'Cadastro das unidades de arquivamento do m�dulo de gest�o documental', 'controlador.php?acao=gd_unidade_arquivamento_cadastrar', 'S');
      $id_recurso_unidade_arquivamento_alterar = $fnCadastrarRecurso($id_sistema, 'gd_unidade_arquivamento_alterar', 'Altera��o das unidades de arquivamento do m�dulo de gest�o documental', 'controlador.php?acao=gd_unidade_arquivamento_alterar', 'S');
      $id_recurso_unidade_arquivamento_listar = $fnCadastrarRecurso($id_sistema, 'gd_unidade_arquivamento_listar', 'Listagem das unidades de arquivamento do m�dulo de gest�o documental', 'controlador.php?acao=gd_unidade_arquivamento_listar', 'S');
      $id_recurso_unidade_arquivamento_excluir = $fnCadastrarRecurso($id_sistema, 'gd_unidade_arquivamento_excluir', 'Exclus�o das unidades de arquivamento do m�dulo de gest�o documental', 'controlador.php?acao=gd_unidade_arquivamento_excluir', 'S');
      $id_recurso_unidade_arquivamento_visualizar = $fnCadastrarRecurso($id_sistema, 'gd_unidade_arquivamento_visualizar', 'Exclus�o das unidades de arquivamento do m�dulo de gest�o documental', 'controlador.php?acao=gd_unidade_arquivamento_visualizar', 'S');

      # CONFIGURA��ES DO M�DULO: MODELOS DE DOCUMENTO

      //Cadastra o recurso de edi��o dos modelos de documento
      $id_recurso_modelos_documento_alterar = $fnCadastrarRecurso($id_sistema, 'gd_modelo_documento_alterar', 'Altera��o dos modelos de documento do m�dulo de gest�o documental', 'controlador.php?acao=gd_modelo_documento_alterar', 'S');


      # RECURSOS DE ARQUIVAMENTO 

      $id_recurso_procedimento_arquivar = $fnCadastrarRecurso($id_sistema, 'gd_procedimento_arquivar', 'Arquivamento de processos', 'controlador.php?acao=gd_procedimento_arquivar', 'S');

      # RECURSOS DE DESARQUIVAMENTO

      $id_recurso_desarquivar_processo = $fnCadastrarRecurso($id_sistema, 'gd_procedimento_desarquivar', 'Desarquivamento de processos', 'controlador.php?acao=gd_procedimento_desarquivar', 'S');


      # RECURSOS DA PEND�NCIA DE ARQUIVAMENTO

      $id_recurso_pendencias_arquivamento = $fnCadastrarRecurso($id_sistema, 'gd_pendencia_arquivamento_listar', 'Pend�ncias de Arquivamento', 'controlador.php?acao=gd_pendencia_arquivamento_listar', 'S');
      $id_recurso_pendencias_arquivamento_anotacao = $fnCadastrarRecurso($id_sistema, 'gd_pendencia_arquivamento_anotar', 'Anota��es das pend�ncias de arquivamento', 'controlador.php?acao=gd_pendencia_arquivamento_anotar', 'S');

      # RECURSOS DA LISTAGEM DE ARQUIVAMENTOS 

      $id_recurso_arquivamento_listar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_listar', 'Listar arquivo da unidade.', 'controlador.php?acao=gd_arquivamento_listar', 'S');
      $id_recurso_arquivamento_editar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_editar', 'Editar processo arquivado.', 'controlador.php?acao=gd_arquivamento_editar', 'S');
      $id_recurso_arquivamento_edicao_concluir = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_edicao_concluir', 'Concluir edi��o de processo arquivado.', 'controlador.php?acao=gd_arquivamento_edicao_concluir', 'S');
      $id_recurso_procedimento_reabrir = $fnCadastrarRecurso($id_sistema, 'gd_procedimento_reabrir', 'Reabrir processo no contexto m�dulo de gest�o documental.', 'controlador.php?acao=gd_procedimento_reabrir', 'S');


      # RECUROS DE CONSULTA AO HIST�RICO DE ARQUIVAMENTO
      $id_recurso_arquivamento_historico_listar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_historico_listar', 'Listar o hist�rico de arquivamento.', 'controlador.php?acao=gd_arquivamento_historico_listar', 'S');

      # RECURSOS DA AVALIA��O DE PROCESSOS

      $id_recurso_avaliacao_processos_listar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_avaliar', 'Listagem de processos arquivados para avalia��o da unidade de arquivo.', 'controlador.php?acao=gd_arquivamento_avaliar', 'S');
      $id_recurso_arquivamento_recolhimento_enviar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_recolhimento_enviar', 'Enviar processo arquivado para recolhimento.', 'controlador.php?acao=gd_arquivamento_recolhimento_enviar', 'S');
      $id_recurso_arquivamento_eliminacao_enviar = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_eliminacao_enviar', 'Enviar processo arquivado para elimina��o.', 'controlador.php?acao=gd_arquivamento_eliminacao_enviar', 'S');
      $id_recurso_arquivamento_devolver = $fnCadastrarRecurso($id_sistema, 'gd_arquivamento_devolver', 'Devolver processos arquivado para unidade que o arquivou.', 'controlador.php?acao=gd_arquivamento_devolver', 'S');


      # PREPARA��O DA LISTA DE ELIMINA��O

      //Cadasta o recurso de listagem da prepara��o de listagem de elimina��o
      $id_recurso_lista_eliminacao_preparacao_listar = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_preparacao_listar', 'Lista de prepara��o para a listagem de elimina��o', 'controlador.php?acao=gd_lista_eliminacao_preparacao_listar', 'S');

      // Cadastra o recurso de gera��o da listagem de elimina��o
      $id_recurso_lista_eliminacao_preparacao_gerar = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_preparacao_gerar', 'Gera��o da listagem de elimina��o', 'controlador.php?acao=gd_lista_eliminacao_preparacao_gerar', 'S');

      // Cadastra o recurso da exclus�o de prapara��o para a listagem de elimina��o
      $id_recurso_lista_eliminacao_preparacao_excluir = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_preparacao_excluir', 'Exclus�o da prepara��o para a listagem de elimina��o', 'controlador.php?acao=gd_lista_eliminacao_preparacao_excluir', 'S');

      // Cadastra o recurso da observa��o de prepara��o para a listagem de elimina��o
      $id_recurso_lista_eliminacao_preparacao_observar = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_preparacao_observar', 'Observa��o/Justificativa da prepara��o para a listagem de elimina��o', 'controlador.php?acao=gd_lista_eliminacao_preparacao_observar', 'S');


      # PREPARA��O DA LISTA DE RECOLHIMENTO

      //Cadasta o recurso da prepara��o de listagem de recolhimento
      $id_recurso_lista_recolhimento_preparacao_listar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_preparacao_listar', 'Lista de prepara��o para a listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_preparacao_listar', 'S');

      // Cadastra o recurso da exclus�o de prepara��o para a listagem de recolhimento
      $id_recurso_lista_recolhimento_preparacao_excluir = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_preparacao_excluir', 'Exclus�o da prepara��o para a listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_preparacao_excluir', 'S');

      // Cadastra o recurso da observa��o de prepara��o para a listagem de recolhimento
      $id_recurso_lista_recolhimento_preparacao_observar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_preparacao_observar', 'Observa��o/Justificativa da prepara��o para a listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_preparacao_observar', 'S');

      // Cadastra o recurso de gera��o da listagem de recolhimento
      $id_recurso_lista_recolhimento_preparacao_gerar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_preparacao_gerar', 'Gera��o da listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_preparacao_gerar', 'S');

      # GEST�O DA LISTAGEM DE RECOLHIMENTO

      //Cadasta o recurso da gest�o de listagem de recolhimento
      $id_recurso_gestao_listagem_recolhimento = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_listar', 'Gest�o da listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_listar', 'S');

      // Cadastra o recurso de visualizar listagem de recolhimento
      $id_recurso_lista_recolhimento_visualizar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_visualizar', 'Visualizar listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_visualizar', 'S');

      // Cadastra o recurso de edi��o do recolhimento
      $id_recurso_lista_recolhimento_editar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_editar', 'Editar listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_editar', 'S');

      // Cadastra o recurso de gera��o do pdf da listagem de recolhimento
      $id_recurso_lista_recolhimento_pdf_gerar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_pdf_gerar', 'Gera��o de pdf da listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_pdf_gerar', 'S');

      // Cadastra o reurso de conclus�o da edi��o do recolhimento
      $id_recurso_lista_recolhimento_edicao_concluir = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_edicao_concluir', 'Concluir edi��o da listagem de recolhimento', 'controlador.php?acao=gd_lista_recolhimento_edicao_concluir', 'S');

      // Cadastra o recurso de recolhimento
      $id_recurso_recolhimento = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_recolher', 'Recolhimento de processos', 'controlador.php?acao=gd_lista_recolhimento_recolher', 'S');

      // Cadastra o recurso de adi��o da lista de recolhimento
      $id_recurso_lista_recolhimento_procedimento_adicionar = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_procedimento_adicionar', 'Adicionar processo na listagem de elimina��o', 'controlador.php?acao=gd_lista_recolhimento_procedimento_adicionar', 'S');

      // Cadastra o recurso de remo��o da lista de recolhimento
      $id_recurso_lista_recolhimento_procedimento_remover = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_procedimento_remover', 'Remover processo da listagem de elimina��o', 'controlador.php?acao=gd_lista_recolhimento_procedimento_remover', 'S');



      # GEST�O DA LISTAGEM DE ELIMINA��O

      //Cadasta o recurso de gest�o da listagem de elimina��o
      $id_recurso_gestao_listagem_eliminacao = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_listar', 'Gest�o da listagem de elimina��o', 'controlador.php?acao=gd_lista_eliminacao_listar', 'S');

      // Cadastra o recurso de visualiza��o da listagem de elimina��o
      $id_recurso_visualizacao_listagem_eliminacao = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_visualizar', 'Visualiza��o da listagem de elimina��o', 'controlador.php?acao=gd_lista_eliminacao_visualizar', 'S');

      // Cadastra o recurso de gera��o do pdf da listagem de elimina��o
      $id_recurso_geracao_pdf_listagem_eliminacao = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_pdf_gerar', 'Gera��o de pdf da listagem de elimina��o', 'controlador.php?acao=gd_lista_eliminacao_pdf_gerar', 'S');

      // Cadastra o recurso de edi��o do elimina��o
      $id_recurso_lista_eliminacao_editar = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_editar', 'Editar listagem de elimina��o', 'controlador.php?acao=gd_lista_eliminacao_editar', 'S');

      // Cadastra o recurso de gera��o do pdf da listagem de elimina��o
      $id_recurso_lista_eliminacao_edicao_concluir = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_edicao_concluir', 'Concluir edi��o da lista de elimina��o', 'controlador.php?acao=gd_lista_eliminacao_edicao_concluir', 'S');

      // Cadastra o recurso de elimina��o
      $id_recurso_eliminacao = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_eliminar', 'Elimina��o de processos', 'controlador.php?acao=gd_lista_eliminacao_eliminar', 'S');

      // Cadastra o recurso de adi��o da lista de elimina��o
      $id_recurso_lista_eliminacao_procedimento_adicionar = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_procedimento_adicionar', 'Adicionar processo na listagem de elimina��o', 'controlador.php?acao=gd_lista_eliminacao_procedimento_adicionar', 'S');

      // Cadastra o recurso de remo��o da lista de elimina��o
      $id_recurso_lista_eliminacao_procedimento_remover = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_procedimento_remover', 'Remover processo da listagem de elimina��o', 'controlador.php?acao=gd_lista_eliminacao_procedimento_remover', 'S');


      // Cadastra o recurso de listagem documentos fisicos para elimina��o
      $id_recurso_listagem_eliminacao_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_documentos_fisicos_listar', 'Listagem dos documentos f�sicos para elimina��o', 'controlador.php?acao=gd_lista_eliminacao_documentos_fisicos_listar', 'S');

      // Cadastra o recurso de registro da elimina��o de documentos fisicos
      $id_recurso_eliminacao_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gd_lista_eliminacao_documentos_fisicos_eliminar', 'Elimina��o dos documentos f�sicos', 'controlador.php?acao=gd_lista_eliminacao_documentos_fisicos_eliminar', 'S');


      // Cadastra o recurso de listagem documentos fisicos para recolhimento
      $id_recurso_listagem_recolhimento_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_documentos_fisicos_listar', 'Listagem dos documentos f�sicos para recolhimento', 'controlador.php?acao=gd_lista_recolhimento_documentos_fisicos_listar', 'S');

      // Cadastra o recurso de registro da elimina��o de documentos fisicos
      $id_recurso_recolhimento_documentos_fisicos = $fnCadastrarRecurso($id_sistema, 'gd_lista_recolhimento_documentos_fisicos_recolher', 'Recolhimento dos documentos f�sicos', 'controlador.php?acao=gd_lista_recolhimento_documentos_fisicos_recolher', 'S');


      // Cadastra o recurso de relat�rios
      $id_recurso_relatorio = $fnCadastrarRecurso($id_sistema, 'gd_relatorio', 'Relat�rio da gest�o documental', 'controlador.php?acao=gd_relatorio', 'S');

      // Cadastro o recurso de listagem de elimina��es
      // $id_recurso_eliminacao_listar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_eliminacao_listar', 'Listagem de elimina��es', 'controlador.php?acao=gd_eliminacao_listar', 'S');

      // gestao_documental_recolhimento_listar
      // $id_recurso_recolhimento_listar = $fnCadastrarRecurso($id_sistema, 'gestao_documental_recolhimento_listar', 'Listar recolhimentos', 'controlador.php?acao=gd_recolhimento_listar', 'S');

      ###########################################################ITENS DE MENU#############################################################################################
      // Cria o item de menu gest�o documental
      $id_menu_gestao_documental = $fnItemMenu($id_menu, $id_item_menu_pai, $id_sistema, null, 'Gest�o Documental', 'N', 'S', 10);

      //Cria o item de menu de edi��o de par�metros
      $id_menu_parametros = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_parametro_alterar, 'Configura��es', 'N', 'S', 1);

      //Cria os itens de menu de justificativas
      $id_menu_justificativas = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Justificativas de Arquivamento / Desarquivamento', 'N', 'S', 1);
      $id_menu_listar_justificativas = $fnItemMenu($id_menu, $id_menu_justificativas, $id_sistema, $id_recurso_justificativa_listar, 'Listar', 'N', 'S', 1);
      $id_menu_incluir_justificativas = $fnItemMenu($id_menu, $id_menu_justificativas, $id_sistema, $id_recurso_justificativa_cadastrar, 'Novo', 'N', 'S', 2);

      //Cria os itens de menu pend�ncias de arquivamento e processos arquivados
      $id_menu_pendencias_arquivamento = $fnItemMenu($id_menu, null, $id_sistema, $id_recurso_pendencias_arquivamento, 'Pend�ncias de Arquivamento', 'N', 'S', 133);
      $id_menu_arquivamento_listar = $fnItemMenu($id_menu, null, $id_sistema, $id_recurso_arquivamento_listar, 'Arquivo da Unidade', 'N', 'S', 131);



      //Cria o item de menu de modelos de documento
      $id_menu_modelos_documento_alterar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_modelos_documento_alterar, 'Modelos de Documentos', 'N', 'S', 2);

      //Cria os itens os itens de menu de unidades de arquivamento
      $id_menu_unidades_arquivamento = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Unidades de Arquivamento', 'N', 'S', 3);
      $id_menu_unidades_arquivamento_novo = $fnItemMenu($id_menu, $id_menu_unidades_arquivamento, $id_sistema, $id_recurso_unidade_arquivamento_cadastrar, 'Novo', 'N', 'S', 1);
      $id_menu_unidades_arquivamento_listar = $fnItemMenu($id_menu, $id_menu_unidades_arquivamento, $id_sistema, $id_recurso_unidade_arquivamento_listar, 'Listar', 'N', 'S', 2);

      // Cria o item de menu principal gest�o documental
      $id_menu_gestao_documental = $fnItemMenu($id_menu, null, $id_sistema, null, 'Gest�o Documental', 'N', 'S', 132);
      $id_menu_avaliacao_processos_listar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_avaliacao_processos_listar, 'Avalia��o de Processos', 'N', 'S', 1);

      // Cria os itens de menu gestao documental -> listagem elimina��o
      $id_menu_listagem_eliminacao = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Listagens de Elimina��o', 'N', 'S', 2);
      $id_menu_prep_list_eliminacao = $fnItemMenu($id_menu, $id_menu_listagem_eliminacao, $id_sistema, $id_recurso_lista_eliminacao_preparacao_listar, 'Prepara��o da Listagem', 'N', 'S', 1);
      $id_menu_gestao_listagem_eliminacao = $fnItemMenu($id_menu, $id_menu_listagem_eliminacao, $id_sistema, $id_recurso_gestao_listagem_eliminacao, 'Gest�o das Listagens', 'N', 'S', 2);
      //    $id_menu_eliminacao_listar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_eliminacao_listar, 'Elimina��es', 'N', 'S', 3);

      // Cria os itens de menu gestao documental -> listagem recolhimento
      $id_menu_listagem_recolhimento = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, null, 'Listagens de Recolhimento', 'N', 'S', 3);
      $id_menu_prep_list_recolhimento = $fnItemMenu($id_menu, $id_menu_listagem_recolhimento, $id_sistema, $id_recurso_lista_recolhimento_preparacao_listar, 'Prepara��o da Listagem', 'N', 'S', 1);
      $id_menu_gestao_listagem_recolhimento = $fnItemMenu($id_menu, $id_menu_listagem_recolhimento, $id_sistema, $id_recurso_gestao_listagem_recolhimento, 'Gest�o das Listagens', 'N', 'S', 2);
      //    $id_menu_recolhimentos_listar = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_recolhimento_listar, 'Recolhimentos', 'N', 'S', 3);

      // Cria o item de menu de relat�rios
      $id_menu_relatorio = $fnItemMenu($id_menu, $id_menu_gestao_documental, $id_sistema, $id_recurso_relatorio, 'Relat�rios', 'N', 'S', 4);
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
        throw new InfraException("Recurso com nome {$strNomeRecurso} n�o pode ser localizado.");
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
        throw new InfraException("Item de menu n�o pode ser localizado.");
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

      # PREPARA��O DA LISTA DE RECOLHIMENTO

      //Cadasta o recurso da prepara��o de listagem de recolhimento
      $id_recurso_listagem_recolhimento_anotacao = $fnCadastrarRecurso($id_sistema, 'gd_listar_recolhimento_anotar', 'Anota��es da listagem de recolhimento', 'controlador.php?acao=gd_listar_recolhimento_anotar', 'S');
      $id_recurso_listagem_eliminacao_anotacao = $fnCadastrarRecurso($id_sistema, 'gd_listar_eliminacao_anotar', 'Anota��es da listagem de elimina��o', 'controlador.php?acao=gd_listar_eliminacao_anotar', 'S');
        
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

      //Cria fun��o gen�rica de cadastro de perfil
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
      $id_perfil_arquivamento = $fnCadastrarPerfil($numIdSistema, 'GD Arquivamento', 'Acesso aos recursos de arquivamento e desarquivamento de processos, Pend�ncias de Arquivamento e Arquivo da Unidade. Tamb�m possibilita consultar a lista de Unidades de Arquivamento e as Justificativas de Arquivamento e Desarquivamento.', 'N', 'S');
      $id_perfil_avaliacao = $fnCadastrarPerfil($numIdSistema, 'GD Avalia��o', 'Acesso aos recursos de avalia��o documental: devolu��o para corre��o, prepara��o e gest�o das listagens de elimina��o e recolhimento.', 'N', 'S');
        
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

      //Atribui as permiss�es aos recursos e menus
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

      //Atribui as permiss�es aos recursos e menus
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
