import { useEffect, useMemo, useState } from 'react';
import Badge from '../Components/Badge';
import Card from '../Components/Card';
import { apiDelete, apiGet, apiPost } from '../Utils/api';

interface LicenseState {
    valid: boolean;
    tier: 'personal' | 'agency' | 'unlimited' | null;
    sites_used: number;
    sites_max: number;
    expires_at: string | null;
    trial: boolean;
    checked_at: string | null;
    network_error: boolean;
}

interface SecurityEvent {
    code: string;
    message: string;
    at: string;
}

interface UpdaterStatus {
    current_version: string;
    latest_version: string | null;
    update_available: boolean;
    released_at: string | null;
    changelog_url: string | null;
    changelog: string;
    last_checked: string | null;
    auto_update: boolean;
    license: LicenseState;
    license_activated: boolean;
    license_key_masked: string | null;
    security_events: SecurityEvent[];
    manage_license_url: string;
    support_url: string;
    update_url: string | null;
}

const buttonPrimary = 'rounded-btn bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-50';
const buttonSecondary = 'rounded-btn border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 disabled:opacity-50';

function formatDate(value: string | null): string {
    if (!value) return 'Not available';
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? value : date.toLocaleString();
}

function changelogBullets(changelog: string): string[] {
    return changelog
        .split('\n')
        .map((line) => line.trim())
        .filter((line) => /^[-*]\s+/.test(line))
        .map((line) => line.replace(/^[-*]\s+/, ''));
}

