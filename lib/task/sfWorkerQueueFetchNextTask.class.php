<?php

class sfWorkerQueueFetchNextTask extends sfBaseTask
{
  public function configure()
  {
    $this->namespace = 'worker-queue';
    $this->name      = 'fetch-next';

    $this->addOptions(array(
      new sfCommandOption('table', null, sfCommandOption::PARAMETER_REQUIRED, 'The table to fetch workers from', 'Worker'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'dev'),
      new sfCommandOption('debug', null, sfCommandOption::PARAMETER_NONE, 'Run in debug mode'),
    ));
  }

  public function execute($arguments = array(), $options = array())
  {
    new sfDatabaseManager($this->configuration);
    $worker = Doctrine::getTable($options['table'])->findNext();

    echo $worker ? $worker->getId() : 0;
  }
}
