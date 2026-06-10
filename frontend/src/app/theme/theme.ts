import {
  createTheme,
} from '@mui/material/styles';
import { flowlyPalette, } from './palette';

export const theme = createTheme({
  palette: {
    mode: 'light',
    primary: {
      main: flowlyPalette.auth.focus,
    },
    background: {
      default: flowlyPalette.auth.background,
      paper: flowlyPalette.auth.background,
    },
    text: {
      primary: flowlyPalette.auth.textPrimary,
      secondary: flowlyPalette.auth.textSecondary,
    },
    error: {
      main: flowlyPalette.auth.error,
    },
    success: {
      main: flowlyPalette.auth.success,
    },
  },
  shape: {
    borderRadius: 12,
  },
  typography: {
    fontFamily: [
      'Inter',
      '-apple-system',
      'BlinkMacSystemFont',
      '"Segoe UI"',
      'Roboto',
      'Arial',
      'sans-serif',
    ].join(','),
    button: {
      fontWeight: 700,
      textTransform: 'none',
    },
  },
  components: {
    MuiCssBaseline: {
      styleOverrides: {
        '*': {
          boxSizing: 'border-box',
        },
        html: {
          minWidth: '320px',
          minHeight: '100%',
        },
        body: {
          minWidth: '320px',
          minHeight: '100%',
          margin: 0,
          backgroundColor: flowlyPalette.auth.background,
        },
        '#root': {
          minHeight: '100dvh',
        },
      },
    },
    MuiButton: {
      defaultProps: {
        disableElevation: true,
      },
    },
  },
});