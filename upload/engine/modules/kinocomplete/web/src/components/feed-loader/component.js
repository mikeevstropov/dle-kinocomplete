import BaseComponent from '../../base/component';
import {requiredArgument} from '../../utils';
import WatchJS from 'melanke-watchjs';
import View from './view';
import Api from '../api';

export default class Component extends BaseComponent {

  /**
   * View instance.
   *
   * @type {View}
   * @protected
   */
  view = null;

  /**
   * Is loading.
   *
   * @type {boolean}
   * @protected
   */
  loading = false;

  /**
   * Progress.
   *
   * @type {number}
   * @protected
   */
  progress = 0;

  /**
   * Feed posts count.
   *
   * @type {number}
   * @protected
   */
  feedPostsCount = 0;

  /**
   * Initial feed posts count.
   *
   * @type {number}
   * @protected
   */
  initialFeedPostsCount = 0;

  /**
   * Feed posts updated.
   *
   * @type {number}
   * @protected
   */
  feedPostsUpdated = 0;

  /**
   * Feed posts skipped.
   *
   * @type {number}
   * @protected
   */
  feedPostsSkipped = 0;

  /**
   * Controller constructor.
   *
   * @param configuration
   * @param view
   * @param api
   * @public
   */
  constructor ({
    configuration = requiredArgument('configuration'),
    view = requiredArgument('view'),
    api = requiredArgument('api'),
  } = {}) {

    super({configuration});

    if (!(view instanceof View))
      throw new Error(
        'Argument "view" must be an instance of View class.'
      );

    if (!(api instanceof Api))
      throw new Error(
        'Argument "api" must be an instance of Api class.'
      );

    this.view = view;
    this.api = api;

    this.feedPostsCount
      = this.initialFeedPostsCount
      = this.view.getFeedPostsCount();

    this.bindButtons();
    this.watchProperties();

    console.log('FeedLoader initialized.');
  }

  /**
   * Bind buttons.
   *
   * @protected
   */
  bindButtons () {

    // Create posts.
    $(this.view.createPostsButton)
      .click(this.createPosts.bind(this));

    $(this.view.createPostsButton)
      .removeClass('disabled');

    // Update posts.
    $(this.view.updatePostsButton)
      .click(this.updatePosts.bind(this));

    $(this.view.updatePostsButton)
      .removeClass('disabled');

    // Clean posts.
    $(this.view.cleanPostsButton)
      .click(this.cleanPosts.bind(this));

    if (this.initialFeedPostsCount)
      $(this.view.cleanPostsButton)
        .removeClass('disabled');
  }

  /**
   * Watch properties.
   *
   * @return {void}
   * @protected
   */
  watchProperties () {

    // Watcher of "loading".
    WatchJS.watch(this, 'loading', () => {

      this.view.setCreatePostsButtonLoading(this.loading);
      this.view.setUpdatePostsButtonLoading(this.loading);
      this.view.setCleanPostsButtonLoading(this.loading);
    });

    // Watcher of "progress".
    WatchJS.watch(this, 'progress', () => {

      this.view.setProgress(this.progress);
    });

    // Watcher of "feedPostsCount".
    WatchJS.watch(this, 'feedPostsCount', () => {

      this.view.setFeedPostsCount(
        this.feedPostsCount
      );

      this.view.setCleanPostsButtonDisabled(
        !this.feedPostsCount
      );
    });

    // Watcher of "feedPostsUpdated".
    WatchJS.watch(this, 'feedPostsUpdated', () => {

      this.view.setFeedPostsUpdated(
        this.feedPostsUpdated
      );
    });

    // Watcher of "feedPostsSkipped".
    WatchJS.watch(this, 'feedPostsSkipped', () => {

      this.view.setFeedPostsSkipped(
        this.feedPostsSkipped
      );
    });
  }

  /**
   * Create posts.
   *
   * @protected
   */
  createPosts () {

    throw new Error(
      'Method "createPosts" must be implemented in a child classes.'
    );
  }

  /**
   * Update posts.
   *
   * @protected
   */
  updatePosts () {

    throw new Error(
      'Method "updatePosts" must be implemented in a child classes.'
    );
  }

  /**
   * Clean posts.
   *
   * @protected
   */
  cleanPosts () {

    throw new Error(
      'Method "cleanPosts" must be implemented in a child classes.'
    );
  }
}
