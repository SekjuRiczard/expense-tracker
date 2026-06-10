import type {
  ReactNode,
} from 'react';

import {
  AuthProvider,
} from '../../features/auth';
import {
  QueryProvider,
} from './QueryProvider';

export interface AppProvidersProps {
  readonly children: ReactNode;
}

export const AppProviders = ({
  children,
}: AppProvidersProps) => {
  return (
    <QueryProvider>
      <AuthProvider>
        {children}
      </AuthProvider>
    </QueryProvider>
  );
};