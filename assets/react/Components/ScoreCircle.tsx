import type { Grade } from '../Types/api';

interface ScoreCircleProps {
    score: number;
    grade: Grade;
    label: string;
}

/**
 * Hand-rolled SVG ring rather than a Chart.js donut: docs/03-UI.md
 * reserves Chart.js for the Line/Bar/Pie/Donut/Area analytics charts
 * that need the Monitoring/Reporting engines' time-series data (Phase
 * 13). A single static ring for one number doesn't need a charting
 * library at all.
 */
export default function ScoreCircle({ score, grade, label }: ScoreCircleProps): JSX.Element {
    const radius = 70;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (Math.min(Math.max(score, 0), 100) / 100) * circumference;

    const color =
        score >= 90 ? 'stroke-success' : score >= 70 ? 'stroke-primary' : score >= 40 ? 'stroke-warning' : 'stroke-danger';

    return (
        <div
            role="img"
            aria-label={`AI readiness score: ${score} percent, grade ${grade}, ${label}`}
            className="relative inline-flex items-center justify-center"
        >
            <svg width="180" height="180" viewBox="0 0 180 180" aria-hidden="true">
                <circle cx="90" cy="90" r={radius} strokeWidth="14" className="stroke-slate-100" fill="none" />
                <circle
                    cx="90"
                    cy="90"
                    r={radius}
                    strokeWidth="14"
                    fill="none"
                    strokeLinecap="round"
                    className={`${color} transition-all duration-200 ease-in-out`}
                    strokeDasharray={circumference}
                    strokeDashoffset={offset}
                    transform="rotate(-90 90 90)"
                />
            </svg>
            <div className="absolute flex flex-col items-center">
                <span className="text-4xl font-bold">{Math.round(score)}%</span>
                <span className="text-sm text-slate-500">{label}</span>
            </div>
        </div>
    );
}
