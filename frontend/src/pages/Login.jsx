import { useState, useContext } from 'react';
import { useNavigate } from 'react-router-dom';
import api, { setAccessToken } from '../api/axios';
import { AuthContext } from '../context/AuthContext';

export default function Login() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');

    const { login } = useContext(AuthContext);
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');

        try {
            // Strzał do endpointu skonfigurowanego w security.yaml (check_path)
            const response = await api.post('/login_check', { email, password });

            // 1. Zapisz token JWT w bezpiecznej pamięci Axios
            setAccessToken(response.data.token);

            // 2. Ustaw globalny stan zalogowania
            login();

            // 3. Przekieruj do chronionego panelu
            navigate('/dashboard');
        } catch (err) {
            setError('Błąd logowania. Sprawdź wprowadzony email i hasło.');
        }
    };

    return (
        <div>
            <h2>Logowanie</h2>
            {error && <p style={{ color: 'red' }}>{error}</p>}

            <form onSubmit={handleSubmit}>
                <div>
                    <label>Email:</label>
                    <input
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                    />
                </div>
                <div>
                    <label>Hasło:</label>
                    <input
                        type="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                    />
                </div>
                <button type="submit">Zaloguj</button>
            </form>
        </div>
    );
}