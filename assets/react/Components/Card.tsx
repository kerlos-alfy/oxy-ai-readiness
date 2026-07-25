import type { ReactNode } from 'react';

interface CardProps {
    title?: string;
    children: ReactNode;
    className?: string;
}

export default function Card({ title, children, className = '' }: CardProps): JSX.Element {
    return (
        <section
            className={`rounded-card bg-card shadow-card p-6 transition duration-200 ease-in-out hover:shadow-card-hover ${className}`}
        >
            {title !== undefined && <h2 className="text-lg font-semibold mb-4">{title}</h2>}
            {children}
        </section>
    );
}
