import {
  Box,
} from '@mui/material';
import {
  useState,
} from 'react';
import {
  Outlet,
} from 'react-router-dom';
import { flowlyPalette, } from '../../theme';
import { AppHeader, } from './AppHeader';
import {
  APP_SIDEBAR_WIDTH,
  AppSidebar,
} from './AppSidebar';
import { MobileNavigationDrawer, } from './MobileNavigationDrawer';

export const AppLayout = () => {
  const [isDrawerOpen, setIsDrawerOpen,] = useState(false);

  return (
    <Box
      sx={{
        display: 'flex',
        minHeight: '100dvh',
        backgroundColor: flowlyPalette.dashboard.background,
      }}
    >
      <Box
        sx={{
          display: {
            xs: 'none',
            md: 'block',
          },
          width: APP_SIDEBAR_WIDTH,
          flexShrink: 0,
        }}
      >
        <Box
          sx={{
            position: 'fixed',
            inset: '0 auto 0 0',
          }}
        >
          <AppSidebar />
        </Box>
      </Box>

      <MobileNavigationDrawer
        onClose={() => {
          setIsDrawerOpen(false);
        }}
        open={isDrawerOpen}
      />

      <Box
        sx={{
          minWidth: 0,
          flex: 1,
        }}
      >
        <AppHeader
          onMenuClick={() => {
            setIsDrawerOpen(true);
          }}
        />

        <Box
          component="main"
          sx={{
            px: {
              xs: 2,
              sm: 3,
              lg: 4,
            },
            py: {
              xs: 2,
              sm: 3,
            },
          }}
        >
          <Outlet />
        </Box>
      </Box>
    </Box>
  );
};
