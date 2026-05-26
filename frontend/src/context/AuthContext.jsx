import { createContext, useState } from 'react';

export const AuthContext = createContext(null);

export function AuthProvider({children}){
    const [authState , setAuthState] = useState({
        isAuthenticated: false,
        isPinVerified: false,
    })

    const login = () => {
        setAuthState({isAuthenticated: true, isPinVerified: false});
    }

    const logout = () => {
        setAuthState({isAuthenticated: false, isPinVerified: false})
    }

    const setPinVerified = () => {
        setAuthState(prev => ({ ...prev, isPinVerified: true }));
    };

    const value = {
        authState,
        login,
        logout,
        setPinVerified
    };

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
}