<?php
function displayHypervisorInfo($hostId)
{
    global $hypervisors;
    $hyper = $hypervisors[$hostId];
    $server = getHypervisor($hyper['driver'],$hyper['parameter']);
    ?>
    <table>
        <tr>
            <td>Name</td>
            <td><?php echo $hyper['name'] ?></td>
        </tr>
        <tr>
            <td>Driver</td>
            <td><?php echo $hyper['driver'] ?></td>
        </tr>
    </table>
    <?php
    displayHypervisorVMs($hostId);
    
}

function displayHypervisorVMs($hyperId)
{
    global $hypervisors;
    $hyper = $hypervisors[$hyperId];
    $server = getHypervisor($hyper['driver'],$hyper['parameter']);
    $vms = $server->listVM();
    
    $i=true;
    ?>
    <table>
    <?php
    foreach ($vms as $vm)
    {
        if($i)
        {
            $i=false;
            ?>
            <thead>
            <tr>
            <th></th>
            <?php
            foreach (array_keys($vm) as $key)
            {
                if($key == 'id') continue;
                ?>
                    <th><?php echo $key ?></th>
                <?php
            }
            ?>
            </tr>
            </thead>
            <?php
        }
        ?>
        <tr>
            <td><a href="?hostid=<?php echo $hyperId ?>&vmid=<?php echo $vm['id'] ?>" ><i class="icon-edit"></i></a></td>
            <?php
            foreach ($vm as $key => $value)
            {
                if($key == 'id') continue;
                ?>
                <td><?php echo $value ?></td>
                <?php
            }
            ?>
        </tr>
        <?php
    }
    ?>
    </table>
<?php
}

