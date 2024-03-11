<?

class MdGestaoDocumentalIntegracao extends SeiIntegracao {

    const VERSAO_MODULO = "1.2.7";


  public function __construct() {
        
  }

  public function getNome() {
      return 'Módulo de Gestão Documental';
  }

  public function getVersao() {
      return self::VERSAO_MODULO;
  }

  public function getInstituicao() {
      return 'Ministério da Gestão e da Inovação em Serviços Públicos - MGI';
  }

  public function inicializar($strVersaoSEI) {
        
  }

  public static function getDiretorio()
    {
      $arrConfig = ConfiguracaoSEI::getInstance()->getValor('SEI', 'Modulos');
      $strModulo = $arrConfig['MdGestaoDocumentalIntegracao'];
      return "modulos/".$strModulo;
  }

  public static function obterIdTarefaModulo($strIdTarefaModulo)
    {
      $objTarefaDTO = new TarefaDTO();
      $objTarefaDTO->retNumIdTarefa();
      $objTarefaDTO->setStrIdTarefaModulo($strIdTarefaModulo);

      $objTarefaRN = new TarefaRN();
      $objTarefaDTO = $objTarefaRN->consultar($objTarefaDTO);

    if($objTarefaDTO){
        return $objTarefaDTO->getNumIdTarefa();
    }else{
        return false;
    }
  }

  public function montarBotaoControleProcessos() {

      $arrBotoes = array();

      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setDistinct(true);
      $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objAtividadeDTO->setDthConclusao(null);
      $objAtividadeDTO->retNumIdUnidade();

      $objAtividadeRN = new AtividadeRN();
      $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

    if($arrObjAtividadeDTO){
        $arrBotoes[] = '<a  href="#" onclick="acaoControleProcessos(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_arquivar&acao_origem=procedimento_controlar&acao_retorno=procedimento_controlar'). '\', true, false)" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/icone_arquivar_processo.png" alt="Arquivar Processo" title="Arquivar Processo" /></a>';
    }

