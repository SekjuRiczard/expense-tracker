import {
  Box,
  Button,
  Stack,
  Typography,
} from '@mui/material';
import { flowlyPalette, } from './app/theme';
import {
  AuthPage,
  useAuth,
} from './features/auth';

const App = () => {
  const {
    logout,
    state,
  } = useAuth();

  if (state.status !== 'authenticated') {
    return <AuthPage />;
  }

  return (
    <Box
      component="main"
      sx={{
        display: 'grid',
        minHeight: '100dvh',
        placeItems: 'center',
        px: 3,
        backgroundColor: flowlyPalette.auth.backgroundMuted,
      }}
    >
      <Stack
        alignItems="center"
        spacing={2}
        sx={{
          width: '100%',
          maxWidth: 520,
          p: 4,
          border: `1px solid ${flowlyPalette.auth.borderMuted}`,
          borderRadius: 4,
          backgroundColor: flowlyPalette.auth.background,
          boxShadow: '0 18px 42px rgba(16, 24, 40, 0.08)',
        }}
      >
        <Typography
          component="h1"
          sx={{
            color: flowlyPalette.auth.textPrimary,
            fontSize: '2rem',
            fontWeight: 850,
            letterSpacing: '-0.045em',
            textAlign: 'center',
          }}
        >
          Welcome to Flowly
        </Typography>

        <Typography
          sx={{
            color: flowlyPalette.auth.textSecondary,
            textAlign: 'center',
          }}
        >
          You are authenticated as {state.user.username}.
        </Typography>

        <Button
          onClick={() => {
            void logout();
          }}
          variant="contained"
          sx={{
            borderRadius: 2.5,
            background: `linear-gradient(
              135deg,
              ${flowlyPalette.auth.buttonGradientStart} 0%,
              ${flowlyPalette.auth.buttonGradientEnd} 100%
            )`,
            fontWeight: 750,
            textTransform: 'none',
          }}
        >
          Sign out
        </Button>
      </Stack>
    </Box>
  );
};

export default App;