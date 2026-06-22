import {
  Box,
  useMediaQuery,
  useTheme,
} from '@mui/material';
import {
  lazy,
  Suspense,
  useState,
} from 'react';
import { AuthLayout, } from '../../components/AuthLayout';
import { LoginForm, } from '../../components/LoginForm';
import { PinView, } from '../../components/PinView';
import { RegisterForm, } from '../../components/RegisterForm';
import { useAuth, } from '../../hooks/useAuth';

const CurrencyPanel = lazy(async () => {
  const module = await import('../../components/CurrencyPanel');

  return {
    default: module.CurrencyPanel,
  };
});

type AuthPageMode =
  | 'login'
  | 'register';

export const AuthPage = () => {
  const {
    state,
  } = useAuth();

  const theme = useTheme();
  const isDesktop = useMediaQuery(theme.breakpoints.up('md'));
  const [mode, setMode,] = useState<AuthPageMode>('login');

  return (
    <AuthLayout
      rightPanel={isDesktop ? (
        <Suspense fallback={null}>
          <CurrencyPanel />
        </Suspense>
      ) : undefined}
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
