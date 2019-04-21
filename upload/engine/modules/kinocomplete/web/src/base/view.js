import {requiredArgument} from '../utils';
import Layout from '../components/layout';

export default class View {

  /**
   * Layout instance.
   *
   * @type {Layout}
   * @protected
   */
  layout = null;

  /**
   * View constructor.
   *
   * @param {View} configuration
   * @public
   */
  constructor ({
    layout = requiredArgument('layout'),
  } = {}) {

    if (!(layout instanceof Layout))
      throw new Error('Argument "layout" must be an instance of Layout class.');

    this.layout = layout;
  }
}
