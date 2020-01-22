<?

class MdGestaoDocumentalIntegracao extends SeiIntegracao {

    public function __construct() {
        
    }

    public function getNome() {
        return 'M�dulo de Gest�o Documental';
    }

    public function getVersao() {
        return '1.0.0';
    }

    public function getInstituicao() {
        return 'Minist�rio do Planejamento, Desenvolvimento e Gest�o';
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
        $flgArquivado = $objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO);

        if ($flgArquivado) {
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

            // Verifica se o arquivamento est� com a situa��o em edi��o
            if($objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_FASE_EDICAO){
                $objArvoreAcaoItemAPI2 = new ArvoreAcaoItemAPI();
                $objArvoreAcaoItemAPI2->setTipo('MD_GD_PROCESSO');
                $objArvoreAcaoItemAPI2->setId('MD_GD_PROCESSO_' . $dblIdProcedimento);
                $objArvoreAcaoItemAPI2->setIdPai($dblIdProcedimento);
                $objArvoreAcaoItemAPI2->setTitle('Processo em Edi��o');
                $objArvoreAcaoItemAPI2->setIcone('modulos/sei-mod-gestao-documental/imagens/processo_editado.gif');
                $objArvoreAcaoItemAPI2->setTarget(null);
                $objArvoreAcaoItemAPI2->setHref('javascript:alert(\'Processo em Edi��o\');');
                $objArvoreAcaoItemAPI2->setSinHabilitado('S');
                $arrObjArvoreAcaoItemAPI[] = $objArvoreAcaoItemAPI2;
        

            }


            
        }

