import React from 'react';
import { Ramble } from '../../types';
import { HistoryItem } from './HistoryItem';

interface HistorySidebarProps {
    rambles: Ramble[];
    selectedId: number | null;
    onSelect: (ramble: Ramble) => void;
    onNew: () => void;
}

export const HistorySidebar: React.FC<HistorySidebarProps> = ({
    rambles,
    selectedId,
    onSelect,
    onNew
}) => {
    // Group rambles by date
    const grouped = rambles.reduce((acc, ramble) => {
        const date = new Date(ramble.created_at).toLocaleDateString(undefined, {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        if (!acc[date]) acc[date] = [];
        acc[date].push(ramble);
        return acc;
    }, {} as Record<string, Ramble[]>);

    return (
        <aside className="w-full lg:w-80 bg-white border-l border-zinc-100 flex flex-col h-full overflow-hidden">
            <div className="p-6 border-b border-zinc-50 flex justify-between items-center">
                <h2 className="text-sm font-black text-zinc-900 uppercase tracking-tighter">Your Mind Archive</h2>
                <button
                    onClick={onNew}
                    className="p-2 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-all duration-300 active:scale-90"
                    title="New Ramble"
                >
                    <span className="text-xl leading-none">+</span>
                </button>
            </div>

            <div className="flex-1 overflow-y-auto px-4 py-6 space-y-8 custom-scrollbar">
                {Object.entries(grouped).map(([date, entries]) => (
                    <section key={date} className="space-y-2">
                        <h3 className="text-[10px] font-black text-zinc-300 uppercase tracking-[0.2em] px-2">{date}</h3>
                        <div className="space-y-1">
                            {entries.map((ramble) => (
                                <HistoryItem
                                    key={ramble.id}
                                    ramble={ramble}
                                    isSelected={selectedId === ramble.id}
                                    onSelect={onSelect}
                                />
                            ))}
                        </div>
                    </section>
                ))}

                {rambles.length === 0 && (
                    <div className="text-center py-12 px-4">
                        <p className="text-zinc-300 text-xs font-medium">No entries yet. Start rambling!</p>
                    </div>
                )}
            </div>
        </aside>
    );
};
