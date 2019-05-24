<?php

namespace Kinocomplete\Test\FeedLoader;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\ExtraField\ExtraFieldFactory;
use Kinocomplete\Container\ContainerFactory;
use Kinocomplete\FeedPost\FeedPostFactory;
use Kinocomplete\FeedLoader\PostProcessor;
use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\ExtraField\ExtraField;
use Kinocomplete\Container\Container;
use Kinocomplete\FeedPost\FeedPost;
use Kinocomplete\Post\PostFactory;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Source\Source;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;
use Kinocomplete\Post\Post;

class PostProcessorTest extends TestCase
{
  use ContainerTrait;

  /**
   * Testing "isVideoArrayInPosts" method.
   */
  public function testCanIsVideoArrayInPosts()
  {
    $video = new Video();

    $videoFactory = function () use ($video) {
      return $video;
    };

    $source = $this->getMockBuilder(
      Source::class
    )->getMock();

    $source
      ->expects($this->once())
      ->method('getVideoFactory')
      ->willReturn($videoFactory);

    $systemApi = $this->getMockBuilder(
      SystemApi::class
    )->setConstructorArgs([
      $this->getContainer()
    ])->getMock();

    $systemApi
      ->expects($this->once())
      ->method('isVideoInPosts')
      ->with($video)
      ->willReturn(true);

    $postFactory = $this->getMockBuilder(
      PostFactory::class
    )->setConstructorArgs([
      $this->getContainer()
    ])->getMock();

    $feedPostFactory = $this->getMockBuilder(
      FeedPostFactory::class
    )->setConstructorArgs([
      $this->getContainer()
    ])->getMock();

    $container = new Container([
      'system_api' => $systemApi,
      'post_factory' => $postFactory,
      'feed_post_factory' => $feedPostFactory
    ]);

    $instance = new PostProcessor(
      $container,
      $source
    );

    Assert::true(
      $instance->isVideoArrayInPosts([])
    );
  }

