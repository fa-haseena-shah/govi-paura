const API_BASE_URL = 'http://127.0.0.1:8000/api';

async function apiFetch(path, options = {}) {
    const token = localStorage.getItem('token');

    const response = await fetch(`${API_BASE_URL}${path}`, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...options.headers,
        },
    });

    const body = await response.json().catch(() => null);

    if (!response.ok) {
        throw new ApiError(response.status, body?.message ?? 'Request failed', body);
    }

    return body;
}

class ApiError extends Error {
    constructor(status, message, body) {
        super(message);
        this.status = status;
        this.body = body;
    }
}