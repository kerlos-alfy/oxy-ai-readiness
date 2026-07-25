import { render, screen, waitFor } from '@testing-library/react';
import { axe } from 'jest-axe';
import DashboardPage from './DashboardPage';
import { mockFetchRoutes } from '../test/mockFetch';

describe('DashboardPage', () => {
    it('answers how-ready/what-is-broken/how-to-fix from live REST data', async () => {
        mockFetchRoutes({
            '/score': {
                success: true,
                data: { score: 92, grade: 'A', label: 'Excellent', confidence: 'high', trend: 'up', calculated_at: '2026-07-25T00:00:00Z' },
            },
            '/audit': {
                success: true,
                data: {
                    scan_type: 'quick',
                    summary: {},
                    score: { score: 92, grade: 'A', label: 'Excellent', confidence: 'high', trend: 'up', calculated_at: '2026-07-25T00:00:00Z' },
                    duration_ms: 12,
                    started_at: '2026-07-25T00:00:00Z',
                    results: [
                        { resource_id: 'markdown', validator: 'markdown-validator', status: 'fail', message: 'Markdown endpoint missing.', execution_time_ms: 1 },
                    ],
                },
            },
            '/recommendations': {
                success: true,
                data: [
                    { id: 'rec-1', title: 'Generate Markdown Endpoint', description: 'Publish the markdown negotiation declaration.', category: 'markdown', priority: 'high', auto_fix_available: true },
                ],
            },
        });

        const { container } = render(<DashboardPage />);

        expect(await screen.findByText(/92%/)).toBeInTheDocument();
        expect(await screen.findByText('Markdown endpoint missing.')).toBeInTheDocument();
        expect(await screen.findByText('Generate Markdown Endpoint')).toBeInTheDocument();

        await waitFor(async () => {
            expect(await axe(container)).toHaveNoViolations();
        });
    });

    it("says no audit has run yet rather than inventing data when /audit returns null", async () => {
        mockFetchRoutes({
            '/score': { success: true, data: { score: 0, grade: 'F', label: 'Poor', confidence: 'low', trend: 'flat', calculated_at: '2026-07-25T00:00:00Z' } },
            '/audit': { success: true, data: null },
            '/recommendations': { success: true, data: [] },
        });

        render(<DashboardPage />);

        expect(await screen.findByText(/no audit has run yet/i)).toBeInTheDocument();
    });
});
