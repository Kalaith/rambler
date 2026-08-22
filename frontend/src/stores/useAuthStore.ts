import { create } from 'zustand';
import {
    appendQueryParam,
    clearStorageValue,
    readFrontpageToken,
    readFrontpageUser,
    readGuestSession,
    saveGuestSession,
    withRedirectParam,
} from '@webhatchery/auth-react';
import { registerAuthTokenResolver } from '../api/client';
import { authService } from '../api/auth';
import { useRambleStore } from './useRambleStore';
import type { User } from '../types';

interface AuthState {
    user: User | null;
    token: string | null;
    authMode: 'frontpage' | 'guest' | null;
    continueAsGuest: () => Promise<boolean>;
    loginWithRedirect: () => void;
    getLinkAccountUrl: () => string;
    logout: () => void;
}

const guestStorageKey = 'rambler-guest-session';
const loginUrl = () => withRedirectParam(requiredEnv('VITE_WEBHATCHERY_LOGIN_URL'));
const signupUrl = () => withRedirectParam(requiredEnv('VITE_WEBHATCHERY_SIGNUP_URL'));

function requiredEnv(name: string): string {
    const value = import.meta.env[name];
    if (typeof value !== 'string' || value.trim() === '') {
        throw new Error(`Missing required environment variable: ${name}`);
    }
    return value;
}

function initialAuth(): Pick<AuthState, 'user' | 'token' | 'authMode'> {
    const token = readFrontpageToken();
    const frontpageUser = readFrontpageUser();
    const guest = readGuestSession<User>(guestStorageKey);
    if (token) {
        return {
            token,
            authMode: 'frontpage',
            user: frontpageUser ? { id: Number(frontpageUser.id ?? 0), email: frontpageUser.email ?? '', is_guest: false, auth_type: 'frontpage' } : null,
        };
    }
    return guest ? { token: guest.token, authMode: 'guest', user: guest.user } : { token: null, authMode: null, user: null };
}

const useAuthStore = create<AuthState>()((set, get) => ({
    ...initialAuth(),
    continueAsGuest: async () => {
        const existing = readGuestSession<User>(guestStorageKey);
        if (existing) {
            set({ user: existing.user, token: existing.token, authMode: 'guest' });
            return true;
        }
        const result = await authService.continueAsGuest();
        if (!result.success || !result.data) return false;
        saveGuestSession(guestStorageKey, result.data);
        set({ user: result.data.user, token: result.data.token, authMode: 'guest' });
        return true;
    },
    loginWithRedirect: () => { window.location.href = loginUrl(); },
    getLinkAccountUrl: () => {
        const user = get().user;
        const base = signupUrl();
        return user?.is_guest ? appendQueryParam(base, 'guest_user_id', String(user.id)) : base;
    },
    logout: () => {
        if (get().authMode === 'guest') clearStorageValue(guestStorageKey);
        useRambleStore.getState().clearRambles();
        set({ user: null, token: null, authMode: null });
        window.location.href = loginUrl();
    },
}));

registerAuthTokenResolver(() => useAuthStore.getState().token ?? readFrontpageToken());

export { useAuthStore };
