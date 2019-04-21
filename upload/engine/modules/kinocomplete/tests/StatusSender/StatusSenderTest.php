<?php

namespace Kinocomplete\Test\StatusSender;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\StatusSender\StatusSender;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class StatusSenderTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var StatusSender
   */
  public $instance;

  /**
   * @var \ReflectionObject
   */
  public $reflection;

  /**
   * StatusSenderTest constructor.
   *
   * @param null $name
   * @param array $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $instance = $this->getMockBuilder(
      StatusSender::class
    )->setMethods([
      'openConnection',
      'closeConnection',
      'sendStatus'
    ])->getMock();

    $reflection = new \ReflectionObject($instance);
    $connected = $reflection->getProperty('connected');
    $connected->setAccessible(true);

    $instance
      ->method('openConnection')
      ->willReturnCallback(function () use (&$connected, &$instance) {
        $connected->setValue($instance, true);
      });

    $instance
      ->method('closeConnection')
      ->willReturnCallback(function () use (&$connected, &$instance) {
        $connected->setValue($instance, false);
      });

    $instance
      ->method('sendStatus')
      ->willReturn(true);

    $this->instance = &$instance;
    $this->reflection = &$reflection;
  }

  /**
   * Before each test.
   */
  public function setUp()
  {
    $defaultInstance = new StatusSender();
    $defaultReflection = new \ReflectionObject($defaultInstance);

    $properties = [
      'connected',
      'totalSteps',
      'currentStep',
      'stepTasks',
      'readyStepTasks'
    ];

    foreach ($properties as $property) {

      $defaultProperty = $defaultReflection->getProperty($property);
      $defaultProperty->setAccessible(true);
      $defaultPropertyValue = $defaultProperty->getValue($defaultInstance);

      $modifiedProperty = $this->reflection->getProperty($property);
      $modifiedProperty->setAccessible(true);
      $modifiedProperty->setValue($this->instance, $defaultPropertyValue);
    }
  }

  /**
   * Testing `setTotalSteps` method.
   *
   * @throws \Exception
   */
  public function testCanSetTotalSteps()
  {
    $this->instance
      ->expects($this->once())
      ->method('sendStatus');

    $this->instance->setTotalSteps(5);

    $totalSteps = $this->reflection->getProperty('totalSteps');
    $totalSteps->setAccessible(true);
    $totalStepsValue = $totalSteps->getValue($this->instance);

    Assert::same($totalStepsValue, 5);
  }

  /**
   * Testing `setCurrentStep` method.
   *
   * @throws \Exception
   */
  public function testCanSetCurrentStep()
  {
    $this->instance
      ->expects($this->exactly(2))
      ->method('sendStatus');

    $this->instance->setTotalSteps(5);
    $this->instance->setCurrentSteps(5);

    $totalSteps = $this->reflection->getProperty('totalSteps');
    $totalSteps->setAccessible(true);
    $totalStepsValue = $totalSteps->getValue($this->instance);

    Assert::same($totalStepsValue, 5);
  }

  /**
   * Testing `setCurrentStep` method exceptions.
   *
   * @throws \Exception
   */
  public function testCannotSetCurrentStep()
  {
    $this->expectException(\OverflowException::class);

    $this->instance->setCurrentSteps(1);
  }

  /**
   * Testing `setStepTasks` method.
   *
   * @throws \Exception
   */
  public function testCanSetStepTasks()
  {
    $this->instance
      ->expects($this->once())
      ->method('sendStatus');

    $this->instance->setStepTasks(5);

    $stepTasks = $this->reflection->getProperty('stepTasks');
    $stepTasks->setAccessible(true);
    $stepTasksValue = $stepTasks->getValue($this->instance);

    Assert::same($stepTasksValue, 5);
  }

  /**
   * Testing `setReadyStepTasks` method.
   *
   * @throws \Exception
   */
  public function testCanSetReadyStepTasks()
  {
    $this->instance
      ->expects($this->exactly(3))
      ->method('sendStatus');

    $this->instance->setStepTasks(10);
    $this->instance->setReadyStepTasks(5);

    $readyStepTasks = $this->reflection->getProperty('readyStepTasks');
    $readyStepTasks->setAccessible(true);
    $readyStepTasksValue = $readyStepTasks->getValue($this->instance);

    Assert::same(
      $readyStepTasksValue,
      5
    );

    $this->instance->setReadyStepTasks(20);
    $readyStepTasksValue = $readyStepTasks->getValue($this->instance);

    Assert::same(
      $readyStepTasksValue,
      10
    );
  }

  /**
   * Testing `getProgressPercentage` method.
   *
   * @throws \Exception
   */
  public function testCanGetProgressPercentage()
  {
    $this->instance->setTotalSteps(2);
    $this->instance->setCurrentSteps(1);
    $this->instance->setStepTasks(300);
    $this->instance->setReadyStepTasks(150);

    $getProgressPercentage = $this->reflection
      ->getMethod('getProgressPercentage');

    $getProgressPercentage->setAccessible(true);

    $percentage = $getProgressPercentage->invoke(
      $this->instance
    );

    Assert::same(
      $percentage,
      25
    );

    $this->instance->setCurrentSteps(0);

    $percentage = $getProgressPercentage->invoke(
      $this->instance
    );

    Assert::same(
      $percentage,
      0
    );
  }

  /**
   * Testing `nextStep` method.
   */
  public function testCanNextStep()
  {
    $expected = 2;

    $this->instance->setTotalSteps($expected);
    $this->instance->nextStep();
    $this->instance->nextStep();

    $currentStep = $this->reflection->getProperty('currentStep');
    $currentStep->setAccessible(true);
    $currentStepValue = $currentStep->getValue($this->instance);

    Assert::same(
      $currentStepValue,
      $expected
    );
  }

  /**
   * Testing `nextStep` method exceptions.
   */
  public function testCannotNextStep()
  {
    $this->expectException(\Exception::class);

    $this->instance->nextStep();
  }
}