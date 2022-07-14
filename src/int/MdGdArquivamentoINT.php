<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Description of ArquivamentoINT
 *
 * @author Eduardo
 */
class MdGdArquivamentoINT extends InfraINT {

    public static function montarSelectJustificativasArquivamento(){

    }

    public static function montarSelectJustificativasDesarquivamento(){
        
    }

    public static function montarSelectSituacoesArquivamento($strItemSelecionado = '') {
        return InfraINT::montarSelectArray('null', '&nbsp', $strItemSelecionado, MdGdArquivamentoRN::obterSituacoesArquivamento());
    }

    public static function montarSelectGuardasArquivamento($strItemSelecionado = '') {
        return InfraINT::montarSelectArray('null', '&nbsp', $strItemSelecionado, MdGdArquivamentoRN::obterGuardasArquivamento());
    }

    public static function montarSelectDestinacoesFinalArquivamento($strItemSelecionado = '') {
        return InfraINT::montarSelectArray('null', '&nbsp', $strItemSelecionado, MdGdArquivamentoRN::obterDestinacoesFinalArquivamento());
    }

    public static function montarSelectCondicionantesArquivamento($strItemSelecionado = '') {
        return InfraINT::montarSelectArray('null', '&nbsp', $strItemSelecionado, ['S' => 'Com Condicionante', 'N' => 'Sem Condicionante']);
    }

    public static function montarSelectUnidadesArquivamento($numIdUnidadeSelecionada = '') {
        // Obtem as unidades de arquivamento mapeadas
        $objMdGdUnidadeArquivamento = new MdGdUnidadeArquivamentoDTO();
        $objMdGdUnidadeArquivamento->retNumIdUnidadeOrigem();
        $objMdGdUnidadeArquivamento->retNumIdUnidadeArquivamento();
        $objMdGdUnidadeArquivamento->setNumIdUnidadeOrigem($numIdUnidadeSelecionada, InfraDTO::$OPER_DIFERENTE);

        $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
        $arrObjMdGdUnidadeArquivamento = $objMdGdUnidadeArquivamentoRN->listar($objMdGdUnidadeArquivamento);

        $arrIdUnidades = InfraArray::mapearArrInfraDTO($arrObjMdGdUnidadeArquivamento, 'IdUnidadeOrigem', 'IdUnidadeOrigem');

        // Realiza os filtros das unidades
        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->retNumIdUnidade();
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->retStrDescricao();
        $objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

        if ($arrIdUnidades) {
            $objUnidadeDTO->setNumIdUnidade($arrIdUnidades, InfraDTO::$OPER_NOT_IN);
        }

        $objUnidadeRN = new UnidadeRN();
        $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);

        foreach ($arrObjUnidadeDTO as $objUnidadeDTO) {
            $objUnidadeDTO->setStrSigla(UnidadeINT::formatarSiglaDescricao($objUnidadeDTO->getStrSigla(), $objUnidadeDTO->getStrDescricao()));
        }

