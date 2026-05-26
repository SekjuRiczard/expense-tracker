import { useState } from 'react';
import { Input } from '@/components/Input';
import { Button } from '@/components/Button';

export const LoginForm = ({ onSubmit, error }) => {
    /** @type {[string, Function]} */
    const [email, setEmail] = useState('');
    /** @type {[string, Function]} */
    const [password, setPassword] = useState('');

    return (
        <form onSubmit={(e) => { e.preventDefault(); onSubmit({ email, password }); }}>
            {error && <p style={{ color: 'red' }}>{error}</p>}
            <Input type="email" value={email} onChange={e => setEmail(e.target.value)} required placeholder="Email" />
            <Input type="password" value={password} onChange={e => setPassword(e.target.value)} required placeholder="Hasło" />
            <Button type="submit">Zaloguj</Button>
        </form>
    );
};