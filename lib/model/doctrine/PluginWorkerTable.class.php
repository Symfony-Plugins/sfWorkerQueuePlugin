<?php
/**
 */
class PluginWorkerTable extends Doctrine_Table
{
  public function findNext()
  {
    $query = $this->createQuery('w');
    $query->orderBy('priority DESC, created_at DESC');
    $query->where('status  = ?', Worker::STATUS_PENDING);
    $query->limit(1);

    return $query->fetchOne();
  }

  /**
   * Schedule a job
   */
  public function schedule($task, $arguments = array(), $options = array(), $priority = 1)
  {
    $worker = new Worker();
    $worker->fromArray(array(
      'task'      => $task,
      'arguments' => $arguments,
      'options'   => $options,
      'priority'  => $priority,
    ));

    $worker->save();

    return $worker;
  }
}