        return InfraINT::montarSelectArrInfraDTO('', '', $numIdUnidadeSelecionada, $arrObjUnidadeDTO, 'IdUnidade', 'Sigla');
    }

    public static function montarSelectAjaxUnidadesArquivamento($strPalavrasPesquisa = '') {
        // Obtem as unidades de arquivamento mapeadas
        $objMdGdUnidadeArquivamento = new MdGdUnidadeArquivamentoDTO();
        $objMdGdUnidadeArquivamento->retNumIdUnidadeOrigem();
        $objMdGdUnidadeArquivamento->retNumIdUnidadeArquivamento();

        $objMdGdUnidadeArquivamentoRN = new MdGdUnidadeArquivamentoRN();
        $arrObjMdGdUnidadeArquivamento = $objMdGdUnidadeArquivamentoRN->listar($objMdGdUnidadeArquivamento);

        $arrIdUnidades = InfraArray::mapearArrInfraDTO($arrObjMdGdUnidadeArquivamento, 'IdUnidadeOrigem', 'IdUnidadeOrigem');

        // Realiza os filtros de unidade
        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->retNumIdUnidade();
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->retStrDescricao();
        $objUnidadeDTO->setNumMaxRegistrosRetorno(50);
        $objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

        if ($arrIdUnidades) {
            $objUnidadeDTO->setNumIdUnidade($arrIdUnidades, InfraDTO::$OPER_NOT_IN);
        }

        if ($strPalavrasPesquisa != '') {
            $objUnidadeDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);
        }

        $objUnidadeRN = new UnidadeRN();
        $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);

        foreach ($arrObjUnidadeDTO as $objUnidadeDTO) {
            $objUnidadeDTO->setStrSigla(UnidadeINT::formatarSiglaDescricao($objUnidadeDTO->getStrSigla(), $objUnidadeDTO->getStrDescricao()));
        }

        return $arrObjUnidadeDTO;
    }

    public static function montarSelectsSerieNomeGerados($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdGrupoSerie=''){
        $objSerieDTO = new SerieDTO();
        $objSerieDTO->retNumIdSerie();
        $objSerieDTO->retStrNome();
    
        if ($numIdGrupoSerie!==''){
          $objSerieDTO->setNumIdGrupoSerie($numIdGrupoSerie);
        }
    
        if ($strValorItemSelecionado!=null){
    
          $objSerieDTO->setBolExclusaoLogica(false);
          $objSerieDTO->adicionarCriterio(array('SinAtivo','IdSerie'),
              array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
              array('S',$strValorItemSelecionado),
              InfraDTO::$OPER_LOGICO_OR);
    
          $objSerieDTO->adicionarCriterio(array('StaAplicabilidade', 'IdSerie'),
              array(InfraDTO::$OPER_IN, InfraDTO::$OPER_IGUAL),
              array(array(SerieRN::$TA_INTERNO_EXTERNO, SerieRN::$TA_INTERNO),$strValorItemSelecionado),
              InfraDTO::$OPER_LOGICO_OR);
        }else{
          $objSerieDTO->setStrStaAplicabilidade(array(SerieRN::$TA_INTERNO_EXTERNO, SerieRN::$TA_INTERNO),InfraDTO::$OPER_IN);
        }
    
        $objSerieDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
    
        $objSerieRN = new SerieRN();
        $arrObjSerieDTO = $objSerieRN->listarRN0646($objSerieDTO);
    
        return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjSerieDTO, 'IdSerie', 'Nome');
      }

      public static function montarSelectAssuntos($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
        $objAssuntoDTO = new AssuntoDTO();
        $objAssuntoDTO->retNumIdAssunto();
        $objAssuntoDTO->retStrCodigoEstruturado();
        $objAssuntoDTO->retStrDescricao();
        $objAssuntoDTO->setOrdStrCodigoEstruturado(InfraDTO::$TIPO_ORDENACAO_ASC);
        
        $objAssuntoRN = new AssuntoRN();
        $arrObjAssuntoDTO = $objAssuntoRN->listarRN0247($objAssuntoDTO);
        $arrAssuntos = [];

        foreach($arrObjAssuntoDTO as $objAssuntoDTO){
            $arrAssuntos[$objAssuntoDTO->getNumIdAssunto()] = $objAssuntoDTO->getStrCodigoEstruturado(). " - ".$objAssuntoDTO->getStrDescricao();
            
            if(strlen($arrAssuntos[$objAssuntoDTO->getNumIdAssunto()]) > 120){
                $arrAssuntos[$objAssuntoDTO->getNumIdAssunto()] = substr($arrAssuntos[$objAssuntoDTO->getNumIdAssunto()], 0, 120)."...";
            }
        }

        return parent::montarSelectArray($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrAssuntos);
      }
      
      public static function montarSelectJustificativas($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
        // Busca as justificativas
        $objMdGdJustificativaDTO = new MdGdJustificativaDTO();
        $objMdGdJustificativaDTO->setStrStaTipo(MdGdJustificativaRN::$STA_TIPO_ARQUIVAMENTO);
        $objMdGdJustificativaDTO->retNumIdJustificativa();
        $objMdGdJustificativaDTO->retStrNome();

        $objMdGdJustificativaRN = new MdGdJustificativaRN();
        $arrMdGdJustificativaDTO = $objMdGdJustificativaRN->listar($objMdGdJustificativaDTO);

        $$arrJustificativas = array();
        foreach($arrMdGdJustificativaDTO as $objMdGdJustificativaDTO){
            $arrJustificativas[$objMdGdJustificativaDTO->getNumIdJustificativa()] = $objMdGdJustificativaDTO->getStrNome();
        }

        return parent::montarSelectArray($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrJustificativas);
      }

    /**
     * Aplica redução do texto de uma propriedade do modelo de dados, adicionando reticências ao final da string
     *
     * @param str $parStrTexto Texto a ser reduzido pela função
     * @param int $parNumTamanho Tamanho do texto para redução
     * @return void
     */
    public function reduzirCampoTexto($parStrTexto, $parNumTamanho)
    {
        $strTexto = $parStrTexto;
        if(!is_null($parStrTexto) && strlen($parStrTexto) > $parNumTamanho){
            $strReticencias = ' ...';
            $numTamanhoMaximoPalavra = 20;

            $strTexto = trim(substr($parStrTexto, 0, $parNumTamanho));
            $arrStrTokens = explode(' ', $strTexto);
            $strUltimaPalavra = $arrStrTokens[count($arrStrTokens) - 1];

            $numTamanhoUltimaPalavra = strlen($strUltimaPalavra) > $numTamanhoMaximoPalavra ? strlen($strReticencias) : strlen($strUltimaPalavra);
            $numTamanhoUltimaPalavra = $numTamanhoUltimaPalavra < strlen($strReticencias) ? strlen($strReticencias) : $numTamanhoUltimaPalavra;
            $strTexto = substr($strTexto, 0, strlen($strTexto) - $numTamanhoUltimaPalavra);
            $strTexto = trim($strTexto) . $strReticencias;
        }

        return $strTexto;
    }

}
