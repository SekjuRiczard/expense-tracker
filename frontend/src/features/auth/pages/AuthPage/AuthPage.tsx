import {
  Box,
} from '@mui/material';
import {
  useState,
} from 'react';
import { AuthLayout, } from '../../components/AuthLayout';
import { CurrencyPanel, } from '../../components/CurrencyPanel';
import { LoginForm, } from '../../components/LoginForm';
import { PinView, } from '../../components/PinView';
import { RegisterForm, } from '../../components/RegisterForm';
import { useAuth, } from '../../hooks/useAuth';

type AuthPageMode =
  | 'login'
  | 'register';

export const AuthPage = () => {
  const {
    state,
  } = useAuth();

  const [mode, setMode,] = useState<AuthPageMode>('login');

  return (
    <AuthLayout
      rightPanel={<CurrencyPanel />}
    >
      <Box
        sx={{
          width: '100%',
          maxWidth: mode === 'register'
            ? 460
            : 430,
        }}
      >
        {state.status === 'unauthenticated' && mode === 'login' && (
          <LoginForm
            onSignUpClick={() => {
              setMode('register');
            }}
          />
        )}

        {state.status === 'unauthenticated' && mode === 'register' && (
          <RegisterForm
            onSignInClick={() => {
              setMode('login');
            }}
          />
        )}

        {state.status === 'pin_verification_required' && (
          <PinView mode="verification" />
        )}

        {state.status === 'pin_setup_required' && (
          <PinView mode="setup" />
        )}
      </Box>
    </AuthLayout>
  );
};