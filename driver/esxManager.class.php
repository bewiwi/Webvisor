<?php
/*
 * Pour que la class fonctionne il faut mettre la clé ssh public sur l'hyperviseur
 * dans le fichier /etc/ssh/keys-root/authorized_keys
 */
class esxManager
{

  /**
   * @var string Chaîne de connexion SSH à l'hôte esx
   */
  private $sshConnexion;

  /**
   * @param $sshConnexion
   */
  public function __construct($param)
  {
    if(! file_exists($param['key']) )
    {
        throw new Exception('Key not found in '.$param['key']);
    }
        
    $str = 'ssh ';
    $str .= ' -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no ';
    $str .= " -i ".$param['key']." ";
    $str .= $param['user'];
    $str .= '@';
    $str .= $param['host'];
    
    $this->sshConnexion = $str;
  }



  /**
   * Renvoie la liste des VM
   * @return array
   * Id obligatoire
   */
  public function listVM()
  {
    list($lines) = $this->remoteCmd('vim-cmd vmsvc/getallvms ');
    $vms       = array();
    $firstLine = true;
    foreach ($lines as $line)
    {
      if ($firstLine)
      {
        $firstLine = false;
        continue;
      }
      preg_match("/^([0-9]+)\s+(.*)\s+\[(.*)\]\s+(.*\.vmx)\s+(.*)\s+.*$/",$line,$matches);
      $vms[] = array(
        'id'         => $matches[1],
        'name'       => $matches[2],
        'datastore'  => $matches[3],
        //'file'       => $matches[4],
        'os'         => $matches[5],
      );
    }

    return $vms;
  }
  
  public function vmParameter($vm)
  {
    list($lines) = $this->remoteCmd(sprintf('vim-cmd vmsvc/get.summary %s',$vm));
    $need = array(
        'powerState'    => 'Status',
        'guestFullName' => 'OS',
        'toolsStatus'   => 'Tools',
        'ipAddress'     => 'Ip',
        'name'          => 'Name',
        'template'      => 'isTemplate',
        'hostName'      => 'Hostname',
        'memorySizeMB'  => 'Memory',
        'numCpu'        => 'CPU',
        );
    $info = array();
    
    foreach ($lines as $line)
    {
        $line = trim($line);
        foreach($need as $key => $index)
        {
            if(preg_match("/^$key = (.*)/",$line,$matches))
            {
                $value = trim($matches[1],',"');
                $info[$index] = $value;
            }
        }
           
    }
    return $info;
  }

  /**
   * Renvoie les snapshots disponibles d'une VM
   * @param $vm int ID de la VM
   * @return array
   */
  public function vmListSnapshots($vm)
  {
    $currentSnap = $this->getCurrentSnapshot($vm);
    list($lines) = $this->remoteCmd(sprintf('vim-cmd vmsvc/snapshot.get %s', $vm));
    $snapshot = array();
    $tmpSnap = array();
    foreach ($lines as $line)
    {
      if (strstr($line, '|-ROOT') || strstr($line, '|-CHILD'))
      {
        if (isset($tmpSnap['id']))
        {
          $snapshot[$tmpSnap['id']] = $tmpSnap;
        }
        $tmpSnap = array();
      }
      elseif (strstr($line, '--Snapshot Name'))
      {
        $tmp             = preg_split("/\: /", $line);
        $tmpSnap['name'] = $tmp[1]; //Name
      }
      elseif (strstr($line, '--Snapshot Id'))
      {
        $tmp           = preg_split("/\: /", $line);
        $tmpSnap['id'] = $tmp[1]; // Id Snapshot
        if ($tmpSnap['id'] == $currentSnap)
        {
          $tmpSnap['current'] = true;
        }
        else
        {
          $tmpSnap['current'] = false;
        }
      }
      elseif (strstr($line, '--Snapshot Desciption'))
      {
        $tmp             = preg_split("/\: /", $line);
        $tmpSnap['desc'] = @$tmp[1]; //Description
      }
      elseif (strstr($line, '--Snapshot Created On'))
      {
        $tmp                  = preg_split("/\: /", $line);
        $tmpSnap['createdOn'] = $tmp[1];
      }
      elseif (strstr($line, '--Snapshot State'))
      {
        $tmp              = preg_split("/\: /", $line);
        $tmpSnap['state'] = $tmp[1]; //PowerOff?
      }
    }
    if(isset($tmpSnap['id']))
    {
        $snapshot[$tmpSnap['id']] = $tmpSnap;
    }
    return $snapshot;
  }

