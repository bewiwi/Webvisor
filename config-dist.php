<?php
$hypervisors=array();

$server = array(
    'driver' => 'esxManager',
    'name' => 'hypervisor1',
    'parameter' => array(
            'host' => 'hypervisor1.mondomain.com',
            'user' => 'root',
            'key' => './keys/id_rsa',
        )
    );
    
$hypervisors[] = $server;

/*$server = array(
    'driver' => 'esxManager',
    'name' => 'BibanetEsx',
    'parameter' => array(
            'host' => 'vm.bibabox.fr',
            'user' => 'root',
            'key' => './keys/id_rsa',
        )
    );
    
$hypervisors[] = $server;*/