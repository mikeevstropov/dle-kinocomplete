import {
  createElementFromHTML,
  requiredArgument,
} from '../../utils';

import BaseComponent from '../../base/component';

export default class Layout extends BaseComponent {

  /**
   * Constructor.
   *
   * @param configuration
   * @public
   */
  constructor ({
    configuration = requiredArgument('configuration'),
  } = {}) {

    super({configuration});

    this.tag = configuration.get('system_version_tag');

    console.log('Layout initialized.');
  }

  /**
   * Load css file and insert
   * into document head tag.
   *
   * @param  fileName
   * @return {Promise}
   * @public
   */
  loadStyle ({
    fileName = requiredArgument('fileName'),
  }) {

    return import(`../../../layout/${this.tag}/${fileName}`);
  }

  /**
   * Get file contents rendered by Twig.
   *
   * @param   {string} fileName File name
   * @param   {object} context Rendering context
   * @returns {Promise} Rendering result
   * @public
   */
  async render ({
    fileName = requiredArgument('fileName'),
    context = {},
  }) {

    const module = await import(`../../../layout/${this.tag}/${fileName}`);
    const template = module.default;
    const html = template(context);

    return createElementFromHTML(html);
  }
}
