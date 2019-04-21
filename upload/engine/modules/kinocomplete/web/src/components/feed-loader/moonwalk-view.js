import {requiredArgument} from '../../utils';
import View from './view';

export default class MoonwalkView extends View {

  /**
   * MoonwalkView constructor.
   *
   * @param {Configuration} configuration
   * @param {Layout} layout
   * @public
   */
  constructor ({
    configuration = requiredArgument('configuration'),
    layout = requiredArgument('layout'),
  } = {}) {

    super({
      configuration,
      layout,
    });

    // Progress bar.
    this.progressBar = document
      .querySelector('.kc__feed-loader__progress-bar_moonwalk');

    if (!this.progressBar)
      throw new Error(
        'Unable to find a progress bar.'
      );

    // Create posts button.
    this.createPostsButton = document
      .querySelector('.kc__feed-loader__create-posts-button_moonwalk');

    if (!this.createPostsButton)
      throw new Error(
        'Unable to find a create posts button.'
      );

    // Update posts button.
    this.updatePostsButton = document
      .querySelector('.kc__feed-loader__update-posts-button_moonwalk');

    if (!this.updatePostsButton)
      throw new Error(
        'Unable to find an update posts button.'
      );

    // Clean posts button.
    this.cleanPostsButton = document
      .querySelector('.kc__feed-loader__clean-posts-button_moonwalk');

    if (!this.cleanPostsButton)
      throw new Error(
        'Unable to find a clean posts button.'
      );

    // Feed posts count container.
    this.feedPostsCountContainer = document
      .querySelector('.kc__feed-loader__feed-posts-count_moonwalk');

    if (!this.feedPostsCountContainer)
      throw new Error(
        'Unable to find a feed posts count container.'
      );

    // Feed posts updated container.
    this.feedPostsUpdatedContainer = document
      .querySelector('.kc__feed-loader__feed-posts-updated_moonwalk');

    if (!this.feedPostsUpdatedContainer)
      throw new Error(
        'Unable to find a feed posts updated container.'
      );

    // Feed posts skipped container.
    this.feedPostsSkippedContainer = document
      .querySelector('.kc__feed-loader__feed-posts-skipped_moonwalk');

    if (!this.feedPostsSkippedContainer)
      throw new Error(
        'Unable to find a feed posts skipped container.'
      );
  }
}
