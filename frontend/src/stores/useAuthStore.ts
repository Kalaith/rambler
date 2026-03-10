import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { User } from '../types';
import { authService } from '../api/auth';
import { useRambleStore } from './useRambleStore';

interface AuthState {
    user: User | null;
    token: string | null;
    authMode: 'frontpage' | 'guest' | null;
    setAuth: (user: User, token: string) => void;
    continueAsGuest: () => Promise<boolean>;
    getLinkAccountUrl: () => string;
    login: (email: string, password: string) => Promise<boolean>;
    register: (email: string, password: string) => Promise<boolean>;
    logout: () => void;
}

const useAuthStore = create<AuthState>()(
    persist(
        (set, get) => ({
            user: null,
            token: null,
            authMode: null,
            setAuth: (user, token) => {
                set({ user, token, authMode: user.is_guest ? 'guest' : 'frontpage' });
            },
            continueAsGuest: async () => {
                const result = await authService.continueAsGuest();
                if (result.success && result.data) {
                    const { user, token } = result.data;
                    set({ user, token, authMode: 'guest' });
                    return true;
                }
                return false;
            },
            getLinkAccountUrl: (): string => {
                const user = get().user;
                const base = import.meta.env.VITE_WEBHATCHERY_SIGNUP_URL || '/signup';
                if (user?.is_guest) {
                    try {
                        const url = new URL(base, window.location.origin);
                        url.searchParams.set('guest_user_id', String(user.id));
                        url.searchParams.set('redirect', window.location.href);
                        return url.toString();
                    } catch {
                        return `${base}?guest_user_id=${user.id}`;
                    }
                }
                return base;
            },
            login: async (email: string, password: string) => {
                const result = await authService.login(email, password);
                if (result.success && result.data) {
                    const { user, token } = result.data;
                    set({ user: user || { id: 0, email }, token, authMode: 'frontpage' });
                    try {
                        const url = new URL(window.location.href);
                        const guestUserId = Number(url.searchParams.get('guest_user_id') || '0');
                        if (guestUserId > 0) {
                            await authService.linkGuest(guestUserId, token);
                            url.searchParams.delete('guest_user_id');
                            window.history.replaceState({}, '', url.toString());
                        }
                    } catch {
                        // Ignore link-account URL handling failures.
                    }
                    return true;
                }
                return false;
            },
            register: async (email: string, password: string) => {
                const result = await authService.register(email, password);
                if (result.success) {
                    await useAuthStore.getState().login(email, password);
                    return true;
                }
                return false;
            },
            logout: () => {
                const { authMode } = get();
                if (authMode === 'guest') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.includes('rambler')) {
                            localStorage.removeItem(key);
                        }
                    });
                    useRambleStore.getState().clearRambles();
                    set({ user: null, token: null, authMode: null });
                    const env = import.meta.env as Record<string, string | undefined>;
                    const basePath = env.VITE_BASE_PATH || '/';
                    window.location.href = basePath;
                    return;
                }

                // Completely destructive logout for all "rambler_" data
                Object.keys(localStorage).forEach(key => {
                    if (key.includes('rambler')) {
                        localStorage.removeItem(key);
                    }
                });

                // Reset other stores
                useRambleStore.getState().clearRambles();

                // Clear state
                set({ user: null, token: null, authMode: null });

                // Force a page reload to ensure all memory-resident state is purged
                const env = import.meta.env as Record<string, string | undefined>;
                const basePath = env.VITE_BASE_PATH || '/';
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
