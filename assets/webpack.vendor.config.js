"use strict";

const CleanWebpackPlugin = require("clean-webpack-plugin");
const ExtractTextWebpackPlugin = require("extract-text-webpack-plugin");
const OptimizeCssAssetsWebpackPlugin = require("optimize-css-assets-webpack-plugin");
const path = require("path");
const TerserWebpackPlugin = require("terser-webpack-plugin");
const webpack = require("webpack");

module.exports = {
    resolve: {
        extensions: [".js", ".jsx", ".json"],
        modules: [path.relative(__dirname, "./node_modules")]
    },
    mode: "production",
    entry: {vendor: ["./src/vendor.js"]},
    output: {
        filename: "[name].[chunkhash].js",
        path: path.resolve(__dirname, "../public/vendor"),
        library: "[name]_dll"
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery",
            "window.jQuery": "jquery"
        }),
        new CleanWebpackPlugin(),
        new webpack.DllPlugin({
            name: "[name]_dll",
            path: path.resolve(__dirname, "./dll/[name].manifest.json")
        }),
        new ExtractTextWebpackPlugin({filename: "[name].[chunkhash].css"}),
        new OptimizeCssAssetsWebpackPlugin({
            assetNameRegExp: /\.css$/,
            cssProcessorOptions: {discardComments: {}}
        })
    ],
    module: {
        rules: [
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
                test: /\.(gif|png|jpg|svg|eot|ttf|woff|woff2)$/,
                use: ["file-loader"],
                exclude: "/node_modules/"
            },
            {
                test: require.resolve("jquery"),
                use: [
                    {
                        loader: "expose-loader",
                        options: "$"
                    },
                    {
                        loader: "expose-loader",
                        options: "jQuery"
                    }
                ],
                exclude: "/node_modules/"
            }
        ]
    },
    optimization: {
        minimizer: [
            new TerserWebpackPlugin({
                terserOptions: {
                    output: {
                        comments: /^\**!|@preserve|@license|@cc_on/
                    }
                }
            })
        ]
    }
};
