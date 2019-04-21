const path = require('path');
const glob = require('glob');

const config = {
  mode: 'production',
  watchOptions: {
    poll: true,
  },
  performance: {
    hints: false,
  },
  module: {
    rules: [
      {
        enforce: 'pre',
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'eslint-loader',
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
      },
      {
        test: /\.css$/,
        exclude: /node_modules/,
        use: [
          'style-loader',
          'css-loader',
        ],
      },
      {
        test: /\.twig$/,
        exclude: /node_modules/,
        loader: 'twig-loader',
      },
    ],
  },
};

const index = Object.assign({}, config, {
  entry: './src/index.js',
  output: {
    publicPath: '/engine/modules/kinocomplete/web/dist/',
    filename: 'kinocomplete.min.js',
    path: path.resolve(__dirname, 'dist'),
  },
});

const injections = Object.assign({}, config, {
  entry: resolveEntries(
    glob.sync('./src/injections/*.js*')
  ),
  output: {
    publicPath: '/engine/modules/kinocomplete/web/dist/injections/',
    filename: '[name].min.js',
    path: path.resolve(__dirname, 'dist/injections'),
  },
});

module.exports = [
  index,
  injections,
];

function resolveEntries (paths) {

  const entries = {};

  paths.forEach((path) => {

    const moduleName = path
      .split('/')
      .slice(-1)[0]
      .match(/(.*).js/)[1];

    entries[moduleName] = path;
  });

  return entries;
}
