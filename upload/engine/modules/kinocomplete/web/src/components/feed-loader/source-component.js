/* global Growl */

import {requiredArgument} from '../../utils';
import Component from './component';

export default class SourceComponent extends Component {

  /**
   * Origin.
   *
   * @type {string}
   */
  origin = null;

  /**
   * Controller constructor.
   *
   * @param configuration
   * @param view
   * @param api
   * @param origin
   * @public
   */
  constructor ({
    configuration = requiredArgument('configuration'),
    view = requiredArgument('view'),
    api = requiredArgument('api'),
    origin = requiredArgument('origin'),
  } = {}) {

    super({
      configuration,
      view,
      api,
    });

    if (!origin || typeof origin !== 'string')
      throw new Error(
        'Argument "origin" must be a non-empty string.'
      );

    this.origin = origin;
  }

  /**
   * Create posts.
   *
   * @protected
   */
  createPosts () {

    this.loading = true;
    this.progress = 0;

    const onMessage = data => {

      this.progress = data.progress;

      this.feedPostsCount
        = this.initialFeedPostsCount
        + data.processed;

      this.feedPostsSkipped = data.skipped;
    };

    const onSuccess = data => {

      this.loading = false;
      this.progress = data.progress;

      this.initialFeedPostsCount
        = this.feedPostsCount;

      if (data.processed) {

        Growl.info({
          title: 'Выполнено',
          text: `Успешно добавлено ${data.processed} новостей.`,
        });

      } else if (data.skipped) {

        Growl.info({
          title: 'Пропущено',
          text: `Было пропущено ${data.skipped} новостей.`,
        });

      } else {

        Growl.info({
          title: 'Выполнено',
          text: 'Новостей добавлено не было.',
        });
      }
    };

    const onError = error => {

      this.loading = false;
      this.progress = 0;

      Growl.error({
        title: 'Ошибка',
        text: error.message,
      });
    };

    this.api.createPosts({
      origin: this.origin,
      onError,
      onMessage,
      onSuccess,
    });
  }

  /**
   * Update posts.
   *
   * @protected
   */
  updatePosts () {

    this.loading = true;
    this.progress = 0;

    const onMessage = data => {

      this.progress = data.progress;

      this.feedPostsUpdated = data.processed;
      this.feedPostsSkipped = data.skipped;
    };

    const onSuccess = data => {

      this.loading = false;
      this.progress = data.progress;

      if (data.processed) {

        Growl.info({
          title: 'Выполнено',
          text: `Успешно обновлено ${data.processed} новостей.`,
        });

      } else if (data.skipped) {

        Growl.info({
          title: 'Пропущено',
          text: `Было пропущено ${data.skipped} новостей.`,
        });

      } else {

        Growl.info({
          title: 'Выполнено',
          text: 'Новостей обновлено не было.',
        });
      }
    };

    const onError = error => {

      this.loading = false;
      this.progress = 0;

      Growl.error({
        title: 'Ошибка',
        text: error.message,
      });
    };

    this.api.updatePosts({
      origin: this.origin,
      onError,
      onMessage,
      onSuccess,
    });
  }

  /**
   * Clean posts.
   *
   * @protected
   */
  cleanPosts () {

    this.loading = true;
    this.progress = 0;
    this.progress = 35;

    this.api.cleanPosts({
      origin: this.origin,
    }).then(response => {

      if (
        !Object.isObject(response)
        || response.status !== 'success'
      ) {

        Growl.error({
          title: 'Ошибка',
          text: 'Некорректный ответ сервера.',
        });

        this.loading = false;
        this.progress = 0;

        return;
      }

      Growl.info({
        title: 'Выполнено',
        text: `Успешно удалено ${this.feedPostsCount} новостей.`,
      });

      this.loading = false;
      this.progress = 100;
      this.feedPostsCount = 0;
      this.initialFeedPostsCount = 0;
      this.feedPostsSkipped = 0;

    }).catch(error => {

      this.loading = false;
      this.progress = 0;

      const message = (error.response &&
        error.response.data &&
        error.response.data.message) ||
        'Неизвестная ошибка';

      Growl.error({
        title: 'Ошибка',
        text: message,
      });
    });
  }
}
