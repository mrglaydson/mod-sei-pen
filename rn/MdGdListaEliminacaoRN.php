<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdListaEliminacaoRN extends InfraRN
{


    public function __construct()
    {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao)
    {
        try {
           
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('gestao_documental_prep_list_eliminacao_gerar', __METHOD__, $objMdGdListaEliminacao);
            
            // Recupera os arquivamentos
            $arrObjMdGdArquivamentoDTO = $objMdGdListaEliminacao->getArrObjMdGdArquivamentoDTO();
            
            // Recupera os tipos de procedimento e documento que serão criados no arquivamento
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
            $objProtocoloDTO->setStrDescricao('Listagem de eliminação');
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

            // REALIZA A INCLUSÃO DO PROCESSO 
            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoDTO = $objProcedimentoRN->gerarRN0156($objProcedimentoDTO);

            //INCLUSÃO DO DOCUMENTO
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
            
            // Cria a listagem de eliminação
            $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
            $objMdGdListaEliminacaoDTO->setDblIdProcedimentoEliminacao($objProcedimentoDTO->getDblIdProcedimento());
            $objMdGdListaEliminacaoDTO->setDblIdDocumentoEliminacao($objDocumentoDTO->getDblIdDocumento());
            $objMdGdListaEliminacaoDTO->setStrNumero($this->obterProximaNumeroListagem());
            $objMdGdListaEliminacaoDTO->setDthEmissaoListagem(date('d/m/Y H:i:s'));
            $objMdGdListaEliminacaoDTO->setNumAnoLimiteInicio(2004); // TODO NOW
            $objMdGdListaEliminacaoDTO->setNumAnoLimiteFim(2018); // TODO NOW
            
            $objMdGdListaEliminacaoBD = new MdGdListaEliminacaoBD($this->getObjInfraIBanco());
            $objMdGdListaEliminacaoDTO = $objMdGdListaEliminacaoBD->cadastrar($objMdGdListaEliminacaoDTO);
            
            $objMdGdListElimProcedimentoBD = new MdGdListaElimProcedimentoBD($this->getObjInfraIBanco());
            $objMdGdArquivamentoRN = new MdGdArquivamentoRN();
            
            // Cria a relação da listagem de eliminação com os procedimentos
            foreach($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO){
                
                //Cria o vílculo da lista com o procedimento
                $objMdGdListaElimProcedimentoDTO = new MdGdListaElimProcedimentoDTO();
                $objMdGdListaElimProcedimentoDTO->setNumIdListaEliminacao($objMdGdListaEliminacaoDTO->getNumIdListaEliminacao());
                $objMdGdListaElimProcedimentoDTO->setDblIdProcedimento($objMdGdArquivamentoDTO->getDblIdProcedimento());
               
                $objMdGdListElimProcedimentoBD->cadastrar($objMdGdListaElimProcedimentoDTO);
                
                // Altera a situação do arquivamento do procedimento
                $objMdGdArquivamentoDTO->setStrSituacao(MdGdArquivamentoRN::$ST_ENVIADO_ELIMINACAO);
                $objMdGdArquivamentoRN->alterar($objMdGdArquivamentoDTO);
            }
            
            
        } catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar a listagem de eliminação.', $e);
        }
    }
    
    
    protected function consultarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao)
    {
        try {

            // CODE:
            // -- Consulta a listagem de eliminação
            
        } catch (Exception $e) {
            throw new InfraException('Erro consultando a listagem de eliminação.', $e);
        }
    }

    protected function listarConectado(MdGdListaEliminacaoDTO $objMdGdListaEliminacao)
    {
        try {

            // CODE
            // -- Listagem das listas de eliminação
        } catch (Exception $e) {
            throw new InfraException('Erro listando as listagens de eliminação.', $e);
        }
    }

  
    public function obterProximaNumeroListagem()
    {
        $objMdGdListaEliminacaoDTO = new MdGdListaEliminacaoDTO();
        $objMdGdListaEliminacaoDTO->setStrNumero('%'.date('Y'), InfraDTO::$OPER_LIKE);
        $objMdGdListaEliminacaoDTO->retTodos();
        
        $arrObjMdGdListaEliminacao = $this->listar($objMdGdListaEliminacaoDTO);
        
        $numeroListagem = count($arrObjMdGdListaEliminacao) + 1;
        
        return $numeroListagem."/".date('Y');
        
    }
    
    public function obterConteudoDocumentoEliminacao($arrObjMdGdArquivamentoDTO)
    {
        $objSessaoSEI = SessaoSEI::getInstance();

        $arrVariaveisModelo = [
            '@orgao@' => $objSessaoSEI->getStrDescricaoOrgaoUsuario(),
            '@unidade@' => $objSessaoSEI->getStrSiglaUnidadeAtual() . ' - '.$objSessaoSEI->getStrSiglaUnidadeAtual(),
            '@numero_listagem@' => $this->obterProximaNumeroListagem(),
            '@folha@' => '1/1', // Verificar depois
            '@tabela' => '',
            '@mensuracao_total@' => count($arrObjMdGdArquivamentoDTO).' processos',
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
        
        foreach($arrObjMdGdArquivamentoDTO as $objMdGdArquivamentoDTO){
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
            $strHtmlTabela .= '<td>'.$strCodigoClassificacao.'</td>';
            $strHtmlTabela .= '<td>'.$strDescritorCodigo.'</td>';
            $strHtmlTabela .= '<td>2010-2018</td>';
            $strHtmlTabela .= '<td>1</td>';
            $strHtmlTabela .= '<td></td>';
            $strHtmlTabela .= '<td>'.$objMdGdArquivamentoDTO->getStrObservacaoEliminacao().'</td>';
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