import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import api from '../api/axios';

export default function Register() {
    const [formData, setFormData] = useState({ email: '', username: '', password: '' });
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await api.post('/register', formData);
            setSuccess('Konto utworzone! Możesz się zalogować.');
            setTimeout(() => navigate('/login'), 2000);
        } catch (err) {
            setError(err.response?.data?.error || 'Błąd rejestracji.');
        }
    };

    return (
        <div>
            <h2>Rejestracja</h2>
            {error && <p style={{ color: 'red' }}>{error}</p>}
            {success && <p style={{ color: 'green' }}>{success}</p>}

            <form onSubmit={handleSubmit}>
                <input type="email" placeholder="Email" onChange={e => setFormData({...formData, email: e.target.value})} required />
                <br/><br/>
                <input type="text" placeholder="Nazwa użytkownika" onChange={e => setFormData({...formData, username: e.target.value})} required />
                <br/><br/>
                <input type="password" placeholder="Hasło (min. 8 znaków)" onChange={e => setFormData({...formData, password: e.target.value})} required />
                <br/><br/>
                <button type="submit">Zarejestruj się</button>
            </form>
            <br />
            <Link to="/login">Masz już konto? Zaloguj się</Link>
        </div>
    );
}