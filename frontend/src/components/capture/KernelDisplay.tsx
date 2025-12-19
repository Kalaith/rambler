import React from 'react';
import { ProcessedResult } from '../../types';

interface KernelDisplayProps {
    result: ProcessedResult;
    small?: boolean;
    isDark?: boolean;
}

export const KernelDisplay: React.FC<KernelDisplayProps> = ({ result, small, isDark }) => (
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
