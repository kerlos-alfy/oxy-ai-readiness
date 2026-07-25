import { useState } from 'react';
import Sidebar, { NAV_ITEMS } from './Layouts/Sidebar';
import Header from './Layouts/Header';
import DashboardPage from './Dashboard/DashboardPage';
import AuditPage from './Audit/AuditPage';
import RobotsPage from './Robots/RobotsPage';
import LlmsPage from './Llms/LlmsPage';
import MarkdownPage from './Markdown/MarkdownPage';
import HeadersPage from './Headers/HeadersPage';
import ContentSignalsPage from './ContentSignals/ContentSignalsPage';

const SCREENS: Record<string, () => JSX.Element> = {
    dashboard: DashboardPage,
    audit: AuditPage,
    robots: RobotsPage,
    llms: LlmsPage,
    markdown: MarkdownPage,
    headers: HeadersPage,
    'content-signals': ContentSignalsPage,
};

export default function App(): JSX.Element {
    const [activeId, setActiveId] = useState('dashboard');
    const activeLabel = NAV_ITEMS.find((item) => item.id === activeId)?.label ?? 'Dashboard';
    const ActiveScreen = SCREENS[activeId] ?? DashboardPage;

    return (
        <div className="flex h-screen w-full bg-surface text-slate-900">
            <Sidebar activeId={activeId} onNavigate={setActiveId} />
            <div className="flex flex-1 flex-col min-w-0">
                <Header title={activeLabel} />
                <main className="flex-1 overflow-y-auto p-6 max-w-[1600px] w-full mx-auto">
                    <ActiveScreen />
                </main>
            </div>
        </div>
    );
}
