import React, { useState } from 'react';
import { AuthCard } from '../components/auth/AuthCard';
import { AuthHeader } from '../components/auth/AuthHeader';
import { useAuthStore } from '../stores/useAuthStore';

export const AuthPage: React.FC = () => {
    const { loginWithRedirect, continueAsGuest, getLinkAccountUrl } = useAuthStore();
    const [error, setError] = useState<string | null>(null);
    return (
        <AuthCard>
            <AuthHeader isLogin />
            <p className="mb-6 text-center text-sm text-slate-600">Sign in through WebHatchery, or start with a guest session.</p>
            {error && <p className="mb-4 text-center text-sm text-red-600">{error}</p>}
            <div className="space-y-3">
                <button type="button" onClick={loginWithRedirect} className="w-full rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white">Sign in with WebHatchery</button>
                <button type="button" onClick={() => void continueAsGuest().catch(() => setError('Guest session failed'))} className="w-full rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white">Continue as Guest</button>
                <a href={getLinkAccountUrl()} className="block text-center text-sm text-indigo-600 underline">Create an account and link guest rambles</a>
            </div>
        </AuthCard>
    );
};
