<?php

//HostId
$hostId = false;
if(isset($_GET['hostid']) && is_numeric($_GET['hostid']) === false)
{
    throw new Exception('Bad type numeric for hostid');
}
elseif (isset($_GET['hostid']))
{
    $hostId = $_GET['hostid'];
}


//VmId
$vmId = false;
if(isset($_GET['vmid']) && is_numeric($_GET['vmid']) === false)
{
    throw new Exception('Bad type numeric for vmid');
}
elseif (isset($_GET['vmid']))
{
    $vmId = $_GET['vmid'];
}

//snapId
$snapId = false;
if(isset($_GET['snapid']) && is_numeric($_GET['snapid']) === false)
{
    throw new Exception('Bad type numeric for snapid');
}
elseif (isset($_GET['snapid']))
{
    $snapId = $_GET['snapid'];
}

//action Ajax
$actionAjax = false;
if (isset($_GET['actionajax']))
{
    $actionAjax = $_GET['actionajax'];
}

$driverCommand = false;
if(isset($_GET['driverCommand']) && ( $vmId === false || $hostId === false ) )
{
    throw new Exception('driverCommand need host and vm id');
}
elseif (isset($_GET['driverCommand']))
{
    $driverCommand = $_GET['driverCommand'];
}


$phpFunction = false;
if( isset($_GET['phpfunction']) && $actionAjax === false )
{
    throw new Exception('phpfunction need actionAjax');
}
elseif (isset($_GET['phpfunction']))
{
    $phpFunction = $_GET['phpfunction'];
}