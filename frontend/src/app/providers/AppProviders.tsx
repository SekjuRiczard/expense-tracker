import {
  CssBaseline,
} from '@mui/material';
import {
  ThemeProvider,
} from '@mui/material/styles';
import type {
  ReactNode,
} from 'react';
import {
  BrowserRouter,
} from 'react-router-dom';
import {
  AuthProvider,
} from '../../features/auth';
import { theme, } from '../theme';
import { QueryProvider, } from './QueryProvider';

export interface AppProvidersProps {
  readonly children: ReactNode;
}

export const AppProviders = ({
  children,
}: AppProvidersProps) => {
  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />

      <BrowserRouter>
        <QueryProvider>
          <AuthProvider>
            {children}
          </AuthProvider>
        </QueryProvider>
      </BrowserRouter>
    </ThemeProvider>
  );
};
