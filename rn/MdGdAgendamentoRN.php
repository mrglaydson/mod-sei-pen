<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdAgendamentoRN extends InfraRN
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    public function verificarTempoGuarda()
    {
        LogSEI::getInstance()->gravar('Verifica��o de periodicidade realizada com sucesso!');
    }
}