  /**
   * Testing "addPostFromVideoArray" method.
   *
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCanAddPostFromVideoArray()
  {
    $video = new Video();
    $video->id = Utils::randomString();

    $post = new Post();
    $feedPost = new FeedPost();

    $videoFactory = function () use ($video) {
      return $video;
    };

    $source = $this->getMockBuilder(
      Source::class
    )->getMock();

    $source
      ->expects($this->once())
      ->method('getVideoFactory')
      ->willReturn($videoFactory);

    $systemApi = $this->getMockBuilder(
      SystemApi::class
    )->setConstructorArgs([
      $this->getContainer()
    ])->getMock();

    $systemApi
      ->expects($this->once())
      ->method('addPost')
      ->with($post);

    $systemApi
      ->expects($this->once())
      ->method('removeFeedPosts')
      ->with(['videoId' => $feedPost->videoId]);

    $systemApi
      ->expects($this->once())
      ->method('addFeedPost')
      ->with($feedPost);

    $postFactory = $this->getMockBuilder(
      PostFactory::class
    )->setConstructorArgs([
      $this->getContainer()
    ])->getMock();

    $postFactory
      ->expects($this->once())
      ->method('fromVideo')
      ->with(
        $video,
        CategoryFactory::CREATE_NOT_EXISTED
      )->willReturn($post);

    $feedPostFactory = $this->getMockBuilder(
      FeedPostFactory::class
    )->setConstructorArgs([
      $this->getContainer()
    ])->getMock();

    $feedPostFactory
      ->expects($this->once())
      ->method('fromPostAndVideo')
      ->with(
        $post,
        $video
      )->willReturn($feedPost);

    $container = new Container([
      'system_api' => $systemApi,
      'post_factory' => $postFactory,
      'feed_post_factory' => $feedPostFactory,
      'images_auto_download' => false,
      'torrents_auto_download' => false
    ]);

    $instance = new PostProcessor(
      $container,
      $source
    );

    $instance->addPostFromVideoArray([]);
  }

  public function testCanGetPostsByVideoArray()
  {
    $video = new Video();
    $post = new Post();

    $videoFactory = function () use ($video) {
      return $video;
    };

    $source = $this->getMockBuilder(
      Source::class
    )->getMock();

    $source
      ->expects($this->once())
      ->method('getVideoFactory')
      ->willReturn($videoFactory);

    $systemApi = $this->getMockBuilder(SystemApi::class)
      ->disableOriginalConstructor()
      ->getMock();

    $systemApi
      ->expects($this->once())
      ->method('getPostsByVideo')
      ->with($video)
      ->willReturn([$post]);

    $container = new Container([
      'system_api' => $systemApi
    ]);

    $instance = new PostProcessor(
      $container,
      $source
    );

    $posts = $instance->getPostsByVideoArray([]);

    Assert::count($posts, 1);

    Assert::isInstanceOf(
      $posts[0],
      Post::class
    );
  }

  /**
   * Testing "updatePostFromVideoArray" method.
   */
  public function testCanUpdatePostByVideoArray()
  {
    /** @var Post $updatedPost */
    $updatedPost = null;

    $post = new Post();
    $post->title = Utils::randomString();
    $post->shortStory = Utils::randomString();
    $post->fullStory = Utils::randomString();

    $firstField = new ExtraField();
    $firstField->name = Utils::randomString();
    $firstField->value = Utils::randomString();

    $secondField = new ExtraField();
    $secondField->name = Utils::randomString();
    $secondField->value = Utils::randomString();

    $post->extraFields = [
      $firstField,
      $secondField
    ];

    /** @var Source $source */
    $source = $this->getContainer()->get('moonwalk_source');

    $systemApi = $this->getMockBuilder(SystemApi::class)
      ->disableOriginalConstructor()
      ->getMock();

    $systemApi
      ->expects($this->once())
      ->method('updatePost')
      ->willReturnCallback(function (
        Post $result
      ) use (&$updatedPost) {
        $updatedPost = $result;
      });

    $videoFields = ContainerFactory::fromNamespace(
      $this->getContainer(),
      'video_field',
      true,
      true
    );

    $videoFields['video_field_world_title'] = $firstField->name;

    $extraFieldFactory = new ExtraFieldFactory(
      new Container($videoFields)
    );

    $postPatterns = ContainerFactory::fromNamespace(
      $this->getContainer(),
      'post_pattern',
      true,
      true
    );

    $postFactory = new PostFactory(
      new Container(
        [
          'extra_fields'                 => $post->extraFields,
          'categories_from_video_type'   => '',
          'categories_from_video_genres' => '',
          'categories_case'              => '',
          'category_factory'             => $this->getContainer()->get('category_factory'),
          'extra_field_factory'          => $extraFieldFactory,
          'action_user'                  => $this->getContainer()->get('action_user')
        ]
        + $videoFields
        + $postPatterns
      )
    );

    $container = new Container(
      [
        'system_api' => $systemApi,
        'post_factory' => $postFactory,
        'post_updater_new_date' => 0,
        'post_updater_video_fields' => [],
        'images_auto_download' => false,
        'torrents_auto_download' => false
      ]
      + $videoFields
    );

    $instance = new PostProcessor(
      $container,
      $source
    );

    $videoArrayJson = file_get_contents(
      FIXTURES_DIR .'/video/moonwalk-video.json'
    );

    $videoArray = json_decode($videoArrayJson, true);

    $updated = $instance->updatePostByVideoArray(
      $post,
      $videoArray
    );

    Assert::true($updated);

    $videoFactory = $source->getVideoFactory();
    $video = $videoFactory($videoArray);

    Assert::same(
      $updatedPost->date,
      $post->date
    );

    Assert::same(
      $updatedPost->title,
      $video->title
    );

    Assert::same(
      $updatedPost->shortStory,
      $video->description
    );

    Assert::same(
      $updatedPost->fullStory,
      $video->description
    );

    Assert::count($updatedPost->extraFields, 2);

    Assert::same(
      $updatedPost->extraFields[0]->name,
      $firstField->name
    );

    Assert::same(
      $updatedPost->extraFields[0]->value,
      $video->worldTitle
    );

    Assert::same(
      $updatedPost->extraFields[1]->name,
      $secondField->name
    );

    Assert::same(
      $updatedPost->extraFields[1]->value,
      $secondField->value
    );
  }

