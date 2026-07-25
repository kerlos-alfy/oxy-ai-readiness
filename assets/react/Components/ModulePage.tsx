import { useState } from 'react';
import Card from './Card';
import Badge from './Badge';
import Button from './Button';
import { useApiGet } from '../Hooks/useApi';
import { apiPost } from '../Utils/api';
import type { ModulePreview, ModuleSlug, ModuleStatus, ValidationResult } from '../Types/api';

interface ModulePageProps {
    slug: ModuleSlug;
    title: string;
}

/**
 * One screen definition reused by all five Phase 8/11 modules (Robots,
 * LLMS, Markdown, Headers, Content Signals) since their REST surface is
 * identical — `/{slug}`, `/{slug}/preview`, `/{slug}/save`,
 * `/{slug}/validate`, `/{slug}/reset` (see routes/api.php). Each
 * module's aspirational per-module screen in docs/03-UI.md (visual
 * crawler-table builder, import/export, negotiation settings, etc.)
 * needs data this project's engines don't produce yet (persisted
 * settings, multi-row config) — deferred, see DECISIONS.md. This screen
 * covers the REST surface that does exist: preview generated content,
 * publish it, validate it, and reset to default.
 */
export default function ModulePage({ slug, title }: ModulePageProps): JSX.Element {
    const status = useApiGet<ModuleStatus>(`/${slug}`);
    const preview = useApiGet<ModulePreview>(`/${slug}/preview`);
    const [validation, setValidation] = useState<ValidationResult[]>();
    const [busy, setBusy] = useState(false);
    const [message, setMessage] = useState<string>();

    const runValidate = (): void => {
        setBusy(true);
        setMessage(undefined);

        apiPost<ValidationResult[]>(`/${slug}/validate`)
            .then((envelope) => {
                if (envelope.success && envelope.data) {
                    setValidation(envelope.data);
                } else {
                    setMessage(envelope.message ?? 'Validation failed.');
                }
            })
            .finally(() => setBusy(false));
    };

    const runSave = (): void => {
        setBusy(true);
        setMessage(undefined);

        apiPost(`/${slug}/save`)
            .then((envelope) => {
                setMessage(envelope.success ? 'Published.' : envelope.message ?? 'Save failed.');
                status.reload();
            })
            .finally(() => setBusy(false));
    };

    const runReset = (): void => {
        setBusy(true);
        setMessage(undefined);

        apiPost(`/${slug}/reset`)
            .then((envelope) => {
                setMessage(envelope.success ? 'Reset to default.' : envelope.message ?? 'Reset failed.');
                status.reload();
                preview.reload();
            })
            .finally(() => setBusy(false));
    };

    return (
        <div className="flex flex-col gap-6">
            <Card>
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-lg font-semibold">{title}</h2>
                        {status.data && (
                            <Badge tone={status.data.published ? 'success' : 'warning'}>
                                {status.data.published ? `Published · v${status.data.version}` : 'Not published'}
                            </Badge>
                        )}
                    </div>
                    <div className="flex gap-2">
                        <Button variant="secondary" onClick={runValidate} disabled={busy}>
                            Validate
                        </Button>
                        <Button onClick={runSave} disabled={busy}>
                            Publish
                        </Button>
                        <Button variant="ghost" onClick={runReset} disabled={busy}>
                            Reset
                        </Button>
                    </div>
                </div>
                {message && (
                    <p role="status" className="mt-3 text-sm text-slate-600">
                        {message}
                    </p>
                )}
            </Card>

            <Card title="Preview">
                {preview.loading && <p>Loading preview…</p>}
                {preview.error && <p className="text-danger">{preview.error}</p>}
                {preview.data && (
                    <pre className="rounded-input bg-surface p-4 text-sm overflow-x-auto whitespace-pre-wrap">
                        {preview.data.content}
                    </pre>
                )}
            </Card>

            {validation && (
                <Card title="Validation results">
                    <ul className="flex flex-col gap-2">
                        {validation.map((result) => (
                            <li key={result.validator} className="flex items-center gap-3">
                                <Badge tone={result.status === 'pass' ? 'success' : result.status === 'warn' ? 'warning' : 'danger'}>
                                    {result.status.toUpperCase()}
                                </Badge>
                                <span className="text-sm">{result.message}</span>
                            </li>
                        ))}
                    </ul>
                </Card>
            )}
        </div>
    );
}
