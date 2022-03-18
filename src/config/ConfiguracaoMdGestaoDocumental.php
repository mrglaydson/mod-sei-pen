<?php

/**
 * Arquivo de configura��o do M�dulo de Gest�o Documental
 *
 * Seu desenvolvimento seguiu os mesmos padr�es de configura��o implementado pelo SEI e SIP e este
 * arquivo precisa ser adicionado � pasta de configura��es do SEI para seu correto carregamento pelo m�dulo.
 */

class ConfiguracaoMdGestaoDocumental extends InfraConfiguracao
{
    private static $instance = null;

    /**
     * Obt�m inst�ncia �nica (singleton) dos dados de configura��o do m�dulo de integra��o com a Conta gov.br
     *
     * @return ConfiguracaoMdGestaoDocumental
     */
    public static function getInstance()
    {
        if (ConfiguracaoMdGestaoDocumental::$instance == null) {
            ConfiguracaoMdGestaoDocumental::$instance = new ConfiguracaoMdGestaoDocumental();
        }
        return ConfiguracaoMdGestaoDocumental::$instance;
    }

    /**
     * Defini��o dos par�metros de configura��o do m�dulo
     *
     * @return array
     */
    public function getArrConfiguracoes()
    {
        return array(
            'GestaoDocumental' => array(),
        );
    }
}
