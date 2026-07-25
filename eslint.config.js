import js from '@eslint/js';
import tsPlugin from '@typescript-eslint/eslint-plugin';
import tsParser from '@typescript-eslint/parser';
import reactPlugin from 'eslint-plugin-react';
import reactHooksPlugin from 'eslint-plugin-react-hooks';
import jsxA11yPlugin from 'eslint-plugin-jsx-a11y';

export default [
    js.configs.recommended,
    {
        files: ['assets/react/**/*.{ts,tsx}'],
        languageOptions: {
            parser: tsParser,
            parserOptions: {
                ecmaFeatures: { jsx: true },
                sourceType: 'module',
            },
            globals: {
                window: 'readonly',
                document: 'readonly',
                fetch: 'readonly',
                console: 'readonly',
            },
        },
        plugins: {
            '@typescript-eslint': tsPlugin,
            react: reactPlugin,
            'react-hooks': reactHooksPlugin,
            'jsx-a11y': jsxA11yPlugin,
        },
        settings: {
            react: { version: 'detect' },
        },
        rules: {
            ...tsPlugin.configs.recommended.rules,
            ...reactPlugin.configs.recommended.rules,
            ...reactHooksPlugin.configs.recommended.rules,
            ...jsxA11yPlugin.configs.recommended.rules,
            'react/react-in-jsx-scope': 'off',
            'no-unused-vars': 'off',
            '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
            // TypeScript's own compiler (see `npm run typecheck`) already catches undefined
            // symbols with full knowledge of ambient DOM/JSX types; eslint's no-undef doesn't
            // know those types and false-positives on them, per typescript-eslint's own docs.
            'no-undef': 'off',
        },
    },
    {
        files: ['assets/react/**/*.test.{ts,tsx}'],
        languageOptions: {
            globals: {
                describe: 'readonly',
                it: 'readonly',
                test: 'readonly',
                expect: 'readonly',
                beforeEach: 'readonly',
                jest: 'readonly',
            },
        },
    },
    {
        ignores: ['dist/**', 'vendor/**', 'node_modules/**'],
    },
];
