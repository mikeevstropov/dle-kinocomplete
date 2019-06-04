import videoModel from '../../models/video-model.json';
import Url from 'urijs';

import {
  duckTypingCheck,
  requiredArgument,
  snakeToCamel,
} from '../../utils';

export default class Api {

  /**
   * Axios.
   *
   * @type {object}
   * @protected
   */
  client = null;

  /**
   * Request path.
   *
   * @type {string}
   * @protected
   */
  requestPath = location.pathname +'?mod=kinocomplete&ajax=';

  /**
   * Api constructor.
   *
   * @param client Axios instance
   * @public
   */
  constructor ({
    client = requiredArgument('client'),
  } = {}) {

    if (typeof client.get !== 'function')
      throw new Error(
        'Client must be an object which contain `get` method.'
      );

    this.client = client;

    console.log('Api initialized.');
  }

  /**
   * Query to URL.
   *
   * @param  {object} query
   * @return {string}
   * @public
   */
  queryToUrl (query) {

    if (!Object.isObject(query))
      throw new Error(
        'Argument "query" must be an object.'
      );

    const queryString = Url.buildQuery(query);

    return this.requestPath +'&'+ queryString;
  }

  /**
   * Event source factory.
   *
   * @param  {string} url
   * @param  {Function} onSuccess
   * @param  {Function} onMessage
   * @param  {Function} onError
   * @return {EventSource}
   * @public
   */
  getEventSource ({
    url = requiredArgument('url'),
    onSuccess,
    onMessage,
    onError,
  } = {}) {

    const eventSource = new EventSource(url);

    // Event "message".
    eventSource.addEventListener('message', e => {

      if (onMessage instanceof Function)
        onMessage(JSON.parse(e.data));
    });

    // Event "disconnect".
    eventSource.addEventListener('disconnect', e => {

      eventSource.close();

      if (onSuccess instanceof Function)
        onSuccess(JSON.parse(e.data));
    });

    // Event "error".
    eventSource.addEventListener('error', e => {

      eventSource.close();

      if (onError instanceof Function) {

        try {

          onError(JSON.parse(e.data));

        } catch (e) {

          onError({
            message: 'Произошла неизвестная ошибка.',
            code: 0,
          });
        }
      }
    });

    return eventSource;
  }

  /**
   * Method GET.
   *
   * @param   {object} query
   * @returns {Promise<*>} Response
   * @public
   */
  async get ({
    query = {},
  } = {}) {

    const url = this.queryToUrl(query);
    const {data} = await this.client.get(url);

    return data;
  }

  /**
   * Get videos.
   *
   * @param   {string} title
   * @returns {Promise<Array>} Array of videos
   * @public
   */
  async getVideos ({
    title = '',
  } = {}) {

    const query = {
      action: 'get-videos',
      title,
    };

    const videos = await this.get({query});

    return videos.map(item => {

      const valid = duckTypingCheck({
        value: item,
        model: videoModel,
        empty: false,
      });

      if (!valid)
        throw new Error(
          'Invalid model founded in api response.'
        );

      item.typeLabel = item.type === 'video'
        ? 'видео'
        : item.type === 'movie'
          ? 'фильм'
          : item.type === 'series'
            ? 'сериал'
            : 'разное';

      return item;
    });
  }

  /**
   * Get video.
   *
   * @param  {string} id
   * @param  {string} origin
   * @return {Promise<*>} Video
   * @public
   */
  async getVideo ({
    id = '',
    origin = '',
  } = {}) {

    const query = {
      action: 'get-video',
      id,
      origin,
    };

    return this.get({query});
  }

  /**
   * Get autocomplete video.
   *
   * @param  {string} id
   * @param  {string} origin
   * @return {Promise<*>} Video
   * @public
   */
  async getAutocompleteVideo ({
    id = '',
    origin = '',
  } = {}) {

    const query = {
      action: 'get-autocomplete-video',
      id,
      origin,
    };

    return this.get({query});
  }

  /**
   * Create posts.
   *
   * @param  origin
   * @param  onSuccess
   * @param  onMessage
   * @param  onError
   * @return {void}
   * @public
   */
  createPosts ({
    origin = requiredArgument('origin'),
    onSuccess,
    onMessage,
    onError,
  } = {}) {

    origin = snakeToCamel(origin);

    const url = this.queryToUrl({
      action: `create-${origin}-posts`,
    });

    this.getEventSource({
      url,
      onError,
      onMessage,
      onSuccess,
    });
  }

  /**
   * Update posts.
   *
   * @param  origin
   * @param  onSuccess
   * @param  onMessage
   * @param  onError
   * @return {void}
   * @public
   */
  updatePosts ({
    origin = requiredArgument('origin'),
    onSuccess,
    onMessage,
    onError,
  } = {}) {

    origin = snakeToCamel(origin);

    const url = this.queryToUrl({
      action: `update-${origin}-posts`,
    });

    this.getEventSource({
      url,
      onError,
      onMessage,
      onSuccess,
    });
  }

  /**
   * Clean moonwalk posts.
   *
   * @param  origin
   * @return {Promise<*>}
   * @public
   */
  async cleanPosts ({
    origin = requiredArgument('origin'),
  } = {}) {

    origin = snakeToCamel(origin);

    const query = {action: `clean-${origin}-posts`};

    return this.get({query});
  }
}
