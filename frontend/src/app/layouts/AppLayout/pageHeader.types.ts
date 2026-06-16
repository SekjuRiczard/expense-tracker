import type { ReactNode } from 'react';

export interface PageHeaderOverride {
  readonly subtitle?: string;
  readonly action?: ReactNode;
}

export interface AppLayoutOutletContext {
  readonly setHeaderOverride: (data: PageHeaderOverride | null) => void;
}
