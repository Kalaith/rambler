import React, { useState } from 'react';
import { useAuthStore } from '../stores/useAuthStore';
import { useNavigate } from 'react-router-dom';
import { AuthCard } from '../components/auth/AuthCard';
import { AuthHeader } from '../components/auth/AuthHeader';
import { AuthForm } from '../components/auth/AuthForm';
import { AuthToggle } from '../components/auth/AuthToggle';

export const AuthPage: React.FC = () => {
    const [isLogin, setIsLogin] = useState(true);
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState<string | null>(null);
    const { login, register, continueAsGuest, getLinkAccountUrl } = useAuthStore();
    const navigate = useNavigate();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError(null);
        try {
            const success = isLogin
                ? await login(email, password)
                : await register(email, password);

            if (success) {
                navigate('/');
            } else {
                setError(isLogin ? 'Invalid credentials' : 'Registration failed');
            }
        } catch {
            setError('Something went wrong. Please try again.');
        }
    };

    return (
        <AuthCard>
            <AuthHeader isLogin={isLogin} />

            <AuthForm
                isLogin={isLogin}
                email={email}
                setEmail={setEmail}
                password={password}
                setPassword={setPassword}
                error={error}
                onSubmit={handleSubmit}
            />

            <div className="mb-6 space-y-3">
                <button
                    type="button"
                    onClick={async () => {
                        const success = await continueAsGuest();
                        if (success) {
                            navigate('/');
                        } else {
                            setError('Guest session failed');
                        }
                    }}
                    className="w-full rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                >
                    Continue as Guest
                </button>
                <a
                    href={getLinkAccountUrl()}
                    className="block text-center text-sm text-indigo-600 underline hover:text-indigo-700"
                >
                    Sign up and link guest rambles
                </a>
            </div>

            <AuthToggle
                isLogin={isLogin}
                onToggle={() => setIsLogin(!isLogin)}
            />
        </AuthCard>
    );
};
