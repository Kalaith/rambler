import React from 'react';
import { useCapture } from '../hooks/useCapture';
import { useRambleStore } from '../stores/useRambleStore';
import { useAuthStore } from '../stores/useAuthStore';
import { CaptureHeader } from '../components/capture/CaptureHeader';
import { RambleEditor } from '../components/capture/RambleEditor';
import { DistilledSidebar } from '../components/capture/DistilledSidebar';
import { HistorySidebar } from '../components/capture/HistorySidebar';

export const CapturePage: React.FC = () => {
    const {
        content,
        setContent,
        currentRambleId,
        result,
        isSaving,
        processing,
        usage,
        rambles,
        handleProcess,
        handleSelectRamble,
        handleNewRamble
    } = useCapture();

    const { deleteRamble } = useRambleStore();
    const { logout } = useAuthStore();

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
                onNew={handleNewRamble}
                onLogout={logout}
                usage={usage}
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
