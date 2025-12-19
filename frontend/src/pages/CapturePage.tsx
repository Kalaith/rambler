import React, { useState, useEffect, useRef } from 'react';
import { useRambleStore } from '../stores/useRambleStore';
import api from '../api/client';
import { ProcessedResult } from '../types';
import { CaptureHeader } from '../components/capture/CaptureHeader';
import { RambleEditor } from '../components/capture/RambleEditor';
import { DistilledSidebar } from '../components/capture/DistilledSidebar';
import { HistorySidebar } from '../components/capture/HistorySidebar';

export const CapturePage: React.FC = () => {
    const [content, setContent] = useState('');
    const [isSaving, setIsSaving] = useState(false);
    const [processing, setProcessing] = useState(false);
    const [result, setResult] = useState<ProcessedResult | null>(null);
    const [currentRambleId, setCurrentRambleId] = useState<number | null>(null);
    const { addRamble, rambles, fetchRambles, deleteRamble } = useRambleStore();

    const timeoutRef = useRef<NodeJS.Timeout | null>(null);

    useEffect(() => {
        fetchRambles();
    }, []);

    // Basic Autosave simulation
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
            let rambleId = currentRambleId;
            if (!rambleId) {
                const ramble = await addRamble(content);
                if (ramble) rambleId = ramble.id;
            }

            if (rambleId) {
                const response = await api.post(`rambles/${rambleId}/process`);
                const newResult = response.data.data;
                setResult(newResult);
                fetchRambles(); // Refresh history
            }
        } catch (error) {
            console.error('Processing failed', error);
        } finally {
            setProcessing(false);
        }
    };

    const handleSelectRamble = (ramble: any) => {
        setCurrentRambleId(ramble.id);
        setContent(ramble.content);
        if (ramble.summary) {
            setResult({
                summary: ramble.summary,
                topics: ramble.topics || [],
                questions: ramble.questions || [],
                ideas: ramble.ideas || []
            });
        } else {
            setResult(null);
        }
    };

    const handleNewRamble = () => {
        setCurrentRambleId(null);
        setContent('');
        setResult(null);
    };

    const handleDeleteRamble = async (id: number) => {
        const success = await deleteRamble(id);
        if (success && currentRambleId === id) {
            handleNewRamble();
        }
    };

    return (
        <div className="flex flex-col h-screen bg-zinc-50 text-zinc-800">
            <CaptureHeader
                isSaving={isSaving}
                processing={processing}
                hasContent={!!content}
                onProcess={handleProcess}
            />

            <div className="flex-1 flex overflow-hidden">
                <main className="flex-1 flex flex-col p-6 sm:p-12 gap-8 overflow-y-auto custom-scrollbar">
                    <div className="flex flex-col xl:flex-row gap-8 min-h-full">
                        <div className="flex-1 flex flex-col gap-8">
                            <RambleEditor content={content} setContent={setContent} />

                            <footer className="text-zinc-300 text-[9px] font-black uppercase tracking-[0.3em] flex items-center gap-4 py-8">
                                <div className="h-px flex-1 bg-zinc-100" />
                                <span>End of Thought &bull; {new Date().toLocaleDateString()}</span>
                                <div className="h-px flex-1 bg-zinc-100" />
                            </footer>
                        </div>

                        {result && (
                            <div className="xl:w-[400px]">
                                <DistilledSidebar result={result} />
                            </div>
                        )}
                    </div>
                </main>

                <HistorySidebar
                    rambles={rambles}
                    selectedId={currentRambleId}
                    onSelect={handleSelectRamble}
                    onDelete={handleDeleteRamble}
                    onNew={handleNewRamble}
                />
            </div>
        </div>
    );
};
