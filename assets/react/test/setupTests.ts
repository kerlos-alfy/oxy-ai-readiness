import '@testing-library/jest-dom';
import { toHaveNoViolations } from 'jest-axe';

expect.extend(toHaveNoViolations);

window.oxyAiReadiness = {
    restUrl: 'https://example.test/wp-json/oxy-ai/v1',
    nonce: 'test-nonce',
    version: '0.1.0',
};
