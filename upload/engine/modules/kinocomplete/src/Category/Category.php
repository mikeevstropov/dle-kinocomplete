<?php

namespace Kinocomplete\Category;

class Category
{
  /**
   * @var string
   */
  public $id = '';

  /**
   * @var string
   */
  public $parentId = '';

  /**
   * @var string
   */
  public $slug = '';

  /**
   * @var string
   */
  public $name = '';

  /**
   * @var int
   */
  public $position = 1;

  /**
   * @var bool
   */
  public $rssAllowed = true;

  /**
   * @var bool
   */
  public $searchAllowed = true;

}