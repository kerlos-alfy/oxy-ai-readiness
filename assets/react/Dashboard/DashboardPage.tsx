import Card from '../Components/Card';
import Badge from '../Components/Badge';
import ScoreCircle from '../Components/ScoreCircle';
import { useApiGet } from '../Hooks/useApi';
import type { AuditReport, Recommendation, ScoreResult } from '../Types/api';

function statusTone(status: string): 'success' | 'warning' | 'danger' {
    if (status === 'pass') {
        return 'success';
    }

    return status === 'warn' ? 'warning' : 'danger';
}

/**
 * Answers docs/03-UI.md's three Dashboard Goal questions with live REST
 * data only: `/score` (how ready), `/audit` last report's failing
 * results (what's broken), `/recommendations` (how to fix). No mock or
 * placeholder numbers — if a report hasn't run yet, the screen says so
 * and offers the real "Run Audit" action rather than inventing data.
 */
export default function DashboardPage(): JSX.Element {
    const score = useApiGet<ScoreResult>('/score');
    const audit = useApiGet<AuditReport | null>('/audit');
    const recommendations = useApiGet<Recommendation[]>('/recommendations');

    const brokenResults = (audit.data?.results ?? []).filter((result) => result.status !== 'pass');

    return (
        <div className="flex flex-col gap-6">
            <Card>
                <div className="flex items-center gap-8">
                    {score.loading && <p>Loading score…</p>}
                    {score.error && <p className="text-danger">{score.error}</p>}
                    {score.data && (
                        <ScoreCircle score={score.data.score} grade={score.data.grade} label={score.data.label} />
                    )}
                    <div>
                        <h2 className="text-lg font-semibold mb-1">How ready is my website?</h2>
                        <p className="text-slate-500 text-sm">
                            {score.data
                                ? `Grade ${score.data.grade} · trend ${score.data.trend} · confidence ${score.data.confidence}`
                                : 'Calculating…'}
                        </p>
                    </div>
                </div>
            </Card>

            <Card title="What is broken?">
                {audit.loading && <p>Loading last audit…</p>}
                {!audit.loading && !audit.data && (
                    <p className="text-slate-500">No audit has run yet. Run one from the Audit screen.</p>
                )}
                {brokenResults.length === 0 && audit.data && <p className="text-success">Nothing broken. Nice.</p>}
                {brokenResults.length > 0 && (
                    <ul className="flex flex-col gap-3">
                        {brokenResults.map((result) => (
                            <li key={`${result.resource_id}-${result.validator}`} className="flex items-center gap-3">
                                <Badge tone={statusTone(result.status)}>{result.status.toUpperCase()}</Badge>
                                <span className="text-sm">{result.message}</span>
                            </li>
                        ))}
                    </ul>
                )}
            </Card>

            <Card title="How can I fix it?">
                {recommendations.loading && <p>Loading recommendations…</p>}
                {recommendations.data?.length === 0 && (
                    <p className="text-slate-500">No recommendations right now.</p>
                )}
                {recommendations.data && recommendations.data.length > 0 && (
                    <ul className="flex flex-col gap-3">
                        {recommendations.data.map((recommendation) => (
                            <li key={recommendation.id} className="flex items-center justify-between gap-3">
                                <div>
                                    <p className="text-sm font-medium">{recommendation.title}</p>
                                    <p className="text-sm text-slate-500">{recommendation.description}</p>
                                </div>
                                <Badge tone="info">{recommendation.priority}</Badge>
                            </li>
                        ))}
                    </ul>
                )}
            </Card>
        </div>
    );
}
