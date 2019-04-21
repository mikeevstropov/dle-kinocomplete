import {
  snakeToCamel,
  duckTypingCheck,
  requiredArgument,
  insertAfterElement,
  camelKeysToSnake,
} from '../../utils';

import videoModel from '../../models/video-model';
import Configuration from '../configuration';
import BaseView from '../../base/view';
import Templating from '../templating';

export default class View extends BaseView {

  /**
   * Configuration instance.
   *
   * @type {Configuration}
   * @protected
   */
  configuration = null;

  /**
   * View constructor.
   *
   * @param {Configuration} configuration
   * @param {Layout} layout
   * @public
   */
  constructor ({
    configuration = requiredArgument('configuration'),
    layout = requiredArgument('layout'),
  } = {}) {

    super({layout});

    if (!(configuration instanceof Configuration))
      throw new Error(
        'Argument "configuration" must be an instance of Configuration class.'
      );

    this.configuration = configuration;
  }

  /**
   * Append style.
   *
   * @return {Promise}
   * @public
   */
  async appendStyle () {

    await this.layout.loadStyle({
      fileName: 'autocomplete/style.css',
    });

    const title = document.querySelector('#title');

    if (!title)
      throw new Error(
        'Element `#title` is not found.'
      );

    title.parentNode.className += ' title-row';
  }

  /**
   * Append trigger.
   *
   * @param  {boolean} error
   * @param  {function} onClick
   * @return {Promise}
   * @public
   */
  async appendTrigger ({
    error = false,
    onClick,
  } = {}) {

    const triggerButtonFile = error
      ? 'autocomplete/error-trigger-button.twig'
      : 'autocomplete/trigger-button.twig';

    const triggerButton = await this.layout.render({
      fileName: triggerButtonFile,
    });

    const title = document.querySelector('#title');

    if (!title)
      throw new Error(
        'Element `#title` is not found.'
      );

    insertAfterElement(
      triggerButton,
      title
    );

    // Click handler.
    triggerButton.onclick = event => {

      event.preventDefault();

      if (typeof onClick === 'function')
        onClick();
    };
  }

  /**
   * Clear fields.
   *
   * @return {void}
   * @public
   */
  clearFields () {

    // Field "metaTitle".
    document.querySelector('input[name="meta_title"]').value = '';

    // Field "metaDescription".
    document.querySelector('input[name="descr"]').value = '';

    // Field "metaKeywords".
    $('#keywords').tokenfield('setTokens', []);

    // Field "title".
    document.querySelector('#title').value = '';

    // Field "description".
    if (document.querySelector('.fr-wrapper')) {

      $('textarea[name="short_story"]')
        .froalaEditor('html.set', '');

      $('textarea[name="full_story"]')
        .froalaEditor('html.set', '');

    } else {

      document.querySelector(
        '[name="short_story"]'
      ).value = '';

      document.querySelector(
        '[name="full_story"]'
      ).value = '';
    }

    // Field "categories".
    const defineCategories = this.configuration
      .get('autocomplete_categories');

    if (defineCategories) {

      const select = $('#category');
      select.val([]);

      const systemVersionId = this.configuration
        .get('system_version_id');

      select.trigger(
        systemVersionId >= 12
          ? 'chosen:updated'
          : 'liszt:updated'
      );
    }

    // Extra fields.
    const extraFields = this.configuration
      .get('extra_fields');

    extraFields.forEach(field => {

      if (field.type === 'text' || field.type === 'textarea') {

        const input = document.querySelector(
          `input[name="xfield[${field.name}]"]`
        );

        if (!input)
          return;

        const links = input.getAttribute('data-rel') === 'links';

        if (links)
          $(input).tokenfield('setTokens', []);

        else
          input.value = '';
      }
    });
  }

