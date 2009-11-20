<?php

class sfWorkerQueueScheduleTask extends sfBaseTask
{
  public function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('command', null, sfCommandOption::PARAMETER_REQUIRED, 'The command'),
      new sfCommandOption('argv', null, sfCommandOption::PARAMETER_REQUIRED, 'The argv'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'The type', 'Command'),
    ));

    $this->namespace = 'worker-queue';
    $this->name      = 'schedule';
  }

  public function execute($arguments = array(), $options = array())
  {
    $manager = new sfDatabaseManager($this->configuration);

    if (!isset($options['command']))
    {
      throw new InvalidArgumentException('You must specify a command to schedule');
    }

    sfWorker::schedule($options['command'], $options['argv'], $options['type']);
  }
}
