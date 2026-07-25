type BadgeTone = 'success' | 'warning' | 'danger' | 'info';

interface BadgeProps {
    tone: BadgeTone;
    children: string;
}

const TONE_CLASSES: Record<BadgeTone, string> = {
    success: 'bg-success/10 text-success',
    warning: 'bg-warning/10 text-warning',
    danger: 'bg-danger/10 text-danger',
    info: 'bg-primary/10 text-primary',
};

export default function Badge({ tone, children }: BadgeProps): JSX.Element {
    return (
        <span className={`inline-block rounded-progress px-3 py-1 text-xs font-medium ${TONE_CLASSES[tone]}`}>
            {children}
        </span>
    );
}
