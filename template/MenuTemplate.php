<?php

function displayMenu()
{
    global $hypervisors;
    ?>
    <ul class="nav nav-list">
    <?php foreach ($hypervisors as $id=>$server ): ?>
        <li class="nav-header"><a href="?hostid=<?php echo $id ?>"><?php echo $server['name'] ?></a></li>
        <?php  displayMenuVMs($id); ?>
    <?php endforeach;?>
    </ul>
    <?php
}

function displayMenuVMs($hyperId)
{
    global $hypervisors;
    $hyper = $hypervisors[$hyperId];
    $server = getHypervisor($hyper['driver'],$hyper['parameter']);
    $vms = $server->listVM();
   
    foreach ($vms as $vm){
    ?>
        <li><a href="?hostid=<?php echo $hyperId ?>&vmid=<?php echo $vm['id'] ?>"><?php echo $vm['name'] ?></a></li>
    <?php
    }
}