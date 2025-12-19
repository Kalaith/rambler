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
    const { login, register } = useAuthStore();
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
        } catch (err) {
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

            <AuthToggle
                isLogin={isLogin}
                onToggle={() => setIsLogin(!isLogin)}
            />
        </AuthCard>
    );
};
