<?php

namespace Kinocomplete\FeedLoader;

use Kinocomplete\StatusSender\StatusSender as BaseStatusSender;

class StatusSender extends BaseStatusSender
{
  /**
   * Number of processed items.
   *
   * @var int
   */
  protected $processedItems = 0;

  /**
   * Number of skipped items.
   *
   * @var int
   */
  protected $skippedItems = 0;

  /**
   * Push processed item.
   */
  public function processItem()
  {
    ++$this->processedItems;

    $this->sendStatus();
  }

  /**
   * Push skipped item.
   */
  public function skipItem()
  {
    ++$this->skippedItems;

    $this->sendStatus();
  }

  /**
   * Get status.
   *
   * @param  int $progress
   * @return array
   */
  protected function getStatus($progress = 0)
  {
    $status = parent::getStatus($progress);
    $status['processed'] = $this->processedItems;
    $status['skipped'] = $this->skippedItems;

    return $status;
  }
}