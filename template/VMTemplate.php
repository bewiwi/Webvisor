<?php
function displayVMInfo($hostId,$vmId)
{
?>
<div class="row-fluid">
    <div class="span6">
        <?php 
        //displayVMParameter($hostId,$vmId); 
        displayAjaxFunctionTiming('displayVMParameter',2000,array('hostid' => $hostId,'vmid'=>$vmId));
        displayVMAction($hostId,$vmId);
        ?>
    </div>
    <div class="span6">
        <?php
        displayVMSnapshotJsFunction($hostId,$vmId);
        //displayVMSnapshotInfo($hostId,$vmId);
        displayAjaxFunctionTiming('displayVMSnapshotInfo',2000,array('hostid' => $hostId,'vmid'=>$vmId));
        ?>
    </div>
</div>

<?php    
}

function displayVMParameter($hostId,$vmId)
{
    global $hypervisors;
    $hyper = $hypervisors[$hostId];
    $server = getHypervisor($hyper['driver'],$hyper['parameter']);
    $infos = $server->vmParameter($vmId);
    ?>
    <table>
    <caption>Info</caption>

            <?php
            foreach ($infos as $key => $value)
            {
                if($key == 'id') continue;
                ?>
                <tr>
                    <th><?php echo htmlspecialchars($key,ENT_COMPAT) ?></th>
                    <td><?php echo htmlspecialchars($value,ENT_COMPAT) ?></td>
                </tr>
                <?php
            }
            ?>
        </tr>
    </table>
<?php
}

function displayVMSnapshotJsFunction($hostId,$vmId)
{
    global $hypervisors;
    $hyper = $hypervisors[$hostId];
    $server = getHypervisor($hyper['driver'],$hyper['parameter']);
    $actions = $server->vmSnapshotAction($vmId);
    
    foreach($actions as $name => $param)
    {
        ?>
           <script type="text/javascript">
            //<![CDATA[
            function sendSnapCommand<?php echo $name ?>(snapId)
            {
                var param = JSON.parse('<?php echo (isset($param['parameter']))?json_encode($param['parameter']):"{}" ?>');
                sendCommand(<?php echo '\''.$param['function'].'\',param,\''.$hostId.'\',\''.$vmId.'\',snapId' ?>);
            }
            //]]>
           </script>
        <?php
    }
}

function displayVMSnapshotInfo($hostId,$vmId)
{
    global $hypervisors;
    $hyper = $hypervisors[$hostId];
    $server = getHypervisor($hyper['driver'],$hyper['parameter']);
    $snaps = $server->vmListSnapshots($vmId);
    if(! count($snaps))
    {
        ?>
        <table>
        <caption>Snapshot</caption>
        <thead>
            <tr>
                <td>No Snapshot</td>
            </tr>
        </thead>
        </table>
        <?php
        return false;
    }
    
    
    ?>

    
    <table>
    <caption>Snapshot</caption>
    <thead>
            <tr>
            <?php
            if (method_exists($server,'vmSnapshotAction'))
            {
                ?>
                <td>Action</td>
                
                <?php
            }
            
            foreach (array_keys($snaps[key($snaps)]) as $key)
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
            foreach ($snaps as $snap)
            {
                ?>
                <tr>
                <?php
                if (method_exists($server,'vmSnapshotAction'))
                {
                    $actions = $server->vmSnapshotAction($vmId);
                    ?>
                    <td>
                    <?php
                    foreach($actions as $name => $param)
                    {
                        ?>
                        
                        
                        <img onClick="sendSnapCommand<?php echo $name; ?>(<?php echo $snap['id']; ?>)" src="web/img/<?php echo $param['image'] ?>" title="<?php echo $name ?>" alt="<?php echo $name ?>" />
                        
                        <?php
                    }
                    ?>
                    </td>
                    <?php
                }
                
                foreach ($snap as $key => $value)
                {
                    if($key == 'id') continue;
                    ?>
                        <td><?php echo htmlspecialchars($value,ENT_COMPAT) ?></td>
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

function displayVMAction($hostId,$vmId){
    global $hypervisors;
    $hyper = $hypervisors[$hostId];
    $server = getHypervisor($hyper['driver'],$hyper['parameter']);
    $actions = $server->vmListAction($vmId);
    
    ?>
    <table>
    <caption>Action</caption>
    <tr>
    <?php foreach ($actions as $name => $param): ?>
        <td>
            <script type="text/javascript">
            //<![CDATA[
            function sendCommand<?php echo $name ?>()
            {
                var param = JSON.parse('<?php echo (isset($param['parameter']))?json_encode($param['parameter']):"{}" ?>');
                sendCommand(<?php echo '\''.$param['function'].'\',param,\''.$hostId.'\',\''.$vmId.'\'' ?>);
            }
            //]]>
            </script>
            <img onClick="sendCommand<?php echo $name; ?>()" src="web/img/<?php echo $param['image'] ?>" title="<?php echo $name ?>" alt="<?php echo $name ?>" />
        </td>
    <?php endforeach; ?>
    </tr>
    </table>
    <?php
}