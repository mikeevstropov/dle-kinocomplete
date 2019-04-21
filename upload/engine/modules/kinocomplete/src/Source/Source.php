<?php

namespace Kinocomplete\Source;

use Kinocomplete\Api\ApiInterface;
use Webmozart\Assert\Assert;

class Source
{
  /**
   * @var ApiInterface
   */
  protected $api;

  /**
   * @var bool
   */
  protected $enabled;

  /**
   * @var bool
   */
  protected $secure;

  /**
   * @var string
   */
  protected $host;

  /**
   * @var string
   */
  protected $basePath;

  /**
   * @var string
   */
  protected $token;

  /**
   * @var string
   */
  protected $videoOrigin;

  /**
   * @var callable
   */
  protected $videoFactory;

  /**
   * @var string
   */
  protected $language;

  /**
   * Get api.
   *
   * @return ApiInterface
   */
  public function getApi()
  {
    Assert::isInstanceOf(
      $this->api,
      ApiInterface::class,
      'Не определен экземпляр ApiInterface для источника.'
    );

    return $this->api;
  }

  /**
   * Set api.
   *
   * @param ApiInterface|null $api
   */
  public function setApi(ApiInterface $api = null)
  {
    $this->api = $api;
  }

  /**
   * Is enabled.
   *
   * @return bool
   */
  public function isEnabled()
  {
    Assert::boolean(
      $this->enabled,
      'Не определен статус активации источника.'
    );

    return $this->enabled;
  }

  /**
   * Set enabled.
   *
   * @param bool|null $enabled
   */
  public function setEnabled($enabled)
  {
    Assert::nullOrBoolean($enabled);

    $this->enabled = $enabled;
  }

  /**
   * Is secure.
   *
   * @return bool
   */
  public function isSecure()
  {
    Assert::boolean(
      $this->secure,
      'Опция "secure" не определена для источника.'
    );

    return $this->secure;
  }

  /**
   * Set secure.
   *
   * @param bool|null $secure
   */
  public function setSecure($secure)
  {
    Assert::nullOrBoolean($secure);

    $this->secure = $secure;
  }

  /**
   * Get host.
   *
   * @return string
   */
  public function getHost()
  {
    Assert::stringNotEmpty(
      $this->host,
      'Опция "host" не определена для источника.'
    );

    return $this->host;
  }

  /**
   * Set host.
   *
   * @param string|null $host
   */
  public function setHost($host)
  {
    Assert::nullOrString($host);

    $this->host = $host;
  }

  /**
   * Get base path.
   *
   * @return string
   */
  public function getBasePath()
  {
    Assert::string(
      $this->basePath,
      'Опция "basePath" не определена для источника.'
    );

    return $this->basePath;
  }

  /**
   * Set base path.
   *
   * @param string|null $basePath
   */
  public function setBasePath($basePath)
  {
    Assert::nullOrString($basePath);

    $this->basePath = $basePath;
  }

  /**
   * Get token.
   *
   * @return string
   */
  public function getToken()
  {
    Assert::string(
      $this->token,
      'Опция "token" не определена для источника.'
    );

    return $this->token;
  }

  /**
   * Set token.
   *
   * @param string|null $token
   */
  public function setToken($token)
  {
    Assert::nullOrString($token);

    $this->token = $token;
  }

  /**
   * Get video origin.
   *
   * @return string
   */
  public function getVideoOrigin()
  {
    Assert::stringNotEmpty(
      $this->videoOrigin,
      'Опция "videoOrigin" не определена для источника.'
    );

    return $this->videoOrigin;
  }

  /**
   * Set video origin.
   *
   * @param string|null $videoOrigin
   */
  public function setVideoOrigin($videoOrigin)
  {
    Assert::nullOrStringNotEmpty($videoOrigin);

    $this->videoOrigin = $videoOrigin;
  }

  /**
   * Get video factory.
   *
   * @return callable
   */
  public function getVideoFactory()
  {
    Assert::isCallable(
      $this->videoFactory,
      'Не определен фабричный метод для создания Video экземпляров.'
    );

    return $this->videoFactory;
  }

  /**
   * Set video factory.
   *
   * @param callable $videoFactory
   */
  public function setVideoFactory(callable $videoFactory = null)
  {
    Assert::nullOrIsCallable($videoFactory);

    $this->videoFactory = $videoFactory;
  }

  /**
   * Get language.
   *
   * @return string
   */
  public function getLanguage()
  {
    Assert::stringNotEmpty(
      $this->language,
      'Опция "language" не определена для источника.'
    );

    return $this->language;
  }

  /**
   * Set language.
   *
   * @param string|null $language
   */
  public function setLanguage($language)
  {
    Assert::nullOrString($language);

    $this->language = $language;
  }

  /**
   * Return URL scheme.
   *
   * @return string
   */
  public function getScheme()
  {
    Assert::boolean(
      $this->secure,
      'Опция "secure" не определена для источника.'
    );

    return $this->secure
      ? 'https://'
      : 'http://';
  }
}