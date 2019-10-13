"use strict";

const CleanWebpackPlugin = require("clean-webpack-plugin");
const ExtractTextWebpackPlugin = require("extract-text-webpack-plugin");
const HardSourceWebpackPlugin = require("hard-source-webpack-plugin");
const HTMLWebpackPlugin = require("html-webpack-plugin");
const glob = require("glob");
const OptimizeCssAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const path = require("path");
const TerserWebpackPlugin = require("terser-webpack-plugin");
const webpack = require("webpack");

let apiRoot = process.env.apiRoot;
if ("undefined" === typeof apiRoot) {
    apiRoot = "";
}

const webPackPromise = new Promise((resolve, reject) => {
    glob("../public/vendor/vendor.*.js", (err, files) => {
        if (0 === files.length) {
            reject("Could not resolve vendor JS");
        }

        let regEx = /\/public\/vendor\/vendor\.(.*)\.js$/g;
        let matches = regEx.exec(files[0]);
        let vendorHash = matches[1];

        resolve({
            resolve: {
                extensions: [".ts", ".js"],
                modules: [path.resolve(__dirname, "./node_modules")]
            },
            mode: "production",
            entry: {
                app: ["./src/vendor.js", "./src/app.ts"]
            },
            plugins: [
                new webpack.ProvidePlugin({
                    $: "jquery",
                    jQuery: "jquery",
                    "window.jQuery": "jquery",
                    Promise: "bluebird"
                }),
                new HardSourceWebpackPlugin(),
                new HTMLWebpackPlugin({
                    vendorHash: vendorHash,
                    template: path.resolve(__dirname, "./src/index.ejs"),
                    minify: {
                        collapseWhitespace: true,
                        removeComments: true,
                        removeRedundantAttributes: true,
                        removeScriptTypeAttributes: true,
                        removeStyleLinkTypeAttributes: true,
                        useShortDoctype: true
                    }
                }),
                new CleanWebpackPlugin(),
                new webpack.DefinePlugin({
                    __API_ROOT__: JSON.stringify(apiRoot)
                }),
                new webpack.DllReferencePlugin({
                    context: ".",
                    manifest: require(path.resolve(__dirname, "./dll/vendor.manifest.json"))
                }),
                new ExtractTextWebpackPlugin({filename: "[name].[chunkhash].css"}),
                new OptimizeCssAssetsPlugin({
                    assetNameRegExp: /\.css$/,
                    cssProcessorOptions: {discardComments: {}}
                })
            ],
            output: {
                filename: "[name].[chunkhash].js",
                path: path.resolve(__dirname, "../public/app")
            },
            module: {
                rules: [
                    {
                        test: /\.ts$/,
                        loaders: "ts-loader",
                        exclude: "/node_modules/",
                        options: {transpileOnly: true}
                    },
                    {
                        test: /\.css$/,
                        use: ExtractTextWebpackPlugin.extract({
                            fallback: "style-loader",
                            use: [
                                "css-loader",
                                {
                                    loader: "postcss-loader",
                                    options: {
                                        config: {
                                            path: path.resolve(__dirname, "./")
                                        }
                                    }
                                }
                            ]
                        })
                    },
                    {
                        test: /\.scss$/,
                        use: ExtractTextWebpackPlugin.extract({
                            fallback: "style-loader",
                            use: [
                                "css-loader",
                                'sass-loader',
                                {
                                    loader: "postcss-loader",
                                    options: {
                                        config: {
                                            path: path.resolve(__dirname, "./")
                                        }
                                    }
                                }
                            ]
                        })
                    },
                    {
                        test: /\.png$/,
                        use: ["file-loader"]
                    }
                ]
            },
            optimization: {
                minimizer: [new TerserWebpackPlugin()]
            }
        });
    })
});

module.exports = webPackPromise;