  /**
   * Update fields.
   *
   * @param  {object} video
   * @param  {boolean} clear
   * @return {void}
   * @public
   */
  updateFields ({
    video = requiredArgument('video'),
    clear = true,
  } = {}) {

    const videoValid = duckTypingCheck({
      model: videoModel,
      value: video,
      soft: true,
    });

    if (!videoValid)
      throw new Error(
        'Unable to update fields by invalid video model.'
      );

    console.log(video);

    if (clear)
      this.clearFields();

    const postPatterns = this.configuration.get('post_patterns');
    const snakeKeysVideo = camelKeysToSnake(video);

    // Field "metaTitle".
    const metaTitle = Templating.renderString({
      template: postPatterns['meta_title'],
      context: snakeKeysVideo,
    });

    const oldMetaTitle = document
      .querySelector('input[name="meta_title"]')
      .value
      .trim();

    if (metaTitle && !oldMetaTitle)
      document.querySelector('input[name="meta_title"]')
        .value = metaTitle;

    // Field "metaDescription".
    const metaDescription = Templating.renderString({
      template: postPatterns['meta_description'],
      context: snakeKeysVideo,
    });

    const oldMetaDescription = document
      .querySelector('input[name="descr"]')
      .value
      .trim();

    if (metaDescription && !oldMetaDescription)
      document.querySelector('input[name="descr"]')
        .value = metaDescription;

    // Field "metaKeywords".
    const metaKeywords = Templating.renderString({
      template: postPatterns['meta_keywords'],
      context: snakeKeysVideo,
    });

    const metaKeywordsInput = $('#keywords');

    const oldMetaKeywords = metaKeywordsInput
      .tokenfield('getTokens');

    if (metaKeywords && !oldMetaKeywords.length)
      metaKeywordsInput.tokenfield(
        'setTokens',
        metaKeywords
      );

    // Field "title".
    const title = Templating.renderString({
      template: postPatterns['title'],
      context: snakeKeysVideo,
    });

    const oldTitle = document
      .querySelector('#title')
      .value
      .trim();

    if (title && !oldTitle)
      document.querySelector('#title')
        .value = title;

    // Field "description".
    const shortStory = Templating.renderString({
      template: postPatterns['short_story'],
      context: snakeKeysVideo,
    });

    const fullStory = Templating.renderString({
      template: postPatterns['full_story'],
      context: snakeKeysVideo,
    });

    if (document.querySelector('.fr-wrapper')) {

      const shortStoryInput = $('textarea[name="short_story"]');
      const fullStoryInput = $('textarea[name="full_story"]');

      const oldShortStory = shortStoryInput.froalaEditor('html.get').trim();
      const oldFullStory = fullStoryInput.froalaEditor('html.get').trim();

      if (shortStory && !oldShortStory)
        shortStoryInput.froalaEditor(
          'html.set',
          shortStory
        );

      if (fullStory && !oldFullStory)
        fullStoryInput.froalaEditor(
          'html.set',
          fullStory
        );

    } else {

      const oldShortStory = document
        .querySelector('[name="short_story"]')
        .value
        .trim();

      const oldFullStory = document
        .querySelector('[name="full_story"]')
        .value
        .trim();

      if (shortStory && !oldShortStory)
        document
          .querySelector('[name="short_story"]')
          .value = shortStory;

      if (fullStory && !oldFullStory)
        document
          .querySelector('[name="full_story"]')
          .value = fullStory;
    }

    // Field "categories".
    const defineCategories = this.configuration
      .get('autocomplete_categories');

    const categorySelect = $('#category');
    const oldCategories = categorySelect.val() || [];

    if (defineCategories && !oldCategories.length) {

      categorySelect.val([]);

      video.categories.forEach(category => {

        if (!category.id || !category.name)
          throw new Error('Invalid category.');

        const foundedOption = categorySelect.find(
          `option[value="${category.id}"]`
        );

        if (foundedOption.length) {
          foundedOption.prop('selected', true);
          return;
        }

        const newOption = $('<option></option>')
          .attr('value', category.id)
          .prop('selected', true)
          .text(category.name);

        categorySelect.append(newOption);
      });

      const systemVersionId = this.configuration
        .get('system_version_id');

      categorySelect.trigger(
        systemVersionId >= 12
          ? 'chosen:updated'
          : 'liszt:updated'
      );
    }

    // Extra fields.
    const videoFields = this
      .configuration
      .get('video_fields');

    const extraFields = this
      .configuration
      .get('extra_fields');

    Object.keys(videoFields).forEach(key => {

      const videoFieldName = snakeToCamel(key);
      const extraFieldName = videoFields[key];
      const videoField = video[videoFieldName];

      const extraField = extraFields.find(
        item => item.name === extraFieldName
      );

      if (!extraField || !videoField)
        return;

      if (extraField.type === 'text' || extraField.type === 'textarea') {

        const input = document.querySelector(
          `input[name="xfield[${extraFieldName}]"]`
        );

        if (!input)
          return;

        const links = input.getAttribute('data-rel') === 'links';

        const value = Array.isArray(videoField)
          ? videoField.join(', ')
          : videoField.toString();

        if (value && links) {

          const oldValue = $(input).tokenfield('getTokens');

          if (!oldValue.length)
            $(input).tokenfield('setTokens', value);

        } else if (value) {

          input.value = input.value.trim()
            ? input.value
            : value;
        }
      }
    });
  }
}
