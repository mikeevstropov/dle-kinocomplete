<?php

namespace Kinocomplete\Post;

use Kinocomplete\ExtraField\ExtraFieldFactory;
use Kinocomplete\Service\DefaultService;
use Kinocomplete\ExtraField\ExtraField;
use Kinocomplete\Templating\Templating;
use Kinocomplete\Category\Category;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;
use Cocur\Slugify\Slugify;

class PostFactory extends DefaultService
{
  /**
   * Create instance from an array
   * received from Database response.
   *
   * @param  array $array
   * @return Post
   * @throws \Exception
   */
  public function fromDatabaseArray(
    array $array
  ) {
    $extraFields = $this->container->get('extra_fields');
    $categoryFactory = $this->container->get('category_factory');

    $post = new Post();

    // Field "id".
    if (array_key_exists('id', $array)) {

      if (
        $array['id'] &&
        is_string($array['id'])
      ) $post->id = $array['id'];
    }

    // Field "slug".
    if (array_key_exists('alt_name', $array)) {

      if (is_string($array['alt_name']))
        $post->slug = $array['alt_name'];
    }

    // Field "title".
    if (array_key_exists('title', $array)) {

      if (is_string($array['title']))
        $post->title = $array['title'];
    }

    // Field "author".
    if (array_key_exists('autor', $array)) {

      if (is_string($array['autor']))
        $post->author = $array['autor'];
    }

    // Field "date".
    if (array_key_exists('date', $array)) {

      if (
        $array['date'] &&
        is_string($array['date'])
      ) {

        $valid = (bool) \DateTime::createFromFormat(
          'Y-m-d H:i:s',
          $array['date']
        );

        if (!$valid)
          throw new \Exception(
            'Дата новости не соответствует шаблону.'
          );

        $post->date = $array['date'];
      }
    }

    // Field "shortStory".
    if (array_key_exists('short_story', $array)) {

      if (is_string($array['short_story']))
        $post->shortStory = $array['short_story'];
    }

    // Field "fullStory".
    if (array_key_exists('full_story', $array)) {

      if (is_string($array['full_story']))
        $post->fullStory = $array['full_story'];
    }

    // Field "metaTitle".
    if (array_key_exists('metatitle', $array)) {

      if (is_string($array['metatitle']))
        $post->metaTitle = $array['metatitle'];
    }

    // Field "metaDescription".
    if (array_key_exists('descr', $array)) {

      if (is_string($array['descr']))
        $post->metaDescription = $array['descr'];
    }

    // Field "metaKeywords".
    if (array_key_exists('keywords', $array)) {

      if (
        $array['keywords'] &&
        is_string($array['keywords'])
      ) {

        $keywords = explode(
          ',',
          $array['keywords']
        );

        $post->metaKeywords = array_map(
          'trim',
          $keywords
        );
      }
    }

    // Field "tags".
    if (array_key_exists('tags', $array)) {

      if (
        $array['tags'] &&
        is_string($array['tags'])
      ) {

        $tags = explode(
          ',',
          $array['tags']
        );

        $post->tags = array_map(
          'trim',
          $tags
        );
      }
    }

    // Field "categories".
    if (array_key_exists('category', $array)) {

      if (
        $array['category'] &&
        is_string($array['category'])
      ) {

        $post->categories = $categoryFactory->fromDatabaseString(
          $array['category']
        );
      }
    }

    // Field "extraFields".
    if (array_key_exists('xfields', $array)) {

      if (
        $array['xfields'] &&
        is_string($array['xfields'])
      ) {

        /** @var ExtraFieldFactory $extraFieldFactory */
        $extraFieldFactory = $this->container->get('extra_field_factory');

        $post->extraFields = $extraFieldFactory->fromValues(
          $array['xfields'],
          $extraFields,
          true
        );
      }
    }

    // Field "commentsAllowed".
    if (array_key_exists('allow_comm', $array)) {

      if ($array['allow_comm'] !== null)
        $post->commentsAllowed = (bool) $array['allow_comm'];
    }

    // Field "mainAllowed".
    if (array_key_exists('allow_main', $array)) {

      if ($array['allow_main'] !== null)
        $post->mainAllowed = (bool) $array['allow_main'];
    }

    // Field "published".
    if (array_key_exists('approve', $array)) {

      if ($array['approve'] !== null)
        $post->published = (bool) $array['approve'];
    }

    // Field "fixed".
    if (array_key_exists('fixed', $array)) {

      if ($array['fixed'] !== null)
        $post->fixed = (bool) $array['fixed'];
    }

    return $post;
  }

