const mode = process.env.NODE_ENV || "development";
const devtool = mode === "development" ? "source-map" : false;
const path = require( 'path' );

let webpackConfig = {
	entry: {
		"index": "./blocks/src/main.js"
	},
	output: {
		path: __dirname + "/blocks/build",
		filename: "[name].js"
	},
	mode: mode,
	devtool: devtool,
	watch: true,
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: [
					{
						loader: "babel-loader"
					}
				]
			}
		]
	}
};

module.exports = webpackConfig;

