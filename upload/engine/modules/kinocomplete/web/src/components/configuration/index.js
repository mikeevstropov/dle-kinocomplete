import {requiredArgument} from '../../utils';

export default class Configuration {

  /**
   * Configuration constructor.
   *
   * @param values
   * @param client
   * @public
   */
  constructor ({
    values = {},
    client = requiredArgument('client'),
  } = {}) {

    if (typeof client.get !== 'function')
      throw new Error(
        'Client must be an object which contain `get` method.'
      );

    this.requestPath = location.pathname
      +'?mod=kinocomplete&action=get-configuration&ajax';

    this._values = values;
    this.client = client;
    this.ready = false;
    this.loading = false;

    console.log('Configuration initialized.');
  }

  /**
   * Load remote configuration.
   *
   * @return {Promise<void>}
   * @public
   */
  async load () {

    this.ready = false;
    this.loading = true;

    const {data} = await this.client.get(
      this.requestPath
    ).catch(error => {

      this.loading = false;
      throw error;
    });

    this._values = data;
    this.loading = false;
    this.ready = true;
  }

  /**
   * Checking if key exist.
   *
   * @param  key
   * @return {boolean}
   * @public
   */
  has (key) {

    return typeof this._values[key] !== 'undefined' &&
      this._values.hasOwnProperty(key);
  }

  /**
   * Getting the value.
   *
   * @param  key
   * @param  isRequired
   * @return {*}
   * @public
   */
  get (key, isRequired = true) {

    if (isRequired && !this.has(key))
      throw new Error('Required property "'+ key +'" is not defined.');

    return this._values[key];
  }
}
