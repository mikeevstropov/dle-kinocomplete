<?php

namespace Kinocomplete\Test\Source;

use PHPUnit\Framework\TestCase;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;
use Kinocomplete\Feed\Feed;

class FeedTest extends TestCase
{
  /**
   * Testing "setName" method.
   */
  public function testCanSetName()
  {
    $expected = Utils::randomString();

    $feed = new Feed();
    $feed->setName($expected);

    $reflection = new \ReflectionObject($feed);
    $property = $reflection->getProperty('name');
    $property->setAccessible(true);

    $result = $property->getValue($feed);

    Assert::same(
      $expected,
      $result
    );
  }

  /**
   * Testing "setName" method exceptions.
   */
  public function testCannotSetName()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->setName(5);
  }

  /**
   * Testing "setLabel" method.
   */
  public function testCanSetLabel()
  {
    $expected = Utils::randomString();

    $feed = new Feed();
    $feed->setLabel($expected);

    $reflection = new \ReflectionObject($feed);
    $property = $reflection->getProperty('label');
    $property->setAccessible(true);

    $result = $property->getValue($feed);

    Assert::same(
      $expected,
      $result
    );
  }

  /**
   * Testing "setLabel" method exceptions.
   */
  public function testCannotSetLabel()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->setLabel(5);
  }

  /**
   * Testing "getName" method.
   */
  public function testCanGetName()
  {
    $expected = Utils::randomString();

    $feed = new Feed();

    $reflection = new \ReflectionObject($feed);
    $property = $reflection->getProperty('name');
    $property->setAccessible(true);

    $property->setValue(
      $feed,
      $expected
    );

    Assert::same(
      $expected,
      $feed->getName()
    );
  }

  /**
   * Testing "getName" method exceptions.
   */
  public function testCannotGetName()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->getName();
  }

  /**
   * Testing "setVideoOrigin" method.
   */
  public function testCanSetVideoOrigin()
  {
    $expected = Video::MOONWALK_ORIGIN;

    $feed = new Feed();
    $feed->setVideoOrigin($expected);

    $reflection = new \ReflectionObject($feed);
    $property = $reflection->getProperty('videoOrigin');
    $property->setAccessible(true);

    $result = $property->getValue($feed);

    Assert::same(
      $expected,
      $result
    );
  }

  /**
   * Testing "setVideoOrigin" method exceptions.
   */
  public function testCannotSetVideoOrigin()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->setVideoOrigin(5);
  }

  /**
   * Testing "getVideoOrigin" method.
   */
  public function testCanGetVideoOrigin()
  {
    $expected = Video::MOONWALK_ORIGIN;

    $feed = new Feed();

    $reflection = new \ReflectionObject($feed);
    $property = $reflection->getProperty('videoOrigin');
    $property->setAccessible(true);

    $property->setValue(
      $feed,
      $expected
    );

    Assert::same(
      $expected,
      $feed->getVideoOrigin()
    );
  }

  /**
   * Testing "getVideoOrigin" method exceptions.
   */
  public function testCannotGetVideoOrigin()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->getVideoOrigin();
  }

  /**
   * Testing "setRequestPath" method.
   */
  public function testCanSetRequestPath()
  {
    $expected = Utils::randomString();

    $feed = new Feed();
    $feed->setRequestPath($expected);

    $reflection = new \ReflectionObject($feed);
    $property = $reflection->getProperty('requestPath');
    $property->setAccessible(true);

    $result = $property->getValue($feed);

    Assert::same(
      $expected,
      $result
    );
  }

  /**
   * Testing "setRequestPath" method exceptions.
   */
  public function testCannotSetRequestPath()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->setRequestPath(5);
  }

  /**
   * Testing "getRequestPath" method.
   */
  public function testCanGetRequestPath()
  {
    $expected = Utils::randomString();

    $feed = new Feed();

    $reflection = new \ReflectionObject($feed);
    $property = $reflection->getProperty('requestPath');
    $property->setAccessible(true);

    $property->setValue(
      $feed,
      $expected
    );

    Assert::same(
      $expected,
      $feed->getRequestPath()
    );
  }

  /**
   * Testing "getRequestPath" method exceptions.
   */
  public function testCannotGetRequestPath()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->getRequestPath();
  }

  /**
   * Testing "setJsonPointer" method.
   */
  public function testCanSetJsonPointer()
  {
    $expected = Utils::randomString();

    $feed = new Feed();
    $feed->setJsonPointer($expected);

    $reflection = new \ReflectionObject($feed);
    $property = $reflection->getProperty('jsonPointer');
    $property->setAccessible(true);

    $result = $property->getValue($feed);

    Assert::same(
      $expected,
      $result
    );
  }

  /**
   * Testing "setJsonPointer" method exceptions.
   */
  public function testCannotSetJsonPointer()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->setJsonPointer(5);
  }

  /**
   * Testing "getJsonPointer" method.
   */
  public function testCanGetJsonPointer()
  {
    $expected = Utils::randomString();

    $feed = new Feed();

    $reflection = new \ReflectionObject($feed);
    $property = $reflection->getProperty('jsonPointer');
    $property->setAccessible(true);

    $property->setValue(
      $feed,
      $expected
    );

    Assert::same(
      $expected,
      $feed->getJsonPointer()
    );
  }

  /**
   * Testing "getJsonPointer" method exceptions.
   */
  public function testCannotGetJsonPointer()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->getJsonPointer();
  }

  /**
   * Testing "setSize" method.
   */
  public function testCanSetSize()
  {
    $expected = 20;

    $feed = new Feed();
    $feed->setSize($expected);

    $reflection = new \ReflectionObject($feed);
    $property = $reflection->getProperty('size');
    $property->setAccessible(true);

    $result = $property->getValue($feed);

    Assert::same(
      $expected,
      $result
    );
  }

  /**
   * Testing "setSize" method exceptions.
   */
  public function testCannotSetSize()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->setSize('string');
  }

  /**
   * Testing "getSize" method.
   */
  public function testCanGetSize()
  {
    $expected = 20;

    $feed = new Feed();

    $reflection = new \ReflectionObject($feed);
    $property = $reflection->getProperty('size');
    $property->setAccessible(true);

    $property->setValue(
      $feed,
      $expected
    );

    Assert::same(
      $expected,
      $feed->getSize()
    );
  }

  /**
   * Testing "getSize" method exceptions.
   */
  public function testCannotGetSize()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->getSize();
  }

  /**
   * Testing "getFileName" method.
   */
  public function testCanGetFileName()
  {
    $feedName    = Utils::randomString();
    $videoOrigin = Utils::randomString();
    $extension   = 'txt';

    $expected = $feedName
      .'-'. $videoOrigin
      .'-feed.'. $extension;

    $feed = new Feed();

    $feed->setName($feedName);
    $feed->setVideoOrigin($videoOrigin);

    Assert::same(
      $expected,
      $feed->getFileName($extension)
    );
  }

  /**
   * Testing "getFileName" method exceptions.
   */
  public function testCannotGetFileName()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = new Feed();
    $feed->getFileName();
  }
}
