import Configuration from '../../../../src/components/configuration';
import axios from 'axios';

describe('checking configuration component', () => {

  test('constructor of `Configuration` must return an instance', async () => {

    expect.assertions(2);

    const client = axios.create();

    const configuration = new Configuration({
      client,
    });

    expect(
      configuration instanceof Configuration
    ).toBeTruthy();

    try {

      new Configuration();

    } catch (e) {

      expect(e instanceof Error).toBeTruthy();
    }
  });

  test('method `has` must return a boolean value', async () => {

    expect.assertions(2);

    const client = axios.create();

    const values = {
      key: 'value',
    };

    const configuration = new Configuration({
      values,
      client,
    });

    expect(configuration.has('key')).toBeTruthy();
    expect(configuration.has('value')).toBeFalsy();
  });

  test('method `get` must return a value', async () => {

    expect.assertions(2);

    const client = axios.create();

    const values = {
      key: 'value',
    };

    const configuration = new Configuration({
      values,
      client,
    });

    expect(configuration.get('key')).toBe('value');

    try {

      configuration.get('value');

    } catch (e) {

      expect(e instanceof Error).toBeTruthy();
    }
  });

  test('method `load` must return an object', async () => {

    expect.assertions(4);

    const client = axios.create();

    client.get = jest.fn();

    client.get.mockReturnValueOnce(
      new Promise(
        resolve => resolve({
          data: {
            key: 'value',
          },
        })
      )
    );

    const configuration = new Configuration({
      client,
    });

    await configuration.load();

    expect(client.get.mock.calls.length).toBe(1);
    expect(configuration.get('key')).toBe('value');
    expect(configuration.ready).toBeTruthy();
    expect(configuration.loading).toBeFalsy();
  });
});
