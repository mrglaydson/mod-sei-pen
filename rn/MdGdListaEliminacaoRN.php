<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaEliminacaoRN extends InfraRN {

    public static $ST_GERADA = 'GE';
    public static $ST_EDICAO = 'ED';
    public static $ST_ELIMINADA = 'EL';

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_prep_list_eliminacao_gerar', __METHOD__, $objMdGdListaEliminacao);

            // Recupera os arquivamentos
            $arrObjMdGdArquivamentoDTO = $objMdGdListaEliminacao->getArrObjMdGdArquivamentoDTO();

            // Recupera os tipos de procedimento e documento que ser�o criados no arquivamento
            $objMdGdParametroRN = new MdGdParametroRN();
            $numIdTipoProcedimentoArquivamento = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO);
            $numIdTipoDocumentoArquivamento = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO);

            // INCLUI  O PROCESSO
            // INFORMA OS ASSUNTOS
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setNumIdAssunto(2);
            $objRelProtocoloAssuntoDTO->setNumSequencia(1);
            $arrayAssuntos[] = $objRelProtocoloAssuntoDTO;

            // INCLUI  OS DEMAIS DADOS DO PROCESSO
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setStrDescricao('Listagem de elimina��o');
            $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
            // $objProtocoloDTO->setNumIdHipoteseLegal($hipoteseLegal);
            $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrayAssuntos);
            $objProtocoloDTO->setArrObjParticipanteDTO(array());
            $objProtocoloDTO->setArrObjObservacaoDTO(array());
            // $objProtocoloDTO->setStrStaGrauSigilo($grauSigilo);

            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProcedimentoDTO->setNumIdTipoProcedimento($numIdTipoProcedimentoArquivamento);
            $objProcedimentoDTO->setObjProtocoloDTO($objProtocoloDTO);
            $objProcedimentoDTO->setStrSinGerarPendencia('S');

            // REALIZA A INCLUS�O DO PROCESSO 
            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoDTO = $objProcedimentoRN->gerarRN0156($objProcedimentoDTO);

            //INCLUS�O DO DOCUMENTO
            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento(null);
            $objDocumentoDTO->setDblIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());

            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo(null);
            $objProtocoloDTO->setStrStaProtocolo('G');
            $objProtocoloDTO->setStrDescricao('');

            $objDocumentoDTO->setNumIdSerie($numIdTipoDocumentoArquivamento);
            $objDocumentoDTO->setDblIdDocumentoEdoc(null);
            $objDocumentoDTO->setDblIdDocumentoEdocBase(null);
            $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objDocumentoDTO->setNumIdTipoConferencia(null);
            $objDocumentoDTO->setStrNumero('');

            $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
            $objProtocoloDTO->setArrObjParticipanteDTO(array());
            $objProtocoloDTO->setArrObjObservacaoDTO(array());

            $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);
            $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);
            $objDocumentoDTO->setStrConteudo($this->obterConteudoDocumentoEliminacao($arrObjMdGdArquivamentoDTO));

            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

            // Cria a listagem de elimina��o
            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setDblIdProcedimentoEliminacao($objProcedimentoDTO->getDblIdProcedimento());
            $objMdGdListaEliminacaoDTO->setDblIdDocumentoEliminacao($objDocumentoDTO->getDblIdDocumento());
            $objMdGdListaEliminacaoDTO->setStrNumero($this->obterProximaNumeroListagem());
            $objMdGdListaEliminacaoDTO->setDthEmissaoListagem(date('d/m/Y H:i:s'));
            $objMdGdListaEliminacaoDTO->setNumAnoLimiteInicio(2004); // TODO NOW
            $objMdGdListaEliminacaoDTO->setNumAnoLimiteFim(2018); // TODO NOW
            $objMdGdListaEliminacaoDTO->setStrSituacao(self::$ST_GERADA);
            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            $objMdGdListaEliminacaoDTO = $objMdGdListaEliminacaoBD->cadastrar($objMdGdListaEliminacaoDTO);

            $objMdGdListElimProcedimentoBD = new MdGdListaElimProcedimentoBD($this->getObjInfraIBanco());
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();

            // Cria a rela��o da listagem de elimina��o com os procedimentos
            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {

                //Cria o v�lculo da lista com o procedimento
                $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
                $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($objMdGdListaEliminacaoDTO->getNumIdListaEliminacao());
                $objMdGdListaElimProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());

                $objMdGdListElimProcedimentoBD->cadastrar($objMdGdListaElimProcedimentoDTO);

                // Altera a situa��o do arquivamento do procedimento
                $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_ENVIADO_ELIMINACAO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }
        } catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar a listagem de elimina��o.', $e);
        }
    }

    protected function alterarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            return $objMdGdListaEliminacaoBD->alterar($objMdGdListaEliminacao);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de elimina��o.', $e);
        }
    }

    protected function consultarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            return $objMdGdListaEliminacaoBD->consultar($objMdGdListaEliminacao);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de elimina��o.', $e);
        }
    }

    protected function listarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao) {
        try {

            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            return $objMdGdListaEliminacaoBD->listar($objMdGdListaEliminacao);
        } catch (Exception $e) {
            throw new InfraException('Erro listando as listagens de elimina��o.', $e);
        }
    }

    public function obterProximaNumeroListagem() {
        $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
        $objMdGdListaEliminacaoDTO->setStrNumero('%' . date('Y'), InfraDTO::$OPER_LIKE);
        $objMdGdListaEliminacaoDTO->retTodos();

        $arrObjMdGdListaEliminacao = $this->listar($objMdGdListaEliminacaoDTO);

        $numeroListagem = count($arrObjMdGdListaEliminacao) + 1;

        return $numeroListagem . "/" . date('Y');
    }

    public function obterConteudoDocumentoEliminacao($arrObjMdGdArquivamentoDTO) {
        $objSessaoSEI = SessaoSEI::getInstance();

        $arrVariaveisModelo = [
            '@orgao@' => $objSessaoSEI->getStrDescricaoOrgaoUsuario(),
            '@unidade@' => $objSessaoSEI->getStrSiglaUnidadeAtual() . ' - ' . $objSessaoSEI->getStrSiglaUnidadeAtual(),
            '@numero_listagem@' => $this->obterProximaNumeroListagem(),
            '@folha@' => '1/1', // Verificar depois
            '@tabela' => '',
            '@mensuracao_total@' => count($arrObjMdGdArquivamentoDTO) . ' processos',
            '@datas_limites_gerais@' => '2010-2018'
        ];

        $strHtmlTabela = '<table border="1" style="width: 1000px;">';
        $strHtmlTabela .= '<thead><tr>';
        $strHtmlTabela .= '<th>C�digo Referente a Classifica��o</th>';
        $strHtmlTabela .= '<th>Descritor do c�digo</th>';
        $strHtmlTabela .= '<th>Datas Limite</th>';
        $strHtmlTabela .= '<th>Unidade - Quantifica��o</th>';
        $strHtmlTabela .= '<th>Unidade - Especifica��o</th>';
        $strHtmlTabela .= '<th>Observa��es e/ou justificativas</th>';
        $strHtmlTabela .= '</thead></tr>';

        $strHtmlTabela .= '<tbody>';

        foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
            // Obtem os dados do assunto
            $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();

            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($objMdGdArquivamentoDTO->getDblIdProtocoloProcedimento());
            $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();
            $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();

            $arrObjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);

            $strCodigoClassificacao = '';
            $strDescritorCodigo = '';

            foreach ($arrObjRelProtocoloAssuntoDTO as $key => $objRelProtocoloAssuntoDTO) {
                if ($key + 1 == count($arrObjRelProtocoloAssuntoDTO)) {
                    $strCodigoClassificacao .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto();
                    $strDescritorCodigo .= $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto();
                } else {
                    $strCodigoClassificacao .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto() . " / ";
                    $strDescritorCodigo .= $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto() . " / ";
                }
            }

            $strHtmlTabela .= '<tr>';
            $strHtmlTabela .= '<td>' . $strCodigoClassificacao . '</td>';
            $strHtmlTabela .= '<td>' . $strDescritorCodigo . '</td>';
            $strHtmlTabela .= '<td>2010-2018</td>';
            $strHtmlTabela .= '<td>1</td>';
            $strHtmlTabela .= '<td></td>';
            $strHtmlTabela .= '<td>' . $objMdGdArquivamentoDTO->getStrObservacaoEliminacao() . '</td>';
            $strHtmlTabela .= '</tr>';
        }

        $strHtmlTabela .= '</tbody>';
        $strHtmlTabela .= '</table>';

        $arrVariaveisModelo['@tabela@'] = $strHtmlTabela;

        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome(MdGdModeloDocumentoRN::MODELO_LISTAGEM_ELIMINACAO);
        $objMdGdModeloDocumentoDTO->retTodos();

        $objMdGdModeloDocumentoRN = new MdGdModeloDocumentoRN();
        $objMdGdModeloDocumentoDTO = $objMdGdModeloDocumentoRN->consultar($objMdGdModeloDocumentoDTO);

        $str = $objMdGdModeloDocumentoDTO->getStrValor();
        $str = strtr($str, $arrVariaveisModelo);
        return $str;
    }

    public function gerarPdfConectado($numIdListagem) {
        $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
        $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($numIdListagem);
        $objMdGdListaElimProcedimentoDTO->retDblIdProcedimento();

        $objMdGdListaElimProcedimentoRN = new MdGdListaElimProcedimentoRN();
        $arrObjMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoRN->listar($objMdGdListaElimProcedimentoDTO);

        $arrIdsEliminacao = explode(',', InfraArray::implodeArrInfraDTO($arrObjMdGdListaElimProcedimentoDTO, 'IdProcedimento'));

        // Busca todos os arquivamentos dos processos daquela listagem
        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

        $objMdGdArquivamentoDTO->retNumIdArquivamento();
        $objMdGdArquivamentoDTO->retDthDataArquivamento();
        $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
        $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
        $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
        $objMdGdArquivamentoDTO->retStrObservacaoEliminacao();
        $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
        $objMdGdArquivamentoDTO->setStrSinAtivo('S');
        $objMdGdArquivamentoDTO->setDblIdProcedimento($arrIdsEliminacao, InfraDTO::$OPER_IN);

        $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);
        $numRegistros = count($arrObjMdGdArquivamentoDTO);

        if ($numRegistros > 0) {
            $strResultado = '';

            $strSumarioTabela = 'Lista de Processos';
            $strCaptionTabela = 'Lista de Processos';

            $strResultado .= '<table width="99%" class="infraTable" border="1">';
            $strResultado .= '<tr>';
            $strResultado .= '<th class="infraTh" width="13%">Descri��o Unidade Corrente</th>';
            $strResultado .= '<th class="infraTh" width="10%">C�digo da Classifica��o</th>';
            $strResultado .= '<th class="infraTh" width="20%">Descritor do C�digo</th>';
            $strResultado .= '<th class="infraTh" width="14%">N� do Processo</th>';
            $strResultado .= '<th class="infraTh" width="15%">Tipo de Processo</th>';
            $strResultado .= '<th class="infraTh" width="10%">Data de arquivamento</th>';
            $strResultado .= '<th class="infraTh" width="10%">Observa��es e/ou Justificativas</th>';
            $strResultado .= '</tr>';
            $strCssTr = '';

            $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();

            for ($i = 0; $i < $numRegistros; $i++) {

                // Obtem os dados do assunto
                $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
                $objRelProtocoloAssuntoDTO->setDblIdProtocolo($arrObjMdGdArquivamentoDTO[$i]->getDblIdProtocoloProcedimento());
                $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();
                $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();

                $arrObjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);

                $strCodigoClassificacao = '';
                $strDescritorCodigo = '';

                foreach ($arrObjRelProtocoloAssuntoDTO as $key => $objRelProtocoloAssuntoDTO) {
                    if ($key + 1 == count($arrObjRelProtocoloAssuntoDTO)) {
                        $strCodigoClassificacao .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto();
                        $strDescritorCodigo .= $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto();
                    } else {
                        $strCodigoClassificacao .= $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto() . " / ";
                        $strDescritorCodigo .= $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto() . " / ";
                    }
                }

                $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
                $strResultado .= $strCssTr;

                $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrDescricaoUnidadeCorrente()) . '</td>';
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($strCodigoClassificacao) . '</td>';
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($strDescritorCodigo) . '</td>';
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrProtocoloFormatado()) . '</td>';
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getDthDataArquivamento()) . '</td>';
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrObservacaoEliminacao()) . '</td>';
                $strResultado .= '</tr>';
            }
            $strResultado .= '</table>';
        }

        $strCaminhoArquivoHtml = DIR_SEI_TEMP . '/gerar-pdf-listagem-eliminacao-' . date('YmdHis') . '.html';
        $strCaminhoArquivoPdf = DIR_SEI_TEMP . '/gerar-pdf-listagem-eliminacao-' . date('YmdHis') . '.pdf';
        file_put_contents($strCaminhoArquivoHtml, $strResultado);

        $strComandoGerarPdf = DIR_SEI_BIN . '/wkhtmltopdf-amd64 --quiet --title md_gd_pdf_listagem_eliminacao-' . InfraUtil::retirarFormatacao('1123123', false) . ' ' . $strCaminhoArquivoHtml . '  ' . $strCaminhoArquivoPdf . ' 2>&1';
        shell_exec($strComandoGerarPdf);
        SeiINT::download(null, $strCaminhoArquivoPdf, 'listagem_recolhiment.pdf', 'attachment', true);
    }

    /**
     * Altera a situa��o da listagem de elimina��o para em edi��o
     *
     * @param MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO
     * @return void
     */
    public function editarListaEliminacaoControlado(MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO){
        try{
            if(!$objMdGdListaEliminacaoDTO->isSetNumIdListaEliminacao()){
                throw new InfraException('Informe o id da lista de elimina��o para deixar em modo de edi��o.');
            }

            $objMdGdListaEliminacaoDTO->retNumIdListaEliminacao();
            $objMdGdListaEliminacaoDTO->retStrSituacao();

            $objMdGdListaEliminacaoDTO = $this->consultar($objMdGdListaEliminacaoDTO);
            
            if($objMdGdListaEliminacaoDTO->getStrSituacao() != self::$ST_GERADA){
                throw new InfraException('A listagem precisa estar na situa��o gerada.'); 
            }

            $objMdGdListaEliminacaoDTO->setStrSituacao(self::$ST_EDICAO);
            return $this->alterar($objMdGdListaEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de elimina��o para o modo de edi��o.', $e);
        }
    }

    /**
     * Conclui a edi��o da listagem de elimina��o
     *
     * @param MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO
     * @return void
     */
    public function concluirEdicaoListaEliminacaoControlado(MdGdListaEliminacaoDTO $objMdGdListaEliminacaoDTO){
        try{

            if(!$objMdGdListaEliminacaoDTO->isSetNumIdListaEliminacao()){
                throw new InfraException('Informe o id da lista de elimina��o para concluir a edi��o da listagem.');
            }
            
            // Obtem os dados da lista de elimina��o
            $objMdGdListaEliminacaoDTO->retNumIdListaEliminacao();
            $objMdGdListaEliminacaoDTO->retStrSituacao();
            $objMdGdListaEliminacaoDTO->retDblIdProcedimentoEliminacao();

            $objMdGdListaEliminacaoDTO = $this->consultar($objMdGdListaEliminacaoDTO);

            if($objMdGdListaEliminacaoDTO->getStrSituacao() != self::$ST_EDICAO){
                throw new InfraException('A listagem precisa estar em edi��o para que sua edi��o seja conclu�da.');
            }

            // Obtem os processos da listagem de elimina��o
            $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
            $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($objMdGdListaEliminacaoDTO->getNumIdListaEliminacao());
            $objMdGdListaElimProcedimentoDTO->retDblIdProcedimento();

            $objMdGdListaElimProcedimentoRN = new MdGdListaElimProcedimentoRN();
            $arrObjMdGdListaElimProcedimentoDTO = $objMdGdListaElimProcedimentoRN->listar($objMdGdListaElimProcedimentoDTO);

            $arrIdsProcedimentos = [];

            foreach($arrObjMdGdListaElimProcedimentoDTO as $objMdGdListaElimProcedimentoDTO){
                $arrIdsProcedimentos[] = $objMdGdListaElimProcedimentoDTO->getDblIdProcedimento();
            }

            // Obtem os arquivamentos dos processos
            $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();
            $objMdGdArquivamentoDTO->setDblIdProcedimento($arrIdsProcedimentos, InfraDTO::$OPER_IN);
            $objMdGdArquivamentoDTO->setStrSinAtivo('S');
            $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
            $objMdGdArquivamentoDTO->retStrObservacaoEliminacao();

            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);
            
            // Gera um novo documento atualizado no processo da listagem de elimina��o
            $objMdGdParametroRN = new MdGdParametroRN();
            $numIdTipoDocumentoArquivamento = $objMdGdParametroRN->obterParametro(MdGdParametroRN::$PAR_TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO);

            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento(null);
            $objDocumentoDTO->setDblIdProcedimento($objMdGdListaEliminacaoDTO->getDblIdProcedimentoEliminacao());

            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo(null);
            $objProtocoloDTO->setStrStaProtocolo('G');
            $objProtocoloDTO->setStrDescricao('');

            $objDocumentoDTO->setNumIdSerie($numIdTipoDocumentoArquivamento);
            $objDocumentoDTO->setDblIdDocumentoEdoc(null);
            $objDocumentoDTO->setDblIdDocumentoEdocBase(null);
            $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objDocumentoDTO->setNumIdTipoConferencia(null);
            $objDocumentoDTO->setStrNumero('');

            $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
            $objProtocoloDTO->setArrObjParticipanteDTO(array());
            $objProtocoloDTO->setArrObjObservacaoDTO(array());

            $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);
            $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);
            $objDocumentoDTO->setStrConteudo($this->obterConteudoDocumentoEliminacao($arrObjMdGdArquivamentoDTO));

            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

            // Atualiza a situa��o da listagem de elimina��o
            $objMdGdListaEliminacaoDTO->setStrSituacao(self::$ST_GERADA);
            return $this->alterar($objMdGdListaEliminacaoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de elimina��o para o modo de edi��o.', $e);
        }
    }
}

?>