    public function vmListAction($vmid)
    {
        $action = array(
            'Start' => array(
                'function'  => 'startVM',
                'image'     => 'start.png',
                ),
            'Stop' => array(
                'function'  => 'stopVM',
                'image'     => 'stop.png',
                ),
            'Snapshot' => array(
                'function'  => 'takeSnapshot',
                'image'     => 'snap.png',
                'parameter' => array(
                            array(
                                'name' => 'name',
                                'label' => 'Name',
                                'type' => 'string',
                                'default' => 'Snapshot',
                            ),
                            array(
                                'name' =>'desc',
                                'label' => 'Desc',
                                'type' => 'string',
                                'default' => '',
                            ),
                            array(
                                'name' => 'save_status',
                                'label' => 'Save Status',
                                'type' => 'bool',
                                'default' => true,
                            ),
                    ),
                ),
                
            ) ;
        return $action;
    }

    public function vmSnapshotAction($vmid)
    {
        $action = array(
            'Remove' => array(
                'function'  => 'removeSnapshot',
                'image'     => 'delete.png',
                'parameter' => array(
                            array(
                                'name' => 'name',
                                'label' => 'Suppress Child',
                                'type' => 'bool',
                                'default' => false,
                            ),
                            ),
                ),
            'Restore' => array(
                'function'  => 'restoreSnapshot',
                'image'     => 'restore.png',
                'parameter' => array(
                            array(
                                'name' => 'name',
                                'label' => 'Suppress PowerOn',
                                'type' => 'bool',
                                'default' => false,
                            ),
                            ),
                ),
            ) ;
        return $action;
    }


  /**
   * Démarre une VM
   * @param $vm int ID de la VM
   * @return void
   */
  public function startVM($vm)
  {
    $this->remoteCmd(sprintf('vim-cmd vmsvc/power.on %s', $vm));
  }

  /**
   * Arrête une VM
   * @param string $vm   int ID de la VM
   * @param bool   $sync Arrêt synchrone ou pas
   * @throws RuntimeException
   * @return void
   */
  public function stopVM($vm, $sync = true)
  {
    if (!$this->isVMRunning($vm))
    {
      return;
    }
    $this->remoteCmd(sprintf('vim-cmd vmsvc/power.off %s', $vm));

    if (!$sync)
    {
      return;
    }

    $timeToStop = 5 * 60; // 5 minutes max pour s'arrêter
    $start      = microtime(true);
    $stopped    = false;
    while (microtime(true) - $start < $timeToStop)
    {
      if ($this->isVMPoweredOff($vm))
      {
        $stopped = true;
        break;
      }
      sleep(3);
    }

    if (!$stopped)
    {
      throw new RuntimeException(sprintf("Impossible d'arrêter la VM : timeout de %d secondes atteint", $timeToStop));
    }
  }

  /**
   * Force l'arret d'une VM
   * @return void
   */
  public function forcePowerOff($vm)
  {
    $this->remoteCmd(sprintf('vim-cmd vmsvc/power.off %s', $vm));
  }

