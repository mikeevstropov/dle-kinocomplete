<?php

namespace Kinocomplete\Test\Api;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\FeedPost\FeedPostFactory;
use Kinocomplete\Container\Container;
use Kinocomplete\Api\BaseSystemApi;
use Kinocomplete\FeedPost\FeedPost;
use Kinocomplete\Category\Category;
use Kinocomplete\Post\PostFactory;
use Kinocomplete\User\UserFactory;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;
use Kinocomplete\User\User;
use Kinocomplete\Post\Post;
use Medoo\Medoo;

class BaseSystemApiTest extends TestCase
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

    $this->instance = new BaseSystemApi($container);
    $this->database = $this->getContainer()->get('database');
  }

  /**
   * Instantiation testing.
   */
  public function testInstanceCreated()
  {
    Assert::isInstanceOf(
      $this->instance,
      BaseSystemApi::class
    );
  }

  /**
   * Testing `addCategory` method.
   *
   * @return Category
   * @throws \Exception
   */
  public function testCanAddCategory()
  {
    $name = 'Category';

    $category = new Category();
    $category->name = $name;

    $addedCategory = $this->instance->addCategory($category);

    Assert::isInstanceOf(
      $addedCategory,
      Category::class
    );

    Assert::stringNotEmpty(
      $addedCategory->id
    );

    Assert::same(
      $addedCategory->name,
      $name
    );

    return $addedCategory;
  }

  /**
   * Testing `getCategory` method.
   *
   * @param   Category $category
   * @return  string
   * @throws  NotFoundException
   * @depends testCanAddCategory
   */
  public function testCanGetCategory(
    Category $category
  ) {
    $fetchedCategory = $this->instance->getCategory(
      $category->id
    );

    Assert::isInstanceOf(
      $fetchedCategory,
      Category::class
    );

    Assert::same(
      $category->id,
      $fetchedCategory->id
    );

    Assert::same(
      $category->name,
      $fetchedCategory->name
    );

    return $fetchedCategory;
  }

  /**
   * Testing `updateCategory` method.
   *
   * @param   Category $category
   * @return  Category
   * @throws  NotFoundException
   * @depends testCanAddCategory
   */
  public function testCanUpdateCategory(
    Category $category
  ) {
    $category->name = Utils::randomString();

    $this->instance->updateCategory(
      $category
    );

    $updatedCategory = $this->instance->getCategory(
      $category->id
    );

    Assert::same(
      $updatedCategory->name,
      $category->name
    );

    return $updatedCategory;
  }

  /**
   * Testing `getCategories` method.
   *
   * @param   Category $category
   * @return  mixed
   * @throws  \Exception
   * @depends testCanUpdateCategory
   */
  public function testCanGetCategories(
    Category $category
  ) {
    $fetchedCategories = $this->instance->getCategories([
      'id' => $category->id
    ]);

    Assert::notEmpty($fetchedCategories);

    $fetchedCategory = $fetchedCategories[0];

    Assert::isInstanceOf(
      $fetchedCategory,
      Category::class
    );

    Assert::same(
      $category->id,
      $fetchedCategory->id
    );

    Assert::same(
      $category->name,
      $fetchedCategory->name
    );

    return $fetchedCategory->id;
  }

  /**
   * Testing `getCategories` method with "join".
   *
   * @throws \Exception
   */
  public function testCanGetCategoriesWithJoin()
  {
    $table   = 'category';
    $where   = ['1'];
    $join    = ['2'];
    $columns = ['3'];

    $database = $this->getMockBuilder(Medoo::class)
      ->disableOriginalConstructor()
      ->getMock();

    /** @var CategoryFactory $factory */
    $factory = $this->getContainer()->get('category_factory');

    $expectedInstance = new Category();
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

    $baseSystemApi = new BaseSystemApi(
      new Container([
        'database' => $database,
        'category_factory' => $factory
      ])
    );

    $instances = $baseSystemApi->getCategories(
      $where,
      $join,
      $columns
    );

    Assert::count($instances, 1);

    Assert::eq(
      $instances[0],
      $expectedInstance
    );

    $arrays = $baseSystemApi->getCategories(
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
   * Testing `hasCategories` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanGetCategories
   */
  public function testCanHasCategories($id)
  {
    Assert::true(
      $this->instance->hasCategories([
        'id[~]' => $id
      ])
    );

    Assert::false(
      $this->instance->hasCategories([
        'id[~]' => '-20'
      ])
    );

    return $id;
  }

  /**
   * Testing `countCategories` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanHasCategories
   */
  public function testCanCountCategories($id)
  {
    $count = $this->instance->countCategories([
      'id' => $id
    ]);

    Assert::same($count, 1);

    return $id;
  }

  /**
   * Testing `removeCategory` method.
   *
   * @param   string $id
   * @throws  NotFoundException
   * @depends testCanCountCategories
   */
  public function testCanRemoveCategory($id)
  {
    $isRemoved = $this->instance->removeCategory($id);

    Assert::true($isRemoved);
  }

  /**
   * Testing `removeCategories` method.
   *
   * @throws \Exception
   */
  public function testCanRemoveCategories()
  {
    $addedCategory = $this->instance->addCategory(
      new Category()
    );

    $count = $this->instance->removeCategories([
      'id' => $addedCategory->id
    ]);

    Assert::same($count, 1);
  }

  /**
   * Testing `getCategory` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotGetCategory()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getCategory('-20');
  }

  /**
   * Testing `updateCategory` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotUpdateCategory()
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->instance->updateCategory(new Category());
  }

  /**
   * Testing `removeCategory` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotRemoveCategory()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->removeCategory('-20');
  }

  /**
   * Testing `addPost` method.
   *
   * @return Post
   * @throws \Exception
   */
  public function testCanAddPost()
  {
    $title = 'Post';

    $post = new Post();
    $post->title = $title;

    $addedPost = $this->instance->addPost($post);

    Assert::isInstanceOf(
      $addedPost,
      Post::class
    );

    Assert::stringNotEmpty(
      $addedPost->id
    );

    Assert::same(
      $addedPost->title,
      $title
    );

    // Checking related post extra.
    $postExtras = $this->database->get(
      'post_extras',
      'news_id',
      ['news_id' => $addedPost->id]
    );

    Assert::count($postExtras, 1);

    return $addedPost;
  }

  /**
   * Testing `getPost` method.
   *
   * @param   Post $post
   * @return  string
   * @throws  NotFoundException
   * @depends testCanAddPost
   */
  public function testCanGetPost(
    Post $post
  ) {
    $fetchedPost = $this->instance->getPost(
      $post->id
    );

    Assert::isInstanceOf(
      $fetchedPost,
      Post::class
    );

    Assert::same(
      $post->id,
      $fetchedPost->id
    );

    Assert::same(
      $post->title,
      $fetchedPost->title
    );

    return $fetchedPost;
  }

  /**
   * Testing `updatePost` method.
   *
   * @param   Post $post
   * @return  Post
   * @throws  NotFoundException
   * @depends testCanAddPost
   */
  public function testCanUpdatePost(
    Post $post
  ) {
    $post->title = Utils::randomString();

    $this->instance->updatePost(
      $post
    );

    $updatedPost = $this->instance->getPost(
      $post->id
    );

    Assert::same(
      $updatedPost->title,
      $post->title
    );

    return $updatedPost;
  }

  /**
   * Testing `getPosts` method.
   *
   * @param   Post $post
   * @return  mixed
   * @throws  \Exception
   * @depends testCanUpdatePost
   */
  public function testCanGetPosts(
    Post $post
  ) {
    $fetchedPosts = $this->instance->getPosts([
      'id' => $post->id
    ]);

    Assert::notEmpty($fetchedPosts);

    $fetchedPost = $fetchedPosts[0];

    Assert::isInstanceOf(
      $fetchedPost,
      Post::class
    );

    Assert::same(
      $post->id,
      $fetchedPost->id
    );

    Assert::same(
      $post->title,
      $fetchedPost->title
    );

    return $fetchedPost->id;
  }

  /**
   * Testing `getPosts` method with "join".
   *
   * @throws \Exception
   */
  public function testCanGetPostsWithJoin()
  {
    $table   = 'post';
    $where   = ['1'];
    $join    = ['2'];
    $columns = ['3'];

    $database = $this->getMockBuilder(Medoo::class)
      ->disableOriginalConstructor()
      ->getMock();

    /** @var PostFactory $factory */
    $factory = $this->getContainer()->get('post_factory');

    $expectedInstance = new Post();
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

    $baseSystemApi = new BaseSystemApi(
      new Container([
        'database' => $database,
        'post_factory' => $factory
      ])
    );

    $instances = $baseSystemApi->getPosts(
      $where,
      $join,
      $columns
    );

    Assert::count($instances, 1);

    Assert::eq(
      $instances[0],
      $expectedInstance
    );

    $arrays = $baseSystemApi->getPosts(
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
   * Testing `hasPosts` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanGetPosts
   */
  public function testCanHasPosts($id)
  {
    Assert::true(
      $this->instance->hasPosts([
        'id[~]' => $id
      ])
    );

    Assert::false(
      $this->instance->hasPosts([
        'id[~]' => '-20'
      ])
    );

    return $id;
  }

  /**
   * Testing `countPosts` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanHasPosts
   */
  public function testCanCountPosts($id)
  {
    $count = $this->instance->countPosts([
      'id' => $id
    ]);

    Assert::same($count, 1);

    return $id;
  }

  /**
   * Testing `removePost` method.
   *
   * @param   string $id
   * @throws  NotFoundException
   * @depends testCanCountPosts
   */
  public function testCanRemovePost($id)
  {
    $isRemoved = $this->instance->removePost($id);

    Assert::true($isRemoved);

    // Checking related post extra.
    $postExtras = $this->database->get(
      'post_extras',
      'news_id',
      ['news_id' => $id]
    );

    Assert::isEmpty($postExtras);
  }

  /**
   * Testing `removePosts` method.
   *
   * @throws \Exception
   */
  public function testCanRemovePosts()
  {
    $addedPost = $this->instance->addPost(
      new Post()
    );

    $count = $this->instance->removePosts([
      'id' => $addedPost->id
    ]);

    Assert::same($count, 1);

    // Checking related post extra.
    $postExtras = $this->database->get(
      'post_extras',
      'news_id',
      ['news_id' => $addedPost->id]
    );

    Assert::isEmpty($postExtras);
  }

  /**
   * Testing `getPost` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotGetPost()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getPost('-20');
  }

  /**
   * Testing `updatePost` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotUpdatePost()
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->instance->updatePost(new Post());
  }

  /**
   * Testing `removePost` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotRemovePost()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->removePost('-20');
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

    $baseSystemApi = new BaseSystemApi(
      new Container([
        'database' => $database,
        'feed_post_factory' => $factory,
        'database_feed_posts_table' => $table
      ])
    );

    $instances = $baseSystemApi->getFeedPosts(
      $where,
      $join,
      $columns
    );

    Assert::count($instances, 1);

    Assert::eq(
      $instances[0],
      $expectedInstance
    );

    $arrays = $baseSystemApi->getFeedPosts(
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

  /**
   * Testing `addUser` method.
   *
   * @return User
   * @throws \Exception
   */
  public function testCanAddUser()
  {
    $user = new User();
    $user->name = Utils::randomString();
    $user->email = Utils::randomString();

    $addedUser = $this->instance->addUser($user);

    Assert::isInstanceOf(
      $addedUser,
      User::class
    );

    Assert::stringNotEmpty(
      $addedUser->id
    );

    Assert::same(
      $addedUser->name,
      $user->name
    );

    Assert::same(
      $addedUser->email,
      $user->email
    );

    return $addedUser;
  }

  /**
   * Testing `getUser` method.
   *
   * @param   User $user
   * @return  string
   * @throws  NotFoundException
   * @depends testCanAddUser
   */
  public function testCanGetUser(
    User $user
  ) {
    $fetchedUser = $this->instance->getUser(
      $user->id
    );

    Assert::isInstanceOf(
      $fetchedUser,
      User::class
    );

    Assert::same(
      $user->id,
      $fetchedUser->id
    );

    Assert::same(
      $user->name,
      $fetchedUser->name
    );

    Assert::same(
      $user->email,
      $fetchedUser->email
    );

    return $fetchedUser;
  }

  /**
   * Testing `updateUser` method.
   *
   * @param   User $user
   * @return  User
   * @throws  NotFoundException
   * @depends testCanAddUser
   */
  public function testCanUpdateUser(
    User $user
  ) {
    $user->name = Utils::randomString();

    $this->instance->updateUser($user);

    $updatedUser = $this->instance->getUser(
      $user->id
    );

    Assert::same(
      $updatedUser->name,
      $user->name
    );

    Assert::same(
      $updatedUser->email,
      $user->email
    );

    return $updatedUser;
  }

  /**
   * Testing `getUsers` method.
   *
   * @param   User $user
   * @return  mixed
   * @throws  \Exception
   * @depends testCanUpdateUser
   */
  public function testCanGetUsers(
    User $user
  ) {
    $fetchedUsers = $this->instance->getUsers([
      'user_id' => $user->id
    ]);

    Assert::notEmpty($fetchedUsers);

    $fetchedUser = $fetchedUsers[0];

    Assert::isInstanceOf(
      $fetchedUser,
      User::class
    );

    Assert::same(
      $user->id,
      $fetchedUser->id
    );

    Assert::same(
      $user->name,
      $fetchedUser->name
    );

    Assert::same(
      $user->email,
      $fetchedUser->email
    );

    return $fetchedUser->id;
  }

  /**
   * Testing `getUsers` method with "join".
   *
   * @throws \Exception
   */
  public function testCanGetUsersWithJoin()
  {
    $table   = 'users';
    $where   = ['1'];
    $join    = ['2'];
    $columns = ['3'];

    $database = $this->getMockBuilder(Medoo::class)
      ->disableOriginalConstructor()
      ->getMock();

    /** @var UserFactory $factory */
    $factory = $this->getContainer()->get('user_factory');

    $expectedInstance = new User();
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

    $baseSystemApi = new BaseSystemApi(
      new Container([
        'database' => $database,
        'user_factory' => $factory
      ])
    );

    $instances = $baseSystemApi->getUsers(
      $where,
      $join,
      $columns
    );

    Assert::count($instances, 1);

    Assert::eq(
      $instances[0],
      $expectedInstance
    );

    $arrays = $baseSystemApi->getUsers(
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
   * Testing `hasUsers` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanGetUsers
   */
  public function testCanHasUsers($id)
  {
    Assert::true(
      $this->instance->hasUsers([
        'user_id[~]' => $id
      ])
    );

    Assert::false(
      $this->instance->hasUsers([
        'user_id[~]' => '-20'
      ])
    );

    return $id;
  }

  /**
   * Testing `countUsers` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanHasUsers
   */
  public function testCanCountUsers($id)
  {
    $count = $this->instance->countUsers([
      'user_id' => $id
    ]);

    Assert::same($count, 1);

    return $id;
  }

  /**
   * Testing `removeUser` method.
   *
   * @param   string $id
   * @throws  NotFoundException
   * @depends testCanCountUsers
   */
  public function testCanRemoveUser($id)
  {
    $isRemoved = $this->instance->removeUser($id);

    Assert::true($isRemoved);
  }

  /**
   * Testing `removeUsers` method.
   *
   * @throws \Exception
   */
  public function testCanRemoveUsers()
  {
    $user = new User();
    $user->name = Utils::randomString();
    $user->email = Utils::randomString();

    $addedUser = $this->instance->addUser($user);

    $count = $this->instance->removeUsers([
      'user_id' => $addedUser->id
    ]);

    Assert::same($count, 1);
  }

  /**
   * Testing `getUser` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotGetUser()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getUser('-20');
  }

  /**
   * Testing `updateUser` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotUpdateUser()
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->instance->updateUser(new User());
  }

  /**
   * Testing `removeUser` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotRemoveUser()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->removeUser('-20');
  }
}
