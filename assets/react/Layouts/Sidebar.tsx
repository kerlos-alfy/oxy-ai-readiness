export interface NavItem {
    id: string;
    label: string;
}

/**
 * docs/03-UI.md's full Navigation list also includes API Catalog, MCP,
 * Agent Skills, Commerce, Logs, Settings, and About. None of those have
 * a REST backend yet (they belong to Phases 13-15), and
 * CLAUDE.md prohibits dashboard-only functionality without an API
 * behind it, so only modules with a working `/x/*` REST surface
 * (Phase 8/11) get a nav entry here — see DECISIONS.md.
 */
export const NAV_ITEMS: NavItem[] = [
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'audit', label: 'Audit' },
    { id: 'robots', label: 'Robots' },
    { id: 'llms', label: 'LLMS' },
    { id: 'markdown', label: 'Markdown' },
    { id: 'headers', label: 'Headers' },
    { id: 'content-signals', label: 'Content Signals' },
];

interface SidebarProps {
    activeId: string;
    onNavigate: (id: string) => void;
}

export default function Sidebar({ activeId, onNavigate }: SidebarProps): JSX.Element {
    return (
        <nav aria-label="Primary" className="w-[280px] shrink-0 bg-card border-r border-slate-100 p-4">
            <ul className="flex flex-col gap-1">
                {NAV_ITEMS.map((item) => (
                    <li key={item.id}>
                        <button
                            type="button"
                            aria-current={activeId === item.id ? 'page' : undefined}
                            onClick={() => onNavigate(item.id)}
                            className={`w-full text-left rounded-btn px-3 py-2 text-sm font-medium transition duration-200 ease-in-out ${
                                activeId === item.id
                                    ? 'bg-primary/10 text-primary'
                                    : 'text-slate-700 hover:bg-slate-100'
                            }`}
                        >
                            {item.label}
                        </button>
                    </li>
                ))}
            </ul>
        </nav>
    );
}
