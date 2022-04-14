<?php
require_once '/opt/sei/web/SEI.php';

if (! array_key_exists( 'MdGestaoDocumentalIntegracao' , $SEI_MODULOS)){

    exit(1);

}else{
  
    foreach ($SEI_MODULOS as $strModulo => $seiModulo) {
        if($strModulo=='MdGestaoDocumentalIntegracao'){

          $v = $seiModulo->getVersao();
          exit(0);

        }
    }


    exit(1);

}
?>