  /**
   * Convert instance to Database array.
   *
   * @param Post $post
   * @return array
   */
  public function toDatabaseArray(
    Post $post
  ) {
    // Checking categories ID.
    foreach ($post->categories as $category) {

      Assert::notEmpty(
        $category->id,
        'Отсутствует идентификатор категории.'
      );
    }

    // Categories identifiers.
    $categoriesMapper = function(Category $category) {
      return $category->id;
    };

    $categoriesIds = array_map(
      $categoriesMapper,
      $post->categories
    );

    // Extra fields.
    $extraFieldsMapper = function(ExtraField $field) {
      return $field->name .'|'. $field->value;
    };

    $extraFields = array_map(
      $extraFieldsMapper,
      $post->extraFields
    );

    $array = [
      'alt_name'    => $post->slug,
      'title'       => $post->title,
      'autor'       => $post->author,
      'date'        => $post->date,
      'short_story' => $post->shortStory,
      'full_story'  => $post->fullStory,
      'metatitle'   => $post->metaTitle,
      'descr'       => $post->metaDescription,
      'keywords'    => implode(', ', $post->metaKeywords),
      'tags'        => implode(', ', $post->tags),
      'category'    => implode(',', $categoriesIds),
      'xfields'     => implode('||', $extraFields),
      'allow_comm'  => (int) $post->commentsAllowed,
      'allow_main'  => (int) $post->mainAllowed,
      'approve'     => (int) $post->published,
      'fixed'       => (int) $post->fixed
    ];

    if ($post->id)
      $array['id'] = $post->id;

    return $array;
  }

  /**
   * Convert instance from Video.
   *
   * @param  Video $video
   * @param  int $categoriesCreationMode
   * @return Post
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function fromVideo(
    Video $video,
    $categoriesCreationMode = 0
  ) {
    $author                    = $this->container->get('action_user')->name;
    $titlePattern              = $this->container->get('post_pattern_title');
    $shortStoryPattern         = $this->container->get('post_pattern_short_story');
    $fullStoryPattern          = $this->container->get('post_pattern_full_story');
    $metaTitlePattern          = $this->container->get('post_pattern_meta_title');
    $metaDescriptionPattern    = $this->container->get('post_pattern_meta_description');
    $metaKeywordsPattern       = $this->container->get('post_pattern_meta_keywords');
    $categoriesFromVideoType   = (bool) $this->container->get('categories_from_video_type');
    $categoriesFromVideoGenres = (bool) $this->container->get('categories_from_video_genres');
    $categoriesCase            = (int) $this->container->get('categories_case');

    $post = new Post();

    $slugify = new Slugify();
    $slugify->activateRuleSet('russian');

    // Field "slug".
    $post->slug = $slugify->slugify($video->title);

    // Field "title".
    $post->title = Templating::renderString(
      $titlePattern,
      (array) $video
    );

    // Field "author".
    $post->author = $author;

    // Field "date".
    $post->date = date("Y-m-d H:i:s");

    // Field "shortStory".
    $post->shortStory = Templating::renderString(
      $shortStoryPattern,
      (array) $video
    );

    // Field "fullStory".
    $post->fullStory = Templating::renderString(
      $fullStoryPattern,
      (array) $video
    );

    // Field "title".
    $post->metaTitle = Templating::renderString(
      $metaTitlePattern,
      (array) $video
    );

    // Field "metaDescription".
    $post->metaDescription = Templating::renderString(
      $metaDescriptionPattern,
      (array) $video
    );

    // Field "metaKeywords".
    $metaKeywords = Templating::renderString(
      $metaKeywordsPattern,
      (array) $video
    );

    $metaKeywords = explode(
      ',',
      $metaKeywords
    );

    $post->metaKeywords = array_map(
      'trim',
      $metaKeywords
    );

    // Field "categories".
    $post->categories = $video->getCategories(
      $this->container->get('category_factory'),
      $categoriesFromVideoType,
      $categoriesFromVideoGenres,
      $categoriesCreationMode,
      $categoriesCase
    );

    /** @var ExtraFieldFactory $extraFieldFactory */
    $extraFieldFactory = $this->container->get('extra_field_factory');

    // Extra fields.
    $post->extraFields = $extraFieldFactory->fromVideo($video);

    return $post;
  }
}
