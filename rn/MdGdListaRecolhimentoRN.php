<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaRecolhimentoRN extends InfraRN {

    public static $ST_GERADA = 'GE';
    public static $ST_EDICAO = 'ED';
    public static $ST_RECOLHIDA = 'RE';

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimento) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gd_lista_recolhimento_preparacao_gerar', __METHOD__, $objMdGdListaRecolhimento);

            // Recupera os arquivamentos
            $arrObjMdGdArquivamentoDTO = $objMdGdListaRecolhimento->getArrObjMdGdArquivamentoDTO();

            // CONSULTA PARA VERIFICA��O DA EXIST�NCIA DE DOCUMENTOS F�SICOS ARQUIVADOS
            $strSinDocumentosFisicos = 'N';
            
            // Obtem os ids dos procedimentos vinculdados e os anos limite inicial e final
            $arrIdsProcedimentos = array();
            $numAnoLimiteInicial = 0;
            $numAnoLimiteFinal = 0;

            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {
                $arrIdsProcedimentos[] = $objMdGdArquivamentoDTO->getDblIdProcedimento();
                $numAnoInicial = (int) substr($objMdGdArquivamentoDTO->getDthDataGuardaCorrente(), 6, 4);
                $numAnoFinal = (int) substr($objMdGdArquivamentoDTO->getDthDataGuardaIntermediaria(), 6, 4);

                $numAnoLimiteInicial = $numAnoLimiteInicial > $numAnoInicial || !$numAnoLimiteInicial ? $numAnoInicial : $numAnoLimiteInicial;
                $numAnoLimiteFinal = $numAnoLimiteFinal < $numAnoFinal ? $numAnoFinal : $numAnoLimiteFinal;
            }

            // Obtem os documentos vinculados aos processos da listagem
            $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
            $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($arrIdsProcedimentos, InfraDTO::$OPER_IN);
            $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();

            $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
            $arrObjRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO);
            
            $arrIdsDocumentos = array();
            if ($arrObjRelProtocoloProtocoloDTO) {
                $arrIdsDocumentos = explode(',', InfraArray::implodeArrInfraDTO($arrObjRelProtocoloProtocoloDTO, 'IdProtocolo2'));
            }
            
            // Obtem todos os arquivamentos fisicos registrados para os documentos do processo
            $objArquivamentoDTO = new ArquivamentoDTO();
            $objArquivamentoDTO->retDblIdProtocoloDocumento();
            $objArquivamentoDTO->setDblIdProtocoloDocumento($arrIdsDocumentos, InfraDTO::$OPER_IN);

            $objArquivamentoRN = new ArquivamentoRN();
            $strSinDocumentosFisicos = $objArquivamentoRN->contar($objArquivamentoDTO) == 0 ? 'N' : 'S';

            // Cria a listagem de recolhimento
            $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
            $objMdGdListaRecolhimentoDTO->setStrNumero($this->obterProximaNumeroListagem());
            $objMdGdListaRecolhimentoDTO->setDthEmissaoListagem(date('d/m/Y H:i:s'));
            $objMdGdListaRecolhimentoDTO->setNumAnoLimiteInicio($numAnoLimiteInicial);
            $objMdGdListaRecolhimentoDTO->setNumAnoLimiteFim($numAnoLimiteFinal);
            $objMdGdListaRecolhimentoDTO->setNumQtdProcessos(count($arrObjMdGdArquivamentoDTO));
            $objMdGdListaRecolhimentoDTO->setStrSituacao(self::$ST_GERADA);
            $objMdGdListaRecolhimentoDTO->setStrSinDocumentosFisicos($strSinDocumentosFisicos);

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->getObjInfraIBanco());
            $objMdGdListaRecolhimentoDTO = $objMdGdListaRecolhimentoBD->cadastrar($objMdGdListaRecolhimentoDTO);

            $objMdGdListRecolProcedimentoBD = new MdGdListaRecolProcedimentoBD($this->getObjInfraIBanco());
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();

            // Cria a rela��o da listagem de recolhimento com os procedimentos
            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {

                //Cria o v�lculo da lista com o procedimento
                $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
                $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($objMdGdListaRecolhimentoDTO->getNumIdListaRecolhimento());
                $objMdGdListaRecolProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());

                $objMdGdListRecolProcedimentoBD->cadastrar($objMdGdListaRecolProcedimentoDTO);

                // Altera a situa��o do arquivamento do procedimento
                $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_ENVIADO_RECOLHIMENTO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }


            // CODE: 
            // -- Cria��o da listagem de recolhimento
        } catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar a listagem de recolhimento.', $e);
        }
    }
    
    
    protected function alterarConectado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO) {
        try {

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->getObjInfraIBanco());
            return $objMdGdListaRecolhimentoBD->alterar($objMdGdListaRecolhimentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro consultando a listagem de recolhimento.', $e);
        }
    }
    
    protected function consultarConectado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimento) {
        try {

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->getObjInfraIBanco());
            return $objMdGdListaRecolhimentoBD->consultar($objMdGdListaRecolhimento);
        } catch (Exception $e) {
            throw new InfraException('Erro consultando a listagem de recolhimento.', $e);
        }
    }

    protected function listarConectado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimento) {
        try {

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->getObjInfraIBanco());
            return $objMdGdListaRecolhimentoBD->listar($objMdGdListaRecolhimento);
        } catch (Exception $e) {
            throw new InfraException('Erro listando as listagens de recolhimento.', $e);
        }
    }

    protected function contarConectado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimento) {
        try {

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->inicializarObjInfraIBanco());
            return $objMdGdListaRecolhimentoBD->contar($objMdGdListaRecolhimento);
        } catch (Exception $e) {
            throw new InfraException('Erro ao contar lista de recolhimentos.', $e);
        }
    }

    public function obterProximaNumeroListagem() {
        $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
        $objMdGdListaRecolhimentoDTO->setStrNumero('%' . date('Y'), InfraDTO::$OPER_LIKE);
        $objMdGdListaRecolhimentoDTO->retTodos();

        $arrObjMdGdListaRecolhimento = $this->listar($objMdGdListaRecolhimentoDTO);

        $numeroListagem = count($arrObjMdGdListaRecolhimento) + 1;

        return $numeroListagem . "/" . date('Y');
    }

    public function gerarPdfConectado($numIdListagem) {
        $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
        $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($_GET['id_listagem_recolhimento']);

        if($_POST['hdnInfraItensSelecionados']){
            $arrIdsProcedimentos = explode(',', $_POST['hdnInfraItensSelecionados']);
            $objMdGdListaRecolProcedimentoDTO->setDblIdProcedimento($arrIdsProcedimentos, InfraDTO::$OPER_IN);
        }
        
        $objMdGdListaRecolProcedimentoDTO->retDblIdProcedimento();

        $objMdGdListaRecolProcedimentoRN = new MdGdListaRecolProcedimentoRN();
        $arrObjMdGdListaRecolProcedimentoDTO = $objMdGdListaRecolProcedimentoRN->listar($objMdGdListaRecolProcedimentoDTO);

        $arrIdsRecolhimento = explode(',', InfraArray::implodeArrInfraDTO($arrObjMdGdListaRecolProcedimentoDTO, 'IdProcedimento'));

        // Busca todos os arquivamentos dos processos daquela listagem
        $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
        $objMdGdArquivamentoDTO = new MdGdArquivamentoDTO();

        $objMdGdArquivamentoDTO->retNumIdArquivamento();
        $objMdGdArquivamentoDTO->retDthDataArquivamento();
        $objMdGdArquivamentoDTO->retStrProtocoloFormatado();
        $objMdGdArquivamentoDTO->retStrNomeTipoProcedimento();
        $objMdGdArquivamentoDTO->retStrDescricaoUnidadeCorrente();
        $objMdGdArquivamentoDTO->retStrObservacaoRecolhimento();
        $objMdGdArquivamentoDTO->retDblIdProtocoloProcedimento();
        $objMdGdArquivamentoDTO->setStrSinAtivo('S');
        $objMdGdArquivamentoDTO->setDblIdProcedimento($arrIdsRecolhimento, InfraDTO::$OPER_IN);

        $arrObjMdGdArquivamentoDTO = $objMdGdArquivamentoRN->listar($objMdGdArquivamentoDTO);
        $numRegistros = count($arrObjMdGdArquivamentoDTO);

        if ($numRegistros > 0) {
            $strResultado = '';

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
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjMdGdArquivamentoDTO[$i]->getStrObservacaoRecolhimento()) . '</td>';
                $strResultado .= '</tr>';
            }
            $strResultado .= '</table>';
        }

        $strCaminhoArquivoHtml = DIR_SEI_TEMP . '/gerar-pdf-listagem-recolhimento-' . date('YmdHis') . '.html';
        $strCaminhoArquivoPdf = DIR_SEI_TEMP . '/gerar-pdf-listagem-recolhimento-' . date('YmdHis') . '.pdf';
        file_put_contents($strCaminhoArquivoHtml, $strResultado);

        $strComandoGerarPdf = DIR_SEI_BIN . '/wkhtmltopdf-amd64 --quiet --title md_gd_pdf_listagem_recolhimento-' . InfraUtil::retirarFormatacao('1123123', false) . ' ' . $strCaminhoArquivoHtml . '  ' . $strCaminhoArquivoPdf . ' 2>&1';
        shell_exec($strComandoGerarPdf);
        SeiINT::download(null, $strCaminhoArquivoPdf, 'listagem_recolhimento.pdf', 'attachment', true);
    }

     /**
     * Altera a situa��o da listagem de recolhimento para em edi��o
     *
     * @param MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO
     * @return void
     */
    public function editarListaRecolhimentoControlado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO){
        try{
            if(!$objMdGdListaRecolhimentoDTO->isSetNumIdListaRecolhimento()){
                throw new InfraException('Informe o id da lista de recolhimento para deixar em modo de edi��o.');
            }

            $objMdGdListaRecolhimentoDTO->retNumIdListaRecolhimento();
            $objMdGdListaRecolhimentoDTO->retStrSituacao();

            $objMdGdListaRecolhimentoDTO = $this->consultar($objMdGdListaRecolhimentoDTO);
            
            if($objMdGdListaRecolhimentoDTO->getStrSituacao() != self::$ST_GERADA){
                throw new InfraException('A listagem precisa estar na situa��o gerada.'); 
            }

            $objMdGdListaRecolhimentoDTO->setStrSituacao(self::$ST_EDICAO);
            return $this->alterar($objMdGdListaRecolhimentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de recolhimento para o modo de edi��o.', $e);
        }
    }

    /**
     * Conclui a edi��o da listagem de recolhimento
     *
     * @param MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO
     * @return void
     */
    public function concluirEdicaoListaRecolhimentoControlado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO){
        try{
            if(!$objMdGdListaRecolhimentoDTO->isSetNumIdListaRecolhimento()){
                throw new InfraException('Informe o id da lista de recolhimento para concluir a edi��o.');
            }

            $objMdGdListaRecolhimentoDTO->retNumIdListaRecolhimento();
            $objMdGdListaRecolhimentoDTO->retStrSituacao();

            $objMdGdListaRecolhimentoDTO = $this->consultar($objMdGdListaRecolhimentoDTO);
            
            if($objMdGdListaRecolhimentoDTO->getStrSituacao() != self::$ST_EDICAO){
                throw new InfraException('A listagem precisa estar na situa��o gerada.'); 
            }

            // Obtem os processos da listagem de recolhimento
            $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
            $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($objMdGdListaRecolhimentoDTO->getNumIdListaRecolhimento());
            $objMdGdListaRecolProcedimentoDTO->retDblIdProcedimento();

            $objMdGdListaRecolProcedimentoRN = new MdGdListaRecolProcedimentoRN();
            $arrObjMdGdListaRecolProcedimentoDTO = $objMdGdListaRecolProcedimentoRN->listar($objMdGdListaRecolProcedimentoDTO);
            $totalProcessos = count($arrObjMdGdListaRecolProcedimentoDTO);

            // Obtem os documentos vinculados aos processos da listagem
            $arrIdsProcedimentos = [];

            foreach($arrObjMdGdListaRecolProcedimentoDTO as $objMdGdListaRecolProcedimentoDTO){
                $arrIdsProcedimentos[] = $objMdGdListaRecolProcedimentoDTO->getDblIdProcedimento();
            }
            
            if($arrIdsProcedimentos){
                $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
                $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($arrIdsProcedimentos, InfraDTO::$OPER_IN);
                $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();

                $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
                $arrObjRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO);
                
                $arrIdsDocumentos = array();
                if ($arrObjRelProtocoloProtocoloDTO) {
                    $arrIdsDocumentos = explode(',', InfraArray::implodeArrInfraDTO($arrObjRelProtocoloProtocoloDTO, 'IdProtocolo2'));
                }

                // Obtem todos os arquivamentos fisicos registrados para os documentos do processo
                $objArquivamentoDTO = new ArquivamentoDTO();
                $objArquivamentoDTO->retDblIdProtocoloDocumento();
                $objArquivamentoDTO->setDblIdProtocoloDocumento($arrIdsDocumentos, InfraDTO::$OPER_IN);

                $objArquivamentoRN = new ArquivamentoRN();
                $strSinDocumentosFisicos = $objArquivamentoRN->contar($objArquivamentoDTO) == 0 ? 'N' : 'S';
            }
            $objMdGdListaRecolhimentoDTO->setStrSituacao(self::$ST_GERADA);
            $objMdGdListaRecolhimentoDTO->setNumQtdProcessos($totalProcessos);
            $objMdGdListaRecolhimentoDTO->setStrSinDocumentosFisicos($strSinDocumentosFisicos);
            return $this->alterar($objMdGdListaRecolhimentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar a listagem de recolhimento para o modo de edi��o.', $e);
        }
    }

    /**
     * Atualiza o n�mero de processos de uma listagem de recolhimento
     *
     * @param MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO
     * @return boolean
     */
    public function atualizarNumeroProcessosControlado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO){
        try{
            if(!$objMdGdListaRecolhimentoDTO->isSetNumIdListaRecolhimento()){
                throw new InfraException('Informe o id da lista de recolhimento atualizar o n�mero de processos.');
            }

            // Obtem o quantidade de processos
            $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
            $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($objMdGdListaRecolhimentoDTO->getNumIdListaRecolhimento());

            $objMdGdListaRecolProcedimentoRN = new MdGdListaRecolProcedimentoRN();
            $numQuantidadeProcessos = $objMdGdListaRecolProcedimentoRN->contar($objMdGdListaRecolProcedimentoDTO);

            // Atualiza a quantidade de processos
            $objMdGdListaRecolhimentoDTO->setNumQtdProcessos($numQuantidadeProcessos);
            return $this->alterar($objMdGdListaRecolhimentoDTO);
        }catch(Exception $e){
            throw new InfraException('Erro ao altualizar o n�mero de processos.', $e);

        }
    }


}

?>