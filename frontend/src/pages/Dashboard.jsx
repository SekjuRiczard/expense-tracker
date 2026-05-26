import { useContext } from 'react';
import { useNavigate } from 'react-router-dom';
import { AuthContext } from '../context/AuthContext';
import { setAccessToken } from '../api/axios';

export default function Dashboard() {
    const { logout } = useContext(AuthContext);
    const navigate = useNavigate();

    const handleLogout = () => {
        setAccessToken(null);
        logout();
        navigate('/login');
    };

    return (
        <div>
            <h2>Witaj w chronionym panelu!</h2>
            <p>Ta strona jest widoczna tylko dla zalogowanych użytkowników.</p>
            <button onClick={handleLogout}>Wyloguj się</button>
        </div>
    );
}