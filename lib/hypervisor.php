<?php
function getHypervisor($driver,$parameter)
{
    if( ! class_exists( $driver ) ) {
        throw new Exception('Driver doesn\'t exist :'.$driver);
    }
    return new $driver($parameter);
}