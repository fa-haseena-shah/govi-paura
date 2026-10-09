const API_BASE_URL = 'http://127.0.0.1:8000/api';

// These calls legitimately answer 401 (wrong credentials), so they must not trigger the "session expired" redirect.
const PUBLIC_AUTH_PATHS = ['/login', '/register'];

// Laravel validation errors (422) carry the field messages in `errors`; show the first one.
function apiErrorMessage(body, fallback) {
    if (body?.errors) {
        const first = Object.values(body.errors).flat()[0];
        if (first) return first;
    }
    return body?.message ?? fallback;
}

async function apiFetch(path, options = {}) {
    const token = localStorage.getItem('token');
    const isFormData = options.body instanceof FormData;

    let response;
    try {
        response = await fetch(`${API_BASE_URL}${path}`, {
            ...options,
            headers: {
                // Don't set Content-Type for FormData — browser sets it with the boundary
                ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
                'Accept': 'application/json',
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
                ...options.headers,
            },
        });
    } catch (networkError) {
        throw new ApiError(0, 'Could not reach the server. Check that the API is running.', null);
    }

    const body = await response.json().catch(() => null);

    if (!response.ok) {
        // expired or revoked token: clear it and send the user back to log in
        if (response.status === 401 && token && !PUBLIC_AUTH_PATHS.includes(path)) {
            localStorage.removeItem('token');
            window.location.href = `${BASE_URL_JS}/auth/login.php`;
        }
        throw new ApiError(response.status, apiErrorMessage(body, 'Request failed'), body);
    }

    return body;
}

async function apiFetchBlob(path) {
    const token = localStorage.getItem('token');
    let response;
    try {
        response = await fetch(`${API_BASE_URL}${path}`, {
            headers: {
                'Accept': 'application/json',
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
            },
        });
    } catch (networkError) {
        throw new ApiError(0, 'Could not reach the server. Check that the API is running.', null);
    }

    if (!response.ok) {
        const body = await response.json().catch(() => null);
        if (response.status === 401 && token) {
            localStorage.removeItem('token');
            window.location.href = `${BASE_URL_JS}/auth/login.php`;
        }
        throw new ApiError(response.status, apiErrorMessage(body, 'Could not open this document.'), body);
    }

    return response.blob();
}

class ApiError extends Error {
    constructor(status, message, body) {
        super(message);
        this.status = status;
        this.body = body;
    }
}
