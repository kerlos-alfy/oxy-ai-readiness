import { render, screen } from '@testing-library/react';
import Badge from './Badge';

describe('Badge', () => {
    it('renders its label with the tone-appropriate styling', () => {
        render(<Badge tone="success">PASS</Badge>);

        expect(screen.getByText('PASS')).toBeInTheDocument();
    });
});
