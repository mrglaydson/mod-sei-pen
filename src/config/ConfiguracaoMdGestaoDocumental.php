<?php

/**
 * Arquivo de configuração do Módulo de Gestão Documental
 *
 * Seu desenvolvimento seguiu os mesmos padrões de configuração implementado pelo SEI e SIP e este
 * arquivo precisa ser adicionado à pasta de configurações do SEI para seu correto carregamento pelo módulo.
 */

class ConfiguracaoMdGestaoDocumental extends InfraConfiguracao
{
    private static $instance = null;

    /**
     * Obtém instância única (singleton) dos dados de configuração do módulo de integração com a Conta gov.br
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
     * Definição dos parâmetros de configuração do módulo
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
