import BaseView from '../../base/view';

export default class View extends BaseView {

  /**
   * Progress bar.
   *
   * @type {Element}
   * @public
   */
  progressBar = null;

  /**
   * Create posts button.
   *
   * @type {Element}
   * @public
   */
  createPostsButton = null;

  /**
   * Update posts button.
   *
   * @type {Element}
   * @public
   */
  updatePostsButton = null;

  /**
   * Clean posts button.
   *
   * @type {Element}
   * @public
   */
  cleanPostsButton = null;

  /**
   * Count container of feed posts.
   *
   * @type {Element}
   * @public
   */
  feedPostsCountContainer = null;

  /**
   * Updated container of feed posts.
   *
   * @type {Element}
   * @public
   */
  feedPostsUpdatedContainer = null;

  /**
   * Skipped container of feed posts.
   *
   * @type {Element}
   * @public
   */
  feedPostsSkippedContainer = null;

  /**
   * View constructor.
   *
   * @param {object} params
   * @public
   */
  constructor (params) {

    super(params);
  }

  /**
   * Set progress of
   * a progress bar.
   *
   * @param value
   * @public
   */
  setProgress (value) {

    this.progressBar.style.width
      = (value || 0) +'%';
  }

  /**
   * Set loading state of
   * create posts button.
   *
   * @param value
   * @public
   */
  setCreatePostsButtonLoading (value) {

    if (value)
      $(this.createPostsButton).addClass('loading');
    else
      $(this.createPostsButton).removeClass('loading');
  }

  /**
   * Set loading state of
   * update posts button.
   *
   * @param value
   * @public
   */
  setUpdatePostsButtonLoading (value) {

    if (value)
      $(this.updatePostsButton).addClass('loading');
    else
      $(this.updatePostsButton).removeClass('loading');
  }

  /**
   * Set loading state of
   * clean posts button.
   *
   * @param value
   * @public
   */
  setCleanPostsButtonLoading (value) {

    if (value)
      $(this.cleanPostsButton).addClass('loading');
    else
      $(this.cleanPostsButton).removeClass('loading');
  }

  /**
   * Set disabled state of
   * create posts button.
   *
   * @param value
   * @public
   */
  setCreatePostsButtonDisabled (value) {

    if (value)
      $(this.createPostsButton).addClass('disabled');
    else
      $(this.createPostsButton).removeClass('disabled');
  }

  /**
   * Set disabled state of
   * update posts button.
   *
   * @param value
   * @public
   */
  setUpdatePostsButtonDisabled (value) {

    if (value)
      $(this.updatePostsButton).addClass('disabled');
    else
      $(this.updatePostsButton).removeClass('disabled');
  }

  /**
   * Set disabled state of
   * clean posts button.
   *
   * @param value
   * @public
   */
  setCleanPostsButtonDisabled (value) {

    if (value)
      $(this.cleanPostsButton).addClass('disabled');
    else
      $(this.cleanPostsButton).removeClass('disabled');
  }

  /**
   * Get number from a feed
   * posts count container.
   *
   * @return {number}
   * @public
   */
  getFeedPostsCount () {

    const value = this
      .feedPostsCountContainer
      .innerHTML;

    return parseInt(value, 10);
  }

  /**
   * Set content of a feed
   * posts count container.
   *
   * @param value
   * @public
   */
  setFeedPostsCount (value) {

    this
      .feedPostsCountContainer
      .innerText = value;
  }

  /**
   * Get number from a feed
   * posts updated container.
   *
   * @return {number}
   * @public
   */
  getFeedPostsUpdated () {

    const value = this
      .feedPostsUpdatedContainer
      .innerHTML;

    return parseInt(value, 10);
  }

  /**
   * Set content of a feed
   * posts updated container.
   *
   * @param value
   * @public
   */
  setFeedPostsUpdated (value) {

    this
      .feedPostsUpdatedContainer
      .innerText = value;
  }

  /**
   * Get number from a feed
   * posts skipped container.
   *
   * @return {number}
   * @public
   */
  getFeedPostsSkipped () {

    const value = this
      .feedPostsSkippedContainer
      .innerHTML;

    return parseInt(value, 10);
  }

  /**
   * Set content of a feed
   * posts skipped container.
   *
   * @param value
   * @public
   */
  setFeedPostsSkipped (value) {

    this
      .feedPostsSkippedContainer
      .innerText = value;
  }
}
