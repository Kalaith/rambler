import React, { useState, useEffect, useRef } from 'react';
import { useRambleStore } from '../stores/useRambleStore';
import api from '../api/client';
import { ProcessedResult } from '../types';

const KernelDisplay: React.FC<{ result: ProcessedResult; small?: boolean; isDark?: boolean }> = ({ result, small, isDark }) => (
    <div className="space-y-6">
        <section className={small ? 'space-y-2' : 'space-y-3'}>
            <h3 className={`text-[9px] font-black uppercase tracking-[0.2em] ${isDark ? 'text-white/40' : 'text-zinc-400'}`}>Summary</h3>
            <p className={`${small ? 'text-xs' : 'text-sm'} leading-relaxed font-medium ${isDark ? 'text-white/90 p-3 bg-white/10 rounded-2xl' : 'text-zinc-700 p-4 bg-zinc-50 rounded-2xl'}`}>
                {result.summary}
            </p>
        </section>

        {result.topics.length > 0 && (
            <section className={small ? 'space-y-2' : 'space-y-3'}>
                <h3 className={`text-[9px] font-black uppercase tracking-[0.2em] ${isDark ? 'text-white/40' : 'text-zinc-400'}`}>Topics</h3>
                <div className="flex flex-wrap gap-2">
                    {result.topics.map((t, i) => (
                        <span key={i} className={`px-2.5 py-1 rounded-lg ${small ? 'text-[9px]' : 'text-[10px] font-bold'} ${isDark ? 'bg-white/10 text-white' : 'bg-indigo-50 text-indigo-600'}`}>
                            {t}
                        </span>
                    ))}
                </div>
            </section>
        )}

        {(!small && (result.questions.length > 0 || result.ideas.length > 0)) && (
            <div className="space-y-6 pt-4">
                <section className="space-y-3">
                    <h3 className={`text-[9px] font-black uppercase tracking-[0.2em] ${isDark ? 'text-white/40' : 'text-zinc-400'}`}>Questions</h3>
                    <ul className="space-y-3">
                        {result.questions.map((q, i) => (
                            <li key={i} className="text-xs flex gap-3 leading-relaxed">
                                <span className={isDark ? 'text-white/30' : 'text-indigo-200'}>?</span>
                                <span className={isDark ? 'text-white/80' : 'text-zinc-600'}>{q}</span>
                            </li>
                        ))}
                    </ul>
                </section>

                <section className="space-y-3">
                    <h3 className={`text-[9px] font-black uppercase tracking-[0.2em] ${isDark ? 'text-white/40' : 'text-zinc-400'}`}>Ideas</h3>
                    <ul className="space-y-3">
                        {result.ideas.map((id, i) => (
                            <li key={i} className="text-xs flex gap-3 leading-relaxed">
                                <span className={isDark ? 'text-white/30' : 'text-indigo-200'}>💡</span>
                                <span className={isDark ? 'text-white/80' : 'text-zinc-600'}>{id}</span>
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        )}
    </div>
);

export const CapturePage: React.FC = () => {
    const [content, setContent] = useState('');
    const [isSaving, setIsSaving] = useState(false);
    const [processing, setProcessing] = useState(false);
    const [result, setResult] = useState<ProcessedResult | null>(null);
    const [currentRambleId, setCurrentRambleId] = useState<number | null>(null);
    const { addRamble, rambles, fetchRambles } = useRambleStore();

    const timeoutRef = useRef<NodeJS.Timeout | null>(null);

    useEffect(() => {
        fetchRambles();
    }, []);

    // Basic Autosave simulation (Actually just creating a new ramble entry when paused)
    useEffect(() => {
        if (content.length > 10 && !currentRambleId) {
            if (timeoutRef.current) clearTimeout(timeoutRef.current);

            timeoutRef.current = setTimeout(async () => {
                setIsSaving(true);
                const ramble = await addRamble(content);
                if (ramble) setCurrentRambleId(ramble.id);
                setIsSaving(false);
            }, 3000);
        }
    }, [content]);

    const handleProcess = async () => {
        if (!content || processing) return;

        setProcessing(true);
        try {
            // Ensure saved first if not already
            let rambleId = currentRambleId;
            if (!rambleId) {
                const ramble = await addRamble(content);
                if (ramble) rambleId = ramble.id;
            }

            if (rambleId) {
                const response = await api.post(`rambles/${rambleId}/process`);
                setResult(response.data.data);
            }
        } catch (error) {
            console.error('Processing failed', error);
        } finally {
            setProcessing(false);
        }
    };

    return (
        <div className="flex flex-col h-screen bg-slate-50 text-slate-800 p-8">
            <header className="flex justify-between items-center mb-8">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight text-slate-900">🧠 The Rambler</h1>
                    <p className="text-slate-500 italic">No spell check. No deep thoughts. Just ramble.</p>
                </div>
                <div className="flex gap-4 items-center">
                    {isSaving && <span className="text-sm text-slate-400 animate-pulse">Autosaving...</span>}
                    <button
                        onClick={handleProcess}
                        disabled={processing || !content}
                        className="px-6 py-2 bg-indigo-600 text-white rounded-full font-medium hover:bg-indigo-700 transition disabled:opacity-50 shadow-lg shadow-indigo-100"
                    >
                        {processing ? 'Processing Kernels...' : 'Extract Kernels'}
                    </button>
                </div>
            </header>

            <main className="flex-1 flex flex-col gap-8 overflow-hidden">
                <div className="flex gap-8 h-1/2 min-h-[400px]">
                    <textarea
                        autoFocus
                        placeholder="Start typing your nonsensical thoughts here..."
                        className="flex-1 p-12 text-xl bg-white rounded-3xl shadow-sm border border-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-100 resize-none leading-relaxed"
                        value={content}
                        onChange={(e) => setContent(e.target.value)}
                    />

                    {result && (
                        <aside className="w-1/3 bg-white rounded-3xl shadow-xl border border-slate-50 p-8 overflow-y-auto animate-in slide-in-from-right duration-500">
                            <h2 className="text-xl font-bold mb-6 text-indigo-900 border-b pb-4">New Kernels</h2>
                            <KernelDisplay result={result} />
                        </aside>
                    )}
                </div>

                <div className="flex-1 overflow-y-auto pr-4">
                    <h2 className="text-2xl font-bold mb-6 text-slate-800">Your History</h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-12">
                        {rambles.map((ramble) => (
                            <div key={ramble.id} className="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-md transition group">
                                <div className="flex justify-between items-start mb-4">
                                    <span className="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                        {new Date(ramble.created_at).toLocaleDateString()}
                                    </span>
                                    <span className="text-[10px] font-bold text-slate-300 group-hover:text-indigo-200 transition">
                                        {ramble.word_count} words
                                    </span>
                                </div>
                                <p className="text-slate-600 line-clamp-3 mb-6 leading-relaxed italic border-l-2 border-indigo-50 pl-4">"{ramble.content}"</p>

                                {ramble.summary ? (
                                    <div className="pt-6 border-t border-slate-50 space-y-4">
                                        <KernelDisplay result={ramble as any} small />
                                    </div>
                                ) : (
                                    <div className="pt-4 flex justify-center">
                                        <button
                                            onClick={() => {
                                                setCurrentRambleId(ramble.id);
                                                setContent(ramble.content);
                                            }}
                                            className="text-xs font-bold text-indigo-600 hover:underline"
                                        >
                                            Reprocess this ramble
                                        </button>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            </main>

            <footer className="mt-8 text-center text-slate-400 text-xs">
                <p>Data stored in plain text. Security not assumed. Manual delete actually deletes.</p>
            </footer>
        </div>
    );
};