  /**
   * Testing "updatePostFromVideoArray" method with new date.
   */
  public function testCanUpdatePostByVideoArrayWithNewDate()
  {
    /** @var Post $updatedPost */
    $updatedPost = null;

    $post = new Post();
    $post->date = '1970-01-01 00:00:01';
    $post->title = Utils::randomString();

    /** @var Source $source */
    $source = $this->getContainer()->get('moonwalk_source');

    $systemApi = $this->getMockBuilder(SystemApi::class)
      ->disableOriginalConstructor()
      ->getMock();

    $systemApi
      ->expects($this->once())
      ->method('updatePost')
      ->willReturnCallback(function (
        Post $result
      ) use (&$updatedPost) {
        $updatedPost = $result;
      });

    $extraFieldFactory = new ExtraFieldFactory(
      new Container([])
    );

    $postPatterns = ContainerFactory::fromNamespace(
      $this->getContainer(),
      'post_pattern',
      true,
      true
    );

    $postFactory = new PostFactory(
      new Container(
        [
          'extra_fields'                 => [],
          'categories_from_video_type'   => '',
          'categories_from_video_genres' => '',
          'categories_case'              => '',
          'category_factory'             => $this->getContainer()->get('category_factory'),
          'extra_field_factory'          => $extraFieldFactory,
          'action_user'                  => $this->getContainer()->get('action_user')
        ]
        + $postPatterns
      )
    );

    $videoFields = ContainerFactory::fromNamespace(
      $this->getContainer(),
      'video_field',
      true,
      true
    );

    $container = new Container(
      [
        'system_api' => $systemApi,
        'post_factory' => $postFactory,
        'post_updater_new_date' => 1,
        'post_updater_video_fields' => [],
        'images_auto_download' => false,
        'torrents_auto_download' => false
      ]
      + $videoFields
    );

    $instance = new PostProcessor(
      $container,
      $source
    );

    $videoArrayJson = file_get_contents(
      FIXTURES_DIR .'/video/moonwalk-video.json'
    );

    $videoArray = json_decode($videoArrayJson, true);

    $updated = $instance->updatePostByVideoArray(
      $post,
      $videoArray
    );

    Assert::true($updated);

    $videoFactory = $source->getVideoFactory();
    $video = $videoFactory($videoArray);

    Assert::notEq(
      $updatedPost->date,
      $post->date
    );

    Assert::same(
      $updatedPost->title,
      $video->title
    );
  }

  /**
   * Testing "updatePostFromVideoArray" method by title.
   */
  public function testCanUpdatePostByVideoArrayByTitle()
  {
    /** @var Post $updatedPost */
    $updatedPost = null;

    $post = new Post();
    $post->title = Utils::randomString();
    $post->shortStory = Utils::randomString();
    $post->fullStory = Utils::randomString();

    $firstField = new ExtraField();
    $firstField->name = Utils::randomString();
    $firstField->value = Utils::randomString();

    $secondField = new ExtraField();
    $secondField->name = Utils::randomString();
    $secondField->value = Utils::randomString();

    $post->extraFields = [
      $firstField,
      $secondField
    ];

    /** @var Source $source */
    $source = $this->getContainer()->get('moonwalk_source');

    $systemApi = $this->getMockBuilder(SystemApi::class)
      ->disableOriginalConstructor()
      ->getMock();

    $systemApi
      ->expects($this->once())
      ->method('updatePost')
      ->willReturnCallback(function (
        Post $result
      ) use (&$updatedPost) {
        $updatedPost = $result;
      });

    $containerProperties = [
      'system_api' => $systemApi,
      'post_factory' => $this->getContainer()->get('post_factory'),
      'post_updater_new_date' => 0,
      'post_updater_video_fields' => ['title'],
      'images_auto_download' => false,
      'torrents_auto_download' => false
    ];

    $videoFields = ContainerFactory::fromNamespace(
      $this->getContainer(),
      'video_field',
      true,
      true
    );

    $container = new Container(
      $containerProperties
      + $videoFields
    );

    $instance = new PostProcessor(
      $container,
      $source
    );

    $videoArrayJson = file_get_contents(
      FIXTURES_DIR .'/video/moonwalk-video.json'
    );

    $videoArray = json_decode($videoArrayJson, true);

    $updated = $instance->updatePostByVideoArray(
      $post,
      $videoArray
    );

    Assert::true($updated);

    $videoFactory = $source->getVideoFactory();
    $video = $videoFactory($videoArray);

    Assert::same(
      $updatedPost->date,
      $post->date
    );

    Assert::same(
      $updatedPost->title,
      $video->title
    );

    Assert::same(
      $updatedPost->shortStory,
      $post->shortStory
    );

    Assert::same(
      $updatedPost->fullStory,
      $post->fullStory
    );

    Assert::count($updatedPost->extraFields, 2);

    Assert::same(
      $updatedPost->extraFields[0]->name,
      $firstField->name
    );

    Assert::same(
      $updatedPost->extraFields[0]->value,
      $firstField->value
    );

    Assert::same(
      $updatedPost->extraFields[1]->name,
      $secondField->name
    );

    Assert::same(
      $updatedPost->extraFields[1]->value,
      $secondField->value
    );
  }

