<?

class MdGestaoDocumentalIntegracao extends SeiIntegracao {

    public function __construct() {
        
    }

    public function getNome() {
        return 'Módulo de Gestão Documental';
    }

    public function getVersao() {
        return '1.0.0';
    }

    public function getInstituicao() {
        return 'Ministério do Planejamento, Desenvolvimento e Gestão';
    }

    public function inicializar($strVersaoSEI) {
        
    }

    public function montarBotaoControleProcessos() {

        $arrBotoes = array();
        return $arrBotoes;
    }

    public function montarIconeControleProcessos($arrObjProcedimentoAPI) {

        $arrIcones = array();
        return $arrIcones;
    }

    public function montarIconeAcompanhamentoEspecial($arrObjProcedimentoAPI) {

        $arrIcones = array();
        return $arrIcones;
    }

    public function montarIconeProcesso(ProcedimentoAPI $objProcedimentoAPI) {

        $arrObjArvoreAcaoItemAPI = array();
        $dblIdProcedimento = $objProcedimentoAPI->getIdProcedimento();

        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
        $objMdGdArquivamentoDTO->setDblIdProcedimento($dblIdProcedimento);
        $objMdGdArquivamentoDTO->setStrSinAtivo('S');
        $objMdGdArquivamentoDTO->retStrSituacao();

        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        $bolArquivado = $objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO);

        if ($bolArquivado) {
            $objArvoreAcaoItemAPI = new ArvoreAcaoItemAPI();
            $objArvoreAcaoItemAPI->setTipo('MD_GD_PROCESSO');
            $objArvoreAcaoItemAPI->setId('MD_GD_PROCESSO_' . $dblIdProcedimento);
            $objArvoreAcaoItemAPI->setIdPai($dblIdProcedimento);
            $objArvoreAcaoItemAPI->setTitle('Processo Arquivado');
            $objArvoreAcaoItemAPI->setIcone('modulos/sei-mod-gestao-documental/imagens/arquivado.gif');
            $objArvoreAcaoItemAPI->setTarget(null);
            $objArvoreAcaoItemAPI->setHref('javascript:alert(\'Processo Arquivado\');');
            $objArvoreAcaoItemAPI->setSinHabilitado('S');
            $arrObjArvoreAcaoItemAPI[] = $objArvoreAcaoItemAPI;

            // Consulta o arquivamento
            $objMdGdArquivamentoDTO = $objMdGdArquivamentoRN->consultar($objMdGdArquivamentoDTO);

            // Verifica se o arquivamento está com a situação em edição
            if($objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_FASE_EDICAO){
                $objArvoreAcaoItemAPI2 = new ArvoreAcaoItemAPI();
                $objArvoreAcaoItemAPI2->setTipo('MD_GD_PROCESSO');
                $objArvoreAcaoItemAPI2->setId('MD_GD_PROCESSO_' . $dblIdProcedimento);
                $objArvoreAcaoItemAPI2->setIdPai($dblIdProcedimento);
                $objArvoreAcaoItemAPI2->setTitle('Processo em Edição');
                $objArvoreAcaoItemAPI2->setIcone('modulos/sei-mod-gestao-documental/imagens/processo_editado.gif');
                $objArvoreAcaoItemAPI2->setTarget(null);
                $objArvoreAcaoItemAPI2->setHref('javascript:alert(\'Processo em Edição\');');
                $objArvoreAcaoItemAPI2->setSinHabilitado('S');
                $arrObjArvoreAcaoItemAPI[] = $objArvoreAcaoItemAPI2;
            }            
        }

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
            $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivamento_historico_listar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="modulos/sei-mod-gestao-documental/imagens/historico_arquivamento.gif" alt="Consultar Histórico de Arquivamento do Processo" title="Consultar Histórico de Arquivamento do Processo" /></a>';
        }
        
        // Botão de arquivamento
        if ($bolAcaoArquivamento && !$bolArquivado && count($arrObjAtividadeDTO) == 1 && $arrObjAtividadeDTO[0]->getNumIdUnidade() == SessaoSEI::getInstance()->getNumIdUnidadeAtual()  && $bolUnidadeArquivamento) {
            $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_arquivar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="modulos/sei-mod-gestao-documental/imagens/arquivamento.gif" alt="Concluir e Arquivar Processo" title="Concluir e Arquivar Processo" /></a>';
        }

        // Botão de desarquivamento
        if ($bolAcaoDesarquivamento && $bolArquivado) {
            $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_desarquivar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="modulos/sei-mod-gestao-documental/imagens/desarquivamento.gif" alt="Desarquivar Processo" title="Desarquivar Processo" /></a>';
        }

        

        return $arrBotoes;
    }

    public function montarIconeDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI) {

        $arrIcones = array();
        return $arrIcones;
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
            $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_arquivar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="modulos/sei-mod-gestao-documental/imagens/arquivamento.gif" alt="Arquivar Processo" title="Concluir e Arquivar Processo" /></a>';
        }

        // Botão de desarquivamento do processo
        if ($bolAcaoDesarquivamento && $bolArquivado) {
            $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_procedimento_desarquivar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="modulos/sei-mod-gestao-documental/imagens/desarquivamento.gif" alt="Desarquivar Processo" title="Desarquivar Processo" /></a>';
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
        return '';
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

            // Parâmetros de configuiração do módulo de arquivamento
            case 'gd_parametro_alterar':
                require_once dirname(__FILE__) . '/gd_parametro_alterar.php';
                return true;

            // Modelos de documento do módulo
            case 'gd_modelo_documento_alterar':
                    require_once dirname(__FILE__) . '/gd_modelo_documento_alterar.php';
                    return true;

            case 'gd_ajuda_variaveis_modelo_arquivamento':
            case 'gd_ajuda_variaveis_modelo_desarquivamento':
            case 'gd_ajuda_variaveis_modelo_listagem_eliminacao':
            case 'gd_ajuda_variaveis_modelo_documento_eliminacao':
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
            
            // Pendências de arquivamento
            case 'gd_pendencia_arquivamento_listar':
            case 'gd_procedimento_reabrir':
                require_once dirname(__FILE__) . '/gd_pendencia_arquivamento_listar.php';
                return true;

            case 'gd_pendencia_arquivamento_anotar':
                require_once dirname(__FILE__) . '/gd_pendencia_arquivamento_anotar.php';
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
            case 'gd_arquivamento_devolver':
                require_once dirname(__FILE__) . '/gd_arquivamento_avaliar.php';
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
        $objMdGdArquivamentoDTO->retStrSituacao();
        
        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();

        if($objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO) == 1){
            $objMdGdArquivamentoDTO = $objMdGdArquivamentoRN->consultar($objMdGdArquivamentoDTO);
            
            $strMsg = '';
            if($objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_ENVIADO_RECOLHIMENTO){
                $strMsg .= 'Processo recolhido.';
            }else if($objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_ENVIADO_ELIMINACAO){
                $strMsg .= 'Processo eliminado.';
            }
            else{
                $strMsg .= 'Processo arquivado.';
            }
            
            if($objMdGdArquivamentoDTO && $objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_FASE_EDICAO){
                $strMsg .= 'Processo arquivado em edição. Após realizar as edições necessárias sua edição deverá ser concluída na avaliação de processos.';
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

}

?>