<?php

namespace Kinocomplete\Templating;

use Kinocomplete\ExtraField\ExtraField;

class SystemTemplating
{
  /**
   * Resolve template tag [kc_xflist_field index="n"]
   *
   * @param  string $template
   * @param  array $extraFields
   * @return string
   */
  static public function resolveXFList($template, $extraFields)
  {
    $callback = function ($matches) use ($extraFields) {

      $name = $matches[1];
      $index = --$matches[2];

      $extraFieldFilter = function (ExtraField $field) use ($name) {
        return $field->name === $name;
      };

      $extraField = current(
        array_filter(
          $extraFields,
          $extraFieldFilter
        )
      );

      if (
        !$extraField ||
        !is_string($extraField->value)
      ) return '';

      $array = explode(',', $extraField->value);
      $array = array_map('trim', $array);

      return array_key_exists($index, $array)
        ? $array[$index]
        : '';
    };

    return preg_replace_callback(
      '/\[kc_xflist_((?!not|has)[\w_]+?)\sindex=\"(\d+?)\"\]/',
      $callback,
      $template
    );
  }

  /**
   * Resolve template block by following pattern
   * [kc_xflist_has_field index="1"][/kc_xflist_has_field]
   *
   * @param  string $template
   * @param  array $extraFields
   * @return string
   */
  static public function resolveXFListHas($template, $extraFields) {

    $callback = function ($matches) use ($extraFields) {

      $name     = $matches[1];
      $index    = $matches[2];
      $contents = $matches[3];
      $endName  = $matches[4];

      if ($name !== $endName)
        return $matches[0];

      $extraFieldFilter = function (ExtraField $field) use ($name) {
        return $field->name === $name;
      };

      $extraField = current(
        array_filter(
          $extraFields,
          $extraFieldFilter
        )
      );

      if (
        !$extraField ||
        !is_string($extraField->value)
      ) return '';

      $array = explode(',', $extraField->value);
      $array = array_map('trim', $array);

      $has = array_key_exists($index, $array)
        && $array[$index];

      return $has ? $contents : '';
    };

    return preg_replace_callback(
      '/\[kc_xflist_has_([\w_]+?)\sindex=\"(\d+?)\"\]([\S\s]+?)\[\/kc_xflist_has_([\w_]+?)\]/',
      $callback,
      $template
    );
  }

  /**
   * Resolve template block by following pattern
   * [kc_xflist_not_field index="1"][/kc_xflist_not_field]
   *
   * @param  string $template
   * @param  array $extraFields
   * @return string
   */
  static public function resolveXFListNot($template, $extraFields) {

    $callback = function ($matches) use ($extraFields) {

      $name     = $matches[1];
      $index    = $matches[2];
      $contents = $matches[3];
      $endName  = $matches[4];

      if ($name !== $endName)
        return $matches[0];

      $extraFieldFilter = function (ExtraField $field) use ($name) {
        return $field->name === $name;
      };

      $extraField = current(
        array_filter(
          $extraFields,
          $extraFieldFilter
        )
      );

      if (
        !$extraField ||
        !is_string($extraField->value)
      ) return $contents;

      $array = explode(',', $extraField->value);
      $array = array_map('trim', $array);

      $has = array_key_exists($index, $array)
        && $array[$index];

      return !$has ? $contents : '';
    };

    return preg_replace_callback(
      '/\[kc_xflist_not_([\w_]+?)\sindex=\"(\d+?)\"\]([\S\s]+?)\[\/kc_xflist_not_([\w_]+?)\]/',
      $callback,
      $template
    );
  }
}
