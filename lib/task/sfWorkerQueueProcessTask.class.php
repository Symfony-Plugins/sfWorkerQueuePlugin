<?php

class sfWorkerQueueProcessTask extends sfBaseTask
{
  protected $last_return_value, $last_output;

  public function configure()
  {
    $this->namespace = 'worker-queue';
    $this->name      = 'process';

    $this->addOptions(array(
      new sfCommandOption('table', null, sfCommandOption::PARAMETER_REQUIRED, 'The table to fetch workers from', 'Worker'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'dev'),
      new sfCommandOption('debug', null, sfCommandOption::PARAMETER_NONE, 'Run in debug mode or not'),
    ));
  }

  public function execute($arguments = array(), $options = array())
  {
    $manager = $this->initializeDatabase();

    $this->logSection('worker-queue', 'now accepting workers');

    while (true)
    {
      $workerId = $this->fetchNextId($options['table']);

      if (false !== $workerId)
      {
        $this->logSection('process', sprintf('processing worker #%d', $workerId));
        $this->handle($workerId, $options['table']);
        $this->logSection('process', 'waiting for new workers');
      }

      $this->last_output = $this->last_return_value = null;
      $this->logMemory();
    }
  }

  /**
   * Logs memory usage
   */
  protected function logMemory()
  {
    if ($this->commandManager->getOptionValue('debug'))
    {
      $this->logSection('memory', memory_get_usage() / 1024);
    }
  }

  /**
   * Attaches command manager for further option value retrieval
   */
  protected function process(sfCommandManager $commandManager, $options)
  {
    $this->commandManager = $commandManager;
    return parent::process($commandManager, $options);
  }

  /**
   * Executes a symfony task in a separate php process
   */
  protected function executeTask($command)
  {
    $env  = $this->commandManager->getOptionValue('env');
    $argv = '--env='.$env;

    if ($this->commandManager->getOptionValue('debug'))
    {
      $argv .= ' --debug';
    }

    $command = sprintf('php ./symfony %s %s', $command, $argv);

    $this->logSection('command', $command);

    return exec($command, $this->last_output, $this->last_return_value);
  }

  /**
   * Fetches next available worker id
   */
  protected function fetchNextId($table)
  {
    $workerId = (integer) $this->executeTask('worker-queue:fetch-next --quiet --table='.$table);

    return $workerId > 0 ? $workerId : false;
  }

  /**
   * Handles a worker in a separate php process
   */
  protected function handle($workerId, $table)
  {
    $this->executeTask('worker-queue:handle-worker --table='.$table.' --worker-id='.$workerId);
  }

  /**
   * Initializes database
   */
  protected function initializeDatabase()
  {
    $this->logSection('worker-queue', 'initializing database');
    $manager =  new sfDatabaseManager($this->configuration);

    $doctrineManager = Doctrine_Manager::getInstance();
    $doctrineManager->setAttribute(Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS, true);
    //$doctrineManager->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'sfDoctrineQueryStaticParser');

    return $manager;
  }

}
