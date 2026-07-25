interface HeaderProps {
    title: string;
}

export default function Header({ title }: HeaderProps): JSX.Element {
    return (
        <header className="h-[72px] shrink-0 flex items-center justify-between px-6 border-b border-slate-100 bg-card">
            <h1 className="text-xl font-semibold">{title}</h1>
            <span className="text-sm text-slate-500">Oxy AI Readiness v{window.oxyAiReadiness?.version ?? ''}</span>
        </header>
    );
}
