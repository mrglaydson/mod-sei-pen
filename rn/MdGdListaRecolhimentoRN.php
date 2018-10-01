<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaRecolhimentoRN extends InfraRN {

    public static $ST_GERADA = 'GE';
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
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_prep_list_recolhimento_gerar', __METHOD__, $objMdGdListaRecolhimento);

            // Recupera os arquivamentos
            $arrObjMdGdArquivamentoDTO = $objMdGdListaRecolhimento->getArrObjMdGdArquivamentoDTO();

            // Cria a listagem de eliminação
            $objMdGdListaRecolhimentoDTO = new MdGdListaRecolhimentoDTO();
            $objMdGdListaRecolhimentoDTO->setStrNumero($this->obterProximaNumeroListagem());
            $objMdGdListaRecolhimentoDTO->setDthEmissaoListagem(date('d/m/Y H:i:s'));
            $objMdGdListaRecolhimentoDTO->setNumAnoLimiteInicio(2004); // TODO NOW
            $objMdGdListaRecolhimentoDTO->setNumAnoLimiteFim(2018); // TODO NOW
            $objMdGdListaRecolhimentoDTO->setNumQtdProcessos(count($arrObjMdGdArquivamentoDTO));
            $objMdGdListaRecolhimentoDTO->setStrSituacao(self::$ST_GERADA);

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->getObjInfraIBanco());
            $objMdGdListaRecolhimentoDTO = $objMdGdListaRecolhimentoBD->cadastrar($objMdGdListaRecolhimentoDTO);

            $objMdGdListRecolProcedimentoBD = new MdGdListaRecolProcedimentoBD($this->getObjInfraIBanco());
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();

            // Cria a relação da listagem de eliminação com os procedimentos
            foreach ($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO) {

                //Cria o vílculo da lista com o procedimento
                $objMdGdListaRecolProcedimentoDTO = new MdGdListaRecolProcedimentoDTO();
                $objMdGdListaRecolProcedimentoDTO->setNumIdListaRecolhimento($objMdGdListaRecolhimentoDTO->getNumIdListaRecolhimento());
                $objMdGdListaRecolProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());

                $objMdGdListRecolProcedimentoBD->cadastrar($objMdGdListaRecolProcedimentoDTO);

                // Altera a situação do arquivamento do procedimento
                $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_ENVIADO_RECOLHIMENTO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }


            // CODE: 
            // -- Criação da listagem de recolhimento
        } catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar a listagem de recolhimento.', $e);
        }
    }
    
    
    protected function alterarConectado(MdGdListaRecolhimentoDTO $objMdGdListaRecolhimentoDTO) {
        try {

            $objMdGdListaRecolhimentoBD = new MdGdListaRecolhimentoBD($this->getObjInfraIBanco());
            return $objMdGdListaRecolhimentoBD->alterar($objMdGdListaRecolhimentoDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro consultando a listagem de eliminação.', $e);
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

            $strSumarioTabela = 'Lista de Processos';
            $strCaptionTabela = 'Lista de Processos';

            $strResultado .= '<table width="99%" class="infraTable" border="1">';
            $strResultado .= '<tr>';
            $strResultado .= '<th class="infraTh" width="13%">Descrição Unidade Corrente</th>';
            $strResultado .= '<th class="infraTh" width="10%">Código da Classificação</th>';
            $strResultado .= '<th class="infraTh" width="20%">Descritor do Código</th>';
            $strResultado .= '<th class="infraTh" width="14%">Nº do Processo</th>';
            $strResultado .= '<th class="infraTh" width="15%">Tipo de Processo</th>';
            $strResultado .= '<th class="infraTh" width="10%">Data de arquivamento</th>';
            $strResultado .= '<th class="infraTh" width="10%">Observações e/ou Justificativas</th>';
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
        SeiINT::download(null, $strCaminhoArquivoPdf, 'listagem_recolhiment.pdf', 'attachment', true);
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
        $strHtmlTabela .= '<th>Código Referente a Classificação</th>';
        $strHtmlTabela .= '<th>Descritor do código</th>';
        $strHtmlTabela .= '<th>Datas Limite</th>';
        $strHtmlTabela .= '<th>Quantificação</th>';
        $strHtmlTabela .= '<th>Especificação</th>';
        $strHtmlTabela .= '<th>Observações e/ou justificativas</th>';
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

}

?>