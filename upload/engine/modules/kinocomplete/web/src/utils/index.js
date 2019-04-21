/**
 * Throwing an error of required argument
 *
 * @param  {string} name
 * @return {*}
 */
export function requiredArgument (name) {

  throw new Error(
    name
      ? 'Required argument "'+ name +'" is not defined.'
      : 'Required argument is not defined.'
  );
}

/**
 * Create DOM element by HTML string
 *
 * @param   {string} htmlString
 * @returns {Node}
 */
export function createElementFromHTML (htmlString) {

  const div = document.createElement('div');
  div.innerHTML = htmlString.trim();

  return div.firstChild;
}

/**
 * DOM element to string
 *
 * @param   {string} htmlElement
 * @returns {string}
 */
export function htmlElementToString (htmlElement) {

  return htmlElement.outerHTML;
}

/**
 * Insert DOM element after reference element
 *
 * @param   {object} element
 * @param   {object} reference
 * @returns {*}
 */
export function insertAfterElement (element, reference) {

  const parent = reference.parentNode;
  const next = reference.nextSibling;

  return parent.insertBefore(element, next)
    || parent.appendChild(element);
}

/**
 * Duck type checking by model fields.
 *
 * @param   {object} value
 * @param   {object} model
 * @param   {boolean} empty
 * @param   {boolean} soft
 * @returns {boolean}
 */
export function duckTypingCheck ({
  value = requiredArgument('value'),
  model = requiredArgument('value'),
  empty = true,
  soft = false,
} = {}) {

  if (!Object.isObject(model))
    throw new Error(
      'Model of Duck Type Checking must be a non-empty object.'
    );

  if (
    empty &&
    (value === '' || value === undefined || value === null)
  ) return true;

  let valid = true;

  if (soft) {

    const valueWithoutExtraFields = {};

    Object.keys(model).forEach(field => {

      if (field in value)
        valueWithoutExtraFields[field] = value[field];
    });

    value = valueWithoutExtraFields;
  }

  Object.keys(model).forEach(
    key => valid = value.hasOwnProperty(key)
      ? valid
      : false
  );

  return valid;
}

/**
 * Duck type checking of many
 * values by model fields.
 *
 * @param   {Array} values
 * @param   {object} model
 * @param   {boolean} empty
 * @param   {boolean} soft
 * @returns {boolean}
 */
export function duckTypingCheckMulti ({
  values = requiredArgument('values'),
  model = requiredArgument('model'),
  empty = true,
  soft = true,
} = {}) {

  if (!Array.isArray(values))
    throw new Error(
      'Value of the Multi Duck Type Checking must be an Array.'
    );

  let valid = true;

  values.forEach(
    (value) => valid = duckTypingCheck({value, model, empty, soft})
      ? valid
      : false
  );

  return valid;
}

/**
 * Snake case to camel case.
 *
 * @param  {string} string
 * @return {String}
 */
export function snakeToCamel (string) {

  return string.replace(
    /(_\w)/g,
    m => m[1].toUpperCase()
  );
}

/**
 * Camel case to snake case.
 *
 * @param  string
 * @return string
 */
export function camelToSnake (string) {

  if (typeof string !== 'string')
    return string;

  const upperChars = string.match(/([A-Z])/g);

  if (!upperChars)
    return string;

  let str = string.toString();

  for (let i = 0, n = upperChars.length; i < n; i++) {

    str = str.replace(
      new RegExp(upperChars[i]),
      '_' + upperChars[i].toLowerCase()
    );
  }

  if (str.slice(0, 1) === '_')
    str = str.slice(1);

  return str;
}

/**
 * Camel keys of object to snake keys.
 *
 * @param  object
 * @return object
 */
export function camelKeysToSnake (object) {

  if (!Object.isObject(object))
    return object;

  const result = {};

  Object.keys(object).forEach(key => {

    if (object.hasOwnProperty(key)) {

      const snakeKey = camelToSnake(key);
      result[snakeKey] = object[key];
    }
  });

  return result;
}
