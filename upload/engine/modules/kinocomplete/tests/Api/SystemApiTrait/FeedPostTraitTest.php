<?php

namespace Kinocomplete\Test\Api\SystemApiTrait;

use Kinocomplete\Api\SystemApiTrait\FeedPostTrait;
use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\FeedPost\FeedPostFactory;
use Kinocomplete\Container\Container;
use Kinocomplete\FeedPost\FeedPost;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;
use Medoo\Medoo;

class FeedPostTraitTest extends TestCase
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

    $this->database = $this->getContainer()->get('database');

    $this->instance = $this->getMockBuilder(
      FeedPostTrait::class
    )->setMethods([
      'getContainer'
    ])->getMockForTrait();

    $this->instance
      ->method('getContainer')
      ->willReturn($this->getContainer());
  }

  /**
   * Testing `addFeedPost` method.
   *
   * @return FeedPost
   * @throws \Exception
   */
  public function testCanAddFeedPost()
  {
    $feedPost = new FeedPost();

    $addedFeedPost = $this->instance->addFeedPost(
      $feedPost
    );

    Assert::isInstanceOf(
      $addedFeedPost,
      FeedPost::class
    );

    Assert::stringNotEmpty(
      $addedFeedPost->id
    );

    return $addedFeedPost;
  }

  /**
   * Testing `getFeedPost` method.
   *
   * @param   FeedPost $feedPost
   * @return  string
   * @throws  NotFoundException
   * @depends testCanAddFeedPost
   */
  public function testCanGetFeedPost(
    FeedPost $feedPost
  ) {
    $fetchedFeedPost = $this->instance->getFeedPost(
      $feedPost->id
    );

    Assert::isInstanceOf(
      $fetchedFeedPost,
      FeedPost::class
    );

    Assert::same(
      $feedPost->id,
      $fetchedFeedPost->id
    );

    return $fetchedFeedPost;
  }

  /**
   * Testing `updateFeedPost` method.
   *
   * @param   FeedPost $feedPost
   * @return  FeedPost
   * @throws  NotFoundException
   * @depends testCanAddFeedPost
   */
  public function testCanUpdateFeedPost(
    FeedPost $feedPost
  ) {
    $feedPost->videoOrigin = Utils::randomString();

    $this->instance->updateFeedPost(
      $feedPost
    );

    $updatedFeedPost = $this->instance->getFeedPost(
      $feedPost->id
    );

    Assert::same(
      $updatedFeedPost->videoOrigin,
      $feedPost->videoOrigin
    );

    return $updatedFeedPost;
  }

  /**
   * Testing `getFeedPosts` method.
   *
   * @param   FeedPost $feedPost
   * @return  mixed
   * @throws  \Exception
   * @depends testCanUpdateFeedPost
   */
  public function testCanGetFeedPosts(
    FeedPost $feedPost
  ) {
    $fetchedFeedPosts = $this->instance->getFeedPosts([
      'id' => $feedPost->id
    ]);

    Assert::notEmpty($fetchedFeedPosts);

    $fetchedFeedPost = $fetchedFeedPosts[0];

    Assert::isInstanceOf(
      $fetchedFeedPost,
      FeedPost::class
    );

    Assert::same(
      $feedPost->id,
      $fetchedFeedPost->id
    );

    return $fetchedFeedPost->id;
  }

  /**
   * Testing `getFeedPosts` method with "join".
   *
   * @throws \Exception
   */
  public function testCanGetFeedPostsWithJoin()
  {
    $table   = $this->getContainer()->get('database_feed_posts_table');
    $where   = ['1'];
    $join    = ['2'];
    $columns = ['3'];

    $database = $this->getMockBuilder(Medoo::class)
      ->disableOriginalConstructor()
      ->getMock();

    /** @var FeedPostFactory $factory */
    $factory = $this->getContainer()->get('feed_post_factory');

    $expectedInstance = new FeedPost();
    $expectedArray = $factory->toDatabaseArray($expectedInstance);

    $database
      ->expects($this->exactly(2))
      ->method('select')
      ->with(
        $table,
        $join,
        $columns,
        $where
      )->willReturn([$expectedArray]);

    $trait = $this->getMockBuilder(
      FeedPostTrait::class
    )->setMethods([
      'getContainer'
    ])->getMockForTrait();

    $trait
      ->method('getContainer')
      ->willReturn(new Container([
        'database' => $database,
        'feed_post_factory' => $factory,
        'database_feed_posts_table' => $table
      ]));

    /** @var FeedPostTrait $trait */
    $instances = $trait->getFeedPosts(
      $where,
      $join,
      $columns
    );

    Assert::count($instances, 1);

    Assert::eq(
      $instances[0],
      $expectedInstance
    );

    $arrays = $trait->getFeedPosts(
      $where,
      $join,
      $columns,
      true
    );

    Assert::count($arrays, 1);

    Assert::same(
      $arrays[0],
      $expectedArray
    );
  }

  /**
   * Testing `hasFeedPosts` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanGetFeedPosts
   */
  public function testCanHasFeedPosts($id)
  {
    Assert::true(
      $this->instance->hasFeedPosts([
        'id[~]' => $id
      ])
    );

    Assert::false(
      $this->instance->hasFeedPosts([
        'id[~]' => '-20'
      ])
    );

    return $id;
  }

  /**
   * Testing `countFeedPosts` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanHasFeedPosts
   */
  public function testCanCountFeedPosts($id)
  {
    $count = $this->instance->countFeedPosts([
      'id' => $id
    ]);

    Assert::same($count, 1);

    return $id;
  }

  /**
   * Testing `countFeedPosts` method with join.
   *
   * @throws \Exception
   */
  public function testCanCountFeedPostsWithJoin()
  {
    $table   = $this->getContainer()->get('database_feed_posts_table');
    $where   = ['1'];
    $join    = ['2'];
    $columns = ['3'];

    $expected = 5;

    $database = $this->getMockBuilder(Medoo::class)
      ->disableOriginalConstructor()
      ->getMock();

    $database
      ->expects($this->once())
      ->method('count')
      ->with(
        $table,
        $join,
        $columns,
        $where
      )->willReturn($expected);

    $trait = $this->getMockBuilder(
      FeedPostTrait::class
    )->setMethods([
      'getContainer'
    ])->getMockForTrait();

    $trait
      ->method('getContainer')
      ->willReturn(new Container([
        'database' => $database,
        'database_feed_posts_table' => $table,
      ]));

    /** @var FeedPostTrait $trait */
    $result = $trait->countFeedPosts(
      $where,
      $join,
      $columns
    );

    Assert::same(
      $result,
      $expected
    );
  }

  /**
   * Testing `removeFeedPost` method.
   *
   * @param   string $id
   * @throws  NotFoundException
   * @depends testCanCountFeedPosts
   */
  public function testCanRemoveFeedPost($id)
  {
    $isRemoved = $this->instance->removeFeedPost($id);

    Assert::true($isRemoved);
  }

  /**
   * Testing `removeFeedPosts` method.
   *
   * @throws \Exception
   */
  public function testCanRemoveFeedPosts()
  {
    $addedFeedPost = $this->instance->addFeedPost(
      new FeedPost()
    );

    $count = $this->instance->removeFeedPosts([
      'id' => $addedFeedPost->id
    ]);

    Assert::same($count, 1);
  }

  /**
   * Testing `getFeedPost` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotGetFeedPost()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getFeedPost('-20');
  }

  /**
   * Testing `updateFeedPost` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotUpdateFeedPost()
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->instance->updateFeedPost(new FeedPost());
  }

  /**
   * Testing `removeFeedPost` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotRemoveFeedPost()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->removeFeedPost('-20');
  }
}
