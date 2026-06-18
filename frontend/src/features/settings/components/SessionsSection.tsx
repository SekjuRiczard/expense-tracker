import { MonitorRounded } from '@mui/icons-material';
import { Alert, Box, Grid, Skeleton } from '@mui/material';
import { SectionCard } from './SectionCard';
import { SessionCard } from './SessionCard';
import type { SettingsSession } from '../types';

export interface SessionsSectionProps {
  readonly sessions: readonly SettingsSession[];
  readonly loading: boolean;
  readonly error: boolean;
  readonly loadingSessionId: string | null;
  readonly onLogoutDevice: (id: string) => void;
}

export const SessionsSection = ({
  sessions,
  loading,
  error,
  loadingSessionId,
  onLogoutDevice,
}: SessionsSectionProps) => {
  const subtitle = loading
    ? 'Sessions & devices'
    : `Sessions & devices (${sessions.length.toString()})`;

  return (
    <SectionCard
      icon={MonitorRounded}
      iconBgColor="#FFFBEB"
      iconColor="#D97706"
      subtitle={subtitle}
      title="Sessions & devices"
    >
      {error && (
        <Alert severity="error" sx={{ borderRadius: '12px' }}>
          Failed to load sessions.
        </Alert>
      )}

      {loading && (
        <Box
          sx={{
            display: 'grid',
            gridTemplateColumns: { xs: '1fr', md: '1fr 1fr' },
            gap: 1.5,
          }}
        >
          {[0, 1].map((i) => (
            <Skeleton
              key={i}
              height={160}
              sx={{ borderRadius: '16px' }}
              variant="rectangular"
            />
          ))}
        </Box>
      )}

      {!loading && !error && sessions.length === 0 && (
        <Alert severity="info" sx={{ borderRadius: '12px' }}>
          No active sessions found.
        </Alert>
      )}

      {!loading && !error && sessions.length > 0 && (
        <Grid container spacing={1.5}>
          {sessions.map((session) => (
            <Grid
              key={session.id}
              size={{ xs: 12, md: 6 }}
            >
              <SessionCard
                isLoggingOut={loadingSessionId === session.id}
                onLogout={() => { onLogoutDevice(session.id); }}
                session={session}
              />
            </Grid>
          ))}
        </Grid>
      )}
    </SectionCard>
  );
};
