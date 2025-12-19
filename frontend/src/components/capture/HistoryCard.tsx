import React from 'react';
import { Ramble } from '../../types';
import { KernelDisplay } from './KernelDisplay';

interface HistoryCardProps {
    ramble: Ramble;
    onReprocess: (ramble: Ramble) => void;
}

export const HistoryCard: React.FC<HistoryCardProps> = ({ ramble, onReprocess }) => (
    <div className="bg-white p-8 rounded-[2rem] shadow-[0_16px_32px_-8px_rgba(0,0,0,0.04)] border border-zinc-100 hover:shadow-[0_24px_48px_-12px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-500 group">
        <div className="flex justify-between items-center mb-6">
            <div className="px-3 py-1 bg-zinc-50 rounded-lg text-[10px] font-black text-zinc-400 uppercase tracking-widest">
                {new Date(ramble.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })}
            </div>
            <div className="text-[10px] font-bold text-zinc-300">
                {ramble.word_count} words
            </div>
        </div>
        <p className="text-zinc-600 text-sm line-clamp-2 mb-8 leading-relaxed font-medium italic group-hover:text-zinc-900 transition-colors">
            &ldquo;{ramble.content}&rdquo;
        </p>

        {ramble.summary ? (
            <div className="pt-6 border-t border-zinc-50 space-y-4">
                <KernelDisplay result={ramble as any} small />
            </div>
        ) : (
            <div className="pt-4 flex justify-start">
                <button
                    onClick={() => onReprocess(ramble)}
                    className="text-[10px] font-black uppercase tracking-widest text-indigo-500 hover:text-indigo-600 flex items-center gap-2 group/btn"
                >
                    Process Entries <span className="group-hover/btn:translate-x-1 transition-transform">→</span>
                </button>
            </div>
        )}
    </div>
);
