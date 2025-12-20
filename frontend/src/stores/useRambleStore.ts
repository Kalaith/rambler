import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { Ramble } from '../types';
import api from '../api/client';

interface RambleState {
    rambles: Ramble[];
    loading: boolean;
    fetchRambles: () => Promise<void>;
    addRamble: (content: string) => Promise<Ramble | null>;
    updateRamble: (id: number, content: string) => Promise<boolean>;
    deleteRamble: (id: number) => Promise<boolean>;
}

export const useRambleStore = create<RambleState>()(
    persist(
        (set) => ({
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
            updateRamble: async (id: number, content: string) => {
                try {
                    await api.put(`rambles/${id}`, { content });
                    set((state) => ({
                        rambles: state.rambles.map((r) =>
                            r.id === id ? { ...r, content, word_count: content.split(/\s+/).length } : r
                        )
                    }));
                    return true;
                } catch (error) {
                    console.error('Failed to update ramble', error);
                    return false;
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
        }),
        {
            name: 'ramble-storage',
        }
    )
);
