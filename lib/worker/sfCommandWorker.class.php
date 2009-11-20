<?php

class sfCommandWorker extends sfBaseWorker
{
  public function schedule($command, $argv = array())
  {
    $worker = new Worker();
    $worker->setCommand($command);
    $worker->setArgv($argv);
    $worker->save();

    return $worker;
  }
}
