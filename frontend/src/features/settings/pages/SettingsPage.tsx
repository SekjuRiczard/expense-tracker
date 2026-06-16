import { Alert, Box, CircularProgress, Container, Grid, Snackbar, Typography } from '@mui/material';
import { useState } from 'react';
import { flowlyPalette } from '../../../app/theme';
import { useAuth } from '../../auth';
import { DemoDataActions } from '../../demoData';
import {
  ChangePasswordDialog,
  ChangePinDialog,
  ProfileSection,
  SecuritySection,
  SessionsSection,
  SettingsTopbar,
  UserSummaryCard,
} from '../components';
import { useSessions, useLogoutDevice } from '../hooks';
import type { SettingsUserInfo } from '../types';

interface SnackbarState {
  readonly open: boolean;
  readonly message: string;
  readonly severity: 'success' | 'error' | 'warning' | 'info';
}

export const SettingsPage = () => {
  const { state } = useAuth();

  const [passwordDialogOpen, setPasswordDialogOpen] = useState(false);
  const [pinDialogOpen, setPinDialogOpen] = useState(false);
  const [snackbar, setSnackbar] = useState<SnackbarState>({
    open: false,
    message: '',
    severity: 'success',
  });
  const [loadingSessionId, setLoadingSessionId] = useState<string | null>(null);

  const showToast = (
    message: string,
    severity: 'success' | 'error' | 'warning' | 'info' = 'success',
  ) => {
    setSnackbar({ open: true, message, severity });
  };

  const sessionsQuery = useSessions();

  const logoutDeviceMutation = useLogoutDevice(() => {
    setLoadingSessionId(null);
    showToast('Device has been logged out.');
  });

  const handleLogoutDevice = (sessionId: string) => {
    setLoadingSessionId(sessionId);
    logoutDeviceMutation.mutate(sessionId);
  };

  if (state.status !== 'authenticated') {
    return (
      <Box
        sx={{
          display: 'grid',
          minHeight: '100vh',
          placeItems: 'center',
          backgroundColor: flowlyPalette.dashboard.background,
        }}
      >
        <CircularProgress />
      </Box>
    );
  }

  const { user } = state;

  const userInfo: SettingsUserInfo = {
    id: user.id,
    username: user.username,
    email: user.email,
    hasPin: user.hasPin,
    hasPassword: true,
    roleLabel: (user.roles ?? []).includes('ROLE_ADMIN') ? 'Admin' : 'User',
  };

  return (
    <Box
      sx={{
        minHeight: '100vh',
        backgroundColor: flowlyPalette.dashboard.background,
      }}
    >
      <SettingsTopbar />

      <Box component="main">
        <Container
          maxWidth="lg"
          sx={{
            px: { xs: 2, sm: 3, lg: 4 },
            py: { xs: 3, sm: 4, md: 5 },
          }}
        >
          <Box sx={{ mb: 3 }}>
            <Typography
              component="h1"
              sx={{
                color: flowlyPalette.dashboard.textPrimary,
                fontSize: { xs: '1.5rem', sm: '1.75rem' },
                fontWeight: 900,
                letterSpacing: '-0.04em',
                lineHeight: 1.15,
              }}
            >
              Account settings
            </Typography>

            <Typography
              sx={{
                mt: 0.5,
                color: flowlyPalette.dashboard.textSecondary,
                fontSize: '0.9rem',
              }}
            >
              Manage your profile and account security
            </Typography>
          </Box>

          <Box sx={{ mb: 3 }}>
            <UserSummaryCard user={userInfo} />
          </Box>

          <Grid container spacing={3}>
            <Grid size={{ xs: 12, lg: 6 }}>
              <ProfileSection user={userInfo} />
            </Grid>

            <Grid size={{ xs: 12, lg: 6 }}>
              <SecuritySection
                onChangePassword={() => { setPasswordDialogOpen(true); }}
                onChangePin={() => { setPinDialogOpen(true); }}
                user={userInfo}
              />
            </Grid>

            <Grid size={{ xs: 12 }}>
              <SessionsSection
                error={sessionsQuery.isError}
                loading={sessionsQuery.isPending}
                loadingSessionId={loadingSessionId}
                onLogoutDevice={handleLogoutDevice}
                sessions={sessionsQuery.data ?? []}
              />
            </Grid>

            {userInfo.roleLabel === 'Admin' && (
              <Grid size={{ xs: 12 }}>
                <DemoDataActions
                  showToast={(msg, sev) => { showToast(msg, sev); }}
                />
              </Grid>
            )}
          </Grid>
        </Container>
      </Box>

      <ChangePasswordDialog
        onClose={() => { setPasswordDialogOpen(false); }}
        onSuccess={(msg) => { showToast(msg); }}
        open={passwordDialogOpen}
      />

      <ChangePinDialog
        hasPin={userInfo.hasPin}
        onClose={() => { setPinDialogOpen(false); }}
        onSuccess={(msg) => { showToast(msg); }}
        open={pinDialogOpen}
      />

      <Snackbar
        anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
        autoHideDuration={4000}
        onClose={() => { setSnackbar((s) => ({ ...s, open: false })); }}
        open={snackbar.open}
      >
        <Alert
          onClose={() => { setSnackbar((s) => ({ ...s, open: false })); }}
          severity={snackbar.severity}
          sx={{ borderRadius: '12px', fontWeight: 600 }}
          variant="filled"
        >
          {snackbar.message}
        </Alert>
      </Snackbar>
    </Box>
  );
};
