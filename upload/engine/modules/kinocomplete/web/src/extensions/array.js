
/**
 * Clone
 *
 * @param  {object} array
 * @return {object}
 */
Array.clone = function (array) {

  return array.slice();
};

/**
 * Deep clone
 *
 * @param  {object} array
 * @return {object}
 */
Array.cloneDeep = function (array) {

  return array.map(item => {

    if (Array.isArray(item))
      return Array.cloneDeep(item);

    else if (Object.isObject(item))
      return Object.cloneDeep(item);

    else
      return item;
  });
};

/**
 * Move an array item by index
 *
 * @param  {object} array
 * @param  {string} oldIndex
 * @param  {string} newIndex
 * @return {object}
 */
Array.move = function (array, oldIndex, newIndex) {

  if (newIndex >= array.length) {

    let k = newIndex - array.length;

    while (k-- + 1)
      array.push(undefined);
  }

  const item = array[newIndex];

  array[newIndex] = array[oldIndex];
  array[oldIndex] = item;

  return array;
};
