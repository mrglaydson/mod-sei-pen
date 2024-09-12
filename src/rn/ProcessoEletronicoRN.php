<?php

require_once __DIR__ . '/../vendor/autoload.php'; 

class ProcessoNaoPodeSerDesbloqueadoException extends Exception {}

/**
 * Classe representando a interface de comunicação com os serviços do Barramento do PEN
 */
class ProcessoEletronicoRN extends InfraRN
{
    /* TAREFAS DE EXPEDIÇÃO DE PROCESSOS */
    //Está definindo o comportamento para a tarefa $TI_PROCESSO_EM_PROCESSAMENTO
  public static $TI_PROCESSO_ELETRONICO_PROCESSO_EXPEDIDO = 'PEN_PROCESSO_EXPEDIDO';
  public static $TI_PROCESSO_ELETRONICO_PROCESSO_RECEBIDO = 'PEN_PROCESSO_RECEBIDO';
  public static $TI_PROCESSO_ELETRONICO_PROCESSO_TRAMITE_CANCELADO = 'PEN_PROCESSO_CANCELADO';
  public static $TI_PROCESSO_ELETRONICO_PROCESSO_TRAMITE_RECUSADO = 'PEN_PROCESSO_RECUSADO';
  public static $TI_PROCESSO_ELETRONICO_PROCESSO_TRAMITE_EXTERNO = 'PEN_OPERACAO_EXTERNA';
  public static $TI_PROCESSO_ELETRONICO_PROCESSO_ABORTADO = 'PEN_EXPEDICAO_PROCESSO_ABORTADA';
  public static $TI_DOCUMENTO_AVULSO_RECEBIDO = 'PEN_DOCUMENTO_AVULSO_RECEBIDO';

    /* TIPO DE PROTOCOLO RECEBIDO PELO BARRAMENTO - SE PROCESSO OU DOCUMENTO AVULSO */
  public static $STA_TIPO_PROTOCOLO_PROCESSO = 'P';
  public static $STA_TIPO_PROTOCOLO_DOCUMENTO_AVULSO = 'D';

    /* NÍVEL DE SIGILO DE PROCESSOS E DOCUMENTOS */
  public static $STA_SIGILO_PUBLICO = '1';
  public static $STA_SIGILO_RESTRITO = '2';
  public static $STA_SIGILO_SIGILOSO = '3';

    /* RELAÇÃO DE SITUAÇÕES POSSÍVEIS EM UM TRÂMITE */
  public static $STA_SITUACAO_TRAMITE_NAO_INICIADO = 0;                       // Não Iniciado - Aguardando envio de Metadados pela solução
  public static $STA_SITUACAO_TRAMITE_INICIADO = 1;                           // Iniciado - Metadados recebidos pela solução
  public static $STA_SITUACAO_TRAMITE_COMPONENTES_ENVIADOS_REMETENTE = 2;     // Componentes digitais recebidos pela solução
  public static $STA_SITUACAO_TRAMITE_METADADOS_RECEBIDO_DESTINATARIO = 3;    // Metadados recebidos pelo destinatário
  public static $STA_SITUACAO_TRAMITE_COMPONENTES_RECEBIDOS_DESTINATARIO = 4; // Componentes digitais recebidos pelo destinatário
  public static $STA_SITUACAO_TRAMITE_RECIBO_ENVIADO_DESTINATARIO = 5;        // Recibo de conclusão do trâmite enviado pelo destinatário do processo
  public static $STA_SITUACAO_TRAMITE_RECIBO_RECEBIDO_REMETENTE = 6;          // Recibo de conclusão do trâmite recebido pelo remetente do processo
  public static $STA_SITUACAO_TRAMITE_CANCELADO = 7;                          // Trâmite do processo ou documento cancelado pelo usuário (Qualquer situação diferente de 5 e 6)
  public static $STA_SITUACAO_TRAMITE_RECUSADO = 8;                           // Trâmite do processo recusado pelo destinatário (Situações 2, 3, 4)
  public static $STA_SITUACAO_TRAMITE_CIENCIA_RECUSA = 9;                     // Remetente ciente da recusa do trâmite
  public static $STA_SITUACAO_TRAMITE_CANCELADO_AUTOMATICAMENTE = 10;

    /* TIPO DE TRÂMITE EXTERNO DE PROCESSO */
  public static $STA_TIPO_TRAMITE_ENVIO = 'E'; // Trâmite de ENVIO de processo externo
  public static $STA_TIPO_TRAMITE_RECEBIMENTO = 'R'; // Trâmite de RECEBIMENTO de processo externo

  public static $STA_TIPO_RECIBO_ENVIO = '1'; // Recibo de envio
  public static $STA_TIPO_RECIBO_CONCLUSAO_ENVIADO = '2'; // Recibo de recebimento enviado
  public static $STA_TIPO_RECIBO_CONCLUSAO_RECEBIDO = '3'; // Recibo de recebimento recebido

    /* OPERAÇÕES DO HISTÓRICO DO PROCESSO */
    // 02 a 18 estão registrados na tabela rel_tarefa_operacao
  public static $OP_OPERACAO_REGISTRO = "01";

    // 5 minutos de timeout para requisições via webservice
    const WS_TIMEOUT_CONEXAO = 300;
    const WS_ESPERA_RECONEXAO = 5;
    const WS_TENTATIVAS_ERRO = 3;
    const WS_TAMANHO_BLOCO_TRANSFERENCIA = 50;

    const ALGORITMO_HASH_DOCUMENTO = 'SHA256';

    /**
    * Motivo para recusar de tramite de componente digital pelo formato
    */
    const MTV_RCSR_TRAM_CD_FORMATO = '01';

    /**
    * Motivo para recusar de tramite de componente digital que está corrompido
    */
    const MTV_RCSR_TRAM_CD_CORROMPIDO = '02';

    /**
    * Motivo para recusar de tramite de componente digital que não foi enviado
    */
    const MTV_RCSR_TRAM_CD_FALTA = '03';

    /**
    * Espécie documentoal não mapeada
    */
    const MTV_RCSR_TRAM_CD_ESPECIE_NAO_MAPEADA = '04';

    /**
    * Motivo para recusar de tramite de componente digital
    */
    const MTV_RCSR_TRAM_CD_OUTROU = '99';

  public static $MOTIVOS_RECUSA = array(
        "01"  => "Formato de componente digital não suportado",
        "02" => "Componente digital corrompido",
        "03" => "Falta de componentes digitais",
        "04" => "Espécie documental não mapeada no destinatário",
        "99" => "Outro"
    );

    private $objPenWs;
    private $strEnderecoWebService;
    private $options;
    private $numTentativasErro;
    private $strComumXSD;
    private $strLocalCert;
    private $strLocalCertPassword;

	private $strClientGuzzle;
	private $strBaseUri;
	private $arrheaders;

	public function __construct() 
	{
	  $objConfiguracaoModPEN = ConfiguracaoModPEN::getInstance();
	  $strEnderecoWebService = $objConfiguracaoModPEN->getValor("PEN", "WebService");
	  $strLocalizacaoCertificadoDigital = $objConfiguracaoModPEN->getValor("PEN", "LocalizacaoCertificado");
	  $strSenhaCertificadoDigital = $objConfiguracaoModPEN->getValor("PEN", "SenhaCertificado");
	  $numTentativasErro = $objConfiguracaoModPEN->getValor("PEN", "NumeroTentativasErro", false, self::WS_TENTATIVAS_ERRO);
	  $numTentativasErro = (is_numeric($numTentativasErro)) ? intval($numTentativasErro) : self::WS_TENTATIVAS_ERRO;
  
	  $this->strEnderecoWebService = $strEnderecoWebService;
	  $this->strComumXSD = $this->strEnderecoWebService . '?xsd=comum.xsd';
	  $this->strLocalCert = $strLocalizacaoCertificadoDigital;
	  $this->strLocalCertPassword = $strSenhaCertificadoDigital;
	  $this->numTentativasErro = $numTentativasErro;
  
	  $this->options = array(
		'soap_version' => SOAP_1_1
		, 'local_cert' => $this->strLocalCert
		, 'passphrase' => $this->strLocalCertPassword
		, 'resolve_wsdl_remote_includes' => true
		, 'connection_timeout' => self::WS_TIMEOUT_CONEXAO
		, 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
		, 'encoding' => 'UTF-8'
		, 'attachment_type' => BeSimple\SoapCommon\Helper::ATTACHMENTS_TYPE_MTOM
		, 'ssl' => array(
		  'allow_self_signed' => true,
		)
	  );
  
  
	  // TODO: lembrar de pegar url dinamicamente quando SOAP for removido
	  $this->strBaseUri = 'https://homolog.api.processoeletronico.gov.br';
	  $this->arrheaders = [
		'Accept' => '*/*',
		'Content-Type' => 'application/json',
	  ];
	
	  $this->strClientGuzzle = new GuzzleHttp\Client([
		'base_uri' => $this->strBaseUri,
		'timeout'  => 5.0,
		'headers'  => $this->arrheaders,
		'cert'     => [$strLocalizacaoCertificadoDigital, $strSenhaCertificadoDigital],
	  ]);
	}


	protected function inicializarObjInfraIBanco()
	{
	  return BancoSEI::getInstance();
	}


    /**
     * Construtor do objeto SoapClien utilizado para comunicação Webservice SOAP
     *
     * @return void
     */
  private function getObjPenWs()
    {
    if($this->objPenWs == null) {

      if (InfraString::isBolVazia($this->strEnderecoWebService)) {
        throw new InfraException('Endereço do serviço de integração do Tramita GOV.BR não informado.');
      }

      if (InfraString::isBolVazia($this->strLocalCertPassword)) {
          throw new InfraException('Dados de autenticação do serviço de integração do Tramita.GOV.BR não informados.');
      }

        $this->validarDisponibilidade();

      try {
          $strWSDL = $this->strEnderecoWebService . '?wsdl';
          $this->objPenWs = new BeSimple\SoapClient\SoapClient($strWSDL, $this->options);
      } catch (Exception $e) {
          $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
          $mensagem = "Falha de comunicação com o Tramita GOV.BR: " . $detalhes;
          throw new \SoapFault("HTTP", $mensagem);
      }
    }

      return $this->objPenWs;
  }


    /**
     * Consulta a lista de repositório de estruturas disponíveis no Barramento de Serviços do PEN
     *
     * @param int $numIdentificacaoDoRepositorioDeEstruturas Código de identificação do repositório de estruturas do PEN
     * @return void
     */
	/**
	 * Consulta a lista de repositório de estruturas disponíveis no Barramento de Serviços do PEN
	 *
	 * @param int $numIdentificacaoDoRepositorioDeEstruturas Código de identificação do repositório de estruturas do PEN
	 */
	public function consultarRepositoriosDeEstruturas($numIdentificacaoDoRepositorioDeEstruturas)
	{
		$objRepositorioDTO = null;
		$endpoint = "/interoperabilidade/rest/v3/repositorios-de-estruturas/{$numIdentificacaoDoRepositorioDeEstruturas}/estruturas-organizacionais";
		try {
			$parametros = [
				'ativo' => true
			];
			$arrResultado = $this->get($endpoint, $parametros);

			if (isset($arrResultado)) {
				foreach ($arrResultado as $repositorio) {
					$objRepositorioDTO = new RepositorioDTO();
					$objRepositorioDTO->setNumId($repositorio['id']);
					$objRepositorioDTO->setStrNome(mb_convert_encoding($repositorio['nome'], 'UTF-8'));
					$objRepositorioDTO->setBolAtivo($repositorio['ativo']);
				}
			}
		} catch (Exception $e) {
			$mensagem = "Falha na obtenção dos Repositórios de Estruturas Organizacionais";
			$detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
			throw new InfraException($mensagem, $e, $detalhes);
		}

		return $objRepositorioDTO;
	}

	/**
     * Lista todo os repositórios de estruturas disponíveis no Barramento de Serviços do PEN
     *
     * @return void
     */
    public function listarRepositoriosDeEstruturas()
    {
        $arrObjRepositorioDTO = [];
        $endpoint = '/interoperabilidade/rest/v3/repositorios-de-estruturas';

        try{
            $parametros = [
                'ativos' => true
            ];

            $arrResultado = $this->get($endpoint, $parametros); 
            if (isset($arrResultado)) {
                foreach ($arrResultado as $repositorio) {
                    $objRepositorioDTO = new RepositorioDTO();
                    $objRepositorioDTO->setNumId($repositorio['id']);
                    $objRepositorioDTO->setStrNome(mb_convert_encoding($repositorio['nome'], 'ISO-8859-1', 'UTF-8'));
                    $objRepositorioDTO->setBolAtivo($repositorio['ativo']);
                    $arrObjRepositorioDTO[] = $objRepositorioDTO;
                }
            }
        } catch(Exception $e){
            $mensagem = "Falha na obtenção dos Repositórios de Estruturas Organizacionais";
            $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
            throw new InfraException($mensagem, $e, $detalhes);
        }

        return $arrObjRepositorioDTO;
    }

