import React, { useState, useEffect, useRef } from 'react';
import { useRambleStore } from '../stores/useRambleStore';
import api from '../api/client';
import { ProcessedResult } from '../types';

export const CapturePage: React.FC = () => {
    const [content, setContent] = useState('');
    const [isSaving, setIsSaving] = useState(false);
    const [processing, setProcessing] = useState(false);
    const [result, setResult] = useState<ProcessedResult | null>(null);
    const [currentRambleId, setCurrentRambleId] = useState<number | null>(null);
    const { addRamble } = useRambleStore();

    const timeoutRef = useRef<NodeJS.Timeout | null>(null);

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

            <main className="flex-1 flex gap-8">
                <textarea
                    autoFocus
                    placeholder="Start typing your nonsensical thoughts here..."
                    className="flex-1 p-12 text-xl bg-white rounded-3xl shadow-sm border border-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-100 resize-none leading-relaxed"
                    value={content}
                    onChange={(e) => setContent(e.target.value)}
                />

                {result && (
                    <aside className="w-1/3 bg-white rounded-3xl shadow-xl border border-slate-50 p-8 overflow-y-auto animate-in slide-in-from-right duration-500">
                        <h2 className="text-xl font-bold mb-6 text-indigo-900 border-b pb-4">Knowledge Kernels</h2>

                        <section className="mb-8">
                            <h3 className="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Summary</h3>
                            <p className="text-slate-700 bg-slate-50 p-4 rounded-xl leading-relaxed">{result.summary}</p>
                        </section>

                        <section className="mb-8">
                            <h3 className="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Topics</h3>
                            <div className="flex flex-wrap gap-2">
                                {result.topics.map((t, i) => (
                                    <span key={i} className="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-sm font-medium">{t}</span>
                                ))}
                            </div>
                        </section>

                        <section className="mb-8">
                            <h3 className="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Questions</h3>
                            <ul className="space-y-2">
                                {result.questions.map((q, i) => (
                                    <li key={i} className="text-slate-600 flex gap-3">
                                        <span className="text-indigo-300">?</span> {q}
                                    </li>
                                ))}
                            </ul>
                        </section>

                        <section>
                            <h3 className="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Ideas</h3>
                            <ul className="space-y-2">
                                {result.ideas.map((id, i) => (
                                    <li key={i} className="text-slate-600 flex gap-3">
                                        <span className="text-indigo-300">💡</span> {id}
                                    </li>
                                ))}
                            </ul>
                        </section>
                    </aside>
                )}
            </main>

            <footer className="mt-8 text-center text-slate-400 text-xs">
                <p>Data stored in plain text. Security not assumed. Manual delete actually deletes.</p>
            </footer>
        </div>
    );
};
