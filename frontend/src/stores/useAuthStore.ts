import { create } from 'zustand';
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

export const useAuthStore = create<AuthState>((set) => ({
    user: null,
    token: localStorage.getItem('token'),
    setAuth: (user, token) => {
        localStorage.setItem('token', token);
        set({ user, token });
    },
    login: async (email, password) => {
        try {
            const response = await api.post('login', { email, password });
            if (response.data.success !== false) {
                const { user, token } = response.data;
                localStorage.setItem('token', token);
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
        localStorage.removeItem('token');
        set({ user: null, token: null });
    },
}));