      return $arrBotoes;
  }

  public function montarIconeControleProcessos($arrObjProcedimentoAPI) {

      $arrIcones = array();
    foreach($arrObjProcedimentoAPI as $objProcedimentoAPI) {   
      foreach($this->verificaArquivamentoEmEdicao($objProcedimentoAPI->getIdProcedimento()) as $objIcone) {
        $arrIcones[$objProcedimentoAPI->getIdProcedimento()][] = $objIcone;
      }
    }
      return $arrIcones;
  }

  public function montarIconeAcompanhamentoEspecial($arrObjProcedimentoAPI) {

      $arrIcones = array();
      return $arrIcones;
  }

  public function verificaArquivamentoEmEdicao($dblIdProcedimento, $iconeProcesso = false){
      $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
      $objMdGdArquivamentoDTO->setDblIdProcedimento($dblIdProcedimento);
      $objMdGdArquivamentoDTO->setStrSinAtivo('S');
      $objMdGdArquivamentoDTO->retStrSituacao();

      $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
      $bolArquivado = $objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO);

      $arrObjArvoreAcaoItemAPI = array();

    if ($bolArquivado) {
      if ($iconeProcesso){
        $objArvoreAcaoItemAPI = new ArvoreAcaoItemAPI();
        $objArvoreAcaoItemAPI->setTipo('MD_GD_PROCESSO');
        $objArvoreAcaoItemAPI->setId('MD_GD_PROCESSO_' . $dblIdProcedimento);
        $objArvoreAcaoItemAPI->setIdPai($dblIdProcedimento);
        $objArvoreAcaoItemAPI->setTitle('Processo Arquivado');
        $objArvoreAcaoItemAPI->setIcone( MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/arquivado.gif');
        $objArvoreAcaoItemAPI->setTarget(null);
        $objArvoreAcaoItemAPI->setHref('javascript:alert(\'Processo Arquivado\');');
        $objArvoreAcaoItemAPI->setSinHabilitado('S');
        $arrObjArvoreAcaoItemAPI[] = $objArvoreAcaoItemAPI;
      }else{
          //Se for montar icone na tela de Controle Processos
          $arrObjArvoreAcaoItemAPI[] = '<a href="javascript:void(0);"
                '.PaginaSEI::montarTitleTooltip('Processo Retornado para Correção', 'Módulo Gestão Documental').'><img
                src="'.MdGestaoDocumentalIntegracao::getDiretorio().'/imagens/arquivado.gif" class="imagemStatus" height="22px" width="24px" /></a>';
      }

        // Consulta o arquivamento
        $objMdGdArquivamentoDTO = $objMdGdArquivamentoRN->consultar($objMdGdArquivamentoDTO);

        // Verifica se o arquivamento está com a situação em edição
      if($objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_FASE_EDICAO){
        if ($iconeProcesso){
            $objArvoreAcaoItemAPI2 = new ArvoreAcaoItemAPI();
            $objArvoreAcaoItemAPI2->setTipo('MD_GD_PROCESSO');
            $objArvoreAcaoItemAPI2->setId('MD_GD_PROCESSO_' . $dblIdProcedimento);
            $objArvoreAcaoItemAPI2->setIdPai($dblIdProcedimento);
            $objArvoreAcaoItemAPI2->setTitle('Processo em Edição');
            $objArvoreAcaoItemAPI2->setIcone(MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/processo_editado.gif');
            $objArvoreAcaoItemAPI2->setTarget(null);
            $objArvoreAcaoItemAPI2->setHref('javascript:alert(\'Processo em Edição\');');
            $objArvoreAcaoItemAPI2->setSinHabilitado('S');
            $arrObjArvoreAcaoItemAPI[] = $objArvoreAcaoItemAPI2;
        }else{
            //Se for montar icone na tela de Controle Processos
            $arrObjArvoreAcaoItemAPI[] = '<a href="javascript:void(0);"
                    '.PaginaSEI::montarTitleTooltip('Processo em Edição', 'Módulo Gestão Documental').'><img
                    src="'.MdGestaoDocumentalIntegracao::getDiretorio().'/imagens/processo_editado.gif" class="imagemStatus" height="22px" width="24px" /></a>';
        }
      }            
    }

      return $arrObjArvoreAcaoItemAPI;
  }

  public function montarIconeProcesso(ProcedimentoAPI $objProcedimentoAPI) {

      $arrObjArvoreAcaoItemAPI = array();
      $dblIdProcedimento = $objProcedimentoAPI->getIdProcedimento();

      $arrObjArvoreAcaoItemAPI = $this->verificaArquivamentoEmEdicao($dblIdProcedimento, true);

      return $arrObjArvoreAcaoItemAPI;
  }

  public function montarBotaoProcesso(ProcedimentoAPI $objProcedimentoAPI) {
      $arrBotoes = array();
      $bolArquivado = false;

      // Valida as permissões dos botões
      $bolAcaoArquivamento = SessaoSEI::getInstance()->verificarPermissao('gd_procedimento_arquivar');
      $bolAcaoDesarquivamento = SessaoSEI::getInstance()->verificarPermissao('gd_procedimento_desarquivar');
      $bolAcaoHistoricoArquivamento = SessaoSEI::getInstance()->verificarPermissao('gd_arquivamento_historico_listar');
    
      // Verifica se o processo se encontra arquivado
      $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
      $objMdGdArquivamentoDTO->setDblIdProcedimento($objProcedimentoAPI->getIdProcedimento());
      $objMdGdArquivamentoDTO->setStrSinAtivo('S');

      $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
      $bolArquivado = $objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO);

    if($bolArquivado){
        $objMdGdArquivamentoDTO->retStrSituacao();
        $objMdGdArquivamentoDTO = $objMdGdArquivamentoRN->consultar($objMdGdArquivamentoDTO);

      if($objMdGdArquivamentoDTO->getStrSituacao() != MdGdArquivamentoRN::$ST_FASE_CORRENTE &&
           $objMdGdArquivamentoDTO->getStrSituacao() != MdGdArquivamentoRN::$ST_FASE_INTERMEDIARIA &&
           $objMdGdArquivamentoDTO->getStrSituacao() != MdGdArquivamentoRN::$ST_PREPARACAO_ELIMINACAO &&
           $objMdGdArquivamentoDTO->getStrSituacao() != MdGdArquivamentoRN::$ST_PREPARACAO_RECOLHIMENTO
        ){
        $bolAcaoDesarquivamento = false;
      }

    }

      // Verifica se o processo encontra-se aberto em mais de uma unidade
      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setDistinct(true);
     // $objAtividadeDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);
      $objAtividadeDTO->setDblIdProtocolo($objProcedimentoAPI->getIdProcedimento());
      $objAtividadeDTO->setDthConclusao(null);
      $objAtividadeDTO->retNumIdUnidade();

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->setDblIdProtocolo($objProcedimentoAPI->getIdProcedimento());
      $objProtocoloDTO->retStrStaEstado();

      $objProtocoloRN = new ProtocoloRN();
      $objProtocoloDTO=$objProtocoloRN->consultarRN0186($objProtocoloDTO);
      $statusProtocolo=$objProtocoloDTO->getStrStaEstado();
      $bolProcessoSobrestado=false;
        
    if($statusProtocolo == ProtocoloRN::$TE_PROCEDIMENTO_SOBRESTADO){
        $bolProcessoSobrestado=true;
    }

      $objAtividadeRN = new AtividadeRN();
      $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

      // Verifica a existência de uma unidade de arquivamento
      $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
      $bolUnidadeArquivamento = $objMdGdUnidadeArquivamentoRN->getNumIdUnidadeArquivamentoAtual() ? true : false;

      // Verifica a existência de registros de histórico de arquivamento
      $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
      $objMdGdArquivamentoDTO->setDblIdProcedimento($_GET['id_procedimento']);
      $objMdGdArquivamentoDTO->retNumIdArquivamento();

      $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        
      // Botão de histórico de arquivamento
    if ($bolAcaoHistoricoArquivamento && $objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO) > 0) {
        $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivamento_historico_listar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/arquivo.svg" alt="Consultar Histórico de Arquivamento do Processo" title="Consultar Histórico de Arquivamento do Processo" /></a>';
    }
        
      // Botão de arquivamento
    if (!$bolProcessoSobrestado && $bolAcaoArquivamento && !$bolArquivado && count($arrObjAtividadeDTO) == 1 && $arrObjAtividadeDTO[0]->getNumIdUnidade() == SessaoSEI::getInstance()->getNumIdUnidadeAtual()  && $bolUnidadeArquivamento && $objProcedimentoAPI->getNivelAcesso() != ProtocoloRN::$NA_SIGILOSO) {
        $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_arquivar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/icone_arquivar_processo.png" alt="Arquivar Processo" title="Arquivar Processo" /></a>';
    }

      // Botão de desarquivamento
    if ($bolAcaoDesarquivamento && $bolArquivado) {
        $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_desarquivar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/icone_desarquivar_processo.png" alt="Desarquivar Processo" title="Desarquivar Processo" /></a>';
    }

        

      return $arrBotoes;
  }

  public function montarIconeDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI) {

      $arrIcones = array();
      return $arrIcones;
  }

  public function atualizarAssuntoProcesso(ProcedimentoAPI $objProcedimentoAPI) {

      //avaliar como o modPEN faz
      //esta no script de instalacao, copiar para esse modulo e criar tarefas

    try{

      if($objProcedimentoAPI->getAssuntosAntigos()!=null ){

        $objAssuntoRN=new AssuntoRN();
        $idTarefaModuloGestao=$this->obterIdTarefaModulo('MOD_GESTAO_ATUALIZAR_ASSUNTO');
                
        foreach ($objProcedimentoAPI->getAssuntos() as $assunto) {
            $objAssuntoDTO=new AssuntoDTO();
            $objAssuntoDTO->retStrCodigoEstruturado();
            $objAssuntoDTO->setNumIdAssunto($assunto->getNumIdAssunto());
            $objAssuntoDTO= $objAssuntoRN->consultarRN0256($objAssuntoDTO);
            $arrayAssuntosId .= $objAssuntoDTO->getStrCodigoEstruturado() . ",";
        }
        $arrayAssuntosId = substr($arrayAssuntosId, 0, -1);

        foreach ($objProcedimentoAPI->getAssuntosAntigos() as $assunto) {
            $objAssuntoDTO=new AssuntoDTO();
            $objAssuntoDTO->retStrCodigoEstruturado();
            $objAssuntoDTO->setNumIdAssunto($assunto->getNumIdAssunto());
            $objAssuntoDTO= $objAssuntoRN->consultarRN0256($objAssuntoDTO);
            $arrayAssuntosAntigosId .= $objAssuntoDTO->getStrCodigoEstruturado() . ",";
        }
        $arrayAssuntosAntigosId = substr($arrayAssuntosAntigosId, 0, -1);

        $arrObjAtributoAndamentoDTO = array();

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('ASSUNTOS_ANTIGOS');
        $objAtributoAndamentoDTO->setStrValor($arrayAssuntosAntigosId);
        $objAtributoAndamentoDTO->setStrIdOrigem($objProcedimentoAPI->getNumeroProtocolo());
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('ASSUNTOS_NOVOS');
        $objAtributoAndamentoDTO->setStrValor($arrayAssuntosId);
        $objAtributoAndamentoDTO->setStrIdOrigem($objProcedimentoAPI->getNumeroProtocolo());
                
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setDblIdProtocolo($objProcedimentoAPI->getNumeroProtocolo());
        $objAtividadeDTO->setNumIdTarefa($idTarefaModuloGestao);

        $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);

        $objAtividadeRN = new AtividadeRN();
        $objAtividadeRN->gerarInternaRN0727($objAtividadeDTO);
      }

    }catch(Exception $e){
        throw new InfraException('Erro alterando historico do modulo Gestão documental', $e);
    }

  }

  public function montarBotaoDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI) {

      $arrBotoes = array();
      $bolArquivado = false;

      // Valida as permissões dos botões
      $bolAcaoArquivamento = SessaoSEI::getInstance()->verificarPermissao('gd_arquivar_processo');
      $bolAcaoDesarquivamento = SessaoSEI::getInstance()->verificarPermissao('gd_desarquivar_processo');

      // Verifica se o processo se encontra arquivado
      $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
      $objMdGdArquivamentoDTO->setDblIdProcedimento($objProcedimentoAPI->getIdProcedimento());
      $objMdGdArquivamentoDTO->setStrSinAtivo('S');

      $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
      $bolArquivado = $objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO);

      // Verifica se o processo encontra-se aberto em mais de uma unidade
      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setDistinct(true);
     // $objAtividadeDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);
      $objAtividadeDTO->setDblIdProtocolo($objProcedimentoAPI->getIdProcedimento());
      $objAtividadeDTO->setDthConclusao(null);
      $objAtividadeDTO->retNumIdUnidade();

      $objAtividadeRN = new AtividadeRN();
      $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

      // Botão de arquivamento do processo
    if ($bolAcaoArquivamento && !$bolArquivado && count($arrObjAtividadeDTO) == 1 && $arrObjAtividadeDTO[0]->getNumIdUnidade() == SessaoSEI::getInstance()->getNumIdUnidadeAtual()) {
        $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_arquivar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/icone_arquivar_processo.png" alt="Arquivar Processo" title="Arquivar Processo" /></a>';
    }

      // Botão de desarquivamento do processo
    if ($bolAcaoDesarquivamento && $bolArquivado) {
        $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_desarquivar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="' . MdGestaoDocumentalIntegracao::getDiretorio() . '/imagens/icone_desarquivar_processo.png" alt="Desarquivar Processo" title="Desarquivar Processo" /></a>';
    }

    if ($arrBotoes) {
        $arrBotoesDocumento = array();
      foreach ($arrObjDocumentoAPI as $objDocumentoAPI) {
          $arrBotoesDocumento[$objDocumentoAPI->getIdDocumento()] = $arrBotoes;
      }
    }

      return $arrBotoesDocumento;
  }

  public function alterarIconeArvoreDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI) {
      $arrIcones = array();
      return $arrIcones;
  }

  public function adicionarElementoMenu() {

      $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
      $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
      $objMdGdArquivamentoDTO->setNumIdUnidadeCorrente(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objMdGdArquivamentoDTO->setStrSinAtivo('S');
      $objMdGdArquivamentoDTO->setStrSituacao([MdGdArquivamentoRN::$ST_FASE_EDICAO], InfraDTO::$OPER_IN);
      $numProcessoEdicao = $objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO);

      $sinPendencia = false;
    if($numProcessoEdicao >= 1){
        $sinPendencia = true;
    }

      return '<link href="'.$this->getDiretorio().'/css/gd_arquivo_unidade.css" rel="stylesheet" type="text/css" media="all" /><script src="'.$this->getDiretorio().'/js/gd_arquivo_unidade.js" type="text/javascript"></script><script>notificarPendencias('.$sinPendencia.', '.$numProcessoEdicao.');</script>';
  }

  public function montarMenuPublicacoes() {
      $arrMenu = array();
      return $arrMenu;
  }

  public function montarMenuUsuarioExterno() {

      $arrMenu = array();
      return $arrMenu;
  }

  public function montarAcaoControleAcessoExterno($arrObjAcessoExternoAPI) {

      $arrIcones = array();
      return $arrIcones;
  }

  public function montarAcaoDocumentoAcessoExternoAutorizado($arrObjDocumentoAPI) {
      $arrIcones = array();
      return $arrIcones;
  }

  public function montarAcaoProcessoAnexadoAcessoExternoAutorizado($arrObjProcedimentoAPI) {
      $arrIcones = array();
      return $arrIcones;
  }

  public function montarBotaoAcessoExternoAutorizado(ProcedimentoAPI $objProcedimentoAPI) {
      $arrBotoes = array();
      return $arrBotoes;
  }

  public function montarBotaoControleAcessoExterno() {
      $arrBotoes = array();
      return $arrBotoes;
  }

  public function processarControlador($strAcao) {
    switch ($strAcao) {

        // Parï¿½metros de configuiração do Módulo de arquivamento
      case 'gd_parametro_alterar':
        require_once dirname(__FILE__) . '/gd_parametro_alterar.php';
          return true;

        // Modelos de documento do Módulo
      case 'gd_modelo_documento_alterar':
              require_once dirname(__FILE__) . '/gd_modelo_documento_alterar.php';
          return true;

      case 'gd_ajuda_variaveis_modelo_arquivamento':
      case 'gd_ajuda_variaveis_modelo_desarquivamento':
      case 'gd_ajuda_variaveis_modelo_listagem_eliminacao':
      case 'gd_ajuda_variaveis_modelo_documento_eliminacao':
      case 'gd_ajuda_variaveis_modelo_listagem_recolhimento':
      case 'gd_ajuda_variaveis_modelo_documento_recolhimento':
          require_once dirname(__FILE__) . '/gd_ajuda_variaveis_modelo.php';
          return true;    
    
        // Justificativas de arquivamento
      case 'gd_justificativa_listar':
      case 'gd_justificativa_excluir':
          require_once dirname(__FILE__) . '/gd_justificativa_listar.php';
          return true;

      case 'gd_justificativa_cadastrar':
      case 'gd_justificativa_alterar':
      case 'gd_justificativa_consultar':
      case 'gd_justificativa_visualizar':
          require_once dirname(__FILE__) . '/gd_justificativa_cadastrar.php';
          return true;
      case 'gd_unidade_arquivamento_selecionar':
              require_once dirname(__FILE__) . '/gd_unidade_arquivamento_selecionar.php';
          return true;

        // Unidades de arquivamento
      case 'gd_unidade_arquivamento_listar':
      case 'gd_unidade_arquivamento_excluir':
          require_once dirname(__FILE__) . '/gd_unidade_arquivamento_listar.php';
          return true;
                
      case 'gd_unidade_arquivamento_cadastrar':
      case 'gd_unidade_arquivamento_alterar':
      case 'gd_unidade_arquivamento_visualizar':
          require_once dirname(__FILE__) . '/gd_unidade_arquivamento_cadastrar.php';
          return true;
            
        // Arquivar procedimento    
      case 'gd_procedimento_arquivar':
          require_once dirname(__FILE__) . '/gd_procedimento_arquivar.php';
          return true;
            
        // Desarquivar procedimento
      case 'gd_procedimento_desarquivar':
          require_once dirname(__FILE__) . '/gd_procedimento_desarquivar.php';
          return true;
            
        // Pendï¿½ncias de arquivamento
      case 'gd_pendencia_arquivamento_listar':
      case 'gd_procedimento_reabrir':
          require_once dirname(__FILE__) . '/gd_pendencia_arquivamento_listar.php';
          return true;

      case 'gd_pendencia_arquivamento_anotar':
          require_once dirname(__FILE__) . '/gd_pendencia_arquivamento_anotar.php';
          return true;

        // Anotação de listagem de recolhimento
      case 'gd_listar_recolhimento_anotar':
          require_once dirname(__FILE__) . '/gd_listar_recolhimento_anotar.php';
          return true;

        // Anotação de listagem de eliminacao
      case 'gd_listar_eliminacao_anotar':
          require_once dirname(__FILE__) . '/gd_listar_eliminacao_anotar.php';
          return true;
            
        // Listar arquivamentos
      case 'gd_arquivamento_listar':
      case 'gd_arquivamento_editar':
      case 'gd_arquivamento_edicao_concluir':
          require_once dirname(__FILE__) . '/gd_arquivamento_listar.php';
          return true;
            
        // Listar histórico de arquivamento
      case 'gd_arquivamento_historico_listar':
          require_once dirname(__FILE__) . '/gd_arquivamento_historico_listar.php';
          return true;

        // Avaliação de processos
      case 'gd_arquivamento_avaliar':
      case 'gd_arquivamento_eliminacao_enviar':
      case 'gd_arquivamento_recolhimento_enviar':
          require_once dirname(__FILE__) . '/gd_arquivamento_avaliar.php';
          return true;

        // Devolução de um arquivamento
      case 'gd_arquivamento_devolver':
          require_once dirname(__FILE__) . '/gd_arquivamento_devolver.php';
          return true;
                
        // Preparação da lista de eliminação
      case 'gd_lista_eliminacao_preparacao_listar':
      case 'gd_lista_eliminacao_preparacao_gerar':
      case 'gd_lista_eliminacao_preparacao_excluir':
          require_once dirname(__FILE__) . '/gd_lista_eliminacao_preparacao_listar.php';
          return true;

      case 'gd_lista_eliminacao_preparacao_observar':
          require_once dirname(__FILE__) . '/gd_lista_eliminacao_preparacao_observar.php';
          return true;

        // Gestão das listagens de eliminação
      case 'gd_lista_eliminacao_listar':
      case 'gd_lista_eliminacao_editar':
      case 'gd_lista_eliminacao_edicao_concluir':
          require_once dirname(__FILE__) . '/gd_lista_eliminacao_listar.php';
          return true;

      case 'gd_lista_eliminacao_visualizar':
      case 'gd_lista_eliminacao_pdf_gerar':
          require_once dirname(__FILE__) . '/gd_lista_eliminacao_visualizar.php';
          return true;
        
        // Edição da listagem de eliminação
      case 'gd_lista_eliminacao_procedimento_adicionar':
          require_once dirname(__FILE__) . '/gd_lista_eliminacao_procedimento_adicionar.php';
          return true;

      case 'gd_lista_eliminacao_procedimento_remover':
          require_once dirname(__FILE__) . '/gd_lista_eliminacao_procedimento_remover.php';
          return true;
            
        // Eliminação de processos
      case 'gd_lista_eliminacao_eliminar':
          require_once dirname(__FILE__) . '/gd_lista_eliminacao_eliminar.php';
          return true;

        // Preparação da lista de recolhimento
      case 'gd_lista_recolhimento_preparacao_listar':
      case 'gd_lista_recolhimento_preparacao_gerar':
      case 'gd_lista_recolhimento_preparacao_excluir':
          require_once dirname(__FILE__) . '/gd_lista_recolhimento_preparacao_listar.php';
          return true;

      case 'gd_lista_recolhimento_preparacao_observar':
          require_once dirname(__FILE__) . '/gd_lista_recolhimento_preparacao_observar.php';
          return true;
    
        // Edição da listagem de recolhimento
      case 'gd_lista_recolhimento_procedimento_adicionar':
          require_once dirname(__FILE__) . '/gd_lista_recolhimento_procedimento_adicionar.php';
          return true;

      case 'gd_lista_recolhimento_procedimento_remover':
          require_once dirname(__FILE__) . '/gd_lista_recolhimento_procedimento_remover.php';
          return true;

        // Gestão das listagens de recolhimento
      case 'gd_lista_recolhimento_listar':
      case 'gd_lista_recolhimento_editar':
      case 'gd_lista_recolhimento_edicao_concluir':
      case 'gd_lista_recolhimento_recolher':
          require_once dirname(__FILE__) . '/gd_lista_recolhimento_listar.php';
          return true;

      case 'gd_lista_recolhimento_visualizar':
      case 'gd_lista_recolhimento_pdf_gerar':
          require_once dirname(__FILE__) . '/gd_lista_recolhimento_visualizar.php';
          return true;

        // Recolher documento fisico
      case 'gd_lista_recolhimento_documentos_fisicos_listar':
          require_once dirname(__FILE__) . '/gd_lista_recolhimento_documento_fisico_listar.php';
          return true;

      case 'gd_lista_recolhimento_documentos_fisicos_recolher':
          require_once dirname(__FILE__) . '/gd_lista_recolhimento_documento_fisico_recolher.php';
          return true;

        // Eliminar documento fisico
      case 'gd_lista_eliminacao_documentos_fisicos_listar':
          require_once dirname(__FILE__) . '/gd_lista_eliminacao_documento_fisico_listar.php';
          return true;

      case 'gd_lista_eliminacao_documentos_fisicos_eliminar':
          require_once dirname(__FILE__) . '/gd_lista_eliminacao_documento_fisico_eliminar.php';
          return true;
            
        // Relatório
      case 'gd_relatorio':
          require_once dirname(__FILE__) . '/gd_relatorio.php';
          return true;
    }

      return false;
  }

  public function processarControladorAjax($strAcao) {

      $xml = null;

    switch ($strAcao) {
      case 'gd_unidade_auto_completar_unidades_arquivamento':
        $arrObjUnidadeDTO = MdGdArquivamentoINT::montarSelectAjaxUnidadesArquivamento($_POST['palavras_pesquisa']);
        $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjUnidadeDTO, 'IdUnidade', 'Sigla');
          break;
      case 'gd_arquivamento_validar_senha':
          $objUsuarioDTOPesquisa = new UsuarioDTO();
          $objUsuarioDTOPesquisa->setBolExclusaoLogica(false);
          $objUsuarioDTOPesquisa->retNumIdUsuario();
          $objUsuarioDTOPesquisa->retStrSigla();
          $objUsuarioDTOPesquisa->retStrNome();
          $objUsuarioDTOPesquisa->retDblCpfContato();
          $objUsuarioDTOPesquisa->retStrStaTipo();
          $objUsuarioDTOPesquisa->retStrSenha();
          $objUsuarioDTOPesquisa->retNumIdContato();
          $objUsuarioDTOPesquisa->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
          
          $objUsuarioRN = new UsuarioRN();
          $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTOPesquisa);
          $bolSenha = 'S';

        if ($objUsuarioDTO->getStrStaTipo()==UsuarioRN::$TU_SIP){

          $objInfraSip = new InfraSip(SessaoSEI::getInstance());
          $objInfraSip->autenticar($_REQUEST['orgao'],
              SessaoSEI::getInstance()->getNumIdContextoUsuario(),
              $objUsuarioDTO->getStrSigla(),
              $_REQUEST['senha']);          
        }else{
          
          $bcrypt = new InfraBcrypt();
          if (!$bcrypt->verificar(md5($_REQUEST['senha']), $objUsuarioDTO->getStrSenha())) {
                $bolSenha = 'N';
          }
        }
     
            $xml = InfraAjax::gerarXMLComplementosArray(array('SinValida'=> $bolSenha));
          break;
      case 'gd_arquivamento_validar_configuracao':
          // Validar configuração do Módulo
          $objMdGdParametroRN = new MdGdParametroRN();
        if(!$objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_DESPACHO_ARQUIVAMENTO)){
            throw new InfraException('não foi configurado o tipo de documento para arquivamento!');
        }

          // Validar unidade de arquivamento
          $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
        if(!$objMdGdUnidadeArquivamentoRN->getNumIdUnidadeArquivamentoAtual()){
            throw new InfraException('A unidade atual não possui unidade de arquivamento configurada');
        }
          $xml = InfraAjax::gerarXMLComplementosArray(array('SinValida'=> 'S'));
    }
                
      return $xml;
  }

  public function processarControladorPublicacoes($strAcao) {

    switch ($strAcao) {

      case 'md_abc_publicacao_exemplo':
        require_once dirname(__FILE__) . '/publicacao_exemplo.php';
          return true;
    }

      return false;
  }

  public function processarControladorExterno($strAcao) {

    switch ($strAcao) {

      case 'md_abc_usuario_externo_exemplo':
        require_once dirname(__FILE__) . '/usuario_externo_exemplo.php';
          return true;
    }

      return false;
  }

  public function verificarAcessoProtocolo($arrObjProcedimentoAPI, $arrObjDocumentoAPI) {

      $ret = null;
      return $ret;
  }

  public function verificarAcessoProtocoloExterno($arrObjProcedimentoAPI, $arrObjDocumentoAPI) {

      $ret = null;
      return $ret;
  }

  public function montarMensagemProcesso(ProcedimentoAPI $objProcedimentoAPI) {
      $strMsg = '';

      $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
      $objMdGdArquivamentoDTO->setDblIdProcedimento($objProcedimentoAPI->getIdProcedimento());
      $objMdGdArquivamentoDTO->setStrSinAtivo('S');
      $objMdGdArquivamentoDTO->retDblIdProcedimento();
      $objMdGdArquivamentoDTO->retStrSiglaUnidadeCorrente();
      $objMdGdArquivamentoDTO->retStrSituacao();
      $objMdGdArquivamentoDTO->retStrObservacaoDevolucao();
        
      $objMdGdArquivamentoRN = new MdGdArquivamentoRN();

    if($objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO) == 1){
        $objMdGdArquivamentoDTO = $objMdGdArquivamentoRN->consultar($objMdGdArquivamentoDTO);
            
        $strMsg = '';
      if($objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_ENVIADO_RECOLHIMENTO){
        $strMsg .= 'Processo incluído em listagem de recolhimento.';
      }else if($objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_ENVIADO_ELIMINACAO){
          $strMsg .= 'Processo incluído em listagem de eliminação.';
      }else if($objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_ELIMINADO){
          $strMsg .= 'Processo eliminado.';
      }else if($objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_RECOLHIDO){
          $strMsg .= 'Processo recolhido.';
      }else{
          $strMsg .= 'Processo arquivado.';
      }
            
      if($objMdGdArquivamentoDTO && $objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_FASE_EDICAO){
          $strMsg = 'Processo arquivado em fase de correção. A ação deve ser concluída no Arquivo da Unidade '.$objMdGdArquivamentoDTO->getStrSiglaUnidadeCorrente().'. </br></br> <span style="font-weight:bold">Motivo da devolução:</span> ' . $objMdGdArquivamentoDTO->getStrObservacaoDevolucao();
      }

    }


      return $strMsg;
  }

  public function reabrirProcesso(ProcedimentoAPI $objProcedimentoAPI){
        
      $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
      $objMdGdArquivamentoDTO->setDblIdProcedimento($objProcedimentoAPI->getIdProcedimento());
      $objMdGdArquivamentoDTO->setStrSinAtivo('S');
      $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_DEVOLVIDO, InfraDTO::$OPER_DIFERENTE);
      $objMdGdArquivamentoDTO->retDblIdProcedimento();

      $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        
    if($objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO) != 0){
        $objInfraException = new InfraException();
        $objInfraException->lancarValidacao('O processo não pode ser reaberto pois encontra-se arquivado!');
        return false;
    }

      return null;
  }

  public function excluirDocumento(DocumentoAPI $objDocumentoAPI){
      $objInfraException = new InfraException();

      // Valida se o documento não estï¿½ vinculado a uma lista de eliminação
      $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
      $objMdGdListaEliminacaoDTO->setDblIdDocumentoEliminacao($objDocumentoAPI->getIdDocumento());
      $objMdGdListaEliminacaoDTO->retDblIdDocumentoEliminacao();

      $objMdGdListaEliminacaoRN = new MdGdListaEliminacaoRN();

    if($objMdGdListaEliminacaoRN->contar($objMdGdListaEliminacaoDTO) > 0){
        $objInfraException->lancarValidacao('O documento não pode ser excluído!');
        return false;
    }

      // Valida se o documento não estï¿½ vinculado a uma lista de recolhimento
      $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
      $objMdGdListaRecolhimentoDTO->setDblIdDocumentoRecolhimento($objDocumentoAPI->getIdDocumento());
      $objMdGdListaRecolhimentoDTO->retDblIdDocumentoRecolhimento();

      $objMdGdListaRecolhimentoRN = new MdGdListaRecolhimentoRN();

    if($objMdGdListaRecolhimentoRN->contar($objMdGdListaRecolhimentoDTO) > 0){
        $objInfraException->lancarValidacao('O documento não pode ser excluído!');
        return false;
    }

      return null;
  }

  public function concluirProcesso($arrObjProcedimentoAPI) {

    foreach($arrObjProcedimentoAPI as $objProcedimentoAPI) {
        $objProcedimentoDTO = new ProcedimentoDTO();
        $objProcedimentoDTO->setDblIdProcedimento($objProcedimentoAPI->getIdProcedimento());
        $objProcedimentoDTO->retStrStaEstadoProtocolo();
        $objProcedimentoDTO->retDblIdProcedimento();

        $objProcedimentoRN = new ProcedimentoRN();
        $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);
            
        // Bloqueia o processo
      if ($objProcedimentoDTO->getStrStaEstadoProtocolo() != ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO) {
        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
        $objMdGdArquivamentoDTO->setDblIdProcedimento($objProcedimentoAPI->getIdProcedimento());
        $objMdGdArquivamentoDTO->setStrSinAtivo('S');
        $objMdGdArquivamentoDTO->retStrSituacao();
        $objMdGdArquivamentoDTO->retDblIdProcedimento();
        
        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        $objMdGdArquivamentoDTO = $objMdGdArquivamentoRN->consultar($objMdGdArquivamentoDTO);

        if($objMdGdArquivamentoDTO) {
          if($objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_FASE_EDICAO){
            $objInfraException = new InfraException();
            $objInfraException->lancarValidacao('O processo não pode ser concluído.');
            return false;
          }
        }

      }

    }

      return null;
  }

  public function gerarDocumento(DocumentoAPI $objDocumentoAPI){
      $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
      $objMdGdArquivamentoDTO->setDblIdProcedimento($objDocumentoAPI->getIdProcedimento());
      $objMdGdArquivamentoDTO->setStrSinAtivo('S');
      $objMdGdArquivamentoDTO->retStrSituacao();
      $objMdGdArquivamentoDTO->retDblIdProcedimento();
      $objMdGdArquivamentoDTO->retStrSiglaUnidadeCorrente();

      $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
      $objMdGdArquivamentoDTO = $objMdGdArquivamentoRN->consultar($objMdGdArquivamentoDTO);

    if($objMdGdArquivamentoDTO) {
      if($objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_FASE_EDICAO){
        $objInfraException = new InfraException();
        $objInfraException->lancarValidacao('O documento não pode ser incluído pois o processo encontra-se em correção no arquivo da unidade '.$objMdGdArquivamentoDTO->getStrSiglaUnidadeCorrente().'.');
        return false;
      }
    }

      return null;
  }

  public function anexarProcesso(ProcedimentoAPI $objProcedimentoAPIPrincipal, ProcedimentoAPI $objProcedimentoAPIAnexado){
      $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
      $objMdGdArquivamentoDTO->setDblIdProcedimento([$objProcedimentoAPIPrincipal->getIdProcedimento(), $objProcedimentoAPIAnexado->getIdProcedimento()], InfraDTO::$OPER_IN);
      $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_FASE_EDICAO);
      $objMdGdArquivamentoDTO->retDblIdProcedimento();

      $objMdGdArquivamentoRN = new MdGdArquivamentoRN();

    if($objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO) != 0){
        $objInfraException = new InfraException();
        $objInfraException->lancarValidacao('O processo não pode ser anexado pois encontra-se arquivado!');
        return false;
    }

      return null;
  }

}

?>
