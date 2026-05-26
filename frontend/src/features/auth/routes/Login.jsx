import { useState, useContext } from 'react';
import { useNavigate } from 'react-router-dom';
import { loginApi } from '@/features/api/authApi';
import { setAccessToken } from '@/api/axios';
import { AuthContext } from '@/providers/AuthProvider';
import { LoginForm } from '@/features/auth/components/LoginForm';

export default function Login() {
    /** @type {[string, Function]} */
    const [error, setError] = useState('');
    /** @type {Object} */
    const { login } = useContext(AuthContext);
    /** @type {Function} */
    const navigate = useNavigate();
    /** @type {Function} */
    const handleLogin = async (credentials) => {
        try {
            /** @type {Object} */
            const response = await loginApi(credentials);
            setAccessToken(response.data.token);
            login();
            navigate('/dashboard');
        } catch (err) {
            setError('Błąd logowania. Sprawdź wprowadzony email i hasło.');
        }
    };

    return (
        <div>
            <h2>Logowanie</h2>
            <LoginForm onSubmit={handleLogin} error={error} />
        </div>
    );
}