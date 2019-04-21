<?php

namespace Kinocomplete\Post;

use Symfony\Component\Process\Exception\LogicException;
use Kinocomplete\ExtraField\ExtraField;
use Webmozart\Assert\Assert;

class Post
{
  /**
   * @var string
   */
  public $id = '';

  /**
   * @var string
   */
  public $slug = '';

  /**
   * @var string
   */
  public $title = '';

  /**
   * @var string
   */
  public $author = '';

  /**
   * @var string
   */
  public $date = '';

  /**
   * @var string
   */
  public $shortStory = '';

  /**
   * @var string
   */
  public $fullStory = '';

  /**
   * @var string
   */
  public $metaTitle = '';

  /**
   * @var string
   */
  public $metaDescription = '';

  /**
   * @var array
   */
  public $metaKeywords = [];

  /**
   * @var array
   */
  public $tags = [];

  /**
   * @var array
   */
  public $categories = [];

  /**
   * @var array
   */
  public $extraFields = [];

  /**
   * @var bool
   */
  public $commentsAllowed = true;

  /**
   * @var bool
   */
  public $mainAllowed = true;

  /**
   * @var bool
   */
  public $published = true;

  /**
   * @var bool
   */
  public $fixed = false;

  /**
   * Post constructor.
   */
  public function __construct()
  {
    $this->date = date('Y-m-d H:i:s');
  }

  /**
   * Get extra field.
   *
   * @param  $name
   * @return ExtraField|false
   */
  public function getExtraField($name)
  {
    Assert::stringNotEmpty(
      $name,
      'Невозможно получить дополнительное поле без имени.'
    );

    $filter = function (ExtraField $field) use ($name) {
      return $field->name === $name;
    };

    return current(
      array_filter(
        $this->extraFields,
        $filter
      )
    );
  }

  /**
   * Remove extra field.
   *
   * @param  string $name
   * @return ExtraField
   */
  public function removeExtraField($name)
  {
    Assert::stringNotEmpty(
      $name,
      'Невозможно удалить дополнительное поле без имени.'
    );

    $searchFilter = function (ExtraField $field) use ($name) {
      return $field->name === $name;
    };

    $field = current(
      array_filter(
        $this->extraFields,
        $searchFilter
      )
    );

    Assert::isInstanceOf(
      $field,
      ExtraField::class,
      'Невозможно удалить несуществующее дополнительное поле.'
    );

    $excludeFilter = function (ExtraField $field) use ($name) {
      return $field->name !== $name;
    };

    $this->extraFields = array_filter(
      $this->extraFields,
      $excludeFilter
    );

    return $field;
  }

  /**
   * Set value of extra field.
   *
   * @param  string $name
   * @param  string $value
   * @return bool
   */
  public function setExtraFieldValue($name, $value)
  {
    Assert::stringNotEmpty(
      $name,
      'Невозможно установить значение дополнительному полю без имени.'
    );

    $isNumericValue = is_numeric($value);
    $isStringNotEmptyValue = $value && is_string($value);

    if (!$isNumericValue && !$isStringNotEmptyValue)
      throw new \InvalidArgumentException(
        'Некорректное значение дополнительного поля.'
      );

    $founded = false;

    $fieldsMapper = function (
      ExtraField $field
    ) use (
      $name,
      $value,
      &$founded
    ) {

      if ($field->name === $name && $founded)
        throw new LogicException(sprintf(
          'Обнаружены дополнительные поля с одинаковым названием: %s',
          $name
        ));

      if ($field->name === $name) {

        $founded = true;
        $field->value = $value;
      }

      return $field;
    };

    $this->extraFields = array_map(
      $fieldsMapper,
      $this->extraFields
    );

    Assert::notEmpty(
      $founded,
      'Не удалось найти дополнительное поле по имени: '. $name
    );

    return $founded;
  }
}