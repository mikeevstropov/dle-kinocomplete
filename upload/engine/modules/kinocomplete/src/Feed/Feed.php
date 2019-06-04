<?php

namespace Kinocomplete\Feed;

use Webmozart\Assert\Assert;

class Feed
{
  /**
   * @var string
   */
  protected $name;

  /**
   * @var string
   */
  protected $label;

  /**
   * @var string
   */
  protected $videoOrigin;

  /**
   * @var string
   */
  protected $requestPath;

  /**
   * @var string
   */
  protected $jsonPointer = '';

  /**
   * @var int
   */
  protected $size;

  /**
   * Get name.
   *
   * @return string
   */
  public function getName()
  {
    Assert::stringNotEmpty(
      $this->name,
      'Имя фида не определено.'
    );

    return $this->name;
  }

  /**
   * Set name.
   *
   * @param string|null $name
   */
  public function setName($name)
  {
    Assert::nullOrString($name);

    $this->name = $name;
  }

  /**
   * Get label.
   *
   * @return string
   */
  public function getLabel()
  {
    Assert::stringNotEmpty(
      $this->label,
      'Название фида не определено.'
    );

    return $this->label;
  }

  /**
   * Set label.
   *
   * @param string|null $label
   */
  public function setLabel($label)
  {
    Assert::nullOrString($label);

    $this->label = $label;
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
      'Источник фида не определен.'
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
    Assert::nullOrString($videoOrigin);

    $this->videoOrigin = $videoOrigin;
  }

  /**
   * Get request path.
   *
   * @return string
   */
  public function getRequestPath()
  {
    Assert::stringNotEmpty(
      $this->requestPath,
      'Путь запроса фида не определен.'
    );

    return $this->requestPath;
  }

  /**
   * Set request path.
   *
   * @param string|null $requestPath
   */
  public function setRequestPath($requestPath)
  {
    Assert::nullOrString($requestPath);

    $this->requestPath = $requestPath;
  }

  /**
   * Get json pointer.
   *
   * @return string
   */
  public function getJsonPointer()
  {
    Assert::string(
      $this->jsonPointer,
      'Опция фида "jsonPointer" не определена.'
    );

    return $this->jsonPointer;
  }

  /**
   * Set json pointer.
   *
   * @param string|null $jsonPointer
   */
  public function setJsonPointer($jsonPointer)
  {
    Assert::nullOrString($jsonPointer);

    $this->jsonPointer = $jsonPointer;
  }

  /**
   * Get size.
   *
   * @return int
   */
  public function getSize()
  {
    Assert::integer(
      $this->size,
      'Размер фида не определен.'
    );

    Assert::greaterThan(
      $this->size,
      0,
      'Размер фида не определен.'
    );

    return $this->size;
  }

  /**
   * Set size.
   *
   * @param int|null $size
   */
  public function setSize($size)
  {
    Assert::nullOrInteger($size);

    $this->size = $size;
  }

  /**
   * Get file name of the Feed.
   *
   * @param  string $extension
   * @return string
   */
  public function getFileName(
    $extension = 'json'
  ) {
    Assert::stringNotEmpty(
      $this->name,
      'Название фида не определено.'
    );

    return $this->name
      .'-'. $this->videoOrigin
      .'-feed.'. $extension;
  }
}
