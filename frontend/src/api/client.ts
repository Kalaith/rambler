import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/',
    headers: {
        'Content-Type': 'application/json',
    },
});

// Callback to be triggered on 401 Unauthorized
let onUnauthorized: (() => void) | null = null;

export const registerUnauthorizedCallback = (callback: () => void) => {
    onUnauthorized = callback;
};

// Request interceptor for JWT
api.interceptors.request.use((config) => {
    // Since we use Zustand persist, the token is inside the 'auth-storage' object
    const authStorage = localStorage.getItem('auth-storage');
    if (authStorage) {
        try {
            const { state } = JSON.parse(authStorage);
            if (state && state.token) {
                config.headers.Authorization = `Bearer ${state.token}`;
            }
        } catch (e) {
            console.error('Failed to parse auth-storage', e);
        }
    }
    return config;
});

// Response interceptor for 401 Handling
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            if (onUnauthorized) {
                onUnauthorized();
            }
        }
        return Promise.reject(error);
    }
);

export default api;
