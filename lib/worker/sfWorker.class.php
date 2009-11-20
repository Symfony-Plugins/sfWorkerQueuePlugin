<?php

class sfWorker
{
  static public function schedule($command, $argv = array(), $type = 'Command')
  {
    $class = sprintf('sf%sWorker', $type);
    $class = new $class();

    return $class->schedule($command, $argv);
  }
}
