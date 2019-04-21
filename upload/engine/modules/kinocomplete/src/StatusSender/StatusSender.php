<?php

namespace Kinocomplete\StatusSender;

use Webmozart\Assert\Assert;

class StatusSender
{
  /**
   * Connected status.
   *
   * @var bool
   */
  protected $connected = false;

  /**
   * Number of total steps.
   *
   * @var int
   */
  protected $totalSteps = 0;

  /**
   * Index of current step.
   *
   * @var int
   */
  protected $currentStep = 0;

  /**
   * Number of step tasks.
   *
   * @var int
   */
  protected $stepTasks = 0;

  /**
   * Number of ready step tasks.
   *
   * @var int
   */
  protected $readyStepTasks = 0;

  /**
   * Get connected status.
   *
   * @return bool
   */
  public function isConnected()
  {
    return $this->connected;
  }

  /**
   * Set total steps.
   *
   * @param  $value
   * @throws \Exception
   */
  public function setTotalSteps($value)
  {
    Assert::integer($value);

    $this->totalSteps = $value;
    $this->sendStatus();
  }

  /**
   * Set current steps.
   *
   * @param  $value
   * @throws \Exception
   * @throws \OverflowException
   */
  public function setCurrentSteps($value)
  {
    Assert::greaterThanEq(
      $value,
      0,
      'Индекс текущего шага должен быть положительным числом.'
    );

    if ($value > $this->totalSteps)
      throw new \OverflowException(
        'Невозможно назначить индекс текущего шага.'
      );

    $this->currentStep = $value;
    $this->sendStatus();
  }

  /**
   * Set step tasks.
   *
   * @param  $value
   * @throws \Exception
   */
  public function setStepTasks($value)
  {
    Assert::integer($value);

    $this->stepTasks = $value;
    $this->sendStatus();
  }

  /**
   * Set ready step tasks.
   *
   * @param  $value
   * @throws \Exception
   */
  public function setReadyStepTasks($value)
  {
    Assert::integer($value);

    $this->readyStepTasks = $value;

    if ($this->readyStepTasks >= $this->stepTasks)
      $this->readyStepTasks = $this->stepTasks;

    $this->sendStatus();
  }

  /**
   * Open connection.
   *
   * @throws \Exception
   */
  public function openConnection()
  {
    if ($this->connected)
      throw new \Exception(
        'Подключение Server-Sent Events не требуется.'
      );

    $this->connected = true;

    header('content-type: text/event-stream');
    header('cache-control: no-cache');

    @ob_end_clean();
  }

  /**
   * Close connection.
   *
   * @param  \Exception $exception
   * @throws \Exception
   */
  public function closeConnection(
    \Exception $exception = null
  ) {
    if (!$this->connected)
      throw new \Exception(
        'Подключение Server-Sent Events отсутствует.'
      );

    $this->connected = false;

    if ($exception) {

      $data = [
        'message' => $exception->getMessage(),
        'code' => $exception->getCode()
      ];

      echo 'event: error'. PHP_EOL;
      echo 'data: '. json_encode($data) . PHP_EOL;
      echo PHP_EOL;

    } else {

      $data = $this->getStatus(100);

      echo 'event: disconnect'. PHP_EOL;
      echo 'data: '. json_encode($data) . PHP_EOL;
      echo PHP_EOL;
    }

    ob_flush();
    flush();
  }

  /**
   * Get progress percentage.
   *
   * @return int
   */
  protected function getProgressPercentage()
  {
    $progress = 0;

    if ($this->totalSteps) {

      $stepWeight = round(
        1 / $this->totalSteps * 100
      );

      $stepsProgress = round(
        $this->currentStep / $this->totalSteps * 100
      );

      $currentStepProgress = $this->stepTasks
        ? round($this->readyStepTasks / $this->stepTasks * $stepWeight)
        : 0;

      $progress = $stepsProgress
        - $stepWeight
        + $currentStepProgress;

      $progress = $progress >= 0
        ? $progress
        : 0;
    }

    return intval($progress);
  }

  /**
   * Get status.
   *
   * @param  int $progress
   * @return array
   */
  protected function getStatus($progress = 0)
  {
    Assert::integer($progress);

    if (!$progress) {

      $progress = $this->getProgressPercentage();
      $progress = $progress > 99 ? 99 : $progress;
    }

    return [
      'progress'       => $progress,
      'totalSteps'     => $this->totalSteps,
      'currentStep'    => $this->currentStep,
      'stepTasks'      => $this->stepTasks,
      'readyStepTasks' => $this->readyStepTasks
    ];
  }

  /**
   * Push current step.
   */
  public function nextStep()
  {
    if ($this->currentStep >= $this->totalSteps)
      throw new \OverflowException(
        'Невозможно увеличить индекс текущего шага.'
      );

    ++$this->currentStep;
    $this->stepTasks = 0;
    $this->readyStepTasks = 0;

    $this->sendStatus();
  }

  /**
   * Send status state to the client.
   *
   * @throws \Exception
   */
  public function sendStatus()
  {
    if (!$this->connected)
      throw new \Exception(
        'Подключение Server-Sent Events отсутствует.'
      );

    $data = $this->getStatus();

    echo 'data: '. json_encode($data) . PHP_EOL;
    echo PHP_EOL;

    ob_flush();
    flush();
  }
}