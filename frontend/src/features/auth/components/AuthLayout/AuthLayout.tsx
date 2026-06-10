import {
  Box,
  Typography,
} from '@mui/material';
import type {
  ReactNode,
} from 'react';
import { flowlyPalette, } from '../../../../app/theme';
import { AuthBrand, } from '../AuthBrand';

export interface AuthLayoutProps {
  readonly children: ReactNode;
  readonly rightPanel?: ReactNode;
}

export const AuthLayout = ({
  children,
  rightPanel,
}: AuthLayoutProps) => {
  return (
    <Box
      sx={{
        display: 'grid',
        minHeight: '100dvh',
        gridTemplateColumns: {
          xs: 'minmax(0, 1fr)',
          md: 'minmax(0, 1fr) minmax(0, 1fr)',
        },
        backgroundColor: flowlyPalette.auth.background,
      }}
    >
      <Box
        component="main"
        sx={{
          display: 'flex',
          minWidth: 0,
          minHeight: '100dvh',
          flexDirection: 'column',
          px: {
            xs: 2.5,
            sm: 4,
            md: 5,
            lg: 8,
          },
          py: {
            xs: 2.5,
            sm: 3.5,
          },
        }}
      >
        <AuthBrand />

        <Box
          sx={{
            display: 'flex',
            width: '100%',
            flex: 1,
            alignItems: 'center',
            justifyContent: 'center',
            py: {
              xs: 5,
              sm: 6,
            },
          }}
        >
          {children}
        </Box>

        <Typography
          sx={{
            color: flowlyPalette.auth.textMuted,
            fontSize: '0.69rem',
            textAlign: 'center',
          }}
        >
          © 2026 Flowly
        </Typography>
      </Box>

      <Box
        component="aside"
        sx={{
          position: 'relative',
          display: {
            xs: 'none',
            md: 'flex',
          },
          minWidth: 0,
          minHeight: '100dvh',
          overflow: 'hidden',
          background: `linear-gradient(
            145deg,
            ${flowlyPalette.auth.panelBackground} 0%,
            ${flowlyPalette.auth.panelBackgroundLight} 55%,
            ${flowlyPalette.auth.panelBackground} 100%
          )`,
        }}
      >
        <Box
          sx={{
            position: 'absolute',
            top: '-12%',
            left: '-14%',
            width: 360,
            height: 360,
            borderRadius: '50%',
            backgroundColor: flowlyPalette.auth.blobBlue,
            filter: 'blur(100px)',
            opacity: 0.48,
            pointerEvents: 'none',
          }}
        />

        <Box
          sx={{
            position: 'absolute',
            top: '33%',
            right: '-18%',
            width: 390,
            height: 390,
            borderRadius: '50%',
            backgroundColor: flowlyPalette.auth.blobGreen,
            filter: 'blur(110px)',
            opacity: 0.42,
            pointerEvents: 'none',
          }}
        />

        <Box
          sx={{
            position: 'absolute',
            bottom: '-18%',
            left: '22%',
            width: 390,
            height: 390,
            borderRadius: '50%',
            backgroundColor: flowlyPalette.auth.blobPurple,
            filter: 'blur(110px)',
            opacity: 0.46,
            pointerEvents: 'none',
          }}
        />

        <Box
          sx={{
            position: 'relative',
            zIndex: 1,
            display: 'flex',
            width: '100%',
            minHeight: '100%',
            alignItems: 'center',
            justifyContent: 'center',
            px: {
              md: 5,
              lg: 8,
            },
            py: 5,
          }}
        >
          {rightPanel}
        </Box>
      </Box>
    </Box>
  );
};