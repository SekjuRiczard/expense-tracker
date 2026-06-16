import {
  ComputerRounded,
  SmartphoneRounded,
  LogoutRounded,
} from '@mui/icons-material';
import {
  Box,
  Button,
  CircularProgress,
  Typography,
} from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { SettingsSession } from '../types';

const parseBrowser = (ua: string | null): string => {
  if (!ua) return 'Unknown browser';
  if (/Edg\//i.test(ua)) return 'Edge';
  if (/Chrome\//i.test(ua) && !/Chromium/i.test(ua)) return 'Chrome';
  if (/Firefox\//i.test(ua)) return 'Firefox';
  if (/Safari\//i.test(ua) && !/Chrome/i.test(ua)) return 'Safari';
  return 'Unknown browser';
};

const parseOS = (ua: string | null): string => {
  if (!ua) return 'Unknown OS';
  if (/Windows NT/i.test(ua)) return 'Windows';
  if (/Mac OS X/i.test(ua)) return 'macOS';
  if (/Linux/i.test(ua) && !/Android/i.test(ua)) return 'Linux';
  if (/Android/i.test(ua)) return 'Android';
  if (/iPhone|iPad|iPod/i.test(ua)) return 'iOS';
  return 'Unknown OS';
};

const isMobileUA = (ua: string | null): boolean => {
  if (!ua) return false;
  return /Mobile|Android|iPhone|iPad/i.test(ua);
};

const formatDate = (iso: string): string => {
  try {
    const d = new Date(iso);
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()}, ${pad(d.getHours())}:${pad(d.getMinutes())}`;
  } catch {
    return iso;
  }
};

const formatDateShort = (iso: string): string => {
  try {
    const d = new Date(iso);
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()}`;
  } catch {
    return iso;
  }
};

export interface SessionCardProps {
  readonly session: SettingsSession;
  readonly onLogout: () => void;
  readonly isLoggingOut: boolean;
}

export const SessionCard = ({
  session,
  onLogout,
  isLoggingOut,
}: SessionCardProps) => {
  const browser = parseBrowser(session.userAgent);
  const os = parseOS(session.userAgent);
  const isMobile = isMobileUA(session.userAgent);
  const DeviceIcon = isMobile ? SmartphoneRounded : ComputerRounded;

  return (
    <Box
      sx={{
        p: 2,
        border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        borderRadius: '16px',
        backgroundColor: flowlyPalette.dashboard.surface,
        transition: 'border-color 160ms ease',
        '&:hover': {
          borderColor: '#A5B4FC',
        },
      }}
    >
      <Box
        sx={{
          display: 'flex',
          alignItems: 'flex-start',
          gap: 1.5,
          mb: 1.5,
        }}
      >
        <Box
          aria-hidden="true"
          sx={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            width: 36,
            height: 36,
            borderRadius: '9px',
            backgroundColor: flowlyPalette.dashboard.indigoSoft,
            flexShrink: 0,
          }}
        >
          <DeviceIcon
            sx={{ color: flowlyPalette.dashboard.indigoDark, fontSize: 18 }}
          />
        </Box>

        <Box sx={{ flex: 1, minWidth: 0 }}>
          <Typography
            sx={{
              color: flowlyPalette.dashboard.textPrimary,
              fontSize: '0.85rem',
              fontWeight: 700,
              lineHeight: 1.3,
              overflow: 'hidden',
              textOverflow: 'ellipsis',
              whiteSpace: 'nowrap',
            }}
          >
            {browser} on {os}
          </Typography>

          <Typography
            sx={{
              mt: 0.2,
              color: flowlyPalette.dashboard.textMuted,
              fontSize: '0.75rem',
            }}
          >
            {session.ipAddress ?? 'Unknown IP'}
          </Typography>
        </Box>
      </Box>

      <Box
        sx={{
          display: 'grid',
          gridTemplateColumns: '1fr 1fr',
          gap: 1,
          mb: 1.5,
        }}
      >
        <InfoItem label="Logged in" value={formatDate(session.createdAt)} />
        <InfoItem
          label="Expires"
          value={formatDateShort(session.expiresAt)}
        />
      </Box>

      <Button
        disabled={isLoggingOut}
        fullWidth
        onClick={onLogout}
        size="small"
        startIcon={
          isLoggingOut
            ? <CircularProgress size={13} sx={{ color: '#E11D48' }} />
            : <LogoutRounded sx={{ fontSize: 14 }} />
        }
        variant="outlined"
        sx={{
          borderColor: '#FECDD3',
          color: '#E11D48',
          borderRadius: '10px',
          fontSize: '0.78rem',
          fontWeight: 600,
          textTransform: 'none',
          '&:hover': {
            borderColor: '#FDA4AF',
            backgroundColor: flowlyPalette.dashboard.roseSoft,
          },
          '&.Mui-disabled': {
            borderColor: '#FECDD3',
            color: '#FDA4AF',
          },
        }}
      >
        Log out device
      </Button>
    </Box>
  );
};

const InfoItem = ({
  label,
  value,
}: {
  readonly label: string;
  readonly value: string;
}) => (
  <Box>
    <Typography
      sx={{
        color: flowlyPalette.dashboard.textMuted,
        fontSize: '0.7rem',
        fontWeight: 500,
        mb: 0.2,
      }}
    >
      {label}
    </Typography>

    <Typography
      sx={{
        color: flowlyPalette.dashboard.textSecondary,
        fontSize: '0.75rem',
        fontWeight: 600,
      }}
    >
      {value}
    </Typography>
  </Box>
);
