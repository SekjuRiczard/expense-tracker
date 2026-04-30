import { createContext, useState } from 'react';

export const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    /** @type {[Object, Function]} */
    const [authState, setAuthState] = useState({ isAuthenticated: false, isPinVerified: false });

    return (
        <AuthContext.Provider value={{
            authState,
            login: () => setAuthState({ isAuthenticated: true, isPinVerified: false }),
            logout: () => setAuthState({ isAuthenticated: false, isPinVerified: false }),
            setPinVerified: () => setAuthState(prev => ({ ...prev, isPinVerified: true }))
        }}>
            {children}
        </AuthContext.Provider>
    );
}