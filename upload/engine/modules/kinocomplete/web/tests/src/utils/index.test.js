import {
  camelToSnake,
  camelKeysToSnake,
} from '../../../src/utils';

describe('checking utils', () => {

  test('method `camelToSnake` must return a string', () => {

    const string = 'camelCase';
    const expected = 'camel_case';

    const result = camelToSnake(string);

    expect(result).toBe(expected);
  });

  test('method `camelKeysToSnake` must return a string', () => {

    const object = {
      'camelKey': 'value',
    };

    const expected = {
      'camel_key': 'value',
    };

    const result = camelKeysToSnake(object);

    expect(result).toEqual(expected);
  });
});
