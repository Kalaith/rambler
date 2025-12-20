import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { User } from '../types';
import { authService } from '../api/auth';
import { useRambleStore } from './useRambleStore';

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
                const result = await authService.login(email, password);
                if (result.success && result.data) {
                    const { user, token } = result.data;
                    set({ user: user || { id: 0, email }, token });
                    return true;
                }
                return false;
            },
            register: async (email, password) => {
                const result = await authService.register(email, password);
                if (result.success) {
                    await useAuthStore.getState().login(email, password);
                    return true;
                }
                return false;
            },
            logout: () => {
                // Completely destructive logout for all "rambler_" data
                Object.keys(localStorage).forEach(key => {
                    if (key.includes('rambler')) {
                        localStorage.removeItem(key);
                    }
                });

                // Reset other stores
                useRambleStore.getState().clearRambles();

                // Clear state
                set({ user: null, token: null });

                // Force a page reload to ensure all memory-resident state is purged
                const basePath = (import.meta as any).env.VITE_BASE_PATH || '/';
                window.location.href = basePath;
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
