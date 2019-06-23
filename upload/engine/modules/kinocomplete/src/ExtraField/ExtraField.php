<?php

namespace Kinocomplete\ExtraField;

class ExtraField
{

  const TEXT_TYPE     = 'text';
  const TEXTAREA_TYPE = 'textarea';
  const SELECT_TYPE   = 'select';
  const IMAGE_TYPE    = 'image';
  const GALLERY_TYPE  = 'imagegalery';
  const FILE_TYPE     = 'file';
  const BOOLEAN_TYPE  = 'yesorno';

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $label;

  /**
   * @var string
   */
  public $type;

  /**
   * @var string
   */
  public $value;

  /**
   * @var bool
   */
  public $linked = false;
}
