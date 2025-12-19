import { useAuthStore } from './stores/useAuthStore';
import { AuthPage } from './pages/AuthPage';
import { CapturePage } from './pages/CapturePage';

function App() {
    const { token } = useAuthStore();

    if (!token) {
        return <AuthPage />;
    }

    return <CapturePage />;
}

export default App;
