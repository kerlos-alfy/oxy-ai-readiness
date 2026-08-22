import type { ApiEnvelope } from '../Types/api';

interface OxyAiReadinessGlobal {
    restUrl: string;
    nonce: string;
    version: string;
}

declare global {
    interface Window {
        oxyAiReadiness?: OxyAiReadinessGlobal;
    }
}

function config(): OxyAiReadinessGlobal {
    const global = window.oxyAiReadiness;
    if (!global) {
        throw new Error('window.oxyAiReadiness is not defined — the admin page did not localize the REST config.');
    }
    return global;
}

async function request<T>(path: string, init?: RequestInit): Promise<ApiEnvelope<T>> {
    const { restUrl, nonce } = config();
    const response = await fetch(`${restUrl}${path}`, {
        ...init,
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': nonce,
            ...init?.headers,
        },
    });

    const payload = (await response.json()) as ApiEnvelope<T> & { message?: string };
    if (!response.ok) {
        throw new Error(payload.message ?? `Request failed with HTTP ${response.status}`);
    }

    return payload;
}

export function apiGet<T>(path: string): Promise<ApiEnvelope<T>> {
    return request<T>(path, { method: 'GET' });
}

export function apiPost<T>(path: string, body?: Record<string, unknown>): Promise<ApiEnvelope<T>> {
    return request<T>(path, { method: 'POST', body: body ? JSON.stringify(body) : undefined });
}

export function apiDelete<T>(path: string): Promise<ApiEnvelope<T>> {
    return request<T>(path, { method: 'DELETE' });
}