        return $arrObjArvoreAcaoItemAPI;
    }

    public function montarBotaoProcesso(ProcedimentoAPI $objProcedimentoAPI) {
        $arrBotoes = array();
        $flgArquivado = false;

        // Valida as permiss�es dos bot�es
        $bolAcaoArquivamento = SessaoSEI::getInstance()->verificarPermissao('gestao_documental_arquivar_processo');
        $bolAcaoDesarquivamento = SessaoSEI::getInstance()->verificarPermissao('gestao_documental_desarquivar_processo');

        // Verifica se o processo se encontra arquivado
        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
        $objMdGdArquivamentoDTO->setDblIdProcedimento($objProcedimentoAPI->getIdProcedimento());
        $objMdGdArquivamentoDTO->setStrSinAtivo('S');

        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        $flgArquivado = $objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO);

        if($flgArquivado){
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
        $objAtividadeDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);
        $objAtividadeDTO->setDblIdProtocolo($objProcedimentoAPI->getIdProcedimento());
        $objAtividadeDTO->setDthConclusao(null);
        $objAtividadeDTO->retNumIdUnidade();

        $objAtividadeRN = new AtividadeRN();
        $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

        if ($bolAcaoArquivamento && !$flgArquivado && count($arrObjAtividadeDTO) == 1 && $arrObjAtividadeDTO[0]->getNumIdUnidade() == SessaoSEI::getInstance()->getNumIdUnidadeAtual()) {
            $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivar_procedimento&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="modulos/sei-mod-gestao-documental/imagens/arquivamento.gif" alt="Arquivar Processo" title="Concluir e Arquivar Processo" /></a>';
        }

        // TODO: VALIDA��O PARA A EXIBI��O DO BOT�O DESARQUIVAMENTO
        if ($bolAcaoDesarquivamento && $flgArquivado) {
            $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_desarquivar_procedimento&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="modulos/sei-mod-gestao-documental/imagens/desarquivamento.gif" alt="Desarquivar Processo" title="Desarquivar Processo" /></a>';
        }

        return $arrBotoes;
    }

    public function montarIconeDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI) {

        $arrIcones = array();
        return $arrIcones;
    }

    public function montarBotaoDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI) {

        $arrBotoes = array();
        $flgArquivado = false;

        // Valida as permiss�es dos bot�es
        $bolAcaoArquivamento = SessaoSEI::getInstance()->verificarPermissao('gestao_documental_arquivar_processo');
        $bolAcaoDesarquivamento = SessaoSEI::getInstance()->verificarPermissao('gestao_documental_desarquivar_processo');

        // Verifica se o processo se encontra arquivado
        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
        $objMdGdArquivamentoDTO->setDblIdProcedimento($objProcedimentoAPI->getIdProcedimento());
        $objMdGdArquivamentoDTO->setStrSinAtivo('S');

        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        $flgArquivado = $objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO);


        // Verifica se o processo encontra-se aberto em mais de uma unidade
        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDistinct(true);
        $objAtividadeDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);
        $objAtividadeDTO->setDblIdProtocolo($objProcedimentoAPI->getIdProcedimento());
        $objAtividadeDTO->setDthConclusao(null);
        $objAtividadeDTO->retNumIdUnidade();

        $objAtividadeRN = new AtividadeRN();
        $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

        if ($bolAcaoArquivamento && !$flgArquivado && count($arrObjAtividadeDTO) == 1 && $arrObjAtividadeDTO[0]->getNumIdUnidade() == SessaoSEI::getInstance()->getNumIdUnidadeAtual()) {
            $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_arquivar_procedimento&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="modulos/sei-mod-gestao-documental/imagens/arquivamento.gif" alt="Arquivar Processo" title="Concluir e Arquivar Processo" /></a>';
        }

        // TODO: VALIDA��O PARA A EXIBI��O DO BOT�O DESARQUIVAMENTO
        if ($bolAcaoDesarquivamento && $flgArquivado) {
            $arrBotoes[] = '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=gd_desarquivar_procedimento&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $objProcedimentoAPI->getIdProcedimento() . '&arvore=1') . '" tabindex="" class="botaoSEI"><img class="infraCorBarraSistema" src="modulos/sei-mod-gestao-documental/imagens/desarquivamento.gif" alt="Desarquivar Processo" title="Desarquivar Processo" /></a>';
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
        // gd_modelos_documento_alterar
        switch ($strAcao) {
            case 'gd_arquivar_procedimento':
                require_once dirname(__FILE__) . '/gd_arquivar_procedimento.php';
                return true;

            case 'gd_desarquivar_procedimento':
                require_once dirname(__FILE__) . '/gd_desarquivar_procedimento.php';
                return true;
                
            case 'gd_anotar_pendencia_arquivamento':
                require_once dirname(__FILE__) . '/gd_anotar_pendencia_arquivamento.php';
                return true;
            case 'gd_unidade_arquivamento_selecionar':
                require_once dirname(__FILE__) . '/gd_unidade_arquivamento_selecao.php';
                return true;
            case 'gd_arquivamento_listar':
            case 'gd_procedimento_editar_arquivamento':
            case 'gd_procedimento_concluir_edicao_arquivamento':
                require_once dirname(__FILE__) . '/gd_arquivamento_listar.php';
                return true;
            case 'gd_relatorio':
                require_once dirname(__FILE__) . '/gd_relatorio.php';
                return true;
            case 'gd_gestao_listagem_recolhimento':
            case 'gd_editar_listagem_recolhimento':
            case 'gd_concluir_edicao_listagem_recol':
            case 'gd_recolhimento':
                require_once dirname(__FILE__) . '/gd_gestao_listagem_recolhimento.php';
                return true;
            case 'gd_adicionar_processo_listagem_elim':
                require_once dirname(__FILE__) . '/gd_adicionar_processo_listagem_elim.php';
                return true;
            case 'gd_adicionar_processo_listagem_recol':
                require_once dirname(__FILE__) . '/gd_adicionar_processo_listagem_recol.php';
                return true;
            case 'gd_remover_processo_listagem_elim':
                require_once dirname(__FILE__) . '/gd_remover_processo_listagem_elim.php';
                return true;
            case 'gd_remover_processo_listagem_recol':
                require_once dirname(__FILE__) . '/gd_remover_processos_listagem_recol.php';
                return true;
            case 'gd_visualizacao_listagem_recolhimento':
            case 'gd_geracao_pdf_listagem_recolhimento':
                require_once dirname(__FILE__) . '/gd_visualizar_listagem_recolhimento.php';
                return true;
            case 'gd_recolhimento_documentos_fisicos':
                require_once dirname(__FILE__) . '/gd_recolhimento_documento_fisico.php';
                return true;
            case 'gd_recolher_documento_fisico':
                require_once dirname(__FILE__) . '/gd_recolher_documento_fisico.php';
                return true;
            case 'gd_gestao_listagem_eliminacao':
            case 'gd_editar_listagem_eliminacao':
            case 'gd_concluir_edicao_listagem_elim':
                require_once dirname(__FILE__) . '/gd_gestao_listagem_eliminacao.php';
                return true;
            case 'gd_eliminar_documento_fisico':
                require_once dirname(__FILE__) . '/gd_eliminar_documento_fisico.php';
                return true;
            case 'gd_eliminacao':
                require_once dirname(__FILE__) . '/gd_eliminar_processo.php';
                return true;
            case 'gd_eliminacao_documentos_fisicos':
                require_once dirname(__FILE__) . '/gd_eliminacao_documento_fisico.php';
                return true;
            case 'gd_visualizacao_listagem_eliminacao':
            case 'gd_listagem_eliminacao_eliminar':
            case 'gd_geracao_pdf_listagem_eliminacao':
                require_once dirname(__FILE__) . '/gd_visualizar_listagem_eliminacao.php';
                return true;
            case 'gd_prep_list_eliminacao_observar':
                require_once dirname(__FILE__) . '/gd_observar_listagem_eliminacao.php';
                return true;
            case 'gd_prep_list_recolhimento_observar':
                require_once dirname(__FILE__) . '/gd_observar_listagem_recolhimento.php';
                return true;
            case 'gd_prep_list_eliminacao_gerar':
            case 'gd_prep_list_eliminacao_listar':
            case 'gd_prep_list_eliminacao_excluir':
                require_once dirname(__FILE__) . '/gd_preparar_listagem_eliminacao.php';
                return true;
            case 'gd_prep_list_recolhimento_gerar':
            case 'gd_prep_list_recolhimento_listar':
            case 'gd_prep_list_recolhimento_excluir':
                require_once dirname(__FILE__) . '/gd_preparar_listagem_recolhimento.php';
                return true;
            case 'gd_avaliacao_processos_listar':
            case 'gd_procedimento_eliminacao_enviar':
            case 'gd_procedimento_recolhimento_enviar':
            case 'gd_procedimento_devolver_arquivamento':
                require_once dirname(__FILE__) . '/gd_avaliacao_processos_listar.php';
                return true;
            case 'gd_modelo_documento_alterar':
                require_once dirname(__FILE__) . '/gd_modelo_documento_alterar.php';
                return true;

            case 'gd_ajuda_variaveis_modelo_arquivamento':
            case 'gd_ajuda_variaveis_modelo_desarquivamento':
            case 'gd_ajuda_variaveis_modelo_listagem_eliminacao':
            case 'gd_ajuda_variaveis_modelo_documento_eliminacao':
                require_once dirname(__FILE__) . '/gd_ajuda_variaveis_modelo.php';
                return true;
            case 'gd_justificativas_listar':
            case 'gd_justificativas_excluir':
                require_once dirname(__FILE__) . '/gd_justificativa_lista.php';
                return true;

            case 'gd_justificativas_cadastrar':
            case 'gd_justificativas_alterar':
            case 'gd_justificativas_consultar':
                require_once dirname(__FILE__) . '/gd_justificativa_cadastro.php';
                return true;
            case 'gd_unidade_arquivamento_listar':
            case 'gd_unidade_arquivamento_excluir':
                require_once dirname(__FILE__) . '/gd_unidade_arquivamento_lista.php';
                return true;
            case 'gd_unidade_arquivamento_cadastrar':
            case 'gd_unidade_arquivamento_alterar':
            case 'gd_unidade_arquivamento_visualizar':
                require_once dirname(__FILE__) . '/gd_unidade_arquivamento_cadastro.php';
                return true;
            case 'gd_parametros_alterar':
                require_once dirname(__FILE__) . '/gd_parametros_alterar.php';
                return true;



            case 'gd_pendencias_arquivamento':
            case 'gd_procedimento_reabrir':
            case 'gd_procedimento_arquivar':
                require_once dirname(__FILE__) . '/gd_pendencias_arquivamento.php';
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
        
        if($objMdGdArquivamentoRN->contar($objMdGdArquivamentoDTO) != 0){
            $strMsg = 'Processo arquivado.';
        }

        $objMdGdArquivamentoDTO = $objMdGdArquivamentoRN->consultar($objMdGdArquivamentoDTO);
        if($objMdGdArquivamentoDTO && $objMdGdArquivamentoDTO->getStrSituacao() == MdGdArquivamentoRN::$ST_FASE_EDICAO){
            $strMsg = 'Processo arquivado em edi��o. Ap�s realizar as edi��es necess�rias sua edi��o dever� ser conclu�da na avalia��o de processos.';
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
            $objInfraException->lancarValidacao('O processo n�o pode ser reaberto pois encontra-se arquivado!');
            return false;
        }

        return null;
    }

}

?>