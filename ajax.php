<?php
include 'include.php';
if($driverCommand)
{
    $hyper = $hypervisors[$hostId];
    $server = getHypervisor($hyper['driver'],$hyper['parameter']);
    
    //TODO Control des params
    $param = $_POST;
    
    $ret = $server->$driverCommand($vmId,$param);
    if($ret === false)
    {
        echo 'Command Erreur';
    }else{
        echo 'Command Ok';
    }
}
elseif($actionAjax == "displayAjaxFunction" && $phpFunction)
{
    if($hostId !== false && $vmId !== false )
    {
        $phpFunction($hostId,$vmId);
    }
    elseif($hostId !== false)
    {
        $phpFunction($hostId);
    }
    else
    {
        $phpFunction();
    }
}