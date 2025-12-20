import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { User } from '../types';
import api from '../api/client';

interface AuthState {
    user: User | null;
    token: string | null;
    setAuth: (user: User, token: string) => void;
    login: (email: string, password: string) => Promise<boolean>;
    register: (email: string, password: string) => Promise<boolean>;
    logout: () => void;
}

const useAuthStore = create<AuthState>()(
    persist(
        (set) => ({
            user: null,
            token: null,
            setAuth: (user, token) => {
                set({ user, token });
            },
            login: async (email, password) => {
                try {
                    const response = await api.post('login', { email, password });
                    if (response.data.success !== false) {
                        const { user, token } = response.data.data;
                        set({ user: user || { id: 0, email }, token });
                        return true;
                    }
                    return false;
                } catch (error) {
                    console.error('Login failed', error);
                    return false;
                }
            },
            register: async (email, password) => {
                try {
                    const response = await api.post('register', { email, password });
                    return response.data.success !== false;
                } catch (error) {
                    console.error('Registration failed', error);
                    return false;
                }
            },
            logout: () => {
                set({ user: null, token: null });
            },
        }),
        {
            name: 'auth-storage',
        }
    )
);

// Register the logout callback to handle 401 errors globally
import { registerUnauthorizedCallback } from '../api/client';
registerUnauthorizedCallback(() => {
    useAuthStore.getState().logout();
});

export { useAuthStore };
