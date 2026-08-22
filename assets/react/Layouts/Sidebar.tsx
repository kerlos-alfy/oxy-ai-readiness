export interface NavItem {
    id: string;
    label: string;
    group?: 'Overview' | 'Signals' | 'Settings';
}

export const NAV_ITEMS: NavItem[] = [
    { id: 'dashboard', label: 'Dashboard', group: 'Overview' },
    { id: 'audit', label: 'Audit', group: 'Overview' },
    { id: 'robots', label: 'Robots', group: 'Signals' },
    { id: 'llms', label: 'LLMS', group: 'Signals' },
    { id: 'markdown', label: 'Markdown', group: 'Signals' },
    { id: 'headers', label: 'Headers', group: 'Signals' },
    { id: 'content-signals', label: 'Content Signals', group: 'Signals' },
    { id: 'license-updates', label: 'License & Updates', group: 'Settings' },
];

interface SidebarProps {
    activeId: string;
    onNavigate: (id: string) => void;
}

export default function Sidebar({ activeId, onNavigate }: SidebarProps): JSX.Element {
    const groups: Array<NonNullable<NavItem['group']>> = ['Overview', 'Signals', 'Settings'];

    return (
        <nav aria-label="Primary" className="w-[280px] shrink-0 bg-card border-r border-slate-100 p-4">
            {groups.map((group) => (
                <div key={group} className="mb-5 last:mb-0">
                    <p className="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{group}</p>
                    <ul className="flex flex-col gap-1">
                        {NAV_ITEMS.filter((item) => item.group === group).map((item) => (
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
                </div>
            ))}
        </nav>
    );
}
