import React from 'react';

interface RambleEditorProps {
    content: string;
    setContent: (content: string) => void;
}

export const RambleEditor: React.FC<RambleEditorProps> = ({ content, setContent }) => {
    const wordCount = content.split(/\s+/).filter(Boolean).length;

    return (
        <div className="flex-1 relative group">
            <textarea
                autoFocus
                placeholder="Type whatever's on your mind. Don't censor. Just go."
                className="w-full h-full min-h-[300px] p-8 sm:p-12 text-lg sm:text-xl bg-white rounded-[2.5rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.06)] border border-transparent focus:border-indigo-100 focus:outline-none focus:ring-4 focus:ring-indigo-50/50 outline-none resize-none leading-relaxed transition-all duration-500 overflow-hidden group-hover:shadow-[0_48px_80px_-24px_rgba(0,0,0,0.08)]"
                value={content}
                onChange={(e) => setContent(e.target.value)}
            />
            <div className="absolute bottom-6 right-8 text-[10px] font-black uppercase tracking-widest text-zinc-300">
                {wordCount} words
            </div>
        </div>
    );
};
