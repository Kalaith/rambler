import React from 'react';
import { ProcessedResult } from '../../types';
import { KernelDisplay } from './KernelDisplay';

interface DistilledSidebarProps {
    result: ProcessedResult;
}

export const DistilledSidebar: React.FC<DistilledSidebarProps> = ({ result }) => (
    <aside className="w-full lg:w-[380px] bg-indigo-600 text-white rounded-[2.5rem] shadow-2xl shadow-indigo-200 p-8 sm:p-10 flex flex-col animate-in slide-in-from-bottom lg:slide-in-from-right duration-700 relative overflow-hidden">
        <div className="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-32 -mt-32" />
        <h2 className="text-xl font-black mb-8 pb-4 border-b border-white/20 relative z-10">Distilled Essence</h2>
        <div className="overflow-y-auto pr-2 relative z-10 custom-scrollbar">
            <KernelDisplay result={result} isDark />
        </div>
    </aside>
);
