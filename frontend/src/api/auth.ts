import api from './client';
import { User } from '../types';

export const authService = {
    async login(email: string, password: string): Promise<{ success: boolean; data?: { user: User; token: string }; error?: any }> {
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

    async register(email: string, password: string): Promise<{ success: boolean; message?: string; error?: any }> {
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
    }
};

export default authService;