  /**
   * Restaure le snapshot d'une VM
   * @param $vm              int ID de la VM
   * @param $snapshot        ID du snapshot
   * @param $suppressPowerOn bool ID du snapshot
   * @return void
   */
  public function restoreSnapshot($vm, $snapshot, $suppressPowerOn = false)
  {
    if ($suppressPowerOn)
    {
      $supp = '1';
    }
    else
    {
      $supp = '0';
    }
    $this->remoteCmd(sprintf('vim-cmd vmsvc/snapshot.revert %s %s %s', $vm, $snapshot, $supp));
  }

  /**
   * Prend un snapshot d'une VM
   *
   * @param $vm        int ID de la VM
   * @param $name      string Description du snapshot
   * @param $desc      string Description du snapshot
   * @param $saveStatu boolean sauvegarde l'état de la mémoire de la VM
   * @param $quiesced  boolean take a quiesced snap => better snapshot / must have tools
   *
   * @return void
   */
  public function takeSnapshot($vm, $param)
  {
    $saveStatu = (isset($param['save_status']) && $param['save_status'] == 'on' )?true:false;
    $name = $param['name'];
    $desc = $param['desc'];
    
    $quiesced=0;
    if ($saveStatu)
    {
      $stat = '1';
    }
    else
    {
      $stat = '0';
    }
    if ($quiesced)
    {
      $quie = '1';
    }
    else
    {
      $quie = '0';
    }
    $this->remoteCmd(sprintf('vim-cmd vmsvc/snapshot.create %s %s %s %s %s', $vm, escapeshellarg($name), escapeshellarg($desc), $stat, $quie));
  }


  /**
   * Supprime un snapshot
   *
   * @param $vm    int ID de la VM
   * @param $snap  int Id du snapshot
   * @param $child boolean supprime les enfants
   */
  public function removeSnapshot($vm, $snap, $child = false)
  {
    if ($child)
    {
      $children = '1';
    }
    else
    {
      $children = '0';
    }
    $snapId = $this->remoteCmd(sprintf('vim-cmd vmsvc/snapshot.remove %s %s %s', $vm, $snap, $children));

    return $snapId[0];
  }

