import React from 'react';

import { UsageLimit } from '../../types';

interface CaptureHeaderProps {
    isSaving: boolean;
    processing: boolean;
    hasContent: boolean;
    onProcess: () => void;
    onNew: () => void;
    onLogout: () => void;
    isGuest?: boolean;
    linkAccountUrl?: string;
    usage: UsageLimit | null;
}

export const CaptureHeader: React.FC<CaptureHeaderProps> = ({
    isSaving,
    processing,
    hasContent,
    onProcess,
    onNew,
    onLogout,
    isGuest = false,
    linkAccountUrl,
    usage
}) => (
    <header className="sticky top-0 z-20 bg-white/80 backdrop-blur-xl border-b border-zinc-100 px-6 py-4 sm:px-12 sm:py-6 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div className="text-center sm:text-left">
            <h1 className="text-2xl sm:text-3xl font-black tracking-tight text-zinc-900 flex items-center gap-2 justify-center sm:justify-start">
                <span className="p-2 bg-indigo-50 rounded-xl text-xl">🧠</span> The Rambler
            </h1>
            <div className="flex items-center gap-3 mt-1">
                <p className="text-zinc-400 text-xs font-medium uppercase tracking-widest leading-none">Direct thought extraction</p>
                {usage && (
                    <div className="flex items-center gap-2 bg-zinc-50 border border-zinc-100 rounded-full px-2 py-1">
                        <div className={`w-1.5 h-1.5 rounded-full ${usage.count >= usage.limit ? 'bg-red-500' : 'bg-emerald-500 animate-pulse'}`} />
                        <span className="text-[10px] font-bold text-zinc-500 tabular-nums">
                            {usage.count} / {usage.limit} <span className="text-zinc-300 ml-1 font-black">AI CELLS</span>
                        </span>
                    </div>
                )}
            </div>
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
            {isGuest && linkAccountUrl && (
                <a
                    href={linkAccountUrl}
                    className="px-4 py-3 text-xs font-bold uppercase tracking-widest text-amber-700 bg-amber-50 border border-amber-200 rounded-2xl hover:bg-amber-100 transition-all duration-300"
                >
                    Link Account
                </a>
            )}
            <div className="w-px h-8 bg-zinc-100 mx-2 hidden sm:block" />
            <button
                onClick={onLogout}
                className="p-3 text-zinc-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all duration-300"
                title="Sign Out"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><polyline points="16 17 21 12 16 7" /><line x1="21" y1="12" x2="9" y2="12" /></svg>
            </button>
        </div>
    </header>
);
