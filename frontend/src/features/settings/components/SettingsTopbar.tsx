import { ArrowBackRounded, LogoutRounded } from '@mui/icons-material';
import { Box, Button, CircularProgress, Container, IconButton, Typography } from '@mui/material';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import logo from '../../../assets/logo.png';
import { flowlyPalette } from '../../../app/theme';
import { useAuth } from '../../auth';

export const SettingsTopbar = () => {
  const navigate = useNavigate();
  const { logout } = useAuth();
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  const handleBack = () => {
    if (window.history.length > 1) {
      navigate(-1);
    } else {
      navigate('/dashboard', { replace: true });
    }
  };

  const handleLogout = async () => {
    setIsLoggingOut(true);
    try {
      await logout();
    } finally {
      setIsLoggingOut(false);
    }
  };

  return (
    <Box
      component="header"
      sx={{
        position: 'sticky',
        top: 0,
        zIndex: 100,
        backgroundColor: flowlyPalette.dashboard.surface,
        borderBottom: `1px solid ${flowlyPalette.dashboard.border}`,
      }}
    >
      <Container maxWidth="lg">
        <Box
          sx={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            py: { xs: 1.5, sm: 2 },
            gap: 2,
          }}
        >
          <Box
            sx={{
              display: 'flex',
              alignItems: 'center',
              gap: { xs: 1, sm: 1.5 },
            }}
          >
            <IconButton
              aria-label="Go back"
              onClick={handleBack}
              size="small"
              sx={{
                width: 36,
                height: 36,
                border: `1px solid ${flowlyPalette.dashboard.border}`,
                borderRadius: '10px',
                color: flowlyPalette.dashboard.textSecondary,
                '&:hover': {
                  backgroundColor: '#F8FAFC',
                  color: flowlyPalette.dashboard.textPrimary,
                },
              }}
            >
              <ArrowBackRounded sx={{ fontSize: 18 }} />
            </IconButton>

            <Box
              sx={{
                display: 'flex',
                alignItems: 'center',
                gap: 1,
              }}
            >
              <Box
                alt="Flowly"
                component="img"
                src={logo}
                sx={{
                  width: { xs: 28, sm: 34 },
                  height: { xs: 28, sm: 34 },
                  objectFit: 'contain',
                }}
              />

              <Typography
                sx={{
                  color: flowlyPalette.dashboard.textPrimary,
                  fontSize: { xs: '1rem', sm: '1.1rem' },
                  fontWeight: 900,
                  letterSpacing: '-0.04em',
                }}
              >
                Flowly
              </Typography>
            </Box>
          </Box>

          <Button
            disabled={isLoggingOut}
            onClick={() => { void handleLogout(); }}
            size="small"
            startIcon={
              isLoggingOut
                ? <CircularProgress size={14} sx={{ color: '#E11D48' }} />
                : <LogoutRounded sx={{ fontSize: 16 }} />
            }
            variant="outlined"
            sx={{
              borderColor: '#FECDD3',
              color: '#E11D48',
              borderRadius: '10px',
              fontSize: '0.8rem',
              fontWeight: 700,
              textTransform: 'none',
              flexShrink: 0,
              '&:hover': {
                borderColor: '#FDA4AF',
                backgroundColor: flowlyPalette.dashboard.roseSoft,
                color: '#BE123C',
              },
              '&.Mui-disabled': {
                borderColor: '#FECDD3',
                color: '#FDA4AF',
              },
            }}
          >
            Log out
          </Button>
        </Box>
      </Container>
    </Box>
  );
};
