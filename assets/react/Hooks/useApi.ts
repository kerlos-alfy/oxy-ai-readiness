import { useCallback, useEffect, useState } from 'react';
import { apiGet } from '../Utils/api';

interface UseApiResult<T> {
    data: T | undefined;
    loading: boolean;
    error: string | undefined;
    reload: () => void;
}

export function useApiGet<T>(path: string): UseApiResult<T> {
    const [data, setData] = useState<T>();
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string>();
    const [reloadToken, setReloadToken] = useState(0);

    useEffect(() => {
        let cancelled = false;
        setLoading(true);
        setError(undefined);

        apiGet<T>(path)
            .then((envelope) => {
                if (cancelled) {
                    return;
                }

                if (envelope.success) {
                    setData(envelope.data);
                } else {
                    setError(envelope.message ?? 'Request failed.');
                }
            })
            .catch(() => {
                if (!cancelled) {
                    setError('Network error.');
                }
            })
            .finally(() => {
                if (!cancelled) {
                    setLoading(false);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [path, reloadToken]);

    const reload = useCallback(() => setReloadToken((token) => token + 1), []);

    return { data, loading, error, reload };
}
