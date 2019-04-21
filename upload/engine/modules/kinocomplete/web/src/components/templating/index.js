import {requiredArgument} from '../../utils';
import Twig from 'twig';

export default class Templating {

  /**
   * Render string.
   *
   * @param  {string} template
   * @param  {Object} context
   * @return string
   * @public
   */
  static renderString ({
    template = requiredArgument('template'),
    context = {},
  } = {}) {
    // Single brackets to double.
    template = template.replace(
      /{([^{}%]+?)}(?![}%])/g,
      '{{$1}}'
    );

    const instance = Twig.twig({
      data: template,
    });

    return instance.render(context);
  }
}
