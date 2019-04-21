<?php

namespace Kinocomplete\Api;

use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Category\Category;
use Kinocomplete\FeedPost\FeedPost;
use Kinocomplete\Post\Post;
use Kinocomplete\User\User;

interface BaseSystemApiInterface
{
  /**
   * Add category.
   *
   * @param  Category $category
   * @return Category
   * @throws \Exception
   */
  public function addCategory(
    Category $category
  );

  /**
   * Get category.
   *
   * @param  string $id
   * @return Category
   * @throws NotFoundException
   */
  public function getCategory($id);

  /**
   * Update category.
   *
   * @param  Category $category
   * @return Category
   * @throws NotFoundException
   */
  public function updateCategory(
    Category $category
  );

  /**
   * Remove category.
   *
   * @param  string $id
   * @return bool
   * @throws NotFoundException
   */
  public function removeCategory($id);

  /**
   * Get categories.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @param  boolean $raw
   * @return array
   * @throws \Exception
   */
  public function getCategories(
    array $where = [],
    array $join = [],
    $columns = '*',
    $raw = false
  );

  /**
   * Has categories.
   *
   * @param  array $where
   * @return bool
   * @throws \Exception
   */
  public function hasCategories(
    array $where = []
  );

  /**
   * Remove categories.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function removeCategories(
    array $where = []
  );

  /**
   * Count categories.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function countCategories(
    array $where = []
  );

  /**
   * Add post.
   *
   * @param  Post $post
   * @return Post
   * @throws \Exception
   */
  public function addPost(
    Post $post
  );

  /**
   * Get post.
   *
   * @param  $id
   * @return Post
   * @throws NotFoundException
   */
  public function getPost($id);

  /**
   * Update post.
   *
   * @param  Post $post
   * @return Post
   * @throws NotFoundException
   */
  public function updatePost(
    Post $post
  );

  /**
   * Remove post.
   *
   * @param  string $id
   * @return bool
   * @throws NotFoundException
   */
  public function removePost($id);

  /**
   * Get posts.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @param  boolean $raw
   * @return array
   * @throws \Exception
   */
  public function getPosts(
    array $where = [],
    array $join = [],
    $columns = '*',
    $raw = false
  );

  /**
   * Has posts.
   *
   * @param  array $where
   * @return bool
   * @throws \Exception
   */
  public function hasPosts(
    array $where = []
  );

  /**
   * Remove posts.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function removePosts(
    array $where = []
  );

  /**
   * Count posts.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function countPosts(
    array $where = []
  );

  /**
   * Add feed post.
   *
   * @param  FeedPost $feedPost
   * @return FeedPost
   * @throws \Exception
   */
  public function addFeedPost(
    FeedPost $feedPost
  );

  /**
   * Get feed post.
   *
   * @param  $id
   * @return FeedPost
   * @throws NotFoundException
   */
  public function getFeedPost($id);

  /**
   * Update feed post.
   *
   * @param  FeedPost $feedPost
   * @return FeedPost
   * @throws NotFoundException
   */
  public function updateFeedPost(
    FeedPost $feedPost
  );

  /**
   * Remove feed post.
   *
   * @param  string $id
   * @return bool
   * @throws NotFoundException
   */
  public function removeFeedPost($id);

  /**
   * Get feed posts.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @param  boolean $raw
   * @return array
   * @throws \Exception
   */
  public function getFeedPosts(
    array $where = [],
    array $join = [],
    $columns = '*',
    $raw = false
  );

  /**
   * Has feed posts.
   *
   * @param  array $where
   * @return bool
   * @throws \Exception
   */
  public function hasFeedPosts(
    array $where = []
  );

  /**
   * Remove feed posts.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function removeFeedPosts(
    array $where = []
  );

  /**
   * Count feed posts.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function countFeedPosts(
    array $where = []
  );

  /**
   * Add user.
   *
   * @param  User $user
   * @return User
   * @throws \Exception
   */
  public function addUser(
    User $user
  );

  /**
   * Get user.
   *
   * @param  string $id
   * @return User
   * @throws NotFoundException
   */
  public function getUser($id);

  /**
   * Update user.
   *
   * @param  User $user
   * @return User
   * @throws NotFoundException
   */
  public function updateUser(
    User $user
  );

  /**
   * Remove user.
   *
   * @param  string $id
   * @return bool
   * @throws NotFoundException
   */
  public function removeUser($id);

  /**
   * Get users.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @param  boolean $raw
   * @return array
   * @throws \Exception
   */
  public function getUsers(
    array $where = [],
    array $join = [],
    $columns = '*',
    $raw = false
  );

  /**
   * Has users.
   *
   * @param  array $where
   * @return bool
   * @throws \Exception
   */
  public function hasUsers(
    array $where = []
  );

  /**
   * Remove users.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function removeUsers(
    array $where = []
  );

  /**
   * Count users.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function countUsers(
    array $where = []
  );
}