<?php

namespace Kinocomplete\Test\Source;

use Kinocomplete\Container\Container;
use Kinocomplete\Video\VideoFactory;
use Kinocomplete\Api\ApiInterface;
use Kinocomplete\Api\MoonwalkApi;
use Kinocomplete\Source\Source;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class SourceTest extends TestCase
{
  /**
   * Testing "setApi" method.
   *
   * @return Source
   */
  public function testCanSetApi()
  {
    $source = new Source();

    $api = new MoonwalkApi(
      new Container()
    );

    $source->setApi(null);
    $source->setApi($api);

    return $source;
  }

  /**
   * Testing "getApi" method.
   *
   * @param   Source $source
   * @depends testCanSetApi
   */
  public function testCanGetApi(Source $source)
  {
    Assert::isInstanceOf(
      $source->getApi(),
      ApiInterface::class
    );
  }

  /**
   * Testing "getApi" method exceptions.
   */
  public function testCannotGetApi()
  {
    $this->expectException(\InvalidArgumentException::class);

    $source = new Source();
    $source->getApi();
  }

  /**
   * Testing "setEnabled" method.
   */
  public function testCanSetEnabled()
  {
    $source = new Source();

    $source->setEnabled(null);
    $source->setEnabled(false);
    $source->setEnabled(true);

    return $source;
  }

  /**
   * Testing "isEnabled" method.
   *
   * @param   Source $source
   * @depends testCanSetEnabled
   */
  public function testCanGetEnabled(Source $source)
  {
    Assert::true($source->isEnabled());
  }

  /**
   * Testing "isEnabled" method exceptions.
   */
  public function testCannotGetEnabled()
  {
    $this->expectException(\InvalidArgumentException::class);

    $source = new Source();
    $source->isEnabled();
  }

  /**
   * Testing "setSecure" method.
   */
  public function testCanSetSecure()
  {
    $source = new Source();

    $source->setSecure(null);
    $source->setSecure(false);
    $source->setSecure(true);

    return $source;
  }

  /**
   * Testing "isSecure" method.
   *
   * @param   Source $source
   * @depends testCanSetSecure
   */
  public function testCanGetSecure(Source $source)
  {
    Assert::true($source->isSecure());
  }

  /**
   * Testing "isSecure" method exceptions.
   */
  public function testCannotGetSecure()
  {
    $this->expectException(\InvalidArgumentException::class);

    $source = new Source();
    $source->isSecure();
  }

  /**
   * Testing "setHost" method.
   *
   * @return Source
   */
  public function testCanSetHost()
  {
    $source = new Source();

    $source->setHost(null);
    $source->setHost('');
    $source->setHost('host');

    return $source;
  }

  /**
   * Testing "getHost" method.
   *
   * @param   Source $source
   * @depends testCanSetHost
   */
  public function testCanGetHost(Source $source)
  {
    Assert::same(
      $source->getHost(),
      'host'
    );
  }

  /**
   * Testing "getHost" method exceptions.
   */
  public function testCannotGetHost()
  {
    $exceptions = 0;

    $source = new Source();

    try {
      $source->getHost();
    } catch (\InvalidArgumentException $e) {
      ++$exceptions;
    }

    $source->setHost('');

    try {
      $source->getHost();
    } catch (\InvalidArgumentException $e) {
      ++$exceptions;
    }

    Assert::same($exceptions, 2);
  }

  /**
   * Testing "setBasePath" method.
   *
   * @return Source
   */
  public function testCanSetBasePath()
  {
    $source = new Source();

    $source->setBasePath(null);
    $source->setBasePath('');
    $source->setBasePath('basePath');

    return $source;
  }

  /**
   * Testing "getBasePath" method.
   *
   * @param   Source $source
   * @depends testCanSetBasePath
   */
  public function testCanGetBasePath(Source $source)
  {
    Assert::same(
      $source->getBasePath(),
      'basePath'
    );
  }

  /**
   * Testing "getBasePath" method exceptions.
   */
  public function testCannotGetBasePath()
  {
    $exceptions = 0;

    $source = new Source();

    try {
      $source->getBasePath();
    } catch (\InvalidArgumentException $e) {
      ++$exceptions;
    }

    $source->setBasePath('');
    $source->getBasePath();

    Assert::same($exceptions, 1);
  }

  /**
   * Testing "setToken" method.
   *
   * @return Source
   */
  public function testCanSetToken()
  {
    $source = new Source();

    $source->setToken(null);
    $source->setToken('');
    $source->setToken('token');

    return $source;
  }

  /**
   * Testing "getToken" method.
   *
   * @param   Source $source
   * @depends testCanSetToken
   */
  public function testCanGetToken(Source $source)
  {
    Assert::same(
      $source->getToken(),
      'token'
    );
  }

  /**
   * Testing "getToken" method exceptions.
   */
  public function testCannotGetToken()
  {
    $exceptions = 0;

    $source = new Source();

    try {
      $source->getToken();
    } catch (\InvalidArgumentException $e) {
      ++$exceptions;
    }

    $source->setToken('');
    $source->getToken();

    Assert::same($exceptions, 1);
  }

  /**
   * Testing "setVideoOrigin" method.
   *
   * @return Source
   */
  public function testCanSetVideoOrigin()
  {
    $source = new Source();

    $source->setVideoOrigin(null);
    $source->setVideoOrigin('videoOrigin');

    return $source;
  }

  /**
   * Testing "getVideoOrigin" method.
   *
   * @param   Source $source
   * @depends testCanSetVideoOrigin
   */
  public function testCanGetVideoOrigin(Source $source)
  {
    Assert::same(
      $source->getVideoOrigin(),
      'videoOrigin'
    );
  }

  /**
   * Testing "getVideoOrigin" method exceptions.
   */
  public function testCannotGetVideoOrigin()
  {
    $this->expectException(\InvalidArgumentException::class);

    $source = new Source();
    $source->getVideoOrigin();
  }

  /**
   * Testing "setVideoFactory" method.
   *
   * @return Source
   */
  public function testCanSetVideoFactory()
  {
    $source = new Source();

    $videoFactory = new VideoFactory(
      new Container()
    );

    $callable = [
      $videoFactory,
      'createByMoonwalk'
    ];

    $source->setVideoFactory(null);
    $source->setVideoFactory($callable);

    return $source;
  }

  /**
   * Testing "getVideoFactory" method.
   *
   * @param   Source $source
   * @depends testCanSetVideoFactory
   */
  public function testCanGetVideoFactory(Source $source)
  {
    Assert::isCallable($source->getVideoFactory());
  }

  /**
   * Testing "getVideoFactory" method exceptions.
   */
  public function testCannotGetVideoFactory()
  {
    $this->expectException(\InvalidArgumentException::class);

    $source = new Source();
    $source->getVideoFactory();
  }

  /**
   * Testing "setLanguage" method.
   *
   * @return Source
   */
  public function testCanSetLanguage()
  {
    $source = new Source();

    $source->setLanguage(null);
    $source->setLanguage('');
    $source->setLanguage('language');

    return $source;
  }

  /**
   * Testing "getLanguage" method.
   *
   * @param   Source $source
   * @depends testCanSetLanguage
   */
  public function testCanGetLanguage(Source $source)
  {
    Assert::same(
      $source->getLanguage(),
      'language'
    );
  }

  /**
   * Testing "getLanguage" method exceptions.
   */
  public function testCannotGetLanguage()
  {
    $exceptions = 0;

    $source = new Source();

    try {
      $source->getLanguage();
    } catch (\InvalidArgumentException $e) {
      ++$exceptions;
    }

    $source->setLanguage('');

    try {
      $source->getLanguage();
    } catch (\InvalidArgumentException $e) {
      ++$exceptions;
    }

    Assert::same($exceptions, 2);
  }

  /**
   * Testing "getScheme" method.
   */
  public function testCanGetScheme()
  {
    $source = new Source();

    $source->setSecure(false);

    Assert::same(
      $source->getScheme(),
      'http://'
    );

    $source->setSecure(true);

    Assert::same(
      $source->getScheme(),
      'https://'
    );
  }

  /**
   * Testing "getScheme" method exceptions.
   */
  public function testCannotGetScheme()
  {
    $this->expectException(\InvalidArgumentException::class);

    $source = new Source();
    $source->getScheme();
  }
}