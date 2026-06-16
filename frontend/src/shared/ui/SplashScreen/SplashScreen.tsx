import { Box, CircularProgress, Stack, Typography } from '@mui/material';
import { LogoMark } from '../LogoMark';

export interface SplashScreenProps {
  readonly message?: string;
}

export const SplashScreen = ({
  message = 'Loading your workspace...',
}: SplashScreenProps) => {
  return (
    <Box
      component="main"
      sx={{
        display: 'grid',
        placeItems: 'center',
        minHeight: '100dvh',
        width: '100%',
        backgroundColor: '#F9FAFB',
        px: 3,
      }}
    >
      <Stack
        sx={{
          alignItems: 'center',
          flexDirection: 'column',
          gap: 3,
        }}
      >
        <LogoMark size={56} />

        <CircularProgress size={26} thickness={4.5} />

        <Typography
          sx={{
            color: '#64748B',
            fontSize: '0.85rem',
            fontWeight: 600,
          }}
        >
          {message}
        </Typography>
      </Stack>
    </Box>
  );
};
