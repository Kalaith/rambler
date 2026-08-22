import axios from 'axios';

const baseUrl = import.meta.env.VITE_API_BASE_URL;
if (!baseUrl) throw new Error('VITE_API_BASE_URL environment variable is required.');
const api = axios.create({
    baseURL: baseUrl,
    headers: {
        'Content-Type': 'application/json',
    },
});

// Callback to be triggered on 401 Unauthorized
let tokenResolver: (() => string | null) | null = null;

export const registerAuthTokenResolver = (resolver: () => string | null) => {
    tokenResolver = resolver;
};

// Request interceptor for JWT
api.interceptors.request.use((config) => {
    const token = tokenResolver?.();
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => Promise.reject(error),
);

export default api;
