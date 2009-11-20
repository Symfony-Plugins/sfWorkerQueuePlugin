<?php

class sfSymfonyTaskWorker extends sfBaseWorker
{
  public function schedule($task, $argv = array())
  {
    $atom = '[a-z_-]+';

    if (!preg_match('/'.$atom.':'.$atom.'/i', $task))
    {
      throw InvalidArgumentException(sprintf('"%s" is not a valid task name', $task));
    }

    $worker = new Worker();
    $worker->setCommand('php ./symfony '.$task.' '.implode(' ', $argv));
    $worker->save();

    return $worker;
  }
}
