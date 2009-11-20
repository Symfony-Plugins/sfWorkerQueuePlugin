<?php

interface sfWorkerInterface
{
  public function schedule($command, $argv = array());
}
