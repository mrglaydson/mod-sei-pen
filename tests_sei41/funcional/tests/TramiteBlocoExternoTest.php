<?php

use Tests\Funcional\Sei\Fixtures\{ProtocoloFixture,ProcedimentoFixture,AtividadeFixture,ParticipanteFixture,RelProtocoloAssuntoFixture,AtributoAndamentoFixture,DocumentoFixture,AssinaturaFixture};

/**
 * EnviarProcessoTest
 * @group group
 */
class TramiteBlocoExternoTest extends CenarioBaseTestCase
{
    private $objProtocoloFixture;
    public static $remetente;
    public static $destinatario;
    public static $penOrgaoExternoId;

    function setUp(): void 
    {
        parent::setUp();
        self::$remetente = $this->definirContextoTeste(CONTEXTO_ORGAO_A);
        self::$destinatario = $this->definirContextoTeste(CONTEXTO_ORGAO_B);

        $parametros = [
            'Descricao' => 'teste'
        ];
        $this->objProtocoloFixture = new ProtocoloFixture();
        $this->objProtocoloFixture->carregar($parametros, function($objProtocoloDTO) {

            $objProcedimentoFixture = new ProcedimentoFixture();
            $objProcedimentoDTO = $objProcedimentoFixture->carregar([
                'IdProtocolo' => $objProtocoloDTO->getDblIdProtocolo()
            ]);

            $objAtividadeFixture = new AtividadeFixture();
            $objAtividadeDTO = $objAtividadeFixture->carregar([
                'IdProtocolo' => $objProtocoloDTO->getDblIdProtocolo(),
            ]);

            $objParticipanteFixture = new ParticipanteFixture();
            $objParticipanteFixture->carregar([
                'IdProtocolo' => $objProtocoloDTO->getDblIdProtocolo(),
                'IdContato' => 100000006,
            ]);

            $objProtocoloAssuntoFixture = new RelProtocoloAssuntoFixture();
            $objProtocoloAssuntoFixture->carregar([
                'IdProtocolo' => $objProtocoloDTO->getDblIdProtocolo()
            ]);

            $objAtributoAndamentoFixture = new AtributoAndamentoFixture();
            $objAtributoAndamentoFixture->carregar([
                'IdAtividade' => $objAtividadeDTO->getNumIdAtividade()
            ]);

            $objDocumentoFixture = new DocumentoFixture();
            $objDocumentoDTO = $objDocumentoFixture->carregar([
                'IdProtocolo' => $objProtocoloDTO->getDblIdProtocolo(),
                'IdProcedimento' => $objProcedimentoDTO->getDblIdProcedimento(),
            ]);

            $objAssinaturaFixture = new AssinaturaFixture();
            $objAssinaturaFixture->carregar([
                'IdProtocolo' => $objProtocoloDTO->getDblIdProtocolo(),
                'IdDocumento' => $objDocumentoDTO->getDblIdDocumento(),
            ]);

            $objBlocoDeTramiteFixture = new \BlocoDeTramiteFixture();
            $objBlocoDeTramiteDTO = $objBlocoDeTramiteFixture->carregar();

            $objBlocoDeTramiteProtocoloFixture = new \BlocoDeTramiteProtocoloFixture();
            $objBlocoDeTramiteProtocoloFixtureDTO = $objBlocoDeTramiteProtocoloFixture->carregar([
                'IdProtocolo' => $objProtocoloDTO->getDblIdProtocolo(),
                'IdBloco' => $objBlocoDeTramiteDTO->getNumId()
            ]);

        });

    }

    public function teste_tramite_bloco_externo()
    {
        // Configura��o do dados para teste do cen�rio
        self::$remetente = $this->definirContextoTeste(CONTEXTO_ORGAO_A);
        self::$destinatario = $this->definirContextoTeste(CONTEXTO_ORGAO_B);
        $this->acessarSistema(
            self::$remetente['URL'],
            self::$remetente['SIGLA_UNIDADE'],
            self::$remetente['LOGIN'],
            self::$remetente['SENHA']
        );

        $this->paginaCadastrarProcessoEmBloco->navegarListagemBlocoDeTramite();
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

        $this->sairSistema();
    }
}