  /**
   * Testing "updatePostFromVideoArray" method by title with new date.
   */
  public function testCanUpdatePostByVideoArrayByTitleWithNewDate()
  {
    /** @var Post $updatedPost */
    $updatedPost = null;

    $post = new Post();
    $post->date = '1970-01-01 00:00:01';
    $post->title = Utils::randomString();

    /** @var Source $source */
    $source = $this->getContainer()->get('moonwalk_source');

    $systemApi = $this->getMockBuilder(SystemApi::class)
      ->disableOriginalConstructor()
      ->getMock();

    $systemApi
      ->expects($this->once())
      ->method('updatePost')
      ->willReturnCallback(function (
        Post $result
      ) use (&$updatedPost) {
        $updatedPost = $result;
      });

    $containerProperties = [
      'system_api' => $systemApi,
      'post_factory' => $this->getContainer()->get('post_factory'),
      'post_updater_new_date' => 1,
      'post_updater_video_fields' => ['title'],
      'images_auto_download' => false,
      'torrents_auto_download' => false
    ];

    $videoFields = ContainerFactory::fromNamespace(
      $this->getContainer(),
      'video_field',
      true,
      true
    );

    $container = new Container(
      $containerProperties
      + $videoFields
    );

    $instance = new PostProcessor(
      $container,
      $source
    );

    $videoArrayJson = file_get_contents(
      FIXTURES_DIR .'/video/moonwalk-video.json'
    );

    $videoArray = json_decode($videoArrayJson, true);

    $updated = $instance->updatePostByVideoArray(
      $post,
      $videoArray
    );

    Assert::true($updated);

    $videoFactory = $source->getVideoFactory();
    $video = $videoFactory($videoArray);

    Assert::notEq(
      $updatedPost->date,
      $post->date
    );

    Assert::same(
      $updatedPost->title,
      $video->title
    );
  }

  /**
   * Testing "updatePostFromVideoArray" method by description.
   */
  public function testCanUpdatePostByVideoArrayByDescription()
  {
    /** @var Post $updatedPost */
    $updatedPost = null;

    $post = new Post();
    $post->title = Utils::randomString();
    $post->shortStory = Utils::randomString();
    $post->fullStory = Utils::randomString();

    $firstField = new ExtraField();
    $firstField->name = Utils::randomString();
    $firstField->value = Utils::randomString();

    $secondField = new ExtraField();
    $secondField->name = Utils::randomString();
    $secondField->value = Utils::randomString();

    $post->extraFields = [
      $firstField,
      $secondField
    ];

    /** @var Source $source */
    $source = $this->getContainer()->get('moonwalk_source');

    $systemApi = $this->getMockBuilder(SystemApi::class)
      ->disableOriginalConstructor()
      ->getMock();

    $systemApi
      ->expects($this->once())
      ->method('updatePost')
      ->willReturnCallback(function (
        Post $result
      ) use (&$updatedPost) {
        $updatedPost = $result;
      });

    $containerProperties = [
      'system_api' => $systemApi,
      'post_factory' => $this->getContainer()->get('post_factory'),
      'post_updater_new_date' => 0,
      'post_updater_video_fields' => ['description'],
      'images_auto_download' => false,
      'torrents_auto_download' => false
    ];

    $videoFields = ContainerFactory::fromNamespace(
      $this->getContainer(),
      'video_field',
      true,
      true
    );

    $container = new Container(
      $containerProperties
      + $videoFields
    );

    $instance = new PostProcessor(
      $container,
      $source
    );

    $videoArrayJson = file_get_contents(
      FIXTURES_DIR .'/video/moonwalk-video.json'
    );

    $videoArray = json_decode($videoArrayJson, true);

    $updated = $instance->updatePostByVideoArray(
      $post,
      $videoArray
    );

    Assert::true($updated);

    $videoFactory = $source->getVideoFactory();
    $video = $videoFactory($videoArray);

    Assert::same(
      $updatedPost->date,
      $post->date
    );

    Assert::same(
      $updatedPost->title,
      $post->title
    );

    Assert::same(
      $updatedPost->shortStory,
      $video->description
    );

    Assert::same(
      $updatedPost->fullStory,
      $video->description
    );

    Assert::count($updatedPost->extraFields, 2);

    Assert::same(
      $updatedPost->extraFields[0]->name,
      $firstField->name
    );

    Assert::same(
      $updatedPost->extraFields[0]->value,
      $firstField->value
    );

    Assert::same(
      $updatedPost->extraFields[1]->name,
      $secondField->name
    );

    Assert::same(
      $updatedPost->extraFields[1]->value,
      $secondField->value
    );
  }

