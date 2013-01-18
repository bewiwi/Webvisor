<?php
if($hostId !== false && $vmId === false )
{
    displayHypervisorInfo($hostId);
}
elseif($hostId !== false && $vmId !== false)
{
    displayVMInfo($hostId,$vmId);

}
else
{
    echo "Booom";
}
