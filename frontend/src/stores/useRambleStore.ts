import { create } from 'zustand';
import { Ramble } from '../types';
import api from '../api/client';

interface RambleState {
    rambles: Ramble[];
    loading: boolean;
    fetchRambles: () => Promise<void>;
    addRamble: (content: string) => Promise<Ramble | null>;
}

export const useRambleStore = create<RambleState>((set) => ({
    rambles: [],
    loading: false,
    fetchRambles: async () => {
        set({ loading: true });
        try {
            const response = await api.get('rambles');
            set({ rambles: response.data.data });
        } catch (error) {
            console.error('Failed to fetch rambles', error);
        } finally {
            set({ loading: false });
        }
    },
    addRamble: async (content: string) => {
        try {
            const response = await api.post('rambles', { content });
            const newRamble = response.data.data;
            set((state) => ({ rambles: [newRamble, ...state.rambles] }));
            return newRamble;
        } catch (error) {
            console.error('Failed to add ramble', error);
            return null;
        }
    },
    deleteRamble: async (id: number) => {
        try {
            await api.delete(`rambles/${id}`);
            set((state) => ({
                rambles: state.rambles.filter((r) => r.id !== id)
            }));
            return true;
        } catch (error) {
            console.error('Failed to delete ramble', error);
            return false;
        }
    },
}));
