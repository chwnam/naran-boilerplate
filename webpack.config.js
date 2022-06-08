const defaultConfig = require('@wordpress/scripts/config/webpack.config')
const {hasArgInCLI} = require('@wordpress/scripts/utils')
const MiniCSSExtractPlugin = require('mini-css-extract-plugin')

const isProduction = process.env.NODE_ENV === 'production'
const hasReactFastRefresh = hasArgInCLI('--hot') && !isProduction

// Modify MiniCSSExtractPlugin to create css files in the 'assets/css/dist' directory.
const plugins = defaultConfig.plugins,
    pluginIdx = plugins.findIndex(plugin => plugin instanceof MiniCSSExtractPlugin)

if (pluginIdx > -1) {
    const newPlugin = new MiniCSSExtractPlugin({
        filename: '../../css/dist/[name].css',
    })
    plugins.splice(pluginIdx, 1, newPlugin)
}

if (!isProduction) {
    module.exports = {
        ...defaultConfig,
        // To support HMR (Hot Module Replacement) feature.
        devServer: {
            ...defaultConfig.devServer,
            allowedHosts: [
                '127.0.0.1',
                'localhost',
                '.dev.site', // NOTE: Modify it to your development server domain. A leading dot indicates subdomain wildcard.
            ],
            hot: hasReactFastRefresh,
        },
    }
} else {
    module.exports = defaultConfig
}
