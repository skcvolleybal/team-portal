const angularEslintPlugin = require("@angular-eslint/eslint-plugin");
const angularTemplatePlugin = require("@angular-eslint/eslint-plugin-template");
const tsParser = require("@typescript-eslint/parser");

module.exports = [
    {
        files: ["**/*.ts"],
        languageOptions: {
            parser: tsParser,
        },
        plugins: {
            "@angular-eslint": angularEslintPlugin,
        },
        rules: {
            // Core ESLint rules
            semi: ["error", "always"],
            quotes: ["warn", "double"],
            "prefer-const": "error",
            "no-var": "error",
            "no-bitwise": "warn",
            "no-console": ["warn", { allow: ["error", "warn"] }],
            "max-len": ["warn", 140],

            // Angular-specific rules
            "@angular-eslint/component-selector": [
                "warn",
                {
                    type: "element",
                    prefix: "tp",
                    style: "kebab-case",
                },
            ],
            "@angular-eslint/component-class-suffix": [
                "error",
                { suffixes: ["Component"] },
            ],
            "@angular-eslint/directive-class-suffix": [
                "error",
                { suffixes: ["Directive"] },
            ],
            "@angular-eslint/no-output-on-prefix": ["error"],
            "@angular-eslint/no-input-rename": ["error"],
            "@angular-eslint/no-output-rename": ["error"],
            "@angular-eslint/use-lifecycle-interface": ["error"],
            "@angular-eslint/use-pipe-transform-interface": ["error"],
        },
    },
    {
        files: ["**/*.html"],
        plugins: {
            "@angular-eslint/template": angularTemplatePlugin,
        },
        languageOptions: {
            parser: require("@angular-eslint/template-parser"),
        },
        rules: {
            // Angular template rules
            "@angular-eslint/template/no-any": "warn",
            "@angular-eslint/template/no-call-expression": "warn",
        },
    },
];
