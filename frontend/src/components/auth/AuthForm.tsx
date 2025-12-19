import React from 'react';

interface AuthFormProps {
    isLogin: boolean;
    email: string;
    setEmail: (email: string) => void;
    password: string;
    setPassword: (password: string) => void;
    error: string | null;
    onSubmit: (e: React.FormEvent) => void;
}

export const AuthForm: React.FC<AuthFormProps> = ({
    isLogin,
    email,
    setEmail,
    password,
    setPassword,
    error,
    onSubmit
}) => (
    <form onSubmit={onSubmit} className="space-y-6">
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
);
