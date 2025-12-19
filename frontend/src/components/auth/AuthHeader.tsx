import React from 'react';

interface AuthHeaderProps {
    isLogin: boolean;
}

export const AuthHeader: React.FC<AuthHeaderProps> = ({ isLogin }) => (
    <div className="text-center mb-10">
        <div className="inline-block p-4 bg-indigo-50 rounded-2xl mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500">
            <span className="text-4xl text-indigo-600 block">🧠</span>
        </div>
        <h1 className="text-4xl font-black text-zinc-900 mb-3 tracking-tight">The Rambler</h1>
        <p className="text-zinc-400 text-sm font-medium">
            {isLogin ? "Clear your mind. Extract the essence." : "Join the thought archive."}
        </p>
    </div>
);
