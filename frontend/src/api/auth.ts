import api from './client';
import { User } from '../types';

export const authService = {
    async continueAsGuest(): Promise<{ success: boolean; data?: { user: User; token: string }; error?: unknown }> {
        try {
            const response = await api.post('guest-session');
            if (response.data.success !== false) {
                return { success: true, data: response.data.data };
            }
            return { success: false, error: response.data.message || 'Guest session failed' };
        } catch (error) {
            console.error('Guest session failed', error);
            return { success: false, error };
        }
    },

    async login(email: string, password: string): Promise<{ success: boolean; data?: { user: User; token: string }; error?: unknown }> {
        try {
            const response = await api.post('login', { email, password });
            if (response.data.success !== false) {
                return { success: true, data: response.data.data };
            }
            return { success: false, error: response.data.message || 'Login failed' };
        } catch (error) {
            console.error('Login failed', error);
            return { success: false, error };
        }
    },

    async register(email: string, password: string): Promise<{ success: boolean; message?: string; error?: unknown }> {
        try {
            const response = await api.post('register', { email, password });
            if (response.data.success !== false) {
                return { success: true };
            }
            return { success: false, message: response.data.message || 'Registration failed' };
        } catch (error) {
            console.error('Registration failed', error);
            return { success: false, error };
        }
    },

    async linkGuest(guestUserId: number, token?: string): Promise<{ success: boolean; data?: unknown; error?: unknown }> {
        try {
            const response = await api.post('auth/link-guest', { guest_user_id: guestUserId }, token ? {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            } : undefined);
            if (response.data.success !== false) {
                return { success: true, data: response.data.data };
            }
            return { success: false, error: response.data.message || 'Link guest failed' };
        } catch (error) {
            console.error('Link guest failed', error);
            return { success: false, error };
        }
    }
};

export default authService;
