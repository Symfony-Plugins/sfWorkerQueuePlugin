<?php

class sfWorkerQueueProcessTask extends sfBaseTask
{
  public function configure()
  {
    $this->namespace = 'worker-queue';
    $this->name      = 'process';

    $this->addOptions(array(
      new sfCommandOption('step', null, sfCommandOption::PARAMETER_REQUIRED, 'The number of seconds to wait between each job', 1),
      new sfCommandOption('table', null, sfCommandOption::PARAMETER_REQUIRED, 'The table to fetch workers from', 'Worker'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'dev'),
      new sfCommandOption('debug', null, sfCommandOption::PARAMETER_NONE, 'Run in debug mode or not'),
    ));
  }

  public function execute($arguments = array(), $options = array())
  {
    $this->initializeDatabase();

    $this->logSection('worker-queue', 'now accepting workers');

    while (0 === sleep($options['step']))
    {
      $worker = Doctrine::getTable($options['table'])->findNext();

      if (false === $worker)
      {
        continue;
      }

      $this->logSection('process', sprintf('processing worker #%d', $worker->getId()));

      try
      {
        $worker->setProcessing();
        $this->handle($worker);
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

      $this->logSection('process', 'waiting for new workers');
    }
  }

  protected function handle(Worker $worker)
  {
    return $worker->run();
  }

  protected function initializeDatabase()
  {
    $this->logSection('worker-queue', 'initializing database');
    return new sfDatabaseManager($this->configuration);
  }

}
