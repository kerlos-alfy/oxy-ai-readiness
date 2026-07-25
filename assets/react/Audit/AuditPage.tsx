import { useState } from 'react';
import Card from '../Components/Card';
import Badge from '../Components/Badge';
import Button from '../Components/Button';
import { useApiGet } from '../Hooks/useApi';
import { apiPost } from '../Utils/api';
import type { AuditReport } from '../Types/api';

function statusTone(status: string): 'success' | 'warning' | 'danger' {
    if (status === 'pass') {
        return 'success';
    }

    return status === 'warn' ? 'warning' : 'danger';
}

export default function AuditPage(): JSX.Element {
    const audit = useApiGet<AuditReport | null>('/audit');
    const [scanning, setScanning] = useState(false);

    const runAudit = (): void => {
        setScanning(true);

        apiPost<AuditReport>('/audit/start', { type: 'quick' })
            .finally(() => {
                setScanning(false);
                audit.reload();
            });
    };

    return (
        <div className="flex flex-col gap-6">
            <Card>
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-lg font-semibold">Audit</h2>
                        {scanning && (
                            <p role="status" className="text-sm text-slate-500 mt-1">
                                Scanning…
                            </p>
                        )}
                    </div>
                    <Button onClick={runAudit} disabled={scanning}>
                        {scanning ? 'Scanning…' : 'Run Audit'}
                    </Button>
                </div>
                {scanning && (
                    <div className="mt-4 h-2 rounded-progress bg-slate-100 overflow-hidden" aria-hidden="true">
                        <div className="h-full w-1/2 bg-primary animate-pulse" />
                    </div>
                )}
            </Card>

            <Card title="Checklist">
                {audit.loading && <p>Loading…</p>}
                {!audit.loading && !audit.data && (
                    <p className="text-slate-500">No audit has run yet.</p>
                )}
                {audit.data && (
                    <ul className="flex flex-col gap-3">
                        {audit.data.results.map((result) => (
                            <li
                                key={`${result.resource_id}-${result.validator}`}
                                className="flex items-center gap-3 border-b border-slate-100 pb-3 last:border-0"
                            >
                                <Badge tone={statusTone(result.status)}>{result.status.toUpperCase()}</Badge>
                                <div>
                                    <p className="text-sm font-medium">{result.resource_id}</p>
                                    <p className="text-sm text-slate-500">{result.message}</p>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </Card>
        </div>
    );
}
