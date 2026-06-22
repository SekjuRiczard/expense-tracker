import {
  Alert,
  Box,
} from '@mui/material';
import {
  lazy,
  Suspense,
} from 'react';
import {
  AuthPage,
  useAuth,
} from './features/auth';
import { SplashScreen } from './shared/ui/SplashScreen';

const AppRouter = lazy(async () => {
  const module = await import('./app/router/AppRouter');

  return {
    default: module.AppRouter,
  };
});

const App = () => {
  const {
    state,
  } = useAuth();

  if (state.status === 'initializing') {
    return <SplashScreen />;
  }

  if (state.status === 'error') {
    return (
      <Box
        component="main"
        sx={{
          display: 'grid',
          minHeight: '100dvh',
          placeItems: 'center',
          px: 3,
        }}
      >
        <Alert severity="error">
          Application session could not be loaded.
        </Alert>
      </Box>
    );
  }

  if (state.status !== 'authenticated') {
    return <AuthPage />;
  }

  return (
    <Suspense fallback={<SplashScreen />}>
      <AppRouter />
    </Suspense>
  );
};

export default App;
