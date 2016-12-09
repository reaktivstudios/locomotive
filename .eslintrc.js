module.exports = {
    "env": {
        "browser": true,
        "es6": true
    },
    "extends": ["eslint:recommended", "plugin:react/recommended"],
    "parserOptions": {
        "ecmaFeatures": {
            "experimentalObjectRestSpread": true,
            "jsx": true
        },
        "sourceType": "module"
    },
    "plugins": [
        "react"
    ],
    "globals": {
      "jQuery": true,
      "require": true,
      "batch": true
    },
    "rules": {
        "array-bracket-spacing": [ "error", "always" ],
        "brace-style" : [ "error", "1tbs" ],
        "camelcase": [ "warn", { "properties": "never" } ],
        "computed-property-spacing": [ "error", "always" ],
        "curly" : "error",
        "eol-last": "error",
        "eqeqeq": "error",
        "indent": [ 1, "tab" ],
        "keyword-spacing": "error",
        "linebreak-style": [ "error", "unix" ],
        "no-caller": "error",
        "no-eq-null": "error",
        "no-else-return": "warn",
        "no-mixed-spaces-and-tabs": [1, "smart-tabs"],
        "no-nested-ternary": "error",
        "no-shadow": "error",
        "no-spaced-func": "error",
        "no-trailing-spaces": "error",
        "no-unused-expressions": "error",
        "no-unused-vars": "error",
        "object-curly-spacing": [ "error", "always" ],
        "quotes": [ "error", "single" ],
        "react/no-direct-mutation-state" : "off",
        "react/jsx-curly-spacing": [ 1, "always" ],
        "react/jsx-space-before-closing" : "warn",
        "semi": [ "error", "always" ],
        "semi-spacing": "warn",
        "space-before-blocks" : "error",
        "space-before-function-paren" : "error",
        "space-in-parens" : [ "error", "always" ],
        "wrap-iife": [ "error", "any" ]
    }
};
