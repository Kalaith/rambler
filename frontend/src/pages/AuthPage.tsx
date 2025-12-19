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
        <div className="min-h-screen flex items-center justify-center bg-slate-50">
            <div className="bg-white p-12 rounded-3xl shadow-2xl w-full max-w-md border border-slate-100">
                <div className="text-center mb-10">
                    <h1 className="text-4xl font-black text-indigo-900 mb-2 tracking-tight">Rambler</h1>
                    <p className="text-slate-400">Sign in to start dumping thoughts.</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label className="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Email</label>
                        <input
                            type="email"
                            required
                            className="w-full p-4 bg-slate-50 rounded-xl border border-transparent focus:bg-white focus:border-indigo-100 focus:ring-4 focus:ring-indigo-50 outline-none transition"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                        />
                    </div>

                    <div>
                        <label className="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Password</label>
                        <input
                            type="password"
                            required
                            className="w-full p-4 bg-slate-50 rounded-xl border border-transparent focus:bg-white focus:border-indigo-100 focus:ring-4 focus:ring-indigo-50 outline-none transition"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                        />
                    </div>

                    {error && <p className="text-red-500 text-sm text-center">{error}</p>}

                    <button className="w-full py-4 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 hover:scale-[1.02] active:scale-[0.98] transition shadow-lg shadow-indigo-100">
                        {isLogin ? 'Login' : 'Create Account'}
                    </button>
                </form>

                <div className="mt-8 text-center text-slate-400 text-sm">
                    {isLogin ? "Don't have an account?" : "Already have an account?"}{' '}
                    <button
                        onClick={() => setIsLogin(!isLogin)}
                        className="text-indigo-600 font-bold hover:underline"
                    >
                        {isLogin ? 'Register' : 'Login'}
                    </button>
                </div>
            </div>
        </div>
    );
};
