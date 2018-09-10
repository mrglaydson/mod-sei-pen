<?php

/**
 * Script de instala��o/atualiza��o do m�dulo de gest�o documental
 *
 * @author Join Tecnologia
 */
try {

    require_once dirname(__FILE__) . '/../web/SEI.php';

    $objMdGdAtualizarSEI = new MdGdAtualizarSeiRN();
    $objMdGdAtualizarSEI->atualizar();
} catch (Exception $e) {
    echo InfraException::inspecionar($e);
    LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
}
