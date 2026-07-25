/** @type {import('tailwindcss').Config} */
export default {
    content: ['./assets/react/**/*.{ts,tsx,html}'],
    darkMode: ['class', '[data-oxy-theme="dark"]'],
    theme: {
        extend: {
            colors: {
                primary: '#2563EB',
                success: '#22C55E',
                warning: '#F59E0B',
                danger: '#EF4444',
                surface: '#F8FAFC',
                card: '#FFFFFF',
                'surface-dark': '#0F172A',
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
            borderRadius: {
                card: '16px',
                btn: '10px',
                input: '10px',
                progress: '999px',
            },
            boxShadow: {
                card: '0 8px 30px rgba(0,0,0,.06)',
                'card-hover': '0 12px 40px rgba(0,0,0,.12)',
            },
            transitionDuration: {
                DEFAULT: '200ms',
            },
            transitionTimingFunction: {
                DEFAULT: 'ease-in-out',
            },
        },
    },
    plugins: [],
};
