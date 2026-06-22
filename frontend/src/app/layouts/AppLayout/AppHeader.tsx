import AddRounded from '@mui/icons-material/AddRounded';
import MenuRounded from '@mui/icons-material/MenuRounded';
import NotificationsNoneRounded from '@mui/icons-material/NotificationsNoneRounded';
import SearchRounded from '@mui/icons-material/SearchRounded';
import {
  Avatar,
  Box,
  Button,
  IconButton,
  InputAdornment,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { Link as RouterLink, useLocation } from 'react-router-dom';
import { useAuth } from '../../../features/auth';
import { flowlyPalette } from '../../theme';
import { navigationItems } from './navigation';
import type { PageHeaderOverride } from './pageHeader.types';

export interface AppHeaderProps {
  readonly onMenuClick: () => void;
  readonly headerOverride?: PageHeaderOverride | null;
}

const pageMetadata: Record<string, {
  readonly title: string;
  readonly subtitle: string;
}> = {
  '/dashboard': {
    title: 'Overview',
    subtitle: 'Welcome back, here is your financial overview.',
  },
  '/wallets': {
    title: 'Wallets',
    subtitle: 'Manage your accounts and available funds.',
  },
  '/transactions': {
    title: 'Transactions',
    subtitle: 'Review and manage your financial activity.',
  },
  '/categories': {
    title: 'Categories',
    subtitle: 'Organize your income and expenses.',
  },
  '/budgets': {
    title: 'Budgets',
    subtitle: 'Track your spending limits and financial goals.',
  },
  '/analytics': {
    title: 'Analytics',
    subtitle: 'Explore your financial insights and trends.',
  },
  '/settings': {
    title: 'Settings',
    subtitle: 'Manage your account and application preferences.',
  },
};

const getInitials = (username: string): string => {
  const normalizedUsername = username.trim();

  if (!normalizedUsername) {
    return 'U';
  }

  const parts = normalizedUsername.split(/\s+/).filter(Boolean);

  if (parts.length === 1) {
    return parts[0].slice(0, 2).toUpperCase();
  }

  return parts
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase();
};

export const AppHeader = ({
  onMenuClick,
  headerOverride,
}: AppHeaderProps) => {
  const { state } = useAuth();
  const location = useLocation();

  const fallbackTitle =
    navigationItems.find((item) => item.path === location.pathname)?.label ??
    'Flowly';

  const staticMeta = pageMetadata[location.pathname] ?? {
    title: fallbackTitle,
    subtitle: 'Manage your finances in one place.',
  };

  const subtitle = headerOverride?.subtitle ?? staticMeta.subtitle;

  const username =
    state.status === 'authenticated' ? state.user.username : 'User';

  const email =
    state.status === 'authenticated' ? state.user.email : '';

  const initials = getInitials(username);

  return (
    <Box
      component="header"
      sx={{
        px: {
          xs: 2,
          sm: 3,
          lg: 4,
        },
        py: {
          xs: 1.75,
          md: 2,
        },
        borderBottom: `1px solid ${flowlyPalette.dashboard.border}`,
        backgroundColor: flowlyPalette.dashboard.surface,
      }}
    >
      <Stack
        sx={{
          alignItems: 'center',
          flexDirection: 'row',
          justifyContent: 'space-between',
          gap: 2,
        }}
      >
        <Stack
          sx={{
            minWidth: 0,
            alignItems: 'center',
            flexDirection: 'row',
            gap: 1.25,
          }}
        >
          <IconButton
            aria-label="Open navigation menu"
            onClick={onMenuClick}
            sx={{
              display: {
                xs: 'inline-flex',
                md: 'none',
              },
              color: flowlyPalette.dashboard.textPrimary,
            }}
          >
            <MenuRounded />
          </IconButton>

          <Box sx={{ minWidth: 0 }}>
            <Typography
              component="h1"
              sx={{
                overflow: 'hidden',
                color: flowlyPalette.dashboard.textPrimary,
                fontSize: {
                  xs: '1.1rem',
                  sm: '1.25rem',
                },
                fontWeight: 850,
                letterSpacing: '-0.035em',
                lineHeight: 1.15,
                textOverflow: 'ellipsis',
                whiteSpace: 'nowrap',
              }}
            >
              {staticMeta.title}
            </Typography>

            <Typography
              sx={{
                mt: 0.35,
                overflow: 'hidden',
                color: flowlyPalette.dashboard.textSecondary,
                fontSize: {
                  xs: '0.75rem',
                  sm: '0.8rem',
                },
                lineHeight: 1.35,
                textOverflow: 'ellipsis',
                whiteSpace: 'nowrap',
              }}
            >
              {subtitle}
            </Typography>
          </Box>
        </Stack>

        <Stack
          sx={{
            flexShrink: 0,
            alignItems: 'center',
            flexDirection: 'row',
            gap: {
              xs: 0.75,
              md: 1,
            },
          }}
        >
          <TextField
            aria-label="Search"
            placeholder="Search..."
            size="small"
            slotProps={{
              input: {
                startAdornment: (
                  <InputAdornment position="start">
                    <SearchRounded
                      sx={{
                        color: flowlyPalette.dashboard.textMuted,
                        fontSize: 19,
                      }}
                    />
                  </InputAdornment>
                ),
              },
            }}
            sx={{
              display: {
                xs: 'none',
                lg: 'block',
              },
              width: {
                lg: 210,
                xl: 240,
              },
              '& .MuiOutlinedInput-root': {
                height: 38,
                borderRadius: 2.25,
                backgroundColor: flowlyPalette.dashboard.background,
                fontSize: '0.8rem',
                '& fieldset': {
                  borderColor: flowlyPalette.dashboard.border,
                },
                '&:hover fieldset': {
                  borderColor: flowlyPalette.dashboard.textMuted,
                },
                '&.Mui-focused fieldset': {
                  borderColor: flowlyPalette.dashboard.indigo,
                },
              },
            }}
          />

          <IconButton
            aria-label="Notifications"
            onClick={() => {
              window.alert('Notifications are under construction.');
            }}
            sx={{
              width: 38,
              height: 38,
              border: `1px solid ${flowlyPalette.dashboard.border}`,
              borderRadius: 2,
              color: flowlyPalette.dashboard.textSecondary,
              backgroundColor: flowlyPalette.dashboard.surface,
              transition: 'background-color 160ms ease, color 160ms ease',
              '&:hover': {
                color: flowlyPalette.dashboard.textPrimary,
                backgroundColor: flowlyPalette.dashboard.background,
              },
            }}
          >
            <NotificationsNoneRounded sx={{ fontSize: 19 }} />
          </IconButton>

          {headerOverride?.action ?? (
            <Button
              onClick={() => {
                window.alert('Transaction creation is under construction.');
              }}
              startIcon={<AddRounded sx={{ fontSize: 18 }} />}
              variant="contained"
              sx={{
                display: {
                  xs: 'none',
                  sm: 'inline-flex',
                },
                minHeight: 38,
                px: 1.6,
                borderRadius: 2,
                backgroundColor: flowlyPalette.dashboard.indigoDark,
                boxShadow: 'none',
                fontSize: '0.8rem',
                fontWeight: 700,
                textTransform: 'none',
                transition:
                  'background-color 160ms ease, box-shadow 160ms ease',
                '&:hover': {
                  backgroundColor: flowlyPalette.dashboard.indigo,
                  boxShadow: 'none',
                },
              }}
            >
              Add transaction
            </Button>
          )}

          <Stack
            aria-label="Open account settings"
            component={RouterLink}
            to="/settings"
            sx={{
              alignItems: 'center',
              flexDirection: 'row',
              gap: 1,
              px: 0.5,
              py: 0.5,
              borderRadius: 2,
              cursor: 'pointer',
              textDecoration: 'none',
              transition: 'background-color 160ms ease',
              '&:hover': {
                backgroundColor: flowlyPalette.dashboard.background,
              },
            }}
          >
            <Stack
              sx={{
                display: {
                  xs: 'none',
                  md: 'flex',
                },
                minWidth: 0,
                alignItems: 'flex-end',
                flexDirection: 'column',
                gap: 0.2,
              }}
            >
              <Typography
                sx={{
                  maxWidth: 130,
                  overflow: 'hidden',
                  color: flowlyPalette.dashboard.textPrimary,
                  fontSize: '0.78rem',
                  fontWeight: 750,
                  lineHeight: 1.25,
                  textOverflow: 'ellipsis',
                  whiteSpace: 'nowrap',
                }}
              >
                {username}
              </Typography>

              <Typography
                sx={{
                  maxWidth: 150,
                  overflow: 'hidden',
                  color: flowlyPalette.dashboard.textSecondary,
                  fontSize: '0.7rem',
                  lineHeight: 1.25,
                  textOverflow: 'ellipsis',
                  whiteSpace: 'nowrap',
                }}
              >
                {email}
              </Typography>
            </Stack>

            <Avatar
              sx={{
                width: 38,
                height: 38,
                backgroundColor: flowlyPalette.dashboard.indigo,
                color: '#FFFFFF',
                fontSize: '0.76rem',
                fontWeight: 800,
              }}
            >
              {initials}
            </Avatar>
          </Stack>
        </Stack>
      </Stack>
    </Box>
  );
};
