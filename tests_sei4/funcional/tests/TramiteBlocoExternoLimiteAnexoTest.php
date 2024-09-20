<?php

use Tests\Funcional\Sei\Fixtures\{ProtocoloFixture,ProcedimentoFixture,AtividadeFixture,ContatoFixture,ParticipanteFixture,RelProtocoloAssuntoFixture,AtributoAndamentoFixture,DocumentoFixture,AssinaturaFixture,AnexoFixture,AnexoProcessoFixture};

class TramiteBlocoExternoLimiteAnexoTest extends CenarioBaseTestCase
{
    protected static $numQtyProcessos = 2; // max: 99
    protected static $tramitar = true; // mude para false, caso queira rodar o script sem o tramite final

    public static $remetente;
    public static $destinatario;
    public static $documentoTeste1;
    public static $documentoTeste2;
    public static $protocoloTestePrincipal;
    public static $processoTestePrincipal;

    function setUp(): void 
    {
        parent::setUp();
        self::$remetente = $this->definirContextoTeste(CONTEXTO_ORGAO_A);
        self::$destinatario = $this->definirContextoTeste(CONTEXTO_ORGAO_B);

    }

    /**
     * Teste inicial de tr�mite de um processo contendo outro anexado
     *
     * @group envio
     * @large
     * 
     * @return void
     */
    public function test_tramitar_processo_anexado_da_origem()
    {
        // Defini��o de dados de teste do processo principal
        self::$documentoTeste1 = $this->gerarDadosDocumentoExternoTeste(self::$remetente, 'arquivo_pequeno_A.pdf');
        self::$documentoTeste2 = $this->gerarDadosDocumentoExternoTeste(self::$remetente, 'arquivo_pequeno_A.pdf');

        $objBlocoDeTramiteFixture = new \BlocoDeTramiteFixture();
        $objBlocoDeTramiteDTO = $objBlocoDeTramiteFixture->carregar();

        for ($i = 0; $i < self::$numQtyProcessos; $i++) {
            $objProtocoloFixture = new ProtocoloFixture();
            $objProtocoloFixtureDTO = $objProtocoloFixture->carregar([
                'Descricao' => 'teste'
            ]);

            $objProcedimentoFixture = new ProcedimentoFixture();
            $objProcedimentoDTO = $objProcedimentoFixture->carregar([
                'IdProtocolo' => $objProtocoloFixtureDTO->getDblIdProtocolo()
            ]);

            $objAtividadeFixture = new AtividadeFixture();
            $objAtividadeDTO = $objAtividadeFixture->carregar([
                'IdProtocolo' => $objProtocoloFixtureDTO->getDblIdProtocolo(),
                'IdTarefa' => TarefaRN::$TI_GERACAO_PROCEDIMENTO,
            ]);

            $objParticipanteFixture = new ParticipanteFixture();
            $objParticipanteFixture->carregar([
                'IdProtocolo' => $objProtocoloFixtureDTO->getDblIdProtocolo(),
                'IdContato' => 100000006,
            ]);

            $objProtocoloAssuntoFixture = new RelProtocoloAssuntoFixture();
            $objProtocoloAssuntoFixture->carregar([
                'IdProtocolo' => $objProtocoloFixtureDTO->getDblIdProtocolo()
            ]);

            $objAtributoAndamentoFixture = new AtributoAndamentoFixture();
            $objAtributoAndamentoFixture->carregar([
                'IdAtividade' => $objAtividadeDTO->getNumIdAtividade()
            ]);

            //Incluir novos documentos relacionados
            $objDocumentoFixture = new DocumentoFixture();
            $objDocumentoDTO = $objDocumentoFixture->carregar([
                'IdProtocolo' => $objProtocoloFixtureDTO->getDblIdProtocolo(),
                'IdProcedimento' => $objProcedimentoDTO->getDblIdProcedimento(),
                'Descricao' => self::$documentoTeste1['DESCRICAO'],
                'StaProtocolo' => ProtocoloRN::$TP_DOCUMENTO_RECEBIDO,
                'StaDocumento' => DocumentoRN::$TD_EXTERNO,
                'IdConjuntoEstilos' => NULL,
            ]);

            //Adicionar anexo ao documento
            $objAnexoFixture = new AnexoFixture();
            $objAnexoFixture->carregar([
                'IdProtocolo' => $objDocumentoDTO->getDblIdDocumento(),
                'Nome' => basename(self::$documentoTeste1['ARQUIVO']),
            ]);

            $objBlocoDeTramiteProtocoloFixture = new \BlocoDeTramiteProtocoloFixture();
            $objBlocoDeTramiteProtocoloFixtureDTO = $objBlocoDeTramiteProtocoloFixture->carregar([
              'IdProtocolo' => $objProtocoloFixtureDTO->getDblIdProtocolo(),
              'IdBloco' => $objBlocoDeTramiteDTO->getNumId()
            ]);

            self::$protocoloTestePrincipal['PROTOCOLO'] = $objProtocoloFixtureDTO->getStrProtocoloFormatado();
        }

        $this->acessarSistema(
            self::$remetente['URL'],
            self::$remetente['SIGLA_UNIDADE'],
            self::$remetente['LOGIN'],
            self::$remetente['SENHA']
        );

        $this->paginaCadastrarProcessoEmBloco->navegarListagemBlocoDeTramite();
        if (self::$tramitar == true) {
            $this->paginaCadastrarProcessoEmBloco->bntTramitarBloco();
            $this->paginaCadastrarProcessoEmBloco->tramitarProcessoExternamente(
                self::$destinatario['REP_ESTRUTURAS'], self::$destinatario['NOME_UNIDADE'],
                self::$destinatario['SIGLA_UNIDADE_HIERARQUIA'], false,
                function ($testCase) {
                  try {
                      $testCase->frame('ifrEnvioProcesso');
                      $mensagemSucesso = utf8_encode('Processo(s) aguardando envio. Favor acompanhar a tramita��o por meio do bloco, na funcionalidade \'Blocos de Tr�mite Externo\'');
                      $testCase->assertStringContainsString($mensagemSucesso, $testCase->byCssSelector('body')->text());
                      $btnFechar = $testCase->byXPath("//input[@id='btnFechar']");
                      $btnFechar->click();
                  } finally {
                      try {
                          $testCase->frame(null);
                          $testCase->frame("ifrVisualizacao");
                      } catch (Exception $e) {
                      }
                  }
      
                  return true;
              }
            );
            sleep(10);
        } else {
            $this->paginaCadastrarProcessoEmBloco->bntVisualizarProcessos();
            $qtyProcessos = $this->paginaCadastrarProcessoEmBloco->retornarQuantidadeDeProcessosNoBloco();
            
            $this->assertEquals($qtyProcessos, self::$numQtyProcessos);
        }

        $this->sairSistema();
    }

