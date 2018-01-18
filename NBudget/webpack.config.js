var Encore = require('@symfony/webpack-encore');

Encore

    .setOutputPath('web/build/')
    .setPublicPath('/build')

    .addEntry('app', './assets/js/vendor.js')
    .addStyleEntry('global', './assets/css/vendor.scss')
    .addStyleEntry('global', './assets/css/global.scss')

    // allow sass/scss files to be processed
    .enableSassLoader()

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    .enableSourceMaps(!Encore.isProduction())

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // show OS notifications when builds finish/fail
    .enableBuildNotifications()
;

module.exports = Encore.getWebpackConfig();
