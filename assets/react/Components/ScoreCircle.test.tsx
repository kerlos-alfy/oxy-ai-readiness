import { render, screen } from '@testing-library/react';
import { axe } from 'jest-axe';
import ScoreCircle from './ScoreCircle';

describe('ScoreCircle', () => {
    it('exposes the score as an accessible name, not just visually', async () => {
        const { container } = render(<ScoreCircle score={92} grade="A" label="Excellent" />);

        expect(screen.getByRole('img', { name: /92 percent/i })).toBeInTheDocument();
        expect(await axe(container)).toHaveNoViolations();
    });
});
