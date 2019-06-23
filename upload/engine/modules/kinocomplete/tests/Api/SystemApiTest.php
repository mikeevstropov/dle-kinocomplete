<?php

namespace Kinocomplete\Test\Api;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Container\ContainerFactory;
use Kinocomplete\FeedPost\FeedPostFactory;
use Kinocomplete\ExtraField\ExtraField;
use Kinocomplete\Container\Container;
use Kinocomplete\FeedPost\FeedPost;
use Kinocomplete\Post\PostFactory;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Video\Video;
use Webmozart\PathUtil\Path;
use Webmozart\Assert\Assert;
use Kinocomplete\Post\Post;
use Medoo\Medoo;

class SystemApiTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var SystemApi
   */
  public $instance;

  /**
   * @var Medoo
   */
  public $database;

  /**
   * SystemApiTest constructor.
   *
   * @param null   $name
   * @param array  $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $container = $this->getContainer();

    $this->database = $this->getContainer()->get('database');
    $this->instance = new SystemApi($container);
  }

  /**
   * Instantiation testing.
   */
  public function testInstanceCreated()
  {
    Assert::isInstanceOf(
      $this->instance,
      SystemApi::class
    );
  }

  /**
   * Testing `getTableEngine` method.
   */
  public function testCanGetTableEngine()
  {
    Assert::oneOf(
      $this->instance->getTableEngine('post'),
      ['InnoDB', 'MyISAM']
    );
  }

  /**
   * Testing `clearSystemCache` method.
   *
   * @throws \Exception
   */
  public function testCanClearSystemCache()
  {
    $name = 'test';
    $fileName = 'test.php';
    $systemCacheDir = $this->getContainer()->get('system_cache_dir');

    $filePath = Path::join(
      $systemCacheDir,
      'system',
      $fileName
    );

    if (file_exists($filePath))
      unlink($filePath);

    touch($filePath);

    $trueResult = $this->instance->clearSystemCache($name);

    Assert::true($trueResult);

    Assert::false(
      file_exists($filePath)
    );

    $falseResult = $this->instance->clearSystemCache($name);

    Assert::false($falseResult);

    Assert::false(
      file_exists($filePath)
    );
  }

  /**
   * Testing `clearSystemCache` method exceptions.
   *
   * @throws \Exception
   */
  public function testCannotClearSystemCache()
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->instance->clearSystemCache('');
  }

  /**
   * Testing "isVideoInPosts" method by id.
   *
   * @throws NotFoundException
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCanIsVideoInPostsById()
  {
    /** @var PostFactory $postFactory */
    $postFactory = $this->getContainer()->get('post_factory');

    /** @var FeedPostFactory $feedPostFactory */
    $feedPostFactory = $this->getContainer()->get('feed_post_factory');

    $video = new Video();
    $video->id = Utils::randomString();
    $video->title = Utils::randomString();

    $post = $postFactory->fromVideo($video);
    $post = $this->instance->addPost($post);

    $feedPost = $feedPostFactory->fromPostAndVideo($post, $video);
    $feedPost = $this->instance->addFeedPost($feedPost);

    $has = $this->instance->isVideoInPosts(
      $video
    );

    Assert::true($has);

    $this->instance->removePost($post->id);

    $postExist = $this->instance->hasPosts([
      'id' => $post->id
    ]);

    Assert::false($postExist);

    $feedPostExist = $this->instance->hasFeedPosts([
      'id' => $feedPost->id
    ]);

    Assert::false($feedPostExist);
  }

  /**
   * Testing "isVideoInPosts" method by id without post.
   *
   * @throws \Kinocomplete\Exception\NotFoundException
   */
  public function testCannotIsVideoInPostsByIdWithoutPost()
  {
    $video = new Video();
    $video->id = Utils::randomString();

    $feedPost = new FeedPost();
    $feedPost->videoId = $video->id;
    $feedPost = $this->instance->addFeedPost($feedPost);

    $has = $this->instance->isVideoInPosts(
      $video
    );

    Assert::false($has);

    $this->instance->removeFeedPost($feedPost->id);

    $feedPostExist = $this->instance->hasFeedPosts([
      'id' => $feedPost->id
    ]);

    Assert::false($feedPostExist);
  }

  /**
   * Testing "isVideoInPosts" method by title.
   *
   * @throws \Kinocomplete\Exception\NotFoundException
   */
  public function testCanIsVideoInPostsByTitle()
  {
    $video = new Video();
    $video->title = Utils::randomString();

    $post = new Post();
    $post->title = $video->title;
    $post = $this->instance->addPost($post);

    $has = $this->instance->isVideoInPosts(
      $video
    );

    Assert::true($has);

    $this->instance->removePost($post->id);

    $has = $this->instance->isVideoInPosts(
      $video
    );

    Assert::false($has);
  }

  /**
   * Testing "isVideoInPosts" method by extra fields.
   *
   * @throws \Kinocomplete\Exception\NotFoundException
   */
  public function testCanIsVideoInPostsByExtraFields()
  {
    $video = new Video();
    $video->worldTitle = Utils::randomString();

    $extraField = new ExtraField();
    $extraField->name = 'WORLD-TITLE';
    $extraField->value = $video->worldTitle;

    $post = new Post();
    $post->extraFields[] = $extraField;
    $post = $this->instance->addPost($post);

    $videoFields = ContainerFactory::fromNamespace(
      $this->getContainer(),
      'video_field',
      true,
      true
    );

    $containerFields = [
      'database' => $this->getContainer()->get('database'),
      'post_accessory_video_fields' => $this->getContainer()->get('post_accessory_video_fields'),
      'extra_field_factory' => $this->getContainer()->get('extra_field_factory'),
      'video_field_world_title' => $extraField->name,
      'video_field_tagline' => Utils::randomString()
    ];

    $instance = new SystemApi(
      new Container(array_merge(
        $videoFields,
        $containerFields
      ))
    );

    $has = $instance->isVideoInPosts(
      $video
    );

    Assert::true($has);

    $this->instance->removePost($post->id);

    $has = $this->instance->isVideoInPosts(
      $video
    );

    Assert::false($has);
  }

  /**
   * Testing "isVideoInPosts" method by empty.
   *
   * @throws \Kinocomplete\Exception\NotFoundException
   */
  public function testCannotIsVideoInPostsByEmpty()
  {
    $video = new Video();

    $post = new Post();
    $post = $this->instance->addPost($post);

    $feedPost = new FeedPost();
    $feedPost->postId = $post->id;
    $feedPost = $this->instance->addFeedPost($feedPost);

    $has = $this->instance->isVideoInPosts($video);

    Assert::false($has);

    $this->instance->removePost($post->id);

    $postExist = $this->instance->hasPosts([
      'id' => $post->id
    ]);

    Assert::false($postExist);

    $feedPostExist = $this->instance->hasFeedPosts([
      'id' => $feedPost->id
    ]);

    Assert::false($feedPostExist);
  }

  /**
   * Testing "getPostsByVideo" method by id.
   *
   * @throws NotFoundException
   */
  public function testCanGetPostsByVideoById()
  {
    $video = new Video();
    $video->id = Utils::randomString();

    $post = new Post();
    $post = $this->instance->addPost($post);

    $feedPost = new FeedPost();
    $feedPost->postId = $post->id;
    $feedPost->videoId = $video->id;
    $feedPost = $this->instance->addFeedPost($feedPost);

    $posts = $this->instance->getPostsByVideo($video);

    Assert::count($posts, 1);

    Assert::isInstanceOf(
      $posts[0],
      Post::class
    );

    Assert::same(
      $posts[0]->id,
      $feedPost->postId
    );

    $this->instance->removePost($post->id);

    $postExist = $this->instance->hasPosts([
      'id' => $post->id
    ]);

    Assert::false($postExist);

    $feedPostExist = $this->instance->hasFeedPosts([
      'id' => $feedPost->id
    ]);

    Assert::false($feedPostExist);

    $posts = $this->instance->getPostsByVideo($video);

    Assert::count($posts, 0);
  }

  /**
   * Testing "getPostsByVideo" method by id.
   *
   * @throws NotFoundException
   */
  public function testCannotGetPostsByVideoByIdWithoutPost()
  {
    $video = new Video();
    $video->id = Utils::randomString();

    $feedPost = new FeedPost();
    $feedPost->videoId = $video->id;
    $feedPost = $this->instance->addFeedPost($feedPost);

    $posts = $this->instance->getPostsByVideo($video);

    Assert::isEmpty($posts);

    $this->instance->removeFeedPost($feedPost->id);

    $feedPostExist = $this->instance->hasFeedPosts([
      'id' => $feedPost->id
    ]);

    Assert::false($feedPostExist);
  }

  /**
   * Testing "getPostsByVideo" method by title.
   *
   * @throws NotFoundException
   */
  public function testCanGetPostsByVideoByTitle()
  {
    $video = new Video();
    $video->title = Utils::randomString();

    $post = new Post();
    $post->title = $video->title;
    $post = $this->instance->addPost($post);

    $posts = $this->instance->getPostsByVideo($video);

    Assert::count($posts, 1);

    Assert::isInstanceOf(
      $posts[0],
      Post::class
    );

    Assert::same(
      $posts[0]->title,
      $video->title
    );

    $this->instance->removePost($post->id);

    $postExist = $this->instance->hasPosts([
      'id' => $post->id
    ]);

    Assert::false($postExist);

    $posts = $this->instance->getPostsByVideo($video);

    Assert::count($posts, 0);
  }

  /**
   * Testing "getPostsByVideo" method by extra fields.
   *
   * @throws NotFoundException
   */
  public function testCanGetPostsByVideoByExtraFields()
  {
    $video = new Video();
    $video->worldTitle = Utils::randomString();

    $extraField = new ExtraField();
    $extraField->name = 'WORLD-TITLE';
    $extraField->value = $video->worldTitle;

    $post = new Post();
    $post->extraFields[] = $extraField;
    $post = $this->instance->addPost($post);

    $videoFields = ContainerFactory::fromNamespace(
      $this->getContainer(),
      'video_field',
      true,
      true
    );

    $postFactory = new PostFactory(
      new Container([
      'extra_fields' => [$extraField],
      'category_factory' => $this->getContainer()->get('category_factory'),
      'extra_field_factory' => $this->getContainer()->get('extra_field_factory')
     ])
    );

    $containerFields = [
      'system'                      => $this->getContainer()->get('system'),
      'database'                    => $this->getContainer()->get('database'),
      'post_factory'                => $postFactory,
      'extra_field_factory'         => $this->getContainer()->get('extra_field_factory'),
      'post_accessory_video_fields' => $this->getContainer()->get('post_accessory_video_fields'),
      'database_feed_posts_table'   => $this->getContainer()->get('database_feed_posts_table'),
      'video_field_world_title'     => $extraField->name,
      'video_field_tagline'         => Utils::randomString()
    ];

    $instance = new SystemApi(
      new Container(array_merge(
        $videoFields,
        $containerFields
      ))
    );

    $posts = $instance->getPostsByVideo($video);

    Assert::count($posts, 1);

    Assert::isInstanceOf(
      $posts[0],
      Post::class
    );

    Assert::same(
      $posts[0]->title,
      $video->title
    );

    $instance->removePost($post->id);

    $postExist = $instance->hasPosts([
      'id' => $post->id
    ]);

    Assert::false($postExist);

    $posts = $instance->getPostsByVideo($video);

    Assert::count($posts, 0);
  }

  /**
   * Testing "getPostsByVideo" method without duplicates.
   *
   * @throws NotFoundException
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCanGetPostsByVideoWithoutDuplicates()
  {
    /** @var PostFactory $postFactory */
    $postFactory = $this->getContainer()->get('post_factory');

    /** @var FeedPostFactory $feedPostFactory */
    $feedPostFactory = $this->getContainer()->get('feed_post_factory');

    $video = new Video();
    $video->id = Utils::randomString();
    $video->title = Utils::randomString();

    $post = $postFactory->fromVideo($video);
    $post = $this->instance->addPost($post);

    $feedPost = $feedPostFactory->fromPostAndVideo($post, $video);
    $feedPost = $this->instance->addFeedPost($feedPost);

    $posts = $this->instance->getPostsByVideo($video);

    Assert::count($posts, 1);

    Assert::isInstanceOf(
      $posts[0],
      Post::class
    );

    Assert::same(
      $posts[0]->id,
      $feedPost->postId
    );

    Assert::same(
      $posts[0]->title,
      $video->title
    );

    $this->instance->removePost($post->id);

    $postExist = $this->instance->hasPosts([
      'id' => $post->id
    ]);

    Assert::false($postExist);

    $feedPostExist = $this->instance->hasFeedPosts([
      'id' => $feedPost->id
    ]);

    Assert::false($feedPostExist);

    $posts = $this->instance->getPostsByVideo($video);

    Assert::count($posts, 0);
  }

  /**
   * Testing "getPostsByVideo" method by empty.
   *
   * @throws NotFoundException
   */
  public function testCannotGetPostsByVideoByEmpty()
  {
    $video = new Video();

    $post = new Post();
    $post = $this->instance->addPost($post);

    $feedPost = new FeedPost();
    $feedPost->postId = $post->id;
    $feedPost = $this->instance->addFeedPost($feedPost);

    $posts = $this->instance->getPostsByVideo($video);

    Assert::count($posts, 0);

    $this->instance->removePost($post->id);

    $postExist = $this->instance->hasPosts([
      'id' => $post->id
    ]);

    Assert::false($postExist);

    $feedPostExist = $this->instance->hasFeedPosts([
      'id' => $feedPost->id
    ]);

    Assert::false($feedPostExist);
  }

  /**
   * Testing `removeFeedPostsAndRelatedPosts` method.
   *
   * @throws \Exception
   */
  public function testCanRemoveFeedPostsAndRelatedPosts()
  {
    /** @var FeedPostFactory $feedPostFactory */
    $feedPostFactory = $this->getContainer()->get('feed_post_factory');

    $post = new Post();
    $addedPost = $this->instance->addPost($post);
    $feedPost = $feedPostFactory->fromPost($addedPost);
    $addedFeedPost = $this->instance->addFeedPost($feedPost);

    $this->instance->removeFeedPostsAndRelatedPosts([
      'id' => $addedFeedPost->id
    ]);

    $exceptions = 0;

    try {
      $this->instance->getFeedPost($addedFeedPost->id);
    } catch (NotFoundException $exception) {
      ++$exceptions;
    }

    try {
      $this->instance->getPost($addedPost->id);
    } catch (NotFoundException $exception) {
      ++$exceptions;
    }

    Assert::same($exceptions, 2);
  }

  /**
   * Testing `removeFeedPostsAndRelatedPosts` method with linked field.
   *
   * @throws \Exception
   */
  public function testCanRemoveFeedPostsAndRelatedPostsWithLinkedField()
  {
    /** @var FeedPostFactory $feedPostFactory */
    $feedPostFactory = $this->getContainer()->get('feed_post_factory');

    $values = [
      'first',
      'second',
      'third',
    ];

    $expectedCount = count($values);

    $extraField = new ExtraField();
    $extraField->name = Utils::randomString();
    $extraField->label = Utils::randomString();
    $extraField->type = ExtraField::TEXT_TYPE;
    $extraField->value = join(', ', $values);
    $extraField->linked = true;

    $post = new Post();
    $post->extraFields = [$extraField];
    $addedPost = $this->instance->addPost($post);

    $count = $this->database->count(
      'xfsearch',
      ['news_id' => $addedPost->id]
    );

    Assert::same(
      $count,
      $expectedCount
    );

    $feedPost = $feedPostFactory->fromPost($addedPost);
    $addedFeedPost = $this->instance->addFeedPost($feedPost);

    $this->instance->removeFeedPostsAndRelatedPosts([
      'id' => $addedFeedPost->id
    ]);

    $count = $this->database->count(
      'xfsearch',
      ['news_id' => $addedPost->id]
    );

    Assert::same($count, 0);
  }
}
