/** @type {import('jest').Config} */
module.exports = {
    testEnvironment: 'jsdom',
    rootDir: '.',
    roots: ['<rootDir>/assets/react'],
    setupFilesAfterEnv: ['<rootDir>/assets/react/test/setupTests.ts'],
    transform: {
        '^.+\\.tsx?$': ['ts-jest', { tsconfig: '<rootDir>/tsconfig.jest.json', useESM: false }],
    },
    moduleFileExtensions: ['ts', 'tsx', 'js', 'jsx', 'json'],
    moduleNameMapper: {
        '\\.(css|less|scss)$': '<rootDir>/assets/react/test/styleMock.cjs',
    },
    testMatch: ['<rootDir>/assets/react/**/*.test.tsx', '<rootDir>/assets/react/**/*.test.ts'],
};
