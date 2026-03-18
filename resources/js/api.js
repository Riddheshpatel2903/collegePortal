const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

function buildHeaders(extra = {}) {
    const headers = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...extra,
    };

    if (token) {
        headers['X-CSRF-TOKEN'] = token;
    }

    const authToken = localStorage.getItem('api_token');
    if (authToken) {
        headers['Authorization'] = `Bearer ${authToken}`;
    }

    return headers;
}

async function handleResponse(response) {
    const content = await response.json().catch(() => null);

    if (!response.ok) {
        const message = content?.message || 'Unknown error';
        throw new Error(message);
    }

    if (content?.status === 'error') {
        throw new Error(content.message || 'API Error');
    }

    return content.data !== undefined ? content.data : content;
}

export async function apiGet(path, params = {}) {
    let url = path;
    if (Object.keys(params).length) {
        url += `?${new URLSearchParams(params).toString()}`;
    }

    const res = await fetch(url, {
        method: 'GET',
        headers: buildHeaders(),
        credentials: 'same-origin',
    });

    return await handleResponse(res);
}

export async function apiPost(path, payload = {}) {
    const res = await fetch(path, {
        method: 'POST',
        headers: buildHeaders(),
        body: JSON.stringify(payload),
        credentials: 'same-origin',
    });

    return await handleResponse(res);
}

export async function apiPut(path, payload = {}) {
    const res = await fetch(path, {
        method: 'PUT',
        headers: buildHeaders(),
        body: JSON.stringify(payload),
        credentials: 'same-origin',
    });

    return await handleResponse(res);
}

export async function apiDelete(path) {
    const res = await fetch(path, {
        method: 'DELETE',
        headers: buildHeaders(),
        credentials: 'same-origin',
    });

    return await handleResponse(res);
}

export function toast(message, type = 'success') {
    const container = document.createElement('div');
    container.className = `fixed bottom-4 right-4 z-50 max-w-xs rounded-lg p-4 text-sm font-medium shadow-lg transition-all duration-300 ` +
        (type === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white');
    container.innerText = message;

    document.body.appendChild(container);
    setTimeout(() => container.remove(), 3500);
}

export function handleApiError(error) {
    console.error(error);
    toast(error.message || 'API operation failed', 'error');
}

window.apiGet = apiGet;
window.apiPost = apiPost;
window.apiPut = apiPut;
window.apiDelete = apiDelete;
window.toast = toast;
window.handleApiError = handleApiError;
