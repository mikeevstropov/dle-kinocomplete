<?php

namespace Kinocomplete\Test\Post;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\ExtraField\ExtraField;
use Kinocomplete\Container\Container;
use Kinocomplete\Category\Category;
use Kinocomplete\Post\PostFactory;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;
use Kinocomplete\Post\Post;

class PostFactoryTest extends TestCase
{
  use ContainerTrait;

  /**
   * Testing `fromDatabaseArray` method.
   *
   * @throws \Exception
   */
  public function testCanFromDatabaseArray()
  {
    $array = [
      'id'          => '1',
      'alt_name'    => 'alt-post',
      'title'       => 'post',
      'autor'       => 'author',
      'date'        => '2018-09-28 14:37:26',
      'short_story' => 'shortStory',
      'full_story'  => 'fullStory',
      'metatitle'   => 'metaTitle',
      'descr'       => 'description',
      'keywords'    => 'firstKeyword, secondKeyword',
      'tags'        => 'firstTag, secondTag',
      'category'    => '1,2',
      'xfields'     => 'firstField|1||secondField|2',
      'allow_comm'  => 1,
      'allow_main'  => 1,
      'approve'     => 1,
      'fixed'       => 1
    ];

    $expected = [
      'id'              => '1',
      'slug'            => 'alt-post',
      'title'           => 'post',
      'author'          => 'author',
      'date'            => '2018-09-28 14:37:26',
      'shortStory'      => 'shortStory',
      'fullStory'       => 'fullStory',
      'metaTitle'       => 'metaTitle',
      'metaDescription' => 'description',
      'metaKeywords'    => 'firstKeyword, secondKeyword',
      'tags'            => 'firstTag, secondTag',
      'categories'      => '1,2',
      'extraFields'     => 'firstField|1||secondField|2',
      'commentsAllowed' => true,
      'mainAllowed'     => true,
      'published'       => true,
      'fixed'           => true
    ];

    $firstCategory      = new Category();
    $firstCategory->id  = '1';
    $secondCategory     = new Category();
    $secondCategory->id = '2';

    $categories = [
      $firstCategory,
      $secondCategory
    ];

    $firstExtraField = new ExtraField();
    $firstExtraField->name = 'firstField';
    $firstExtraField->label = 'First field';
    $firstExtraField->type = ExtraField::TEXT_TYPE;
    $firstExtraField->value = '1';

    $secondExtraField = new ExtraField();
    $secondExtraField->name = 'secondField';
    $secondExtraField->label = 'Second field';
    $secondExtraField->type = ExtraField::TEXT_TYPE;
    $secondExtraField->value = '2';

    $extraFields = [
      $firstExtraField,
      $secondExtraField
    ];

    $categoryFactory = new CategoryFactory(
      new Container([
        'categories' => $categories,
      ])
    );

    $container = new Container([
      'extra_fields' => $extraFields,
      'category_factory' => $categoryFactory,
      'extra_field_factory' => $this->getContainer()->get('extra_field_factory')
    ]);

    $postFactory = new PostFactory(
      $container
    );

    $post = $postFactory->fromDatabaseArray(
      $array
    );

    $postArray = (array) $post;

    $postArray['metaKeywords'] = implode(', ', $post->metaKeywords);
    $postArray['tags'] = implode(', ', $post->tags);

    $postArray['categories'] = array_reduce(
      $post->categories,
      function ($stack, Category $item) {
        $stack .= $stack
          ? ','. $item->id
          : $item->id;
        return $stack;
      },
      ''
    );

    $postArray['extraFields'] = array_reduce(
      $post->extraFields,
      function ($stack, ExtraField $item) {
        $value = $item->name .'|'. $item->value;
        $stack .= $stack ? '||'. $value : $value;
        return $stack;
      },
      ''
    );

    $this->assertEquals(
      $expected,
      $postArray
    );
  }

  /**
   * Testing `toDatabaseArray` method.
   */
  public function testCanToDatabaseArray()
  {
    $array = [
      'id'          => '1',
      'alt_name'    => 'alt-post',
      'title'       => 'post',
      'autor'       => 'author',
      'date'        => '2018-09-28 14:37:26',
      'short_story' => 'shortStory',
      'full_story'  => 'fullStory',
      'metatitle'   => 'metaTitle',
      'descr'       => 'description',
      'keywords'    => 'firstKeyword, secondKeyword',
      'tags'        => 'firstTag, secondTag',
      'category'    => '1,2',
      'xfields'     => 'firstField|1||secondField|2',
      'allow_comm'  => 1,
      'allow_main'  => 1,
      'approve'     => 1,
      'fixed'       => 1
    ];

    $firstCategory      = new Category();
    $firstCategory->id  = '1';
    $secondCategory     = new Category();
    $secondCategory->id = '2';

    $categories = [
      $firstCategory,
      $secondCategory,
    ];

    $firstExtraField = new ExtraField();
    $firstExtraField->name = 'firstField';
    $firstExtraField->label = 'First field';
    $firstExtraField->type = ExtraField::TEXT_TYPE;
    $firstExtraField->value = '1';

    $secondExtraField = new ExtraField();
    $secondExtraField->name = 'secondField';
    $secondExtraField->label = 'Second field';
    $secondExtraField->type = ExtraField::TEXT_TYPE;
    $secondExtraField->value = '2';

    $extraFields = [
      $firstExtraField,
      $secondExtraField
    ];

    $post                  = new Post();
    $post->id              = $array['id'];
    $post->slug            = $array['alt_name'];
    $post->title           = $array['title'];
    $post->author          = $array['autor'];
    $post->date            = $array['date'];
    $post->shortStory      = $array['short_story'];
    $post->fullStory       = $array['full_story'];
    $post->metaTitle       = $array['metatitle'];
    $post->metaDescription = $array['descr'];
    $post->metaKeywords    = ['firstKeyword', 'secondKeyword'];
    $post->tags            = ['firstTag', 'secondTag'];
    $post->categories      = $categories;
    $post->extraFields     = $extraFields;
    $post->commentsAllowed = true;
    $post->mainAllowed     = true;
    $post->published       = true;
    $post->fixed           = true;

    $container = new Container();

    $postFactory = new PostFactory(
      $container
    );

    $result = $postFactory->toDatabaseArray(
      $post
    );

    $this->assertEquals(
      $array,
      $result
    );
  }

