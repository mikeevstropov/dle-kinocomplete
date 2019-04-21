import Configuration from '../components/configuration';
import {requiredArgument} from '../utils';

export default class Component {

  /**
   * Configuration instance.
   *
   * @type {Configuration}
   * @protected
   */
  configuration = null;

  /**
   * Component constructor.
   *
   * @param {Configuration} configuration
   * @public
   */
  constructor ({
    configuration = requiredArgument('configuration'),
  } = {}) {

    if (!(configuration instanceof Configuration))
      throw new Error('Invalid configuration type.');

    if (!configuration.ready)
      throw new Error('Configuration is not ready.');

    this.configuration = configuration;
  }
}
