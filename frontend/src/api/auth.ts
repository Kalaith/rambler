import api from './client';
import type { User } from '../types';

export const authService = {
    async continueAsGuest(): Promise<{ success: boolean; data?: { user: User; token: string }; error?: unknown }> {
        try {
            const response = await api.post('guest-session');
            return response.data.success !== false
                ? { success: true, data: response.data.data }
                : { success: false, error: response.data.message || 'Guest session failed' };
        } catch (error) {
            return { success: false, error };
        }
    },
};

export default authService;
