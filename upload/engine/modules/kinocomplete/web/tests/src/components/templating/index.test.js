import Templating from '../../../../src/components/templating';

describe('checking templating component', () => {

  test('method `renderString` must return a string', () => {

    const template = 'My {{variable}}.';
    const expected = 'My template.';
    const context = {variable: 'template'};

    const result = Templating.renderString({
      template,
      context,
    });

    expect(result).toBe(expected);
  });

  test('method `renderString` with single brackets must return a string', () => {

    const template = 'My {% if third is not defined %}{first}{% endif %} {second}.';
    const expected = 'My amazing template.';

    const context = {
      first: 'amazing',
      second: 'template',
    };

    const result = Templating.renderString({
      template,
      context,
    });

    expect(result).toBe(expected);
  });
});