  /**
   * Testing "updatePostFromVideoArray" method by extra fields.
   */
  public function testCanUpdatePostByVideoArrayByExtraFields()
  {
    /** @var Post $updatedPost */
    $updatedPost = null;

    $post = new Post();
    $post->title = Utils::randomString();
    $post->shortStory = Utils::randomString();
    $post->fullStory = Utils::randomString();

    $firstField = new ExtraField();
    $firstField->name = Utils::randomString();
    $firstField->value = Utils::randomString();

    $secondField = new ExtraField();
    $secondField->name = Utils::randomString();
    $secondField->value = Utils::randomString();

    $post->extraFields = [
      $firstField,
      $secondField
    ];

    /** @var Source $source */
    $source = $this->getContainer()->get('moonwalk_source');

    $systemApi = $this->getMockBuilder(SystemApi::class)
      ->disableOriginalConstructor()
      ->getMock();

    $systemApi
      ->expects($this->once())
      ->method('updatePost')
      ->willReturnCallback(function (
        Post $result
      ) use (&$updatedPost) {
        $updatedPost = $result;
      });

    $videoFields = ContainerFactory::fromNamespace(
      $this->getContainer(),
      'video_field',
      true,
      true
    );

    $videoFields['video_field_world_title'] = $firstField->name;

    $extraFieldFactory = new ExtraFieldFactory(
      new Container($videoFields)
    );

    $postPatterns = ContainerFactory::fromNamespace(
      $this->getContainer(),
      'post_pattern',
      true,
      true
    );

    $postFactory = new PostFactory(
      new Container(
        [
          'extra_fields'                 => $post->extraFields,
          'categories_from_video_type'   => '',
          'categories_from_video_genres' => '',
          'categories_case'              => '',
          'category_factory'             => $this->getContainer()->get('category_factory'),
          'extra_field_factory'          => $extraFieldFactory,
          'action_user'                  => $this->getContainer()->get('action_user')
        ]
        + $videoFields
        + $postPatterns
      )
    );

    $container = new Container(
      [
        'system_api' => $systemApi,
        'post_factory' => $postFactory,
        'post_updater_new_date' => 0,
        'post_updater_video_fields' => ['world_title'],
        'images_auto_download' => false,
        'torrents_auto_download' => false
      ]
      + $videoFields
    );

    $instance = new PostProcessor(
      $container,
      $source
    );

    $videoArrayJson = file_get_contents(
      FIXTURES_DIR .'/video/moonwalk-video.json'
    );

    $videoArray = json_decode($videoArrayJson, true);

    $updated = $instance->updatePostByVideoArray(
      $post,
      $videoArray
    );

    Assert::true($updated);

    $videoFactory = $source->getVideoFactory();
    $video = $videoFactory($videoArray);

    Assert::same(
      $updatedPost->date,
      $post->date
    );

    Assert::same(
      $updatedPost->title,
      $post->title
    );

    Assert::same(
      $updatedPost->shortStory,
      $post->shortStory
    );

    Assert::same(
      $updatedPost->fullStory,
      $post->fullStory
    );

    Assert::count($updatedPost->extraFields, 2);

    Assert::same(
      $updatedPost->extraFields[0]->name,
      $firstField->name
    );

    Assert::same(
      $updatedPost->extraFields[0]->value,
      $video->worldTitle
    );

    Assert::same(
      $updatedPost->extraFields[1]->name,
      $secondField->name
    );

    Assert::same(
      $updatedPost->extraFields[1]->value,
      $secondField->value
    );
  }
}
