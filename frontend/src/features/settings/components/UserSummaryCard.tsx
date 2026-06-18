import { MailOutlineRounded } from '@mui/icons-material';
import { Avatar, Box, Chip, Paper, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { SettingsUserInfo } from '../types';

const getInitials = (username: string): string => {
  const normalized = username.trim();
  if (!normalized) return 'U';

  const parts = normalized.split(/\s+/).filter(Boolean);
  if (parts.length === 1) {
    return parts[0].slice(0, 2).toUpperCase();
  }
  return parts
    .slice(0, 2)
    .map((p) => p[0])
    .join('')
    .toUpperCase();
};

export interface UserSummaryCardProps {
  readonly user: SettingsUserInfo;
}

export const UserSummaryCard = ({ user }: UserSummaryCardProps) => {
  const initials = getInitials(user.username);

  return (
    <Paper
      elevation={0}
      sx={{
        p: { xs: 2.5, sm: 3, md: 4 },
        border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        borderRadius: '20px',
        backgroundColor: flowlyPalette.dashboard.surface,
      }}
    >
      <Box
        sx={{
          display: 'flex',
          alignItems: 'center',
          gap: { xs: 2, sm: 2.5 },
          flexWrap: 'wrap',
        }}
      >
        <Avatar
          aria-label={`${user.username} avatar`}
          sx={{
            width: 64,
            height: 64,
            background: 'linear-gradient(135deg, #6366F1, #4F46E5)',
            color: '#FFFFFF',
            fontSize: '1.15rem',
            fontWeight: 800,
            flexShrink: 0,
          }}
        >
          {initials}
        </Avatar>

        <Box sx={{ minWidth: 0 }}>
          <Box
            sx={{
              display: 'flex',
              alignItems: 'center',
              gap: 1,
              flexWrap: 'wrap',
            }}
          >
            <Typography
              sx={{
                color: flowlyPalette.dashboard.textPrimary,
                fontSize: { xs: '1.1rem', sm: '1.2rem' },
                fontWeight: 800,
                letterSpacing: '-0.03em',
                lineHeight: 1.2,
              }}
            >
              {user.username}
            </Typography>

            <Chip
              label={user.roleLabel}
              size="small"
              sx={{
                height: 22,
                fontSize: '0.7rem',
                fontWeight: 700,
                backgroundColor: flowlyPalette.dashboard.indigoSoft,
                color: flowlyPalette.dashboard.indigoDark,
                border: `1px solid #C7D2FE`,
                '& .MuiChip-label': { px: 1 },
              }}
            />
          </Box>

          <Box
            sx={{
              display: 'flex',
              alignItems: 'center',
              gap: 0.75,
              mt: 0.75,
            }}
          >
            <MailOutlineRounded
              sx={{
                color: flowlyPalette.dashboard.textMuted,
                fontSize: 15,
                flexShrink: 0,
              }}
            />
            <Typography
              sx={{
                color: flowlyPalette.dashboard.textSecondary,
                fontSize: '0.85rem',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                whiteSpace: 'nowrap',
              }}
            >
              {user.email}
            </Typography>
          </Box>
        </Box>
      </Box>
    </Paper>
  );
};
