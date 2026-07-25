import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import Sidebar, { NAV_ITEMS } from './Sidebar';

describe('Sidebar', () => {
    it('marks the active item with aria-current and calls onNavigate on click', async () => {
        const onNavigate = jest.fn();
        render(<Sidebar activeId="dashboard" onNavigate={onNavigate} />);

        expect(screen.getByRole('button', { name: 'Dashboard' })).toHaveAttribute('aria-current', 'page');

        await userEvent.click(screen.getByRole('button', { name: 'Audit' }));

        expect(onNavigate).toHaveBeenCalledWith('audit');
    });

    it('renders only nav items with a real REST-backed screen', () => {
        const ids = NAV_ITEMS.map((item) => item.id);

        expect(ids).toEqual(['dashboard', 'audit', 'robots', 'llms', 'markdown', 'headers', 'content-signals']);
    });
});
