<?php

abstract class sfBaseWorker implements sfWorkerInterface
{
  public function getFullCommand()
  {
    return sprintf('%s %s', $this->getCommand(), $this->getArgv());
  }
}