  /**
   * Testing `toDatabaseArray` method exceptions.
   */
  public function testCannotToDatabaseArray()
  {
    $this->expectException(\InvalidArgumentException::class);

    $categoryWithoutId = new Category();
    $categoryWithoutId->name = 'categoryWithoutId';

    $post = new Post();
    $post->categories = [$categoryWithoutId];

    $postFactory = new PostFactory(
      new Container()
    );

    $postFactory->toDatabaseArray($post);
  }

  /**
   * Testing `fromVideo` method.
   *
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCanFromVideo()
  {
    $video = new Video();
    $video->title = 'Title';
    $video->description = 'Description';

    $container = new Container([
      'post_pattern_title'            => '{{title}}',
      'post_pattern_short_story'      => '{{description}}',
      'post_pattern_full_story'       => '{{description}}',
      'post_pattern_meta_title'       => '{{title}}',
      'post_pattern_meta_description' => '{{description}}',
      'post_pattern_meta_keywords'    => '{{title}}',
      'categories_from_video_type'    => false,
      'categories_from_video_genres'  => false,
      'categories_case'               => 0,
      'category_factory'              => $this->getContainer()->get('category_factory'),
      'extra_field_factory'           => $this->getContainer()->get('extra_field_factory'),
      'action_user'                   => $this->getContainer()->get('action_user')
    ]);

    $postFactory = new PostFactory(
      $container
    );

    $post = $postFactory->fromVideo(
      $video
    );

    Assert::isInstanceOf(
      $post,
      Post::class
    );

    Assert::same(
      $post->slug,
      strtolower($video->title)
    );

    Assert::same(
      $post->title,
      $video->title
    );

    Assert::stringNotEmpty(
      $post->author
    );

    Assert::stringNotEmpty(
      $post->date
    );

    Assert::same(
      $post->shortStory,
      $video->description
    );

    Assert::same(
      $post->fullStory,
      $video->description
    );

    Assert::same(
      $post->metaTitle,
      $video->title
    );

    Assert::same(
      $post->metaDescription,
      $video->description
    );

    Assert::same(
      $post->metaKeywords,
      [$video->title]
    );

    Assert::count(
      $post->categories,
      0
    );
  }

  /**
   * Testing `fromVideo` method with
   * option "categoriesFromVideoType".
   *
   * @throws \Exception
   */
  public function testCanFromVideoWithCategoriesFromVideoType()
  {
    $categoryName = 'фильмы';

    $video = new Video();
    $video->type = Video::MOVIE_TYPE;

    $container = new Container([
      'post_pattern_title'            => '',
      'post_pattern_short_story'      => '',
      'post_pattern_full_story'       => '',
      'post_pattern_meta_title'       => '',
      'post_pattern_meta_description' => '',
      'post_pattern_meta_keywords'    => '',
      'categories_from_video_type'    => true,
      'categories_from_video_genres'  => false,
      'categories_case'               => DEFAULT_MODE,
      'category_factory'              => $this->getContainer()->get('category_factory'),
      'extra_field_factory'           => $this->getContainer()->get('extra_field_factory'),
      'action_user'                   => $this->getContainer()->get('action_user')
    ]);

    $postFactory = new PostFactory(
      $container
    );

    $post = $postFactory->fromVideo(
      $video
    );

    Assert::isInstanceOf(
      $post,
      Post::class
    );

    Assert::count(
      $post->categories,
      1
    );

    Assert::isInstanceOf(
      $post->categories[0],
      Category::class
    );

    Assert::same(
      mb_strtolower($post->categories[0]->name),
      $categoryName
    );
  }

  /**
   * Testing `fromVideo` method with
   * option "categoriesFromVideoGenres".
   *
   * @throws \Exception
   */
  public function testCanFromVideoWithCategoriesFromVideoGenres()
  {
    $categoryName = Utils::randomString();

    $video = new Video();
    $video->genres = [$categoryName];

    $container = new Container([
      'post_pattern_title'            => '',
      'post_pattern_short_story'      => '',
      'post_pattern_full_story'       => '',
      'post_pattern_meta_title'       => '',
      'post_pattern_meta_description' => '',
      'post_pattern_meta_keywords'    => '',
      'categories_from_video_type'    => false,
      'categories_from_video_genres'  => true,
      'categories_case'               => DEFAULT_MODE,
      'category_factory'              => $this->getContainer()->get('category_factory'),
      'extra_field_factory'           => $this->getContainer()->get('extra_field_factory'),
      'action_user'                   => $this->getContainer()->get('action_user')
    ]);

    $postFactory = new PostFactory(
      $container
    );

    $post = $postFactory->fromVideo(
      $video
    );

    Assert::isInstanceOf(
      $post,
      Post::class
    );

    Assert::count(
      $post->categories,
      1
    );

    Assert::isInstanceOf(
      $post->categories[0],
      Category::class
    );

    Assert::same(
      $post->categories[0]->name,
      $categoryName
    );
  }
}