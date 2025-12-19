import React from 'react';
import { Ramble } from '../../types';
import { HistoryCard } from './HistoryCard';

interface HistoryArchiveProps {
    rambles: Ramble[];
    onReprocess: (ramble: Ramble) => void;
}

export const HistoryArchive: React.FC<HistoryArchiveProps> = ({ rambles, onReprocess }) => (
    <section className="mt-12">
        <div className="flex items-center gap-4 mb-8">
            <h2 className="text-2xl font-black text-zinc-900 uppercase tracking-tighter">Your Mind Archive</h2>
            <div className="flex-1 h-px bg-zinc-100" />
            <span className="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">{rambles.length} Entries</span>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 pb-20">
            {rambles.map((ramble) => (
                <HistoryCard
                    key={ramble.id}
                    ramble={ramble}
                    onReprocess={onReprocess}
                />
            ))}
        </div>
    </section>
);
