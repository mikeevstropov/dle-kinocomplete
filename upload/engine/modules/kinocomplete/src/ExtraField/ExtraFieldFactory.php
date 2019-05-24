<?php

namespace Kinocomplete\ExtraField;

use Kinocomplete\Container\ContainerFactory;
use Kinocomplete\Service\DefaultService;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;

class ExtraFieldFactory extends DefaultService
{
  /**
   * Create instance from definition by
   * following pattern "name|label||type".
   *
   * @param  string $definition
   * @return ExtraField
   */
  public function fromDefinition(
    $definition
  ) {
    Assert::stringNotEmpty($definition);

    $array = explode('|', $definition);

    $name = array_key_exists(0, $array)
      ? $array[0]
      : null;

    $label = array_key_exists(1, $array)
      ? $array[1]
      : null;

    $type = array_key_exists(3, $array)
      ? $array[3]
      : null;

    if (!$name || !$label || !$type)
      throw new \InvalidArgumentException(
        'Определение дополнительного поля имеет неизвестный формат.'
      );

    $extraField = new ExtraField();
    $extraField->name = $name;
    $extraField->label = $label;
    $extraField->type = $type;

    return $extraField;
  }

  /**
   * Create instances from definitions
   * by following pattern on each line
   * "name|label||type".
   *
   * @param  string $definitions
   * @return array
   */
  public function fromDefinitions(
    $definitions
  ) {
    Assert::string($definitions);

    $lines = explode("\n", $definitions);
    $extraFields = [];

    foreach ($lines as $line) {

      try {

        $extraFields[] = $this->fromDefinition($line);

      } catch (\Exception $exception) {}
    }

    return $extraFields;
  }

  /**
   * Create instance from value by
   * following pattern "name|content".
   *
   * @param  ExtraField $field
   * @param  string $value
   * @return ExtraField
   * @throws \Exception
   */
  public function fromValue(
    ExtraField $field,
    $value
  ) {
    Assert::stringNotEmpty($value);

    $name = $field->name;

    $matches = [];

    preg_match(
      "/^$name\|([^\|]+)$/",
      $value,
      $matches
    );

    if (count($matches) !== 2)
      throw new \Exception(
        'Значение дополнительного поля имеет неизвестный формат.'
      );

    $field->value = $matches[1];

    return $field;
  }

  /**
   * Create instances from values by
   * following pattern "name|value||name|value".
   *
   * @param  string $string
   * @param  array $extraFields
   * @param  bool $soft Skip not defined fields.
   * @return array
   * @throws \Exception
   */
  public function fromValues(
    $string,
    array $extraFields,
    $soft = false
  ) {
    Assert::string($string);

    foreach ($extraFields as $field) {

      Assert::isInstanceOf(
        $field,
        ExtraField::class
      );
    }

    $rawValues = explode(
      '||',
      $string
    );

    $rawValues = array_map(
      'trim',
      $rawValues
    );

    $instances = [];

    foreach ($rawValues as $rawValue) {

      $nameAndValue = explode(
        '|',
        $rawValue
      );

      $nameAndValue = array_map(
        'trim',
        $nameAndValue
      );

      if (count($nameAndValue) !== 2)
        throw new \Exception(
          'Не удалось разобрать значение дополнительного поля по строке.'
        );

      $name = $nameAndValue[0];
      $value = $nameAndValue[1];

      $fieldFilter = function (ExtraField $field) use ($name) {
        return $field->name === $name;
      };

      $matchedField = current(
        array_filter(
          $extraFields,
          $fieldFilter
        )
      );

      // Extra field of value has not found.
      if (!$matchedField) {

        if ($soft) {

          continue;

        } else {

          throw new \Exception(
            'Не удалось найти дополнительное поле по названию.'
          );
        }
      }

      Assert::string(
        $value,
        'Значение дополнительного поля не является строкой.'
      );

      $matchedField->value = $value;
      $instances[] = $matchedField;
    }

    return $instances;
  }

  /**
   * To string value by following
   * pattern "name|value".
   *
   * @param  ExtraField $field
   * @return string
   */
  public function toValue(
    ExtraField $field
  ) {
    Assert::stringNotEmpty(
      $field->name,
      'Дополнительное поле не имеет названия.'
    );

    Assert::notEmpty(
      $field->value,
      'Дополнительное поле не имеет значения.'
    );

    return $field->name .'|'. $field->value;
  }

  /**
   * From video.
   *
   * @param  Video $video
   * @return array
   */
  public function fromVideo(
    Video $video
  ) {
    $videoFields = ContainerFactory::fromNamespace(
      $this->container,
      'video_field',
      true
    );

    $fields = [];

    foreach ($videoFields as $field => $name) {

      if (!$name)
        continue;

      $propertyName = Utils::snakeToCamel($field);
      $value = $video->$propertyName;

      if (is_array($value))
        $value = implode(', ', $value);

      $isNumeric = is_numeric($value);
      $isNotEmptyString = $value && is_string($value);

      if (!$isNumeric && !$isNotEmptyString)
        continue;

      $newField = new ExtraField();
      $newField->name = $name;
      $newField->value = $value;

      $fields[] = $newField;
    }

    return $fields;
  }
}
