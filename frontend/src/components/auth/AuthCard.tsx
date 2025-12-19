import React from 'react';

interface AuthCardProps {
    children: React.ReactNode;
}

export const AuthCard: React.FC<AuthCardProps> = ({ children }) => (
    <div className="min-h-screen flex items-center justify-center bg-zinc-50 p-6 sm:p-0">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_120%,rgba(99,102,241,0.08),transparent)] pointer-events-none" />

        <div className="bg-white p-8 sm:p-12 rounded-[2.5rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.08)] w-full max-w-md border border-zinc-100 relative group transition-all duration-500 hover:shadow-[0_48px_80px_-24px_rgba(99,102,241,0.12)]">
            {children}
        </div>
    </div>
);
