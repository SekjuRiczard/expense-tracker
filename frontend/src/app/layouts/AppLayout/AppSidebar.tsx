import {
  Box,
  List,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Stack,
  Typography,
} from '@mui/material';
import { NavLink, } from 'react-router-dom';
import logo from '../../../assets/logo.png';
import { flowlyPalette, } from '../../theme';
import { navigationItems, } from './navigation';

export const APP_SIDEBAR_WIDTH = 248;

export const AppSidebar = () => {
  return (
    <Box
      component="nav"
      sx={{
        width: APP_SIDEBAR_WIDTH,
        minHeight: '100dvh',
        px: 2,
        py: 2.5,
        borderRight: `1px solid ${flowlyPalette.dashboard.border}`,
        backgroundColor: flowlyPalette.dashboard.surface,
      }}
    >
      <Stack
        sx={{
          alignItems: 'center',
          flexDirection: 'row',
          gap: 1.1,
          px: 1,
          pb: 3,
        }}
      >
        <Box
          alt="Flowly logo"
          component="img"
          src={logo}
          sx={{
            width: 40,
            height: 40,
            objectFit: 'contain',
          }}
        />

        <Typography
          sx={{
            color: flowlyPalette.dashboard.textPrimary,
            fontSize: '1.2rem',
            fontWeight: 900,
            letterSpacing: '-0.04em',
          }}
        >
          Flowly
        </Typography>
      </Stack>

      <List
        disablePadding
        sx={{
          display: 'grid',
          gap: 0.5,
        }}
      >
        {navigationItems.map((item) => {
          const Icon = item.icon;

          return (
            <ListItemButton
              component={NavLink}
              key={item.path}
              to={item.path}
              sx={{
                minHeight: 44,
                borderRadius: 2,
                color: flowlyPalette.dashboard.textSecondary,
                '&.active': {
                  backgroundColor: flowlyPalette.dashboard.indigoSoft,
                  color: flowlyPalette.dashboard.indigoDark,
                },
                '&:hover': {
                  backgroundColor: flowlyPalette.dashboard.borderSoft,
                },
              }}
            >
              <ListItemIcon
                sx={{
                  minWidth: 36,
                  color: 'inherit',
                }}
              >
                <Icon fontSize="small" />
              </ListItemIcon>

              <ListItemText
                primary={item.label}
                slotProps={{
                  primary: {
                    sx: {
                      fontSize: '0.9rem',
                      fontWeight: 700,
                    },
                  },
                }}
              />
            </ListItemButton>
          );
        })}
      </List>
    </Box>
  );
};
