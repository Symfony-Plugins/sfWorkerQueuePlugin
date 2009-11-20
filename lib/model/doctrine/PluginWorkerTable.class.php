<?php
/**
 */
class PluginWorkerTable extends Doctrine_Table
{
  public function findNext()
  {
    $query = Doctrine_Query::create()->from('Worker w');
    $query->orderBy('priority DESC, created_at DESC');
    $query->where('status  = ?', Worker::STATUS_PENDING);
    $query->limit(1);

    return $query->fetchOne();
  }
}
