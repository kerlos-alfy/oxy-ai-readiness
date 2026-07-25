import type { ButtonHTMLAttributes } from 'react';

type ButtonVariant = 'primary' | 'secondary' | 'danger' | 'ghost';

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: ButtonVariant;
}

const VARIANT_CLASSES: Record<ButtonVariant, string> = {
    primary: 'bg-primary text-white hover:bg-primary/90',
    secondary: 'bg-white text-slate-900 border border-slate-200 hover:bg-slate-50',
    danger: 'bg-danger text-white hover:bg-danger/90',
    ghost: 'bg-transparent text-slate-700 hover:bg-slate-100',
};

export default function Button({ variant = 'primary', className = '', ...props }: ButtonProps): JSX.Element {
    return (
        <button
            className={`rounded-btn px-4 py-2 text-sm font-medium transition duration-200 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed ${VARIANT_CLASSES[variant]} ${className}`}
            {...props}
        />
    );
}
