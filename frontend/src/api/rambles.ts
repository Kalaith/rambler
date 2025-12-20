import api from './client';
import { ProcessedResult } from '../types';

export const rambleService = {
    async process(id: number): Promise<{ success: boolean; data?: ProcessedResult; error?: any }> {
        try {
            const response = await api.post(`rambles/${id}/process`);
            if (response.data.success !== false) {
                return { success: true, data: response.data.data };
            }
            return { success: false, error: response.data.message || 'Processing failed' };
        } catch (error) {
            console.error('Processing failed', error);
            return { success: false, error };
        }
    },

    async getUsage(): Promise<{ success: boolean; data?: any; error?: any }> {
        try {
            const response = await api.get('usage');
            if (response.data.success !== false) {
                return { success: true, data: response.data.data };
            }
            return { success: false, error: response.data.message || 'Failed to fetch usage' };
        } catch (error) {
            console.error('Failed to fetch usage', error);
            return { success: false, error };
        }
    }
};

export default rambleService;
