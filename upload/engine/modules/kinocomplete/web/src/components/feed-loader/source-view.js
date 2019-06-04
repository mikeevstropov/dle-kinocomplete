import {requiredArgument} from '../../utils';
import View from './view';

export default class SourceView extends View {

  /**
   * SourceView constructor.
   *
   * @param {Layout} layout
   * @param {string} origin
   * @public
   */
  constructor ({
    layout = requiredArgument('layout'),
    origin = requiredArgument('origin'),
  } = {}) {

    super({layout});

    if (!origin || typeof origin !== 'string')
      throw new Error(
        'Argument "origin" must be a non-empty string.'
      );

    // Progress bar.
    this.progressBar = document.querySelector(
      `.kc__feed-loader__progress-bar_${origin}`
    );

    if (!this.progressBar)
      throw new Error(
        'Unable to find a progress bar.'
      );

    // Create posts button.
    this.createPostsButton = document.querySelector(
      `.kc__feed-loader__create-posts-button_${origin}`
    );

    if (!this.createPostsButton)
      throw new Error(
        'Unable to find a create posts button.'
      );

    // Update posts button.
    this.updatePostsButton = document.querySelector(
      `.kc__feed-loader__update-posts-button_${origin}`
    );

    if (!this.updatePostsButton)
      throw new Error(
        'Unable to find an update posts button.'
      );

    // Clean posts button.
    this.cleanPostsButton = document.querySelector(
      `.kc__feed-loader__clean-posts-button_${origin}`
    );

    if (!this.cleanPostsButton)
      throw new Error(
        'Unable to find a clean posts button.'
      );

    // Feed posts count container.
    this.feedPostsCountContainer = document.querySelector(
      `.kc__feed-loader__feed-posts-count_${origin}`
    );

    if (!this.feedPostsCountContainer)
      throw new Error(
        'Unable to find a feed posts count container.'
      );

    // Feed posts updated container.
    this.feedPostsUpdatedContainer = document.querySelector(
      `.kc__feed-loader__feed-posts-updated_${origin}`
    );

    if (!this.feedPostsUpdatedContainer)
      throw new Error(
        'Unable to find a feed posts updated container.'
      );

    // Feed posts skipped container.
    this.feedPostsSkippedContainer = document.querySelector(
      `.kc__feed-loader__feed-posts-skipped_${origin}`
    );

    if (!this.feedPostsSkippedContainer)
      throw new Error(
        'Unable to find a feed posts skipped container.'
      );
  }
}
