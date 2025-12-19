import React from 'react';
import { Ramble } from '../../types';

interface HistoryItemProps {
    ramble: Ramble;
    isSelected: boolean;
    onSelect: (ramble: Ramble) => void;
}

export const HistoryItem: React.FC<HistoryItemProps> = ({ ramble, isSelected, onSelect }) => (
    <button
        onClick={() => onSelect(ramble)}
        className={`w-full text-left p-4 rounded-2xl transition-all duration-300 group ${isSelected
                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100'
                : 'hover:bg-zinc-100 text-zinc-600'
            }`}
    >
        <div className="flex justify-between items-center mb-1">
            <span className={`text-[10px] font-black uppercase tracking-widest ${isSelected ? 'text-indigo-200' : 'text-zinc-400'}`}>
                {new Date(ramble.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })}
            </span>
            <span className={`text-[9px] font-bold ${isSelected ? 'text-indigo-300' : 'text-zinc-300'}`}>
                {ramble.word_count} words
            </span>
        </div>
        <p className={`text-xs line-clamp-1 font-medium italic ${isSelected ? 'text-white' : 'group-hover:text-zinc-900'}`}>
            &ldquo;{ramble.content}&rdquo;
        </p>
    </button>
);