	/**
	 * Método responsável por consultar as estruturas das unidades externas no barramento.
	 *
	 * @param int $idRepositorioEstrutura
	 * @param int $numeroDeIdentificacaoDaEstrutura
	 * @param bool $bolRetornoRaw
	 * @throws InfraException
	 * @return EstruturaDTO
	 */
	public function consultarEstrutura($idRepositorioEstrutura, $numeroDeIdentificacaoDaEstrutura, $bolRetornoRaw = false)
	{
		$endpoint = "/interoperabilidade/rest/v3/repositorios-de-estruturas/{$idRepositorioEstrutura}/estruturas-organizacionais/$numeroDeIdentificacaoDaEstrutura";
		try {

			$parametros = [];
			$arrResultado = $this->get($endpoint, $parametros);

			if ($bolRetornoRaw !== false) {
				$arrResultado['nome'] = mb_convert_encoding($arrResultado['nome'], 'ISO-8859-1', 'UTF-8');
				$arrResultado['sigla'] = mb_convert_encoding($arrResultado['sigla'], 'ISO-8859-1', 'UTF-8');

				if (isset($arrResultado['hierarquia']) && is_array($arrResultado['hierarquia'])) {
					foreach ($arrResultado['hierarquia'] as &$arrHierarquia) {
						$arrHierarquia['nome'] = mb_convert_encoding($arrHierarquia['nome'], 'ISO-8859-1', 'UTF-8');
					}
				}

				return self::converterArrayParaObjeto($arrResultado);
			}

			$objEstruturaDTO = new EstruturaDTO();
			$objEstruturaDTO->setNumNumeroDeIdentificacaoDaEstrutura($arrResultado['numeroDeIdentificacaoDaEstrutura']);
			$objEstruturaDTO->setStrNome(mb_convert_encoding($arrResultado['nome'], 'ISO-8859-1', 'UTF-8'));
			$objEstruturaDTO->setStrSigla(mb_convert_encoding($arrResultado['sigla'], 'ISO-8859-1', 'UTF-8'));
			$objEstruturaDTO->setBolAtivo($arrResultado['ativo']);
			$objEstruturaDTO->setBolAptoParaReceberTramites($arrResultado['aptoParaReceberTramites']);
			$objEstruturaDTO->setStrCodigoNoOrgaoEntidade($arrResultado['codigoNoOrgaoEntidade']);

			return $objEstruturaDTO;
		} catch (Exception $e) {
			$mensagem = "Falha na obtenção de unidades externas";
			$detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
			throw new InfraException($mensagem, $e, $detalhes);
		}
	}

    /**
    * Método responsável por recuperar pela estutura pai a estrutura de filhos de uma unidade
    * @param $idRepositorioEstrutura
    * @param null $numeroDeIdentificacaoDaEstrutura
    * @param bool $bolRetornoRaw
    * @return array
    * @throws InfraException
    */
  public function consultarEstruturasPorEstruturaPai($idRepositorioEstrutura, $numeroDeIdentificacaoDaEstrutura = null, $bolRetornoRaw = false)
    {
    try {
        $parametros = new stdClass();
        $parametros->filtroDeEstruturasPorEstruturaPai = new stdClass();
        $parametros->filtroDeEstruturasPorEstruturaPai->identificacaoDoRepositorioDeEstruturas = $idRepositorioEstrutura;

      if(!is_null($numeroDeIdentificacaoDaEstrutura)){
        $parametros->filtroDeEstruturasPorEstruturaPai->numeroDeIdentificacaoDaEstrutura = $numeroDeIdentificacaoDaEstrutura;
      }

        $parametros->filtroDeEstruturasPorEstruturaPai->apenasAtivas = true;
        $result = $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->consultarEstruturasPorEstruturaPai($parametros);
        });

        $estruturasUnidades = is_array($result->estruturasEncontradasNoFiltroPorEstruturaPai->estrutura) ? $result->estruturasEncontradasNoFiltroPorEstruturaPai->estrutura : array($result->estruturasEncontradasNoFiltroPorEstruturaPai->estrutura);

        //Cria um array com os nomes da unidades para realizar a ordenação das mesmas
        $nomesUnidades = [];
      foreach ($estruturasUnidades as $estrutura) {
        $nomesUnidades[] = $estrutura->nome;
      }

        //Ordena as unidades pelo nome
        array_multisort($nomesUnidades, SORT_ASC, $estruturasUnidades);

