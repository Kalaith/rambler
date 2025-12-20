import { useAuthStore } from './stores/useAuthStore';
import { AuthPage } from './pages/AuthPage';
import { CapturePage } from './pages/CapturePage';

function App() {
    const { token, user } = useAuthStore();

    if (!token) {
        return <AuthPage />;
    }

    return <CapturePage key={user?.id || 'guest'} />;
}

export default App;
