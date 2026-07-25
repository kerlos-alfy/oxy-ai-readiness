/** Maps a REST path (e.g. `/score`) to the JSON body its mocked fetch should return. */
export function mockFetchRoutes(routes: Record<string, unknown>): jest.Mock {
    const fetchMock = jest.fn((input: RequestInfo | URL) => {
        const url = typeof input === 'string' ? input : input.toString();
        const path = Object.keys(routes).find((route) => url.endsWith(route));
        const body = path !== undefined ? routes[path] : { success: false, message: 'Unmocked route: ' + url };

        return Promise.resolve({
            json: () => Promise.resolve(body),
        } as Response);
    });

    global.fetch = fetchMock as unknown as typeof fetch;

    return fetchMock;
}
