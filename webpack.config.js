const path = require('path');

module.exports = {
    mode: 'production',
    entry: {
        'fontawesome.compiled': './assets/js/fontawesome-entry.js',
    },
    output: {
        path: path.resolve(__dirname, 'assets/js/compiled'),
        filename: '[name].js',
        clean: true,
    },
    module: {
        rules: [
            {
                test: /\.css$/i,
                use: ['style-loader', 'css-loader'],
            },
        ],
    },
};