        return $estruturasUnidades;
    }
    catch (Exception $e) {
        $mensagem = "Falha na obtenção de unidades externas";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

	/**
	 * Lista estruturas de um repositório de estruturas.
	 *
	 * @param int $idRepositorioEstrutura ID do repositório de estruturas.
	 * @param string $nome
	 * @param int $numeroDeIdentificacaoDaEstruturaRaizDaConsulta 
	 * @param string $nomeUnidade 
	 * @param string $siglaUnidade 
	 * @param int $offset Offset 
	 * @param int $registrosPorPagina 
	 * @param bool $parBolPermiteRecebimento 
	 * @param bool $parBolPermiteEnvio
	 * @throws InfraException 
	 * @return EstruturaDTO 
	 */
	public function listarEstruturas(
		$idRepositorioEstrutura,
		$nome = '',
		$numeroDeIdentificacaoDaEstruturaRaizDaConsulta = null,
		$nomeUnidade = null,
		$siglaUnidade = null,
		$offset = null,
		$registrosPorPagina = null,
		$parBolPermiteRecebimento = null,
		$parBolPermiteEnvio = null
	) {
		$arrObjEstruturaDTO = [];
		try {
			$idRepositorioEstrutura = filter_var($idRepositorioEstrutura, FILTER_SANITIZE_NUMBER_INT);
			if (!$idRepositorioEstrutura) {
				throw new InfraException('Repositório de Estruturas inválido');
			}

			$parametros = [
				'apenasAtivas' => true,
				'identificacaoDoRepositorioDeEstruturas' => $idRepositorioEstrutura,
				'sigla' => $siglaUnidade,
				'nome' => $nomeUnidade,
				'permiteRecebimento' => $parBolPermiteRecebimento,
				'permiteEnvio' => $parBolPermiteEnvio,
				'numeroDeIdentificacaoDaEstruturaRaizDaConsulta' => $numeroDeIdentificacaoDaEstruturaRaizDaConsulta ?? $nome,
				'registroInicial' => $offset,
				'quantidadeDeRegistros' => $registrosPorPagina,
			];

			$arrResultado = $this->consultarEstruturas($idRepositorioEstrutura, $parametros);

			if ($arrResultado['totalDeRegistros'] > 0) {

				foreach ($arrResultado['estruturas'] as $estrutura) {
					$objEstruturaDTO = new EstruturaDTO();
					$objEstruturaDTO->setNumNumeroDeIdentificacaoDaEstrutura($estrutura['numeroDeIdentificacaoDaEstrutura']);
					$objEstruturaDTO->setStrNome(mb_convert_encoding($estrutura['nome'], 'ISO-8859-1', 'UTF-8'));
					$objEstruturaDTO->setStrSigla(mb_convert_encoding($estrutura['sigla'], 'ISO-8859-1', 'UTF-8'));
					$objEstruturaDTO->setBolAtivo($estrutura['ativo']);
					$objEstruturaDTO->setBolAptoParaReceberTramites($estrutura['aptoParaReceberTramites']);
					$objEstruturaDTO->setStrCodigoNoOrgaoEntidade($estrutura['codigoNoOrgaoEntidade']);
					$objEstruturaDTO->setNumTotalDeRegistros($arrResultado['totalDeRegistros']);

					$arrHerarquia = array_map(function ($nivel) {
						return mb_convert_encoding($nivel['sigla'], 'ISO-8859-1', 'UTF-8');
					}, $estrutura['hierarquia'] ?? []);

					$objEstruturaDTO->setArrHierarquia($arrHerarquia);

					$arrObjEstruturaDTO[] = $objEstruturaDTO;
				}
			}
		} catch (Exception $e) {
			$mensagem = "Falha na obtenção de unidades externas";
			$detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
			throw new InfraException($mensagem, $e, $detalhes);
		}

		return $arrObjEstruturaDTO;
	}

    /**
	 * 
	 * @param int $idRepositorioEstrutura ID do repositório de estruturas.
	 * @param string $nome
	 * @param int $numeroDeIdentificacaoDaEstruturaRaizDaConsulta 
	 * @param string $nomeUnidade 
	 * @param string $siglaUnidade 
	 * @param int $offset Offset 
	 * @param int $registrosPorPagina 
	 * @param bool $parBolPermiteRecebimento 
	 * @param bool $parBolPermiteEnvio
	 * @throws InfraException 
	 * @return EstruturaDTO 
	 */
	public function listarEstruturasAutoCompletar(
		$idRepositorioEstrutura,
		$nome = '',
		$numeroDeIdentificacaoDaEstruturaRaizDaConsulta = null,
		$nomeUnidade = null,
		$siglaUnidade = null,
		$offset = null,
		$registrosPorPagina = null,
		$parBolPermiteRecebimento = null,
		$parBolPermiteEnvio = null
	  ) {
		$arrObjEstruturaDTO = array('diferencaDeRegistros' => 0, 'itens' => array());
	
		try {
			$idRepositorioEstrutura = filter_var($idRepositorioEstrutura, FILTER_SANITIZE_NUMBER_INT);
			if (!$idRepositorioEstrutura) {
				throw new InfraException("Repositório de Estruturas inválido");
		  	}
	
			$parametros = [
				'identificacaoDoRepositorioDeEstruturas' => $idRepositorioEstrutura,
				'apenasAtivas' => true,
				'numeroDeIdentificacaoDaEstruturaRaizDaConsulta' => $numeroDeIdentificacaoDaEstruturaRaizDaConsulta,
				'sigla' => $siglaUnidade,
				'nome' => !is_null($nomeUnidade) ? mb_convert_encoding($nomeUnidade, 'UTF-8') : (is_null($numeroDeIdentificacaoDaEstruturaRaizDaConsulta) && !is_null($nome) ? (is_numeric($nome) ? intval($nome) : mb_convert_encoding($nome, 'UTF-8')) : null),
				'registroInicial' => !is_null($registrosPorPagina) && !is_null($offset) ? $offset : null,
				'quantidadeDeRegistros' => !is_null($registrosPorPagina) && !is_null($offset) ? $registrosPorPagina : null,
				'permiteRecebimento' => $parBolPermiteRecebimento,
				'permiteEnvio' => $parBolPermiteEnvio
		  	];
		  
		  	$parametros = array_filter($parametros, function($value) {
				return !is_null($value);
		 	});
	
		  	$arrResultado = $this->consultarEstruturas($idRepositorioEstrutura, $parametros);
		
		  	if ($arrResultado['totalDeRegistros'] > 0) {
				foreach ($arrResultado['estruturas'] as $estrutura) {
	
					$objEstruturaDTO = new EstruturaDTO();
					$objEstruturaDTO->setNumNumeroDeIdentificacaoDaEstrutura($estrutura['numeroDeIdentificacaoDaEstrutura']);
					$objEstruturaDTO->setStrNome(mb_convert_encoding($estrutura['nome'], 'UTF-8'));
					$objEstruturaDTO->setStrSigla(mb_convert_encoding($estrutura['sigla'], 'UTF-8'));
					$objEstruturaDTO->setBolAtivo($estrutura['ativo']);
					$objEstruturaDTO->setBolAptoParaReceberTramites($estrutura['aptoParaReceberTramites']);
					$objEstruturaDTO->setStrCodigoNoOrgaoEntidade($estrutura['codigoNoOrgaoEntidade']);
					$objEstruturaDTO->setNumTotalDeRegistros($arrResultado['totalDeRegistros']);
			
					$arrHerarquia = array_map(function($nivel) {
						return mb_convert_encoding($nivel['sigla'], 'UTF-8');
					}, $estrutura['hierarquia'] ?? []);
				
					$objEstruturaDTO->setArrHierarquia($arrHerarquia);
			
					$arrObjEstruturaDTO["itens"][] = $objEstruturaDTO;
				}
			
				$totalDeRegistros = $arrResultado['totalDeRegistros'];
				$arrObjEstruturaDTO["diferencaDeRegistros"] = $totalDeRegistros > count($arrObjEstruturaDTO["itens"]) ?
				$totalDeRegistros - count($arrObjEstruturaDTO["itens"]) : 0;
		  }
		} catch (Exception $e) {
			$mensagem = "Falha na obtenção de unidades externas";
			$detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
			throw new InfraException($mensagem, $e, $detalhes);
		}
	
		return $arrObjEstruturaDTO;
	  }

  public function consultarMotivosUrgencia()
    {
      $curl = curl_init($this->strComumXSD);

    try{
        $bolEmProducao = boolval(ConfiguracaoSEI::getInstance()->getValor('SEI', 'Producao'));
        curl_setopt($curl, CURLOPT_URL, $this->strComumXSD);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $bolEmProducao);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $bolEmProducao);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->strLocalCert);
        curl_setopt($curl, CURLOPT_SSLCERTPASSWD, $this->strLocalCertPassword);

        $output = $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($curl) {
            return curl_exec($curl);
        });

        $dom = new DOMDocument();
        $dom->loadXML($output);

        $xpath = new DOMXPath($dom);

        $rootNamespace = $dom->lookupNamespaceUri($dom->namespaceURI);
        $xpath->registerNamespace('x', $rootNamespace);
        $entries = $xpath->query('/x:schema/x:simpleType[@name="motivoDaUrgencia"]/x:restriction/x:enumeration');

        $resultado = array();
      foreach ($entries as $entry) {
        $valor = $entry->getAttribute('value');
        $documentationNode = $xpath->query('x:annotation/x:documentation', $entry);
        $descricao = $documentationNode->item(0)->nodeValue;
        $resultado[$valor] = utf8_decode($descricao);
      }
    } finally{
        curl_close($curl);
    }

      return $resultado;
  }


    /**
     * Busca as espécies documentais aceitas pelo Barramento de Serviços do PEN
     *
     * As espécies aceitas estão registradas no WSDL do serviço e são obtidas a partir de análise deste descritor do serviço
     *
     * @return array
     */
  public function consultarEspeciesDocumentais()
    {
      $curl = curl_init($this->strComumXSD);

    try{
        curl_setopt($curl, CURLOPT_URL, $this->strComumXSD);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

      if(!ConfiguracaoSEI::getInstance()->getValor('SEI', 'Producao')){
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      }
        curl_setopt($curl, CURLOPT_SSLCERT, $this->strLocalCert);
        curl_setopt($curl, CURLOPT_SSLCERTPASSWD, $this->strLocalCertPassword);

        $output = $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($curl) {
            return curl_exec($curl);
        });

        $dom = new DOMDocument();
        $dom->loadXML($output);

        $xpath = new DOMXPath($dom);
        $rootNamespace = $dom->lookupNamespaceUri($dom->namespaceURI);
        $xpath->registerNamespace('x', $rootNamespace);
        $entries = $xpath->query('/x:schema/x:complexType[@name="especie"]/x:sequence/x:element[@name="codigo"]/x:simpleType/x:restriction/x:enumeration');
      if (count($entries) == 0){
        $erro_curl = empty(curl_error($curl))?'Não houve':curl_error($curl);
        throw new InfraException("Não foi achado nenhuma espécie documental. Favor checar a configuração. Possível erro do curl: ".$erro_curl);
      }
        $resultado = array();
      foreach ($entries as $entry) {
        $valor = $entry->getAttribute('value');
        $documentationNode = $xpath->query('x:annotation/x:documentation', $entry);
        $descricao = $documentationNode->item(0)->nodeValue;
        $resultado[$valor] = utf8_decode($descricao);
      }
    } finally{
        curl_close($curl);
    }

      return $resultado;
  }


  public function enviarProcesso($parametros)
    {
    try {
        return $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->enviarProcesso($parametros);
        });

    } catch (\SoapFault $e) {
      $naoRespEstruturaOrg = 'Transação não autorizada, pois o sistema não é responsável pela estrutura organizacional remetente';
      if (InfraString::formatarJavaScript(utf8_decode($e->faultstring)) == $naoRespEstruturaOrg) {
        $strMensagem = "Por favor, observe o seguinte procedimento para realizar o mapeamento adequado: Acesse a funcionalidade Administração, em seguida selecione Tramita GOV.BR e, por fim, proceda ao mapeamento utilizando somente as unidades pertinentes ao seu órgão/entidade na funcionalidade Mapeamento de Unidades. Certifique-se de seguir esse processo para garantir a correta execução do mapeamento.";
      } else {
        $strMensagem = str_replace(array("\n", "\r"), ' ', InfraString::formatarJavaScript(utf8_decode($e->faultstring)));
      }
      if ($e instanceof \SoapFault && !empty($e->detail->interoperabilidadeException->codigoErro) && $e->detail->interoperabilidadeException->codigoErro == '0005') {
          $$strMensagem .= 'O código mapeado para a unidade ' . utf8_decode($parametros->novoTramiteDeProcesso->processo->documento[0]->produtor->unidade->nome) . ' está incorreto.';
      }

        $e->faultstring = $this->validarTramitaEmAndamento($parametros, $strMensagem);
        $strMensagem = $e->faultstring;
        $strDetalhes = str_replace(array("\n", "\r"), ' ', InfraString::formatarJavaScript($this->tratarFalhaWebService($e)));
        throw new InfraException($strMensagem, $e, $strDetalhes);
    } catch (\Exception $e) {
        $mensagem = "Falha no envio externo do processo. Verifique log de erros do sistema para maiores informações.";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

  private function validarTramitaEmAndamento($parametros, $strMensagem)
    {
    if (strpos($strMensagem, 'já possui trâmite em andamento')) {
        $objProcessoEletronicoDTO = new ProcessoEletronicoDTO();
        $objProcessoEletronicoDTO->setDblIdProcedimento($parametros->dblIdProcedimento);

        $objProcessoEletronicoRN = new ProcessoEletronicoRN();
        $objUltimoTramiteDTO = $objProcessoEletronicoRN->consultarUltimoTramite($objProcessoEletronicoDTO);
        $numIdTramite = $objUltimoTramiteDTO->getNumIdTramite();

      if (!is_null($numIdTramite) && $numIdTramite > 0) {
        $strMensagem = "O trâmite ainda não foi concluído. Acompanhe no Painel de Controle o andamento da tramitação, antes de realizar uma nova tentativa. NRE: " . $objUltimoTramiteDTO->getStrNumeroRegistro() . ". Processo: " . $parametros->novoTramiteDeProcesso->processo->protocolo . ".";
      }
    }
      return $strMensagem;

  }

  public function listarPendencias($bolTodasPendencias)
    {
      $arrObjPendenciaDTO = array();

    try {
        $parametros = new stdClass();
        $parametros->filtroDePendencias = new stdClass();
        $parametros->filtroDePendencias->todasAsPendencias = $bolTodasPendencias;
        $result = $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->listarPendencias($parametros);
        });

      if(isset($result->listaDePendencias->IDT)){
        if(!is_array($result->listaDePendencias->IDT)) {
            $result->listaDePendencias->IDT = array($result->listaDePendencias->IDT);
        }

        foreach ($result->listaDePendencias->IDT as $idt) {
                $item = new PendenciaDTO();
                $item->setNumIdentificacaoTramite($idt->_);
                $item->setStrStatus($idt->status);
                $arrObjPendenciaDTO[] = $item;
        }
      }
    } catch (\Exception $e) {
        $mensagem = "Falha na listagem de pendências de trâmite de processos";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }

      return $arrObjPendenciaDTO;
  }

  private function tratarFalhaWebService(Exception $fault)
    {
      $mensagem = InfraException::inspecionar($fault);
    if($fault instanceof SoapFault && isset($fault->detail->interoperabilidadeException)) {
        $strWsException = $fault->detail->interoperabilidadeException;
        $mensagem = utf8_decode($strWsException->mensagem);

        // Fixação de mensagem de erro para quando já existe um trâmite em andamento
      if($strWsException->codigoErro == "0044"){
        $mensagem = 'Processo já possui um trâmite em andamento.';
      }
    }

      return $mensagem;
  }

  public function construirCabecalho($strNumeroRegistro, $idRepositorioOrigem, $idUnidadeOrigem, $idRepositorioDestino,
        $idUnidadeDestino, $urgente = false, $motivoUrgencia = 0, $enviarTodosDocumentos = false, $dblIdProcedimento = null)
    {
      $cabecalho = new stdClass();

    if(isset($strNumeroRegistro)) {
        $cabecalho->NRE = $strNumeroRegistro;
    }

      $cabecalho->remetente = new stdClass();
      $cabecalho->remetente->identificacaoDoRepositorioDeEstruturas = $idRepositorioOrigem;
      $cabecalho->remetente->numeroDeIdentificacaoDaEstrutura = $idUnidadeOrigem;

      $cabecalho->destinatario = new stdClass();
      $cabecalho->destinatario->identificacaoDoRepositorioDeEstruturas = $idRepositorioDestino;
      $cabecalho->destinatario->numeroDeIdentificacaoDaEstrutura = $idUnidadeDestino;

      $cabecalho->urgente = $urgente;
      $cabecalho->motivoDaUrgencia = $motivoUrgencia;
      //Parâmetro abaixo foi descontinuado por falhas e substituido pelo enviarApenasComponentesDigitaisPendentes
      //$cabecalho->obrigarEnvioDeTodosOsComponentesDigitais = $enviarTodosDocumentos;
      $cabecalho->enviarApenasComponentesDigitaisPendentes = !$enviarTodosDocumentos;

      $this->atribuirInformacoesAssunto($cabecalho, $dblIdProcedimento);
      $this->atribuirInformacoesModulo($cabecalho);

      return $cabecalho;
  }

  private function atribuirInformacoesModulo($objCabecalho)
    {

    try{

        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $arrPropAdicionais=$objCabecalho->propriedadeAdicional;
        $arrPropAdicionais[] = new SoapVar("<propriedadeAdicional
            chave='MODULO_PEN_VERSAO'>". $objInfraParametro->getValor('VERSAO_MODULO_PEN') . "</propriedadeAdicional>", XSD_ANYXML);

        $objCabecalho->propriedadeAdicional= $arrPropAdicionais;

    }catch(Exception $e){

        throw new InfraException($mensagem, $e);
    }


  }


  private function atribuirInformacoesAssunto($objCabecalho, $dblIdProcedimento)
    {


    try{


      if(!isset($dblIdProcedimento)){
          throw new InfraException('Parâmetro $dblIdProcedimento não informado.');
      }

      $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
      $objRelProtocoloAssuntoDTO->setDblIdProtocolo($dblIdProcedimento);
      $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();
      $objRelProtocoloAssuntoDTO->retNumIdAssunto();
      $objRelProtocoloAssuntoDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

      $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
      $arrobjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);

      $arrDadosAssunto = array();
      $contagem=1;
      $objProcessoEletronicoRN = new ProcessoEletronicoRN();

      foreach ($arrobjRelProtocoloAssuntoDTO as $objRelProtocoloAssuntoDTO) {

          $idAssunto = $objRelProtocoloAssuntoDTO->getNumIdAssunto();
          $assuntoDTO = new AssuntoDTO();
          $assuntoDTO->setNumIdAssunto($idAssunto);
          $assuntoDTO->retNumPrazoCorrente();
          $assuntoDTO->retNumPrazoIntermediario();
          $assuntoDTO->retStrStaDestinacao();
          $assuntoDTO->retStrObservacao();
          $assuntoDTO->retStrCodigoEstruturado();

          $objAssuntoRN = new AssuntoRN();
          $infoAssunto = $objAssuntoRN->consultarRN0256($assuntoDTO);

        switch ($infoAssunto->getStrStaDestinacao()) {
          case AssuntoRN::$TD_ELIMINACAO:
            $destinacao = "Eliminação";
              break;

          case AssuntoRN::$TD_GUARDA_PERMANENTE:
              $destinacao = "Guarda Permanente";
              break;
        }


          $valorInput=$objRelProtocoloAssuntoDTO->getStrDescricaoAssunto()?utf8_encode($objProcessoEletronicoRN->reduzirCampoTexto(htmlspecialchars($objRelProtocoloAssuntoDTO->getStrDescricaoAssunto()), 10000)):"NA";
          $arrDadosAssunto[] = new SoapVar("<propriedadeAdicional
            chave='CLASSIFICACAO_Descricao_" . $contagem . "'>" . $valorInput . "</propriedadeAdicional>", XSD_ANYXML);

          $valorInput=$infoAssunto->getStrCodigoEstruturado()?utf8_encode($infoAssunto->getStrCodigoEstruturado()):"NA";
          $arrDadosAssunto[] = new SoapVar("<propriedadeAdicional
            chave='CLASSIFICACAO_CodigoEstruturado_" . $contagem . "'>" . $valorInput . "</propriedadeAdicional>", XSD_ANYXML);

          $valorInput=$infoAssunto->getNumPrazoCorrente()? (int) $infoAssunto->getNumPrazoCorrente() :"NA";
          $arrDadosAssunto[] = new SoapVar("<propriedadeAdicional
            chave='CLASSIFICACAO_PrazoCorrente_" . $contagem . "'>" . $valorInput . "</propriedadeAdicional>", XSD_ANYXML);

          $valorInput=$infoAssunto->getNumPrazoIntermediario()?(int) $infoAssunto->getNumPrazoIntermediario():"NA";
          $arrDadosAssunto[] = new SoapVar("<propriedadeAdicional
            chave='CLASSIFICACAO_PrazoIntermediario_" . $contagem . "'>" . $valorInput . "</propriedadeAdicional>", XSD_ANYXML);

          $valorInput=$destinacao?utf8_encode($destinacao):"NA";
          $arrDadosAssunto[] = new SoapVar("<propriedadeAdicional
            chave='CLASSIFICACAO_Destinacao_" . $contagem . "'>" . $valorInput . "</propriedadeAdicional>", XSD_ANYXML);

          $valorInput=$infoAssunto->getStrObservacao()?utf8_encode($objProcessoEletronicoRN->reduzirCampoTexto(htmlspecialchars($infoAssunto->getStrObservacao()), 10000)):"NA";
          $arrDadosAssunto[] = new SoapVar("<propriedadeAdicional
            chave='CLASSIFICACAO_Observacao_" . $contagem . "'>" . $valorInput . "</propriedadeAdicional>", XSD_ANYXML);


          $contagem++;
      }

      $objCabecalho->propriedadeAdicional= $arrDadosAssunto;

    }catch(Exception $e){

        throw new InfraException($mensagem, $e);
    }

  }

  public function enviarComponenteDigital($parametros)
    {
    try {
        return $this->tentarNovamenteSobErroHTTP(function($objPenWs) use (&$parametros) {
            return $objPenWs->enviarComponenteDigital($parametros);
        });
    } catch (\Exception $e) {
        $mensagem = "Falha no envio de componentes digitais";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }


    /**
    * Método responsável por realizar o envio da parte de um componente digital
    * @param $parametros
    * @return mixed
    * @throws InfraException
    */
  public function enviarParteDeComponenteDigital($parametros)
    {
    try {
        return $this->tentarNovamenteSobErroHTTP(function($objPenWs) use (&$parametros) {
            return $objPenWs->enviarParteDeComponenteDigital($parametros);
        });
    } catch (\Exception $e) {
        $mensagem = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e);
    }
  }

    /**
    * Método responsável por sinalizar o término do envio das partes de um componente digital
    * @param $parametros
    * @return mixed
    * @throws InfraException
    */
  public function sinalizarTerminoDeEnvioDasPartesDoComponente($parametros)
    {
    try {
        return $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->sinalizarTerminoDeEnvioDasPartesDoComponente($parametros);
        });
    } catch (\Exception $e) {
        $mensagem = "Falha em sinalizar o término de envio das partes do componente digital";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

  public function solicitarMetadados($parNumIdentificacaoTramite)
    {
    try {
        $parametros = new stdClass();
        $parametros->IDT = $parNumIdentificacaoTramite;
        return $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            $objMetadadosProtocolo = $objPenWs->solicitarMetadados($parametros);
            $objMetadadosProtocolo->IDT = $parametros->IDT;
            return $objMetadadosProtocolo;
        });
    } catch (\Exception $e) {
        $mensagem = "Falha na solicitação de metadados do processo";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

  public static function converterDataWebService($dataHoraSEI)
    {
      $resultado = '';
    if(isset($dataHoraSEI)){
        $resultado = InfraData::getTimestamp($dataHoraSEI);
        $resultado = date(DateTime::W3C, $resultado);
    }

      return $resultado;
  }

  public static function converterDataSEI($dataHoraWebService)
    {
      $resultado = null;
    if(isset($dataHoraWebService)){
        $resultado = strtotime($dataHoraWebService);
        $resultado = date('d/m/Y H:i:s', $resultado);
    }

      return $resultado;
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

  public function cadastrarTramiteDeProcesso($parDblIdProcedimento, $parStrNumeroRegistro, $parNumIdentificacaoTramite, $parStrStaTipoTramite, $parDthRegistroTramite, $parNumIdRepositorioOrigem,
        $parNumIdEstruturaOrigem, $parNumIdRepositorioDestino, $parNumIdEstruturaDestino, $parObjProtocolo, $parNumTicketComponentesDigitais = null, $parObjComponentesDigitaisSolicitados = null, $bolSinProcessamentoEmLote = false, $numIdUnidade = null)
    {

    if(!isset($parDblIdProcedimento) || $parDblIdProcedimento == 0) {
        throw new InfraException('Parâmetro $parDblIdProcedimento não informado.');
    }

    if(!isset($parStrNumeroRegistro)) {
        throw new InfraException('Parâmetro $parStrNumeroRegistro não informado.');
    }

    if(!isset($parNumIdentificacaoTramite) || $parNumIdentificacaoTramite == 0) {
        throw new InfraException('Parâmetro $parStrNumeroRegistro não informado.');
    }

    if(!isset($parStrStaTipoTramite) || !in_array($parStrStaTipoTramite, array(ProcessoEletronicoRN::$STA_TIPO_TRAMITE_ENVIO, ProcessoEletronicoRN::$STA_TIPO_TRAMITE_RECEBIMENTO))) {
        throw new InfraException('Parâmetro $parStrStaTipoTramite inválio');
    }

    if(!isset($parNumIdRepositorioOrigem) || $parNumIdRepositorioOrigem == 0) {
        throw new InfraException('Parâmetro $parNumIdRepositorioOrigem não informado.');
    }

    if(!isset($parNumIdEstruturaOrigem) || $parNumIdEstruturaOrigem == 0) {
        throw new InfraException('Parâmetro $parNumIdEstruturaOrigem não informado.');
    }

    if(!isset($parNumIdRepositorioDestino) || $parNumIdRepositorioDestino == 0) {
        throw new InfraException('Parâmetro $parNumIdRepositorioDestino não informado.');
    }

    if(!isset($parNumIdEstruturaDestino) || $parNumIdEstruturaDestino == 0) {
        throw new InfraException('Parâmetro $parNumIdEstruturaDestino não informado.');
    }

    if(!isset($parObjProtocolo)) {
        throw new InfraException('Parâmetro $objProcesso não informado.');
    }

      //Monta dados do processo eletrônico
      $objProcessoEletronicoDTO = new ProcessoEletronicoDTO();
      $objProcessoEletronicoDTO->setStrNumeroRegistro($parStrNumeroRegistro);
      $objProcessoEletronicoDTO->setDblIdProcedimento($parDblIdProcedimento);
      $objProcessoEletronicoDTO->setStrStaTipoProtocolo($parObjProtocolo->staTipoProtocolo);

      //Montar dados dos procedimentos apensados
    if(isset($parObjProtocolo->processoApensado)){
      if(!is_array($parObjProtocolo->processoApensado)){
          $parObjProtocolo->processoApensado = array($parObjProtocolo->processoApensado);
      }

        $arrObjRelProcessoEletronicoApensadoDTO = array();
        $objRelProcessoEletronicoApensadoDTO = null;
      foreach ($parObjProtocolo->processoApensado as $objProcessoApensado) {
          $objRelProcessoEletronicoApensadoDTO = new RelProcessoEletronicoApensadoDTO();
          $objRelProcessoEletronicoApensadoDTO->setStrNumeroRegistro($parStrNumeroRegistro);
          $objRelProcessoEletronicoApensadoDTO->setDblIdProcedimentoApensado($objProcessoApensado->idProcedimentoSEI);
          $objRelProcessoEletronicoApensadoDTO->setStrProtocolo($objProcessoApensado->protocolo);
          $arrObjRelProcessoEletronicoApensadoDTO[] = $objRelProcessoEletronicoApensadoDTO;
      }

        $objProcessoEletronicoDTO->setArrObjRelProcessoEletronicoApensado($arrObjRelProcessoEletronicoApensadoDTO);
    }

      //Monta dados do trâmite do processo
      $objTramiteDTO = new TramiteDTO();
      $objTramiteDTO->setStrNumeroRegistro($parStrNumeroRegistro);
      $objTramiteDTO->setNumIdTramite($parNumIdentificacaoTramite);
      $objTramiteDTO->setNumTicketEnvioComponentes($parNumTicketComponentesDigitais);
      $objTramiteDTO->setDthRegistro($this->converterDataSEI($parDthRegistroTramite));
    if($bolSinProcessamentoEmLote){
        $objTramiteDTO->setNumIdUnidade($numIdUnidade);
    }else{
        $objTramiteDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    }
      $objTramiteDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
      $objTramiteDTO->setNumIdRepositorioOrigem($parNumIdRepositorioOrigem);
      $objTramiteDTO->setNumIdEstruturaOrigem($parNumIdEstruturaOrigem);
      $objTramiteDTO->setNumIdRepositorioDestino($parNumIdRepositorioDestino);
      $objTramiteDTO->setNumIdEstruturaDestino($parNumIdEstruturaDestino);
      $objTramiteDTO->setStrStaTipoTramite($parStrStaTipoTramite);
      $objProcessoEletronicoDTO->setArrObjTramiteDTO(array($objTramiteDTO));

      //Monta dados dos componentes digitais
      $parObjProtocoloDesmembrado = ProcessoEletronicoRN::desmembrarProcessosAnexados($parObjProtocolo);
      $arrObjComponenteDigitalDTO = $this->montarDadosComponenteDigital($parStrNumeroRegistro, $parNumIdentificacaoTramite, $parObjProtocoloDesmembrado, $parObjComponentesDigitaisSolicitados);

      $objTramiteDTO->setArrObjComponenteDigitalDTO($arrObjComponenteDigitalDTO);
      $objProcessoEletronicoDTO = $this->cadastrarTramiteDeProcessoInterno($objProcessoEletronicoDTO);

      return $objProcessoEletronicoDTO;
  }



  protected function cadastrarTramiteDeProcessoInternoControlado(ProcessoEletronicoDTO $parObjProcessoEletronicoDTO)
    {
    if(!isset($parObjProcessoEletronicoDTO)) {
        throw new InfraException('Parâmetro $parObjProcessoEletronicoDTO não informado.');
    }

      $idProcedimento = $parObjProcessoEletronicoDTO->getDblIdProcedimento();

      //Registra os dados do processo eletrônico
      $objProcessoEletronicoDTOFiltro = new ProcessoEletronicoDTO();
      $objProcessoEletronicoDTOFiltro->setStrNumeroRegistro($parObjProcessoEletronicoDTO->getStrNumeroRegistro());
      $objProcessoEletronicoDTOFiltro->setDblIdProcedimento($parObjProcessoEletronicoDTO->getDblIdProcedimento());
      $objProcessoEletronicoDTOFiltro->setStrStaTipoProtocolo($parObjProcessoEletronicoDTO->getStrStaTipoProtocolo());
      $objProcessoEletronicoDTOFiltro->retStrNumeroRegistro();
      $objProcessoEletronicoDTOFiltro->retDblIdProcedimento();
      $objProcessoEletronicoDTOFiltro->retStrStaTipoProtocolo();

      $objProcessoEletronicoBD = new ProcessoEletronicoBD($this->getObjInfraIBanco());
      $objProcessoEletronicoDTO = $objProcessoEletronicoBD->consultar($objProcessoEletronicoDTOFiltro);

    if(empty($objProcessoEletronicoDTO)) {
        $objProcessoEletronicoDTO = $objProcessoEletronicoBD->cadastrar($objProcessoEletronicoDTOFiltro);
    }

      //Registrar processos apensados
    if($parObjProcessoEletronicoDTO->isSetArrObjRelProcessoEletronicoApensado()) {
        $objRelProcessoEletronicoApensadoBD = new RelProcessoEletronicoApensadoBD($this->getObjInfraIBanco());
      foreach ($parObjProcessoEletronicoDTO->getArrObjRelProcessoEletronicoApensado() as $objRelProcessoEletronicoApensadoDTOFiltro) {
        if($objRelProcessoEletronicoApensadoBD->contar($objRelProcessoEletronicoApensadoDTOFiltro) == 0){
          $objRelProcessoEletronicoApensadoBD->cadastrar($objRelProcessoEletronicoApensadoDTOFiltro);
        }
      }
    }

      //Registrar informações sobre o trâmite do processo
      $arrObjTramiteDTO = $parObjProcessoEletronicoDTO->getArrObjTramiteDTO();
      $parObjTramiteDTO = $arrObjTramiteDTO[0];

      $objTramiteDTO = new TramiteDTO();
      $objTramiteDTO->retNumIdTramite();
      $objTramiteDTO->setStrNumeroRegistro($parObjTramiteDTO->getStrNumeroRegistro());
      $objTramiteDTO->setNumIdTramite($parObjTramiteDTO->getNumIdTramite());

      $objTramiteBD = new TramiteBD($this->getObjInfraIBanco());
      $objTramiteDTO = $objTramiteBD->consultar($objTramiteDTO);

    if(empty($objTramiteDTO)) {
        $objTramiteDTO = $objTramiteBD->cadastrar($parObjTramiteDTO);
    }

      $objProcessoEletronicoDTO->setArrObjTramiteDTO(array($objTramiteDTO));

      //Registra informações sobre o componente digital do documento
      $arrObjComponenteDigitalDTO = array();
      $objComponenteDigitalBD = new ComponenteDigitalBD($this->getObjInfraIBanco());

      $arrObjComponenteDigitalDTO = array();
    foreach ($parObjTramiteDTO->getArrObjComponenteDigitalDTO() as $objComponenteDigitalDTO) {

        //Verifica se o documento foi inserido pelo trâmite atual
      if($objComponenteDigitalDTO->getDblIdDocumento() != null){
          $objComponenteDigitalDTO->setDblIdProcedimento($idProcedimento);
          $objComponenteDigitalDTOFiltro = new ComponenteDigitalDTO();
          $objComponenteDigitalDTOFiltro->setNumIdTramite($objComponenteDigitalDTO->getNumIdTramite());
          $objComponenteDigitalDTOFiltro->setStrNumeroRegistro($objComponenteDigitalDTO->getStrNumeroRegistro());
          $objComponenteDigitalDTOFiltro->setDblIdProcedimento($objComponenteDigitalDTO->getDblIdProcedimento());
          $objComponenteDigitalDTOFiltro->setDblIdDocumento($objComponenteDigitalDTO->getDblIdDocumento());
          $objComponenteDigitalDTOFiltro->setNumOrdem($objComponenteDigitalDTO->getNumOrdem());
          $objComponenteDigitalDTOFiltro->setNumOrdemDocumento($objComponenteDigitalDTO->getNumOrdemDocumento());

        if($objComponenteDigitalBD->contar($objComponenteDigitalDTOFiltro) == 0){
          $objComponenteDigitalDTO->setStrTarjaLegada("N");
          $objComponenteDigitalDTO = $objComponenteDigitalBD->cadastrar($objComponenteDigitalDTO);
        }
        else {
            //Verifica se foi setado o envio
          if(!$objComponenteDigitalDTO->isSetStrSinEnviar()){
            $objComponenteDigitalDTO->setStrSinEnviar('N');
          }
        }
          $arrObjComponenteDigitalDTO[] = $objComponenteDigitalDTO;
      }
    }

      $objTramiteDTO->setArrObjComponenteDigitalDTO($arrObjComponenteDigitalDTO);
      return $objProcessoEletronicoDTO;
  }

    /**
    * Retorna o hash do objecto do solicitarMetadadosResponse
    *
    * @param object $objMeta tem que ser o componenteDigital->hash
    * @return string
    */
  public static function getHashFromMetaDados($objMeta)
    {
      $strHashConteudo = '';

    if (isset($objMeta)) {
      if(is_string($objMeta)){
        $strHashConteudo = $objMeta;
      } else {
          $matches = array();
          $strHashConteudo = (isset($objMeta->enc_value)) ? $objMeta->enc_value : $objMeta->conteudo;

        if (preg_match('/^<hash.*>(.*)<\/hash>$/', $strHashConteudo, $matches, PREG_OFFSET_CAPTURE)) {
          $strHashConteudo = $matches[1][0];
        }
      }
    }

      return $strHashConteudo;
  }

  private function montarDadosMaisDeUmComponenteDigital($objDocumento, $parStrNumeroRegistro, $parNumIdentificacaoTramite, $parObjProtocolo, $parObjComponentesDigitaisSolicitados)
    {
      $arrayComponentesDigitais = $objDocumento->componenteDigital;
      $arrObjComponenteDigitalDTO = array();
      $arrayTeste = array();
      $contComponentes = 0;

    foreach ($arrayComponentesDigitais as $indice => $objComponenteDigital){
        $contComponentes++;
        $objComponenteDigitalDTO = new ComponenteDigitalDTO();
        $objComponenteDigitalDTO->setStrNumeroRegistro($parStrNumeroRegistro);
        //TODO: Error utilizar idProcedimentoSEI devido processos apensados
        $objComponenteDigitalDTO->setDblIdProcedimento($parObjProtocolo->idProcedimentoSEI);
        $objComponenteDigitalDTO->setDblIdDocumento($objDocumento->idDocumentoSEI);
        $objComponenteDigitalDTO->setNumOrdemDocumento($objDocumento->ordem);
        $objComponenteDigitalDTO->setNumOrdem($objComponenteDigital->ordem);
        $objComponenteDigitalDTO->setNumIdTramite($parNumIdentificacaoTramite);
        $objComponenteDigitalDTO->setStrProtocolo($parObjProtocolo->protocolo);

      if(isset($objDocumento->idProcedimentoAnexadoSEI)){
        $objComponenteDigitalDTO->setDblIdProcedimentoAnexado($objDocumento->idProcedimentoAnexadoSEI);
        $objComponenteDigitalDTO->setStrProtocoloProcedimentoAnexado($objDocumento->protocoloDoProcessoAnexado);
        $objComponenteDigitalDTO->setNumOrdemDocumentoAnexado($objDocumento->ordemAjustada);
      }


        $objComponenteDigitalDTO->setStrNome($objComponenteDigital->nome);
        $strHashConteudo = static::getHashFromMetaDados($objComponenteDigital->hash);

        $objComponenteDigitalDTO->setStrHashConteudo($strHashConteudo);
        $objComponenteDigitalDTO->setStrAlgoritmoHash(self::ALGORITMO_HASH_DOCUMENTO);
        $objComponenteDigitalDTO->setStrTipoConteudo($objComponenteDigital->tipoDeConteudo);
        $objComponenteDigitalDTO->setStrMimeType($objComponenteDigital->mimeType);
        $objComponenteDigitalDTO->setStrDadosComplementares($objComponenteDigital->dadosComplementaresDoTipoDeArquivo);

        //Registrar componente digital necessita ser enviado pelo trâmite específico      //TODO: Teste $parObjComponentesDigitaisSolicitados aqui
      if(isset($parObjComponentesDigitaisSolicitados)){
          $arrObjItensSolicitados = is_array($parObjComponentesDigitaisSolicitados->processo) ? $parObjComponentesDigitaisSolicitados->processo : array($parObjComponentesDigitaisSolicitados->processo);

        foreach ($arrObjItensSolicitados as $objItemSolicitado) {
          if(!is_null($objItemSolicitado)){
              $objItemSolicitado->hash = is_array($objItemSolicitado->hash) ? $objItemSolicitado->hash : array($objItemSolicitado->hash);

            if($objItemSolicitado->protocolo == $objComponenteDigitalDTO->getStrProtocolo() && in_array($strHashConteudo, $objItemSolicitado->hash) && !$objDocumento->retirado) {
              $objComponenteDigitalDTO->setStrSinEnviar("S");
            }
          }
        }
      }

        //TODO: Avaliar dados do tamanho do documento em bytes salvo na base de dados
        $objComponenteDigitalDTO->setNumTamanho($objComponenteDigital->tamanhoEmBytes);
        $objComponenteDigitalDTO->setNumIdAnexo($objComponenteDigital->idAnexo);

        array_push($arrObjComponenteDigitalDTO, $objComponenteDigitalDTO);
    }

      return $arrObjComponenteDigitalDTO;
  }


  private function montarDadosComponenteDigital($parStrNumeroRegistro, $parNumIdentificacaoTramite, $parObjProtocolo, $parObjComponentesDigitaisSolicitados)
    {
      //Monta dados dos componentes digitais
      $arrObjComponenteDigitalDTO = array();
      $arrObjDocumento = self::obterDocumentosProtocolo($parObjProtocolo, true);

      $arrObjComponenteDigitalDTOAux = array();
    foreach ($arrObjDocumento as $objDocumento) {
        $quantidadeDeComponentesDigitais = count($objDocumento->componenteDigital);
      if($quantidadeDeComponentesDigitais > 1){
        $arrObjComponenteDigitalDTOAux = self::montarDadosMaisDeUmComponenteDigital($objDocumento, $parStrNumeroRegistro, $parNumIdentificacaoTramite, $parObjProtocolo, $parObjComponentesDigitaisSolicitados);
      }else{
          $objComponenteDigitalDTO = new ComponenteDigitalDTO();
          $objComponenteDigitalDTO->setStrNumeroRegistro($parStrNumeroRegistro);
          //TODO: Error utilizar idProcedimentoSEI devido processos apensados
          $objComponenteDigitalDTO->setDblIdProcedimento($parObjProtocolo->idProcedimentoSEI);
          $objComponenteDigitalDTO->setDblIdDocumento($objDocumento->idDocumentoSEI);
          $objComponenteDigitalDTO->setNumOrdemDocumento($objDocumento->ordem);
          $objComponenteDigitalDTO->setNumOrdem(1);
          $objComponenteDigitalDTO->setNumIdTramite($parNumIdentificacaoTramite);
          $objComponenteDigitalDTO->setStrProtocolo($parObjProtocolo->protocolo);

        if(isset($objDocumento->ordemDoDocumentoReferenciado)){
          $objComponenteDigitalDTO->setNumOrdemDocumentoReferenciado(intval($objDocumento->ordemDoDocumentoReferenciado));
        }

        if(isset($objDocumento->idProcedimentoAnexadoSEI)){
            $objComponenteDigitalDTO->setDblIdProcedimentoAnexado($objDocumento->idProcedimentoAnexadoSEI);
            $objComponenteDigitalDTO->setStrProtocoloProcedimentoAnexado($objDocumento->protocoloDoProcessoAnexado);
            $objComponenteDigitalDTO->setNumOrdemDocumentoAnexado($objDocumento->ordemAjustada);
        }

          //Por enquanto, considera que o documento possui apenas um componente digital
        if(is_array($objDocumento->componenteDigital) && count($objDocumento->componenteDigital) != 1) {
            throw new InfraException("Erro processando componentes digitais do processo " . $parObjProtocolo->protocolo . "\n Somente é permitido o recebimento de documentos com apenas um Componente Digital.");
        }

          $objComponenteDigital = is_array($objDocumento->componenteDigital) ? $objDocumento->componenteDigital[0] : $objDocumento->componenteDigital;
          $objComponenteDigitalDTO->setStrNome(utf8_decode($objComponenteDigital->nome));

        if(isset($objDocumento->especie)){
            $objComponenteDigitalDTO->setNumCodigoEspecie(intval($objDocumento->especie->codigo));
            $objComponenteDigitalDTO->setStrNomeEspecieProdutor(utf8_decode($objDocumento->especie->nomeNoProdutor));
        }

          $strHashConteudo = static::getHashFromMetaDados($objComponenteDigital->hash);
          $objComponenteDigitalDTO->setStrHashConteudo($strHashConteudo);
          $objComponenteDigitalDTO->setStrAlgoritmoHash(self::ALGORITMO_HASH_DOCUMENTO);
          $objComponenteDigitalDTO->setStrTipoConteudo($objComponenteDigital->tipoDeConteudo);
          $objComponenteDigitalDTO->setStrMimeType($objComponenteDigital->mimeType);
          $objComponenteDigitalDTO->setStrDadosComplementares($objComponenteDigital->dadosComplementaresDoTipoDeArquivo);

          //Registrar componente digital necessita ser enviado pelo trâmite específico      //TODO: Teste $parObjComponentesDigitaisSolicitados aqui
        if(isset($parObjComponentesDigitaisSolicitados)){
            $arrObjItensSolicitados = is_array($parObjComponentesDigitaisSolicitados->processo) ? $parObjComponentesDigitaisSolicitados->processo : array($parObjComponentesDigitaisSolicitados->processo);
          foreach ($arrObjItensSolicitados as $objItemSolicitado) {
            if(!is_null($objItemSolicitado)){
              $objItemSolicitado->hash = is_array($objItemSolicitado->hash) ? $objItemSolicitado->hash : array($objItemSolicitado->hash);

              if($objItemSolicitado->protocolo == $objComponenteDigitalDTO->getStrProtocolo() && in_array($strHashConteudo, $objItemSolicitado->hash) && !$objDocumento->retirado) {
                      $objComponenteDigitalDTO->setStrSinEnviar("S");
              }
            }
          }
        }

          //TODO: Avaliar dados do tamanho do documento em bytes salvo na base de dados
          $objComponenteDigitalDTO->setNumTamanho($objComponenteDigital->tamanhoEmBytes);

          $objComponenteDigitalDTO->setNumIdAnexo($objComponenteDigital->idAnexo);
          $arrObjComponenteDigitalDTO[] = $objComponenteDigitalDTO;
      }
        $arrObjComponenteDigitalDTO = array_merge($arrObjComponenteDigitalDTOAux, $arrObjComponenteDigitalDTO);
    }

      //Chamada recursiva sobre os documentos dos processos apensados
    if(isset($parObjProtocolo->processoApensado) && count($parObjProtocolo->processoApensado)) {
      foreach ($parObjProtocolo->processoApensado as $objProcessoApensado) {
          $arrObj = $this->montarDadosComponenteDigital($parStrNumeroRegistro, $parNumIdentificacaoTramite, $objProcessoApensado, $parObjComponentesDigitaisSolicitados);
          $arrObjComponenteDigitalDTO = array_merge($arrObjComponenteDigitalDTO, $arrObj);
      }
    }
      return $arrObjComponenteDigitalDTO;
  }

  public function receberComponenteDigital($parNumIdentificacaoTramite, $parStrHashComponenteDigital, $parStrProtocolo, $parObjParteComponente = null)
    {
    try
      {
        $parametros = new stdClass();
        $parametros->parametrosParaRecebimentoDeComponenteDigital = new stdClass();
        $parametros->parametrosParaRecebimentoDeComponenteDigital->identificacaoDoComponenteDigital = new stdClass();
        $parametros->parametrosParaRecebimentoDeComponenteDigital->identificacaoDoComponenteDigital->IDT = $parNumIdentificacaoTramite;
        $parametros->parametrosParaRecebimentoDeComponenteDigital->identificacaoDoComponenteDigital->protocolo = $parStrProtocolo;
        $parametros->parametrosParaRecebimentoDeComponenteDigital->identificacaoDoComponenteDigital->hashDoComponenteDigital = $parStrHashComponenteDigital;

        //Se for passado o parametro $parObjParteComponente retorna apenas parte especifica do componente digital
      if(!is_null($parObjParteComponente)){
        $parametros->parametrosParaRecebimentoDeComponenteDigital->parte = $parObjParteComponente;
      }

        return $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->receberComponenteDigital($parametros);
        });
    } catch (\SoapFault $fault) {
        $mensagem = $this->tratarFalhaWebService($fault);
        throw new InfraException(InfraString::formatarJavaScript($mensagem), $fault);
    } catch (\Exception $e) {
        throw new InfraException("Error Processing Request", $e);
    }
  }

  public function consultarTramites($parNumIdTramite = null, $parNumeroRegistro = null, $parNumeroUnidadeRemetente = null, $parNumeroUnidadeDestino = null, $parProtocolo = null, $parNumeroRepositorioEstruturas = null)
    {
    try
      {
        $arrObjTramite = array();
        $parametros = new stdClass();
        $parametros->filtroDeConsultaDeTramites = new stdClass();
        $parametros->filtroDeConsultaDeTramites->IDT = $parNumIdTramite;

      if(!is_null($parNumeroRegistro)){
        $parametros->filtroDeConsultaDeTramites->NRE = $parNumeroRegistro;
      }

      if(!is_null($parNumeroUnidadeRemetente) && !is_null($parNumeroRepositorioEstruturas)){
          $parametros->filtroDeConsultaDeTramites->remetente = new stdClass();
          $parametros->filtroDeConsultaDeTramites->remetente->identificacaoDoRepositorioDeEstruturas = $parNumeroRepositorioEstruturas;
          $parametros->filtroDeConsultaDeTramites->remetente->numeroDeIdentificacaoDaEstrutura = $parNumeroUnidadeRemetente;
      }

      if(!is_null($parNumeroUnidadeDestino) && !is_null($parNumeroRepositorioEstruturas)){
          $parametros->filtroDeConsultaDeTramites->destinatario = new stdClass();
          $parametros->filtroDeConsultaDeTramites->destinatario->identificacaoDoRepositorioDeEstruturas = $parNumeroRepositorioEstruturas;
          $parametros->filtroDeConsultaDeTramites->destinatario->numeroDeIdentificacaoDaEstrutura = $parNumeroUnidadeDestino;
      }

      if(!is_null($parProtocolo)){
          $parametros->filtroDeConsultaDeTramites->protocolo = $parProtocolo;
      }

        $objTramitesEncontrados = $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->consultarTramites($parametros);
        });

      if(isset($objTramitesEncontrados->tramitesEncontrados) && isset($objTramitesEncontrados->tramitesEncontrados->tramite)) {
        $arrObjTramite = $objTramitesEncontrados->tramitesEncontrados->tramite;
        if(!is_array($arrObjTramite)) {
            $arrObjTramite = array($objTramitesEncontrados->tramitesEncontrados->tramite);
        }
      }

        return $arrObjTramite;

    } catch (\Exception $e) {
        $mensagem = "Falha na consulta de trâmites de processo";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

  public function consultarTramitesProtocolo($parProtocoloFormatado)
    {
    try
      {
        $arrObjTramite = array();
        $parametros = new stdClass();
        $parametros->filtroDeConsultaDeTramites = new stdClass();
        $parametros->filtroDeConsultaDeTramites->protocolo = $parProtocoloFormatado;

        $objTramitesEncontrados = $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->consultarTramites($parametros);
        });

      if(isset($objTramitesEncontrados->tramitesEncontrados)) {

        $arrObjTramite = $objTramitesEncontrados->tramitesEncontrados->tramite;
        if(!is_array($arrObjTramite)) {
            $arrObjTramite = array($objTramitesEncontrados->tramitesEncontrados->tramite);
        }
      }

        return $arrObjTramite;
    } catch (\Exception $e) {
        $mensagem = "Falha na consulta de trâmites de processo";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

  public function cienciaRecusa($parNumIdTramite)
    {
    try
      {
        $parametros = new stdClass();
        $parametros->IDT = $parNumIdTramite;
        //return $this->getObjPenWs()->cienciaRecusa($parametro);
        return $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->cienciaRecusa($parametros);
        });
    } catch (\Exception $e) {
        $mensagem = "Falha no registro de ciência da recusa de trâmite";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

    /**
    * Retorna o estado atual do procedimento no api-pen
    *
    * @param integer $dblIdProcedimento
    * @param integer $numIdRepositorio
    * @param integer $numIdEstrutura
    * @return integer
    */
  public function consultarEstadoProcedimento($strProtocoloFormatado = '', $numIdRepositorio = null, $numIdEstrutura = null)
    {
      $objBD = new GenericoBD($this->inicializarObjInfraIBanco());

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->setStrProtocoloFormatado($strProtocoloFormatado);
      $objProtocoloDTO->setNumMaxRegistrosRetorno(1);
      $objProtocoloDTO->retDblIdProtocolo();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->retStrStaEstado();

      $objProtocoloDTO = $objBD->consultar($objProtocoloDTO);

    if (empty($objProtocoloDTO)) {
        throw new InfraException(utf8_encode(sprintf('Nenhum procedimento foi encontrado com o id %s', $strProtocoloFormatado)));
    }

    if ($objProtocoloDTO->getStrStaEstado() != ProtocoloRn::$TE_PROCEDIMENTO_BLOQUEADO) {
        throw new InfraException(utf8_encode('O processo não esta com o estado com "Em Processamento" ou "Bloqueado"'));
    }

      $objTramiteDTO = new TramiteDTO();
      $objTramiteDTO->setNumIdProcedimento($objProtocoloDTO->retDblIdProtocolo());
      $objTramiteDTO->setOrd('Registro', InfraDTO::$TIPO_ORDENACAO_DESC);
      $objTramiteDTO->setNumMaxRegistrosRetorno(1);
      $objTramiteDTO->retNumIdTramite();

      $objTramiteBD = new TramiteBD($this->getObjInfraIBanco());
      $arrObjTramiteDTO = $objTramiteBD->listar($objTramiteDTO);

    if(!$arrObjTramiteDTO){
        throw new InfraException('Trâmite não encontrado');
    }

      $objTramiteDTO = $arrObjTramiteDTO[0];

      $objFiltro = new stdClass();
      $objFiltro->filtroDeConsultaDeTramites = new stdClass();
      $objFiltro->filtroDeConsultaDeTramites->IDT = $objTramiteDTO->getNumIdTramite();

      $objResultado = $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($objFiltro) {
          return $objPenWs->consultarTramites($objFiltro);
      });

      $objTramitesEncontrados = $objResultado->tramitesEncontrados;

    if (empty($objTramitesEncontrados) || !isset($objTramitesEncontrados->tramite)) {
        throw new InfraException(utf8_encode(sprintf('Nenhum tramite foi encontrado para o procedimento %s', $strProtocoloFormatado)));
    }

    if(!is_array($objTramitesEncontrados->tramite)){
        $objTramitesEncontrados->tramite = array($objTramitesEncontrados->tramite);
    }

      $arrObjTramite = (array) $objTramitesEncontrados->tramite;

      $objTramite = array_pop($arrObjTramite);

    if (empty($numIdRepositorio)) {
        $objPenParametroRN = new PenParametroRN();
        $numIdRepositorio = $objPenParametroRN->getParametro('PEN_ID_REPOSITORIO_ORIGEM');
    }

    if (empty($numIdEstrutura)) {
        $objPenUnidadeDTO = new PenUnidadeDTO();
        $objPenUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objPenUnidadeDTO->retNumIdUnidadeRH();
        $objPenUnidadeDTO = $objBD->consultar($objPenUnidadeDTO);

      if (empty($objPenUnidadeDTO)) {
          throw new InfraException(utf8_encode('Número da Unidade RH não foi encontrado'));
      }

        $numIdEstrutura = $objPenUnidadeDTO->getNumIdUnidadeRH();
    }

    if ($objTramite->remetente->numeroDeIdentificacaoDaEstrutura != $numIdEstrutura ||
              $objTramite->remetente->identificacaoDoRepositorioDeEstruturas != $numIdRepositorio) {
        throw new InfraException(utf8_encode('O último trâmite desse processo não pertence a esse órgão'));
    }

    switch ($objTramite->situacaoAtual) {
      case static::$STA_SITUACAO_TRAMITE_RECIBO_ENVIADO_DESTINATARIO:
          $objReceberReciboTramiteRN = new ReceberReciboTramiteRN();
          $objReceberReciboTramiteRN->receberReciboDeTramite($objTramite->IDT);
          break;

      case static::$STA_SITUACAO_TRAMITE_RECIBO_RECEBIDO_REMETENTE:
          throw new InfraException(utf8_encode('O trâmite externo deste processo já foi concluído'));
        break;

      default:
        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAtividadeDTO->setNumIdTarefa(ProcessoEletronicoRN::$TI_PROCESSO_ELETRONICO_PROCESSO_ABORTADO);
        $objAtividadeDTO->setArrObjAtributoAndamentoDTO(array());

        $objAtividadeRN = new AtividadeRN();
        $objAtividadeRN->gerarInternaRN0727($objAtividadeDTO);

        $objProtocoloDTO->setStrStaEstado(ProtocoloRN::$TE_NORMAL);
        $objBD->alterar($objProtocoloDTO);

        if($objTramite->situacaoAtual == static::$STA_SITUACAO_TRAMITE_COMPONENTES_RECEBIDOS_DESTINATARIO && $objTramite->situacaoAtual == static::$STA_SITUACAO_TRAMITE_METADADOS_RECEBIDO_DESTINATARIO){
            $this->cancelarTramite($objTramite->IDT);
        }

          return PenConsoleRN::format(sprintf('Processo %s foi atualizado com sucesso', $objProtocoloDTO->getStrProtocoloFormatado()), 'blue');
    }
  }

  public function enviarReciboDeTramite($parNumIdTramite, $parDthRecebimento, $parStrReciboTramite)
    {
    try
      {
        $strHashAssinatura = null;
        $objPrivatekey = openssl_pkey_get_private("file://".$this->strLocalCert, $this->strLocalCertPassword);

      if ($objPrivatekey === false) {
        throw new InfraException("Erro ao obter chave privada do certificado digital.");
      }

        openssl_sign($parStrReciboTramite, $strHashAssinatura, $objPrivatekey, 'sha256');
        $strHashDaAssinaturaBase64 = base64_encode($strHashAssinatura);

        $parametros = new stdClass();
        $parametros->dadosDoReciboDeTramite = new stdClass();
        $parametros->dadosDoReciboDeTramite->IDT = $parNumIdTramite;
        $parametros->dadosDoReciboDeTramite->dataDeRecebimento = $parDthRecebimento;
        $parametros->dadosDoReciboDeTramite->hashDaAssinatura = $strHashDaAssinaturaBase64;

        $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->enviarReciboDeTramite($parametros);
        });

        return $strHashDaAssinaturaBase64;

    } catch (\Exception $e) {
        $mensagem = "Falha no envio de recibo de trâmite de processo";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    } finally {
      if(isset($objPrivatekey)){
          openssl_free_key($objPrivatekey);
      }
    }
  }

  public function receberReciboDeTramite($parNumIdTramite)
    {
    try {
        $parametros = new stdClass();
        $parametros->IDT = $parNumIdTramite;
        return $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->receberReciboDeTramite($parametros);
        });

    } catch (\Exception $e) {
        $mensagem = "Falha no recebimento de recibo de trâmite. ". $this->tratarFalhaWebService($e);
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

    /**
    * Retorna um objeto DTO do recibo de envio do processo ao barramento
    *
    * @param int $parNumIdTramite
    * @return ReciboTramiteEnviadoDTO
    */
  public function receberReciboDeEnvio($parNumIdTramite)
    {
    try {
        $parametros = new stdClass();
        $parametros->IDT = $parNumIdTramite;
        $resultado = $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->receberReciboDeEnvio($parametros);
        });

        return $resultado->conteudoDoReciboDeEnvio;
    }
    catch (\Exception $e) {
        $mensagem = "Falha no recebimento de recibo de trâmite de envio. " . $this->tratarFalhaWebService($e);
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

  // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded
  public function converterOperacaoDTO($objOperacaoPEN)
    {
    if(!isset($objOperacaoPEN)) {
        throw new InfraException('Parâmetro $objOperacaoPEN não informado.');
    }

      $objOperacaoDTO = new OperacaoDTO();
      $objOperacaoDTO->setStrCodigo(utf8_decode($objOperacaoPEN->codigo));
      $objOperacaoDTO->setStrComplemento(utf8_decode($objOperacaoPEN->complemento));
      $objOperacaoDTO->setDthOperacao($this->converterDataSEI($objOperacaoPEN->dataHora));

      $strIdPessoa =  ($objOperacaoPEN->pessoa->numeroDeIdentificacao) ?: null;
      $objOperacaoDTO->setStrIdentificacaoPessoaOrigem(utf8_decode($strIdPessoa));

      $strNomePessoa =  ($objOperacaoPEN->pessoa->nome) ?: null;
      $objOperacaoDTO->setStrNomePessoaOrigem(utf8_decode($strNomePessoa));

    switch ($objOperacaoPEN->codigo) {
      case "01":
            $objOperacaoDTO->setStrNome("Registro");
          break;
      case "02":
            $objOperacaoDTO->setStrNome("Envio de documento avulso/processo");
          break;
      case "03":
            $objOperacaoDTO->setStrNome("Cancelamento/exclusão ou envio de documento");
          break;
      case "04":
            $objOperacaoDTO->setStrNome("Recebimento de documento");
          break;
      case "05":
            $objOperacaoDTO->setStrNome("Autuação");
          break;
      case "06":
            $objOperacaoDTO->setStrNome("Juntada por anexação");
          break;
      case "07":
            $objOperacaoDTO->setStrNome("Juntada por apensação");
          break;
      case "08":
            $objOperacaoDTO->setStrNome("Desapensação");
          break;
      case "09":
            $objOperacaoDTO->setStrNome("Arquivamento");
          break;
      case "10":
            $objOperacaoDTO->setStrNome("Arquivamento no Arquivo Nacional");
          break;
      case "11":
            $objOperacaoDTO->setStrNome("Eliminação");
          break;
      case "12":
            $objOperacaoDTO->setStrNome("Sinistro");
          break;
      case "13":
            $objOperacaoDTO->setStrNome("Reconstituição de processo");
          break;
      case "14":
            $objOperacaoDTO->setStrNome("Desarquivamento");
          break;
      case "15":
            $objOperacaoDTO->setStrNome("Desmembramento");
          break;
      case "16":
            $objOperacaoDTO->setStrNome("Desentranhamento");
          break;
      case "17":
            $objOperacaoDTO->setStrNome("Encerramento/abertura de volume no processo");
          break;
      case "18":
            $objOperacaoDTO->setStrNome("Registro de extravio");
          break;
      default:
            $objOperacaoDTO->setStrNome("Registro");
          break;
    }

      return $objOperacaoDTO;
  }

  public function obterCodigoOperacaoPENMapeado($numIdTarefa)
    {
      $strCodigoOperacao = self::$OP_OPERACAO_REGISTRO;
    if(isset($numIdTarefa) && $numIdTarefa != 0) {
        $objRelTarefaOperacaoDTO = new RelTarefaOperacaoDTO();
        $objRelTarefaOperacaoDTO->retStrCodigoOperacao();
        $objRelTarefaOperacaoDTO->setNumIdTarefa($numIdTarefa);

        $objRelTarefaOperacaoBD = new RelTarefaOperacaoBD(BancoSEI::getInstance());
        $objRelTarefaOperacaoDTO = $objRelTarefaOperacaoBD->consultar($objRelTarefaOperacaoDTO);

      if($objRelTarefaOperacaoDTO != null) {
        $strCodigoOperacao = $objRelTarefaOperacaoDTO->getStrCodigoOperacao();
      }
    }

      return $strCodigoOperacao;
  }

  public function obterIdTarefaSEIMapeado($strCodigoOperacao)
    {
      return self::$TI_PROCESSO_ELETRONICO_PROCESSO_TRAMITE_EXTERNO;
  }


    /**
    * Cancela um tramite externo de um procedimento para outra unidade, gera
    * falha caso a unidade de destino já tenha começado a receber o procedimento.
    *
    * @param type $idTramite
    * @param type $idProtocolo
    * @throws Exception|InfraException
    * @return null
    */
  public function cancelarTramite($idTramite)
    {
      $parametros = new stdClass();
      $parametros->IDT = $idTramite;

    try{
        $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->cancelarEnvioDeTramite($parametros);
        });
    }
    catch(\Exception $e) {
        $mensagem = "Falha no cancelamento de trâmite de processo";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

    /**
    * Método que faz a recusa de um trâmite
    *
    * @param integer $idTramite
    * @param string $justificativa
    * @param integer $motivo
    * @return mixed
    * @throws InfraException
    */
  public function recusarTramite($idTramite, $justificativa, $motivo)
    {
    try {
        $objProcessoEletronicoRN = new ProcessoEletronicoRN();
        $parametros = new stdClass();
        $parametros->recusaDeTramite = new stdClass();
        $parametros->recusaDeTramite->IDT = $idTramite;
        $parametros->recusaDeTramite->justificativa = utf8_encode($objProcessoEletronicoRN->reduzirCampoTexto($justificativa, 1000));
        $parametros->recusaDeTramite->motivo = $motivo;

        $resultado = $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->recusarTramite($parametros);
        });

    } catch (Exception $e) {
        $mensagem = "Falha na recusa de trâmite de processo";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

  public function cadastrarTramitePendente($numIdentificacaoTramite, $idAtividadeExpedicao)
    {
    try {
        $tramitePendenteDTO = new TramitePendenteDTO();
        $tramitePendenteDTO->setNumIdTramite($numIdentificacaoTramite);
        $tramitePendenteDTO->setNumIdAtividade($idAtividadeExpedicao);
        $tramitePendenteBD = new TramitePendenteBD($this->getObjInfraIBanco());
        $tramitePendenteBD->cadastrar($tramitePendenteDTO);
    } catch (\Exception $e) {
        $mensagem = "Falha no cadastramento de trâmite pendente";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

  public function isDisponivelCancelarTramite($strProtocolo = '')
    {
      //Obtem o id_rh que representa a unidade no barramento
      $objPenParametroRN = new PenParametroRN();
      $numIdRespositorio = $objPenParametroRN->getParametro('PEN_ID_REPOSITORIO_ORIGEM');

      //Obtem os dados da unidade
      $objPenUnidadeDTO = new PenUnidadeDTO();
      $objPenUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objPenUnidadeDTO->retNumIdUnidadeRH();

      $objGenericoBD = new GenericoBD($this->inicializarObjInfraIBanco());
      $objPenUnidadeDTO = $objGenericoBD->consultar($objPenUnidadeDTO);

      //Obtem os dados do último trâmite desse processo no barramento
      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->setStrProtocoloFormatado($strProtocolo);
      $objProtocoloDTO->retDblIdProtocolo();

      $objProtocoloRN = new ProtocoloRN();
      $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

      $objTramiteDTO = new TramiteDTO();
      $objTramiteDTO->setNumIdProcedimento($objProtocoloDTO->retDblIdProtocolo());
      $objTramiteDTO->setOrd('Registro', InfraDTO::$TIPO_ORDENACAO_DESC);
      $objTramiteDTO->setNumMaxRegistrosRetorno(1);
      $objTramiteDTO->retNumIdTramite();

      $objTramiteBD = new TramiteBD($this->getObjInfraIBanco());
      $arrObjTramiteDTO = $objTramiteBD->listar($objTramiteDTO);

    if(!$arrObjTramiteDTO){
        return false;
    }

      $objTramiteDTO = $arrObjTramiteDTO[0];

    try {
        $parametros = new stdClass();
        $parametros->filtroDeConsultaDeTramites = new stdClass();
        $parametros->filtroDeConsultaDeTramites->IDT = $objTramiteDTO->getNumIdTramite();
        $parametros->filtroDeConsultaDeTramites->remetente = new stdClass();
        $parametros->filtroDeConsultaDeTramites->remetente->identificacaoDoRepositorioDeEstruturas = $numIdRespositorio;
        $parametros->filtroDeConsultaDeTramites->remetente->numeroDeIdentificacaoDaEstrutura = $objPenUnidadeDTO->getNumIdUnidadeRH();

        $objMeta = $this->tentarNovamenteSobErroHTTP(function($objPenWs) use ($parametros) {
            return $objPenWs->consultarTramites($parametros);
        });

      if($objMeta->tramitesEncontrados) {
        $arrObjMetaTramite = !is_array($objMeta->tramitesEncontrados->tramite) ? array($objMeta->tramitesEncontrados->tramite) : $objMeta->tramitesEncontrados->tramite;
        $objMetaTramite = $arrObjMetaTramite[0];

        $strSituacoesDisponíveisCancelamento = array(
          static::$STA_SITUACAO_TRAMITE_INICIADO, static::$STA_SITUACAO_TRAMITE_COMPONENTES_ENVIADOS_REMETENTE,
          static::$STA_SITUACAO_TRAMITE_METADADOS_RECEBIDO_DESTINATARIO, static::$STA_SITUACAO_TRAMITE_COMPONENTES_RECEBIDOS_DESTINATARIO
        );

        if(in_array($objMetaTramite->situacaoAtual, $strSituacoesDisponíveisCancelamento)){
            return true;
        }
      }

        return false;
    }
    catch(SoapFault $e) {
        return false;
    }
    catch(Exception $e) {
        return false;
    }
  }

  public function consultarHipotesesLegais() {
    try{
        $hipoteses = $this->tentarNovamenteSobErroHTTP(function($objPenWs) {
            return $objPenWs->consultarHipotesesLegais();
        });

      if (empty($hipoteses)) {
        return [];
      }
        return $hipoteses;

    } catch(Exception $e){
        $mensagem = "Falha na obtenção de hipóteses legais";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

  protected function contarConectado(ProcessoEletronicoDTO $objProcessoEletronicoDTO)
    {
    try {
        $objProcessoEletronicoBD = new ProcessoEletronicoBD($this->getObjInfraIBanco());
        return $objProcessoEletronicoBD->contar($objProcessoEletronicoDTO);
    }catch(Exception $e){
        $mensagem = "Falha na contagem de processos eletrônicos registrados";
        $detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
        throw new InfraException($mensagem, $e, $detalhes);
    }
  }

  private function tentarNovamenteSobErroHTTP($callback, $numTentativa = 1)
    {
    try {
        return $callback($this->getObjPenWs());
    } catch (\SoapFault $fault) {
      if(in_array($fault->faultcode, array("HTTP", "WSDL")) && $this->numTentativasErro >= $numTentativa){
          sleep(self::WS_ESPERA_RECONEXAO);
          return $this->tentarNovamenteSobErroHTTP($callback, ++$numTentativa);
      } else {
          throw $fault;
      }
    }
  }

  public static function desbloquearProcesso($parDblIdProcedimento)
    {
    try{
        $objEntradaDesbloquearProcessoAPI = new EntradaDesbloquearProcessoAPI();
        $objEntradaDesbloquearProcessoAPI->setIdProcedimento($parDblIdProcedimento);

        $objSeiRN = new SeiRN();
        $objSeiRN->desbloquearProcesso($objEntradaDesbloquearProcessoAPI);
    } catch (InfraException $e) {
        throw new ProcessoNaoPodeSerDesbloqueadoException("Erro ao desbloquear processo", 1, $e);
    }
  }

  public static function comparacaoOrdemAjustadaDocumentos($parDocumento1, $parDocumento2)
    {
      $numOrdemDocumento1 = isset($parDocumento1->ordemAjustada) ? intval($parDocumento1->ordemAjustada) : intval($parDocumento1->ordem);
      $numOrdemDocumento2 = isset($parDocumento2->ordemAjustada) ? intval($parDocumento2->ordemAjustada) : intval($parDocumento2->ordem);
      return $numOrdemDocumento1 - $numOrdemDocumento2;
  }

  public static function comparacaoOrdemDocumentos($parDocumento1, $parDocumento2)
    {
      $numOrdemDocumento1 = intval($parDocumento1->ordem);
      $numOrdemDocumento2 = intval($parDocumento2->ordem);
      return $numOrdemDocumento1 - $numOrdemDocumento2;
  }

  public static function comparacaoOrdemComponenteDigitais($parComponenteDigital1, $parComponenteDigital2)
    {
      $numOrdemComponenteDigital1 = intval($parComponenteDigital1->ordem);
      $numOrdemComponenteDigital2 = intval($parComponenteDigital2->ordem);
      return $numOrdemComponenteDigital1 - $numOrdemComponenteDigital2;
  }

  public static function obterDocumentosProtocolo($parObjProtocolo, $parBolExtrairAnexados = false)
    {
      $arrObjDocumento = array();
    if(isset($parObjProtocolo->documento)){
        $arrObjProtocolo = is_array($parObjProtocolo->documento) ? $parObjProtocolo->documento : array($parObjProtocolo->documento);
        usort($arrObjProtocolo, array("ProcessoEletronicoRN", "comparacaoOrdemAjustadaDocumentos"));

        //Tratamento recursivo para processos anexados
      foreach ($arrObjProtocolo as $objProtocolo) {
        $bolEhProcessoAnexado = $objProtocolo->staTipoProtocolo == ProcessoEletronicoRN::$STA_TIPO_PROTOCOLO_PROCESSO;
        if($parBolExtrairAnexados && $bolEhProcessoAnexado){
            $arrProtocolosAnexados = ProcessoEletronicoRN::obterDocumentosProtocolo($objProtocolo, $parBolExtrairAnexados);
            $arrObjDocumento = array_merge($arrObjDocumento, $arrProtocolosAnexados);
        } else {
          if(!$bolEhProcessoAnexado){
                $objProtocolo->idProcedimentoSEI = $parObjProtocolo->idProcedimentoSEI;
          }

            $objProtocolo->idProtocoloSEI = ($bolEhProcessoAnexado) ? $objProtocolo->idProcedimentoSEI : $objProtocolo->idDocumentoSEI;
            $arrObjDocumento[] = $objProtocolo;
        }
      }
    } else {
        //Quando se tratar de um Documento Avulso, a ordem será sempre 1
        $parObjProtocolo->ordem = 1;
        $parObjProtocolo->ordemAjustada = 1;
        $parObjProtocolo->componenteDigital = self::obterComponentesDocumentos($parObjProtocolo);
        return array($parObjProtocolo);
    }

    if($parBolExtrairAnexados){
        usort($arrObjDocumento, array("ProcessoEletronicoRN", "comparacaoOrdemDocumentos"));
    }

      $arrObjDocumentoPadronizados = ($parBolExtrairAnexados) ? $arrObjDocumento : $arrObjProtocolo;

    foreach ($arrObjDocumentoPadronizados as $objDocumento) {
        $objDocumento->componenteDigital = self::obterComponentesDocumentos($objDocumento);
    }

      return $arrObjDocumentoPadronizados;
  }


  public static function obterComponentesDocumentos($parObjDocumento)
    {
      $arrObjComponenteDigital=array();
    if(isset($parObjDocumento->componenteDigital)){

        $arrObjComponenteDigital = is_array($parObjDocumento->componenteDigital) ? $parObjDocumento->componenteDigital : array($parObjDocumento->componenteDigital);
        usort($arrObjComponenteDigital, array("ProcessoEletronicoRN", "comparacaoOrdemComponenteDigitais"));
    }


      return $arrObjComponenteDigital;

  }

    /**
     * Retorna a referência para o processo ou documento avulso
     *
     * @param stdclass $parobjMetadadosProcedimento
     * @return Mixed Protocolo representado um processo ou um documento avulso
     */
  public static function obterProtocoloDosMetadados($parobjMetadadosProcedimento)
    {
      $objProcesso = $parobjMetadadosProcedimento->metadados->processo;
      $objDocumento = $parobjMetadadosProcedimento->metadados->documento;
      $objProtocolo = isset($objProcesso) ? $objProcesso : $objDocumento;

      //Caso seja processo receberá em staTipoProtocolo P e caso seja documento avulso receberá D
      $objProtocolo->staTipoProtocolo = isset($objProcesso) ? ProcessoEletronicoRN::$STA_TIPO_PROTOCOLO_PROCESSO : ProcessoEletronicoRN::$STA_TIPO_PROTOCOLO_DOCUMENTO_AVULSO;

      return $objProtocolo;
  }

    /**
    * Busca a unidade ao qual o processo foi anteriormente expedido.
    * Caso seja o primeiro trâmite, considera a unidade atual
    *
    * @return integer Id da unidade
    */
  public static function obterUnidadeParaRegistroDocumento($parDblIdProcedimento)
    {
      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setStrIdTarefaModuloTarefa(ProcessoEletronicoRN::$TI_PROCESSO_ELETRONICO_PROCESSO_EXPEDIDO);
      $objAtividadeDTO->setDblIdProcedimentoProtocolo($parDblIdProcedimento);
      $objAtividadeDTO->setOrd('Conclusao', InfraDTO::$TIPO_ORDENACAO_DESC);
      $objAtividadeDTO->setNumMaxRegistrosRetorno(1);
      $objAtividadeDTO->retNumIdUnidade();

      $objAtividadeRN = new AtividadeRN();
      $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);
      $numIdUnidade = SessaoSEI::getInstance()->getNumIdUnidadeAtual();

    if(!empty($arrObjAtividadeDTO)){
        $objAtividadeDTO = $arrObjAtividadeDTO[0];
        $numIdUnidade = $objAtividadeDTO->getNumIdUnidade();
    }

      return $numIdUnidade;
  }

    /**
    * Método responsável por obter os componentes digitais do documento
    * @param $parObjDocumento
    * @return array
    */
  public static function obterComponentesDigitaisDocumento($parObjDocumento)
    {
      $arrObjComponenteDigital = array();
    if(isset($parObjDocumento->componenteDigital)){
        $arrObjComponenteDigital = is_array($parObjDocumento->componenteDigital) ? $parObjDocumento->componenteDigital : array($parObjDocumento->componenteDigital);
    }

      return $arrObjComponenteDigital;
  }


    /**
    * Método responsável pelo desmembramento de processos anexados
    *
    * Método responsável por desmembrar os metadados do processo recebido caso ele possua outros processos anexados
    * O desmembramento é necessário para que o processo possa ser recriado na mesma estrutura original, ou seja, em vários
    * processos diferentes, um anexado ao outro
    *
    * @param object $parObjProtocolo
    *
    * @return $objProtocolo
    */
  public static function desmembrarProcessosAnexados($parObjProtocolo)
    {
    if(!ProcessoEletronicoRN::existeProcessoAnexado($parObjProtocolo)){
        return $parObjProtocolo;
    }

      $arrObjRefProcessosAnexados = array();
      $objProcessoPrincipal = clone $parObjProtocolo;
      $objProcessoPrincipal->documento = array();
      $arrObjDocumentosOrdenados = ProcessoEletronicoRN::obterDocumentosProtocolo($parObjProtocolo, true);
      usort($arrObjDocumentosOrdenados, array("ProcessoEletronicoRN", "comparacaoOrdemDocumentos"));

      // Agrupamento dos documentos por processo
    foreach ($arrObjDocumentosOrdenados as $objDocumento) {
        $bolDocumentoAnexado = ProcessoEletronicoRN::documentoFoiAnexado($parObjProtocolo, $objDocumento);
        $strProtocoloProcAnexado = ($bolDocumentoAnexado) ? $objDocumento->protocoloDoProcessoAnexado : $objProcessoPrincipal->protocolo;

        // Cria uma nova presentação para o processo anexado identico ao processo principal
        // As informações do processo anexado não são consideradas pois não existem metadados no modelo do PEN,
        // existe apenas o número do protocolo de referência
      if($bolDocumentoAnexado && !array_key_exists($strProtocoloProcAnexado, $arrObjRefProcessosAnexados)){
          $objProcessoAnexado = clone $objProcessoPrincipal;
          $objProcessoAnexado->documento = array();
          $objProcessoAnexado->protocolo = $strProtocoloProcAnexado;
          $objProcessoAnexado->ordemAjustada = count($objProcessoPrincipal->documento) + 1;
          $objProcessoPrincipal->documento[] = $objProcessoAnexado;
          $arrObjRefProcessosAnexados[$strProtocoloProcAnexado] = $objProcessoAnexado;
      }

        $objProcessoDoDocumento = ($bolDocumentoAnexado) ? $arrObjRefProcessosAnexados[$strProtocoloProcAnexado] : $objProcessoPrincipal;
        $objDocumentoReposicionado = clone $objDocumento;
        $objDocumentoReposicionado->ordemAjustada = count($objProcessoDoDocumento->documento) + 1;
        $objProcessoDoDocumento->documento[] = $objDocumentoReposicionado;
    }

      return $objProcessoPrincipal;
  }

    /**
     * Identifica se o protocolo recebido possui outros processos anexados
     *
     * @param stdClass $parObjProtocolo
     * @return bool
     */
  public static function existeProcessoAnexado($parObjProtocolo)
    {
      $arrObjDocumentos = ProcessoEletronicoRN::obterDocumentosProtocolo($parObjProtocolo, true);

      // Verifica se existe algum processo anexado, retornando a referência original do processo caso não exista
      $bolExisteProcessoAnexado = array_reduce($arrObjDocumentos, function($bolExiste, $objDoc) {
          return $bolExiste || ProcessoEletronicoRN::documentoFoiAnexado($parObjProtocolo, $objDoc);
      });

      return $bolExisteProcessoAnexado;
  }


  public static function obterTamanhoBlocoTransferencia(){
    $numTamanhoBlocoMB = ProcessoEletronicoRN::WS_TAMANHO_BLOCO_TRANSFERENCIA;

    try{
        $numTamanhoBlocoMB = ConfiguracaoModPEN::getInstance()->getValor(
            "PEN",
            "TamanhoBlocoArquivoTransferencia",
            false,
            ProcessoEletronicoRN::WS_TAMANHO_BLOCO_TRANSFERENCIA
        );

        // Limita valores possíveis entre 1MB e 200MB
        $numTamanhoBlocoMB = intval($numTamanhoBlocoMB) ?: ProcessoEletronicoRN::WS_TAMANHO_BLOCO_TRANSFERENCIA;
        $numTamanhoBlocoMB = max(min($numTamanhoBlocoMB, 200), 1);
    } catch(Exception $e){
        $strMensagem = "Erro na recuperação do tamanho do bloco de arquivos para transferência para o Tramita.gov.br. Parâmetro [TamanhoBlocoArquivoTransferencia]. Detalhes: " . $e->getMessage();
        LogSEI::getInstance()->gravar($strMensagem, InfraLog::$ERRO);
    }
    finally{
      if (empty($numTamanhoBlocoMB)){
        $numTamanhoBlocoMB = ProcessoEletronicoRN::WS_TAMANHO_BLOCO_TRANSFERENCIA;
      }
    }

    return $numTamanhoBlocoMB;
  }


    /**
     * Identifica se um determinado documento recebido pelo PEN originou-se de uma anexação de processos
     *
     * @return bool
     */
  private static function documentoFoiAnexado($parObjProtocolo, $parObjDocumento)
    {
      return (
          isset($parObjDocumento->protocoloDoProcessoAnexado) &&
          !empty($parObjDocumento->protocoloDoProcessoAnexado) &&
          $parObjProtocolo->protocolo != $parObjDocumento->protocoloDoProcessoAnexado
      );
  }

    /**
     * Testa a disponibilidade do Barramento de Serviços do PEN
     *
     * @return bool
     */
  public function validarDisponibilidade()
    {
    try {
        $objVerificadorInstalacaoRN = new VerificadorInstalacaoRN();
        $objVerificadorInstalacaoRN->verificarConexaoBarramentoPEN();
    } catch (\Exception $e) {
        throw new InfraException("Falha de comunicação com o Tramita GOV.BR. Por favor, tente novamente mais tarde.", $e);
    }
  }

    /**
     * Recupera os dados do último trâmite de recebimento válido realizado para determinado número de processo eletrônico
     *
     * @param ProcessoEletronicoDTO $parObjProcessoEletronicoDTO
     * @return void
     */
  protected function consultarUltimoTramiteRecebidoConectado(ProcessoEletronicoDTO $parObjProcessoEletronicoDTO)
    {
      $objTramiteBD = new TramiteBD($this->getObjInfraIBanco());
      return $objTramiteBD->consultarUltimoTramite($parObjProcessoEletronicoDTO, ProcessoEletronicoRN::$STA_TIPO_TRAMITE_RECEBIMENTO);
  }

    /**
     * Recupera os dados do último trâmite válido realizado para determinado número de processo eletrônico
     *
     * @param ProcessoEletronicoDTO $parObjProcessoEletronicoDTO
     * @return void
     */
  protected function consultarUltimoTramiteConectado(ProcessoEletronicoDTO $parObjProcessoEletronicoDTO)
    {
      $objTramiteBD = new TramiteBD($this->getObjInfraIBanco());
      return $objTramiteBD->consultarUltimoTramite($parObjProcessoEletronicoDTO);
  }


    /**
     * Lista componentes digitais de determinado trâmite
     *
     * @param TramiteDTO $parObjTramiteDTO
     * @return void
     */
  protected function listarComponentesDigitaisConectado(TramiteDTO $parObjTramiteDTO, $dblIdDocumento = null)
    {
      $objComponenteDigitalBD = new ComponenteDigitalBD($this->getObjInfraIBanco());
      return $objComponenteDigitalBD->listarComponentesDigitaisPeloTramite($parObjTramiteDTO->getNumIdTramite(), $dblIdDocumento);
  }

    /**
     * Verifica a existência de algum documento contendo outro referenciado no próprio processo
     *
     * @param TramiteDTO $parObjTramiteDTO
     * @return void
     */
  protected function possuiComponentesComDocumentoReferenciadoConectado(TramiteDTO $parObjTramiteDTO)
    {
      $objComponenteDigitalBD = new ComponenteDigitalBD($this->getObjInfraIBanco());
      return $objComponenteDigitalBD->possuiComponentesComDocumentoReferenciado($parObjTramiteDTO);
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

  public static function descompactarComponenteDigital($strCaminhoAnexoCompactado, $numOrdemComponenteDigital){

    if(!is_readable($strCaminhoAnexoCompactado)) {
        throw new InfraException("Anexo de documento não pode ser localizado");
    }

      $objAnexoRN = new AnexoRN();
      $strNomeArquivoTemporario = DIR_SEI_TEMP . '/' . $objAnexoRN->gerarNomeArquivoTemporario();

      $arrStrNomeArquivos = array();
      $zipArchive = new ZipArchive();
    if($zipArchive->open($strCaminhoAnexoCompactado)){
      try {
        for($i = 0; $i < $zipArchive->numFiles; $i++){
          $arrStrNomeArquivos[] = $zipArchive->getNameIndex($i);
        }

          $strNomeComponenteDigital = $arrStrNomeArquivos[$numOrdemComponenteDigital - 1];
          $strPathArquivoNoZip = "zip://".$strCaminhoAnexoCompactado."#".$strNomeComponenteDigital;
          copy($strPathArquivoNoZip, $strNomeArquivoTemporario);
      } finally {
          $zipArchive->close();
      }
    } else {
        throw new InfraException("Falha na leitura dos componentes digitais compactados em $strCaminhoAnexoCompactado");
    }

      return [$strNomeArquivoTemporario, $strNomeComponenteDigital];
  }


    /**
     * Recupera a lista de todos os documentos do processo, principal ou anexados, mantendo a ordem correta entre eles e indicando qual
     * sua atual associação com o processo
     *
     * @param Num $idProcedimento
     * @param Num parDblIdDocumento Filtro de dados de associação de um documento específico
     * @return array Lista de Ids dos documentos do processo em ordem
     */
  public function listarAssociacoesDocumentos($idProcedimento)
    {
    if(!isset($idProcedimento)){
        throw new InfraException('Parâmetro $idProcedimento não informado.');
    }

      //Recupera toda a lista de documentos vinculados ao processo, considerando a ordenação definida pelo usuário
      $arrTipoAssociacao = array(
          RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO, RelProtocoloProtocoloRN::$TA_DOCUMENTO_MOVIDO, RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO
      );

      $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
      $objRelProtocoloProtocoloDTO->retDblIdRelProtocoloProtocolo();
      $objRelProtocoloProtocoloDTO->retDblIdProtocolo1();
      $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
      $objRelProtocoloProtocoloDTO->retStrStaAssociacao();
      $objRelProtocoloProtocoloDTO->setStrStaAssociacao($arrTipoAssociacao, InfraDTO::$OPER_IN);
      $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($idProcedimento);
      $objRelProtocoloProtocoloDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

      $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
      $arrObjRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO);

      $arrIdDocumentos = array();
      foreach($arrObjRelProtocoloProtocoloDTO as $objRelProtocoloProtocoloDTO) {
        if (in_array($objRelProtocoloProtocoloDTO->getStrStaAssociacao(), [RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO, RelProtocoloProtocoloRN::$TA_DOCUMENTO_MOVIDO])) {
            // Adiciona documentos em ordem presentes diretamente ao processo
            $arrIdDocumentos[] = array("IdProtocolo" => $objRelProtocoloProtocoloDTO->getDblIdProtocolo2(), "StaAssociacao" => $objRelProtocoloProtocoloDTO->getStrStaAssociacao());
        } elseif($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO) {
            // Adiciona documentos presente no processo anexado, mantendo a ordem de todo o conjunto
            $numIdProtocoloAnexado = $objRelProtocoloProtocoloDTO->getDblIdProtocolo2();
            $arrIdDocumentosAnexados = $this->listarAssociacoesDocumentos($numIdProtocoloAnexado);
            $arrIdDocumentos = array_merge($arrIdDocumentos, $arrIdDocumentosAnexados);
        }
      }
      return $arrIdDocumentos;
    }

	/**
  	 * Converter arrays associativo para objetos
   	*/
	public static function converterArrayParaObjeto($array) 
	{
		if (is_array($array)) {
			$object = new stdClass();
			foreach ($array as $key => $value) {
				$object->$key = self::converterArrayParaObjeto($value);
			}
			return $object;
		}

		return $array;
	}

	/**
	 * Consulta as estruturas de um repositório de estruturas.
	 *
	 * @param int $idRepositorioEstrutura O ID do repositório de estruturas.
	 * @param array $parametros Parâmetros adicionais para a consulta.
	 * @throws InfraException Falha na obtenção de unidades externas.
	 * @return array
	 */
	public function consultarEstruturas($idRepositorioEstrutura, $parametros = [])
	{
		$endpoint = "/interoperabilidade/rest/v3/repositorios-de-estruturas/{$idRepositorioEstrutura}/estruturas-organizacionais";
		try {
			$arrResultado = $this->get($endpoint, $parametros);
			return $arrResultado;
		} catch (Exception $e) {
			$mensagem = "Falha na obtenção de unidades externas";
			$detalhes = InfraString::formatarJavaScript($this->tratarFalhaWebService($e));
			throw new InfraException($mensagem, $e, $detalhes);
	    }
	}

    /**
     * Iniciar requisição HTTP utilizado para comunicação Webservice REST
   	 *
     */
	private function getArrPenWsRest($method, $endpoint, $options = []) 
	{
		try {
			$arrResultado = $this->strClientGuzzle->request($method, $endpoint, $options);
			return json_decode($arrResultado->getBody(), true);
		} catch (RequestException $e) {
			$statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
			return [
			  'error' => true,
			  'status_code' => $statusCode,
			  'message' => $e->getMessage()
			];
	  	}
	}
  
	private function get($endpoint, $params = []) {
	  return $this->getArrPenWsRest('GET', $endpoint, ['query' => $params]);
	}
  
	private function post($endpoint, $data = []) {
	  return $this->getArrPenWsRest('POST', $endpoint, ['json' => $data]);
	}
  
	private function put($endpoint, $data = []) {
	  return $this->getArrPenWsRest('PUT', $endpoint, ['json' => $data]);
	}
  
	private function delete($endpoint, $params = []) {
	  return $this->getArrPenWsRest('DELETE', $endpoint, ['query' => $params]);
	}
}