export default function LicenseUpdatesPage(): JSX.Element {
    const [status, setStatus] = useState<UpdaterStatus | null>(null);
    const [key, setKey] = useState('');
    const [showKey, setShowKey] = useState(false);
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [expanded, setExpanded] = useState(false);

    const load = async (): Promise<void> => {
        try {
            setError(null);
            const response = await apiGet<UpdaterStatus>('/updater/status');
            setStatus(response.data);
        } catch (reason) {
            setError(reason instanceof Error ? reason.message : 'Could not load updater status.');
        }
    };

    useEffect(() => {
        void load();
    }, []);

    const bullets = useMemo(() => changelogBullets(status?.changelog ?? ''), [status?.changelog]);

    const activate = async (): Promise<void> => {
        setBusy(true);
        setError(null);
        try {
            const response = await apiPost<UpdaterStatus>('/updater/license', { key });
            setStatus(response.data);
            setKey('');
        } catch (reason) {
            setError(reason instanceof Error ? reason.message : 'License activation failed.');
            await load();
        } finally {
            setBusy(false);
        }
    };

    const deactivate = async (): Promise<void> => {
        setBusy(true);
        try {
            const response = await apiDelete<UpdaterStatus>('/updater/license');
            setStatus(response.data);
            setKey('');
            setError(null);
        } catch (reason) {
            setError(reason instanceof Error ? reason.message : 'Could not remove the license.');
        } finally {
            setBusy(false);
        }
    };

    const check = async (): Promise<void> => {
        setBusy(true);
        setError(null);
        try {
            const response = await apiPost<UpdaterStatus>('/updater/check');
            setStatus(response.data);
        } catch (reason) {
            setError(reason instanceof Error ? reason.message : 'Update check failed.');
        } finally {
            setBusy(false);
        }
    };

    const setAutoUpdate = async (enabled: boolean): Promise<void> => {
        setBusy(true);
        try {
            const response = await apiPost<UpdaterStatus>('/updater/auto-update', { enabled });
            setStatus(response.data);
        } catch (reason) {
            setError(reason instanceof Error ? reason.message : 'Could not change auto-update setting.');
        } finally {
            setBusy(false);
        }
    };

    const licenseLabel = !status?.license_activated
        ? 'Not activated'
        : status.license.trial
          ? 'Trial'
          : status.license.valid
            ? 'Valid'
            : 'Invalid';
    const licenseTone = !status?.license_activated
        ? 'neutral'
        : status.license.trial
          ? 'warning'
          : status.license.valid
            ? 'success'
            : 'danger';

    return (
        <div className="flex flex-col gap-6">
            <div>
                <h1 className="text-2xl font-semibold">License &amp; Updates</h1>
                <p className="mt-1 text-sm text-slate-500">Manage your license and signed plugin updates.</p>
            </div>

            {error && <div role="alert" className="rounded-card border border-red-200 bg-red-50 p-4 text-sm text-danger">{error}</div>}

            <Card title="License">
                {!status && <p className="text-sm text-slate-500">Loading license status…</p>}
                {status && (
                    <div className="flex flex-col gap-5">
                        <div className="flex flex-wrap items-center gap-3">
                            <Badge tone={licenseTone}>{licenseLabel}</Badge>
                            {status.license.tier && <Badge tone="info">{status.license.tier}</Badge>}
                            {status.license.network_error && <span className="text-sm text-amber-700">Validation service unreachable — previous state preserved.</span>}
                        </div>

                        <div className="flex max-w-2xl flex-col gap-2 sm:flex-row">
                            <div className="relative flex-1">
                                <label htmlFor="license-key" className="sr-only">License key</label>
                                <input
                                    id="license-key"
                                    type={showKey ? 'text' : 'password'}
                                    value={key}
                                    onChange={(event) => setKey(event.target.value)}
                                    placeholder={status.license_key_masked ?? 'Enter license key'}
                                    autoComplete="off"
                                    className="w-full rounded-btn border border-slate-200 px-3 py-2 pe-20 text-sm"
                                />
                                <button type="button" onClick={() => setShowKey((value) => !value)} className="absolute inset-y-0 end-2 text-xs font-medium text-slate-500">
                                    {showKey ? 'Hide' : 'Show'}
                                </button>
                            </div>
                            <button type="button" disabled={busy || key.trim() === ''} onClick={() => void activate()} className={buttonPrimary}>Activate</button>
                            {status.license_activated && <button type="button" disabled={busy} onClick={() => void deactivate()} className={buttonSecondary}>Deactivate</button>}
                        </div>

                        {status.license_activated && status.license.valid && (
                            <dl className="grid gap-4 text-sm sm:grid-cols-3">
                                <div><dt className="text-slate-500">Tier</dt><dd className="font-medium capitalize">{status.license.tier}</dd></div>
                                <div><dt className="text-slate-500">Sites used</dt><dd className="font-medium">{status.license.sites_used} of {status.license.sites_max}</dd></div>
                                <div><dt className="text-slate-500">Expiry</dt><dd className="font-medium">{status.license.expires_at ? formatDate(status.license.expires_at) : 'No expiry reported'}</dd></div>
                            </dl>
                        )}

                        {status.license_activated && !status.license.valid && !status.license.network_error && (
                            <p className="text-sm text-danger">This license is invalid. <a className="underline" href={status.support_url} target="_blank" rel="noreferrer">Contact support</a>.</p>
                        )}
                        <p><a className="text-sm font-medium text-primary underline" href={status.manage_license_url} target="_blank" rel="noreferrer">Manage license</a></p>
                    </div>
                )}
            </Card>

            <Card title="Updates">
                {!status && <p className="text-sm text-slate-500">Loading update status…</p>}
                {status && (
                    <div className="flex flex-col gap-5">
                        <div className="grid gap-4 text-sm sm:grid-cols-3">
                            <div><p className="text-slate-500">Current version</p><p className="font-semibold">{status.current_version}</p></div>
                            <div><p className="text-slate-500">Latest version</p><div className="flex items-center gap-2"><p className="font-semibold">{status.latest_version ?? 'Not checked yet'}</p>{status.update_available && <Badge tone="warning">Update available</Badge>}</div></div>
                            <div><p className="text-slate-500">Release date</p><p className="font-semibold">{formatDate(status.released_at)}</p></div>
                        </div>

                        <div>
                            <p className="mb-2 text-sm font-medium">Changelog</p>
                            {bullets.length === 0 ? <p className="text-sm text-slate-500">No changelog has been fetched yet.</p> : (
                                <>
                                    <ul className="list-disc space-y-1 ps-5 text-sm text-slate-700">
                                        {(expanded ? bullets : bullets.slice(0, 3)).map((bullet) => <li key={bullet}>{bullet}</li>)}
                                    </ul>
                                    {bullets.length > 3 && <button type="button" className="mt-2 text-sm font-medium text-primary underline" onClick={() => setExpanded((value) => !value)}>{expanded ? 'Show less' : 'Show full changelog'}</button>}
                                </>
                            )}
                        </div>

                        <label className="flex items-center gap-3 text-sm font-medium">
                            <input type="checkbox" checked={status.auto_update} disabled={busy} onChange={(event) => void setAutoUpdate(event.target.checked)} />
                            Enable automatic updates for Oxy AI Readiness
                        </label>

                        <div className="flex flex-wrap items-center gap-3">
                            {status.update_available && status.update_url && <a href={status.update_url} className={buttonPrimary}>Update now</a>}
                            <button type="button" disabled={busy} onClick={() => void check()} className={buttonSecondary}>{busy ? 'Checking…' : 'Check for updates now'}</button>
                            <span className="text-xs text-slate-500">Last checked: {formatDate(status.last_checked)}</span>
                        </div>
                    </div>
                )}
            </Card>

            {status && status.security_events.length > 0 && (
                <Card title="Update security monitoring">
                    <ul className="space-y-3">
                        {status.security_events.map((event) => (
                            <li key={`${event.at}-${event.code}`} className="rounded-btn border border-red-100 bg-red-50 p-3 text-sm">
                                <div className="flex items-center gap-2"><Badge tone="danger">Blocked</Badge><strong>{event.code}</strong></div>
                                <p className="mt-1 text-slate-700">{event.message}</p>
                                <time className="text-xs text-slate-500">{formatDate(event.at)}</time>
                            </li>
                        ))}
                    </ul>
                </Card>
            )}
        </div>
    );
}