  /**
   * Permet de savoir si une VM est présente sur cet hôte
   * @param int $vmSearch ID de la VM
   * @return boolean
   */
  public function hasVM($vmSearch)
  {
    foreach ($this->listVM() as $vmInfos)
    {
      if ($vmInfos['id'] == $vmSearch || $vmInfos['name'] === $vmSearch)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Permet de savoir si une VM possède un snapshot donné
   * @param $vm int ID de la VM
   * @param $snapshotSearch
   * @return bool
   */
  public function hasSnapshot($vm, $snapshotSearch)
  {
    foreach ($this->listSnapshots($vm) as $snapshotInfos)
    {
      if ($snapshotInfos['id'] == $snapshotSearch || $snapshotInfos['name'] === $snapshotSearch)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Permet de savoir si une VM possède un snapshot donné
   * @param int    $vmId ID de la VM
   * @param string $snapshotSearch
   * @throws InvalidArgumentException
   * @return array
   */
  public function getSnapshot($vmId, $snapshotSearch)
  {
    foreach ($this->listSnapshots($vmId) as $snapshotInfos)
    {
      if ($snapshotInfos['id'] == $snapshotSearch || $snapshotInfos['name'] === $snapshotSearch)
      {
        return $snapshotInfos;
      }
    }

    throw new InvalidArgumentException('Unknown snapshot [%s]', $snapshotSearch);
  }


  /**
   * Permet de savoir si une VM tourne
   * @param $vm int ID de la VM
   * @return bool
   */
  public function isVMRunning($vm)
  {
    return $this->getVMState($vm) === 'Powered on';
  }

  /**
   * Permet de savoir si une VM est arrêtée complètement
   * @param $vm int ID de la VM
   * @return bool
   */
  public function isVMPoweredOff($vm)
  {
    return $this->getVMState($vm) === 'Powered off';
  }


  /**
   * Permet de récupérer l'état courant d'une VM
   * @throws LogicException
   * @param $vm int ID de la VM
   * @return string Etat de la VM ('Powered on', 'Powered off')
   */
  public function getVMState($vm)
  {
    list($lines) = $this->remoteCmd(sprintf('vim-cmd vmsvc/power.getstate %s', $vm));
    if (count($lines) !== 2)
    {
      throw new LogicException(sprintf('Cannot fetch VM state'));
    }
    $stateLine = $lines[1];

    return $stateLine;
  }

  /**
   * Permet de récupérer l'id d'une VM par son Nom
   * @throws LogicException
   * @param $vmName string nom de la VM recherche
   * @return string Id de la VM ou false
   */
  public function getVMIdByName($vmName)
  {
    $vms = $this->listVM();
    foreach ($vms as $vm)
    {
      if ($vm['name'] == $vmName)
      {
        return $vm['id'];
      }
    }

    return false;
  }

  /**
   * Permet de récupérer l'Id du snapshot courant
   * @param $vm int ID de la VM
   * @return int Id du snapshot
   */
  public function getCurrentSnapshot($vm)
  {
    $snapId = $this->remoteCmd(sprintf('vim-cmd vmsvc/get.snapshotinfo %s |grep currentSnapshot |sed \'s/^.*snapshot-\([0-9]*\).*$/\1/g\' ', $vm));

    return isset($snapId[0][0]) ? $snapId[0][0] : null;
  }

  /**
   * Permet de récupérer l'Id du snapshot courant
   * @param $vm int ID de la VM
   * @return int Id du snapshot
   */
  public function getSnapshotInfo($vm)
  {
    $snapId = $this->remoteCmd(sprintf('vim-cmd vmsvc/get.snapshotinfo %s |grep currentSnapshot |sed \'s/^.*snapshot-\([0-9]*\).*$/\1/g\' ', $vm));

    return isset($snapId[0][0]) ? $snapId[0][0] : null;
  }


  /**
   * Récupére l'état des VMWare tools
   * @param int $vm ID de la VM
   * @return string toolsOk/toolsOld/toolsNotInstalled/toolsNotRunning
   */
  public function getVmToolsStatus($vm)
  {
    list($lines) = $this->remoteCmd(sprintf('vim-cmd vmsvc/get.summary %s |grep toolsStatus |sed \'s/^.*\"\(.*\)\".*$/\1/g\'', $vm));
    $status = $lines[0];

    return $status;
  }

  /**
   * Récupére l'ip de la VM si possible
   * @param $vmId int ID de la VM
   * @throws RuntimeException
   * @return string IP
   */
  public function getVmIp($vmId)
  {
    $timeToStop = 5 * 60; // 5 minutes max pour s'arrêter
    $start      = microtime(true);
    while (microtime(true) - $start < $timeToStop)
    {
      list($lines) = $this->remoteCmd(sprintf('vim-cmd vmsvc/get.summary %s |grep ipAddress |sed \'s/^.*\"\(.*\)\".*$/\1/g\'', $vmId));
      $ip = $lines[0];
      if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $ip))
      {
        return $ip;
      }
      
      // attendre que l'ip soit récupérable 
      sleep(5);
    }

    throw new RuntimeException(sprintf("Impossible de récuperer l'adresse IP de la VM : timeout de %d secondes atteint", $timeToStop));
  }

  /**
   * Permet d'exécuter une commande distante
   * @throws RuntimeException
   * @param $cmd string Commande shell à exécuter
   * @return array
   */
  private function remoteCmd($cmd)
  {
    $remoteCmd = sprintf('%s "%s"', $this->sshConnexion, $cmd);
    $ret       = null;
    $output    = array();
    exec($remoteCmd, $output, $ret);

    if ($ret)
    {
      throw new RuntimeException(sprintf("Command (%s) in error (%d)", $remoteCmd, $ret));
    }

    return array($output, $ret);
  }

}