    public function test_verificar_envio_processo()
    {      
        $orgaosDiferentes = self::$remetente['URL'] != self::$destinatario['URL'];

        $this->acessarSistema(self::$remetente['URL'], self::$remetente['SIGLA_UNIDADE'], self::$remetente['LOGIN'], self::$remetente['SENHA']);

        $this->paginaCadastrarProcessoEmBloco->navegarListagemBlocoDeTramite();
        $this->paginaCadastrarProcessoEmBloco->bntVisualizarProcessos();

        $this->waitUntil(function ($testCase) use (&$orgaosDiferentes) {
            sleep(5);
            $testCase->refresh();
            $linhasDaTabela = $testCase->elements($testCase->using('xpath')->value('//table[@id="tblBlocos"]/tbody/tr'));

            $totalConcluidos = 0;
            foreach ($linhasDaTabela as $linha) {
                $statusTd = $linha->byXPath('./td[7]');
                if (self::$tramitar == true) {
                    $statusImg = $statusTd->byXPath(utf8_encode("(//img[@title='Conclu�do'])"));
                } else {
                    $statusImg = $statusTd->byXPath(utf8_encode("(//img[@title='Em aberto'])"));
                }
                $totalConcluidos++;
            }
            $this->assertEquals($totalConcluidos, self::$numQtyProcessos);
            return true;
        }, PEN_WAIT_TIMEOUT);
        
        sleep(5);
    }

    public function test_verificar_envio_tramite_em_bloco()
    {
        $this->acessarSistema(
            self::$remetente['URL'],
            self::$remetente['SIGLA_UNIDADE'],
            self::$remetente['LOGIN'],
            self::$remetente['SENHA']
        );
        $this->paginaCadastrarProcessoEmBloco->navegarListagemBlocoDeTramite();
        $novoStatus = $this->paginaCadastrarProcessoEmBloco->retornarTextoColunaDaTabelaDeBlocos();

        if (self::$tramitar == true) {
            $this->assertEquals(utf8_encode("Conclu�do"), $novoStatus);
        } else {
            $this->assertEquals(utf8_encode("Aberto"), $novoStatus);
        }  

        $this->sairSistema();
    }
}