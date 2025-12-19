import React, { useState } from 'react';
import api from '../api/client';
import { useAuthStore } from '../stores/useAuthStore';

export const AuthPage: React.FC = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [isLogin, setIsLogin] = useState(true);
    const [error, setError] = useState('');
    const { setAuth } = useAuthStore();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');

        try {
            const endpoint = isLogin ? 'login' : 'register';
            const response = await api.post(endpoint, { email, password });

            if (isLogin) {
                setAuth(response.data.user || { id: 0, email }, response.data.token);
            } else {
                setIsLogin(true);
                alert('Registered! Please login.');
            }
        } catch (err: any) {
            setError(err.response?.data?.message || 'Something went wrong');
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-zinc-50 p-6 sm:p-0">
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_120%,rgba(99,102,241,0.08),transparent)] pointer-events-none" />

            <div className="bg-white p-8 sm:p-12 rounded-[2.5rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.08)] w-full max-w-md border border-zinc-100 relative group transition-all duration-500 hover:shadow-[0_48px_80px_-24px_rgba(99,102,241,0.12)]">
                <div className="text-center mb-10">
                    <div className="inline-block p-4 bg-indigo-50 rounded-2xl mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500">
                        <span className="text-4xl text-indigo-600 block">🧠</span>
                    </div>
                    <h1 className="text-4xl font-black text-zinc-900 mb-3 tracking-tight">The Rambler</h1>
                    <p className="text-zinc-400 text-sm font-medium">Clear your mind. Extract the essence.</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="space-y-2">
                        <label className="block text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400 ml-1">Email Address</label>
                        <input
                            type="email"
                            required
                            placeholder="your@email.com"
                            className="w-full px-5 py-4 bg-zinc-50 rounded-2xl border border-transparent focus:bg-white focus:border-indigo-200 focus:ring-4 focus:ring-indigo-50 outline-none transition-all duration-300 text-zinc-700 placeholder:text-zinc-300"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                        />
                    </div>

                    <div className="space-y-2">
                        <label className="block text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400 ml-1">Password</label>
                        <input
                            type="password"
                            required
                            placeholder="••••••••"
                            className="w-full px-5 py-4 bg-zinc-50 rounded-2xl border border-transparent focus:bg-white focus:border-indigo-200 focus:ring-4 focus:ring-indigo-50 outline-none transition-all duration-300 text-zinc-700 placeholder:text-zinc-300"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                        />
                    </div>

                    {error && (
                        <div className="p-4 bg-rose-50 rounded-2xl border border-rose-100 animate-in fade-in slide-in-from-top-2">
                            <p className="text-rose-600 text-xs font-semibold text-center">{error}</p>
                        </div>
                    )}

                    <button className="w-full py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 hover:shadow-[0_20px_40px_-12px_rgba(79,70,229,0.3)] active:scale-[0.98] transition-all duration-500 shadow-xl shadow-indigo-100/50">
                        {isLogin ? 'Sign In' : 'Create Account'}
                    </button>
                </form>

                <div className="mt-10 pt-8 border-t border-zinc-50 text-center">
                    <p className="text-zinc-400 text-xs font-medium">
                        {isLogin ? "New to Rambler?" : "Already have an account?"}{' '}
                        <button
                            onClick={() => setIsLogin(!isLogin)}
                            className="text-indigo-600 font-bold hover:text-indigo-700 transition"
                        >
                            {isLogin ? 'Claim your space' : 'Welcome back'}
                        </button>
                    </p>
                </div>
            </div>
        </div>
    );
};
