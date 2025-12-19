import React from 'react';

interface AuthToggleProps {
    isLogin: boolean;
    onToggle: () => void;
}

export const AuthToggle: React.FC<AuthToggleProps> = ({ isLogin, onToggle }) => (
    <div className="mt-10 pt-8 border-t border-zinc-50 text-center">
        <p className="text-zinc-400 text-xs font-medium">
            {isLogin ? "New to Rambler?" : "Already have an account?"}{' '}
            <button
                onClick={onToggle}
                className="text-indigo-600 font-bold hover:text-indigo-700 transition"
            >
                {isLogin ? 'Claim your space' : 'Welcome back'}
            </button>
        </p>
    </div>
);
