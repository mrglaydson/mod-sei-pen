<?php

/**
 * Testes de tr�mite de processos em lote
 *
 * Este mesmo documento deve ser recebido e assinalado como cancelado no destinat�rio e
 * a devolu��o do mesmo processo n�o deve ser impactado pela inser��o de outros documentos
 */
class NovoMapamentoOrgaoExterno extends CenarioBaseTestCase
{
    public static $remetente;
    public static $destinatario;
    public static $processoTeste;
    public static $documentoTeste1;
    public static $documentoTeste2;
    public static $protocoloTeste;

    /**
     * Teste inicial de tr�mite de um processo contendo um documento movido
     *
     * @group envio
     *
     * @return void
     */
    public function test_novo_mapeamento_orgao_externo()
    {

        // Configura��o do dados para teste do cen�rio
        self::$remetente = $this->definirContextoTeste(CONTEXTO_ORGAO_A);
        self::$destinatario = $this->definirContextoTeste(CONTEXTO_ORGAO_B);

        $this->acessarSistema(self::$remetente['URL'], self::$remetente['SIGLA_UNIDADE'], self::$remetente['LOGIN'], self::$remetente['SENHA']);
        $this->navegarPara('pen_map_orgaos_externos_cadastrar');

        $this->paginaCadastroOrgaoExterno->novo();
        
        sleep(10);

        $this->assertTrue(true);
    }
}
