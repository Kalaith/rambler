import React from 'react';

interface CaptureHeaderProps {
    isSaving: boolean;
    processing: boolean;
    hasContent: boolean;
    onProcess: () => void;
    onNew: () => void;
}

export const CaptureHeader: React.FC<CaptureHeaderProps> = ({
    isSaving,
    processing,
    hasContent,
    onProcess,
    onNew
}) => (
    <header className="sticky top-0 z-20 bg-white/80 backdrop-blur-xl border-b border-zinc-100 px-6 py-4 sm:px-12 sm:py-6 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div className="text-center sm:text-left">
            <h1 className="text-2xl sm:text-3xl font-black tracking-tight text-zinc-900 flex items-center gap-2 justify-center sm:justify-start">
                <span className="p-2 bg-indigo-50 rounded-xl text-xl">🧠</span> The Rambler
            </h1>
            <p className="text-zinc-400 text-xs font-medium uppercase tracking-widest mt-1">Direct thought extraction</p>
        </div>
        <div className="flex gap-4 items-center w-full sm:w-auto">
            {isSaving && <span className="text-[10px] font-bold uppercase tracking-widest text-indigo-400 animate-pulse hidden sm:block">Syncing...</span>}
            <button
                onClick={onNew}
                className="px-6 py-3 bg-zinc-50 text-zinc-600 rounded-2xl font-bold hover:bg-zinc-100 transition-all duration-300 flex items-center justify-center gap-2 border border-zinc-100"
            >
                New Note
            </button>
            <button
                onClick={onProcess}
                disabled={processing || !hasContent}
                className="flex-1 sm:flex-initial px-8 py-3 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-all duration-300 disabled:opacity-30 shadow-xl shadow-indigo-100/50 flex items-center justify-center gap-2"
            >
                {processing ? (
                    <>
                        <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                        <span>Distilling...</span>
                    </>
                ) : 'Extract Kernels'}
            </button>
        </div>
    </header>
);
