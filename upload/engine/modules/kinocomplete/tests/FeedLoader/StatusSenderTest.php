<?php

namespace Kinocomplete\Test\FeedLoader;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\FeedLoader\StatusSender;
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
   * FeedLoaderStatusSenderTest constructor.
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
      'processedItems',
      'skippedItems',
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
   * Testing `processItem` method.
   */
  public function testCanProcessItem()
  {
    $this->instance
      ->expects($this->once())
      ->method('sendStatus');

    $this->instance->processItem();

    $processedItems = $this->reflection->getProperty('processedItems');
    $processedItems->setAccessible(true);
    $processedItemsValue = $processedItems->getValue($this->instance);

    Assert::same($processedItemsValue, 1);
  }

  /**
   * Testing `skipItem` method.
   */
  public function testCanSkipItem()
  {
    $this->instance
      ->expects($this->once())
      ->method('sendStatus');

    $this->instance->skipItem();

    $skippedItems = $this->reflection->getProperty('skippedItems');
    $skippedItems->setAccessible(true);
    $skippedItemsValue = $skippedItems->getValue($this->instance);

    Assert::same($skippedItemsValue, 1);
  }
}