import {
  Alert,
  Box,
  CircularProgress,
} from '@mui/material';
import { AppRouter, } from './app/router';
import {
  AuthPage,
  useAuth,
} from './features/auth';

const App = () => {
  const {
    state,
  } = useAuth();

  if (state.status === 'initializing') {
    return (
      <Box
        component="main"
        sx={{
          display: 'grid',
          minHeight: '100dvh',
          placeItems: 'center',
        }}
      >
        <CircularProgress />
      </Box>
    );
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

  return <AppRouter />;
};

export default App;
