<?php

class sfWorkerQueueHandleWorkerTask extends sfBaseTask
{
  public function configure()
  {
    $this->namespace = 'worker-queue';
    $this->name      = 'handle-worker';

    $this->addOptions(array(
      new sfCommandOption('worker-id', null, sfCommandOption::PARAMETER_REQUIRED, 'The number of seconds to wait between each job', 1),
      new sfCommandOption('table', null, sfCommandOption::PARAMETER_REQUIRED, 'The table to fetch workers from', 'Worker'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'dev'),
      new sfCommandOption('debug', null, sfCommandOption::PARAMETER_NONE, 'Run in debug mode'),
    ));
  }

  public function execute($arguments = array(), $options = array())
  {
    new sfDatabaseManager($this->configuration);

    $worker = Doctrine_Core::getTable($options['table'])->find($options['worker-id']);

    try
    {
      $worker->setProcessing();
      $worker->run();
      if ($options['debug'])
      {
        $this->logSection('worker-queue', 'worker output:');
        echo $worker->getOutput();
        echo PHP_EOL;
      }
      $worker->setProcessed();
    }
    catch (Exception $e)
    {
      $this->logBlock($e->getMessage(), 'ERROR');
      echo $e->getTraceAsString();
      $worker->setPending();
    }

    $worker->free(true);
  }
}
