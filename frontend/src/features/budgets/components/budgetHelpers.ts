import {
  CheckCircleRounded,
  ErrorRounded,
  WarningAmberRounded,
} from '@mui/icons-material';
import type { SvgIconComponent } from '@mui/icons-material';
import { flowlyPalette } from '../../../app/theme';
import type {
  BudgetPeriodType,
  BudgetStatus,
  CurrencyCode,
} from '../types';

export const BUDGET_PERIOD_LABELS: Record<BudgetPeriodType, string> = {
  monthly: 'Monthly',
  yearly: 'Yearly',
  custom: 'Custom',
};

export const CURRENCY_OPTIONS: readonly {
  value: CurrencyCode;
  label: string;
}[] = [
  { value: 'PLN', label: 'PLN — Polish Zloty' },
  { value: 'EUR', label: 'EUR — Euro' },
  { value: 'USD', label: 'USD — US Dollar' },
  { value: 'GBP', label: 'GBP — British Pound' },
];

export interface BudgetStatusMeta {
  readonly label: string;
  readonly icon: SvgIconComponent;
  readonly textColor: string;
  readonly barColor: string;
}

export const BUDGET_STATUS_META: Record<BudgetStatus, BudgetStatusMeta> = {
  ok: {
    label: 'Budget under control',
    icon: CheckCircleRounded,
    textColor: '#047857',
    barColor: flowlyPalette.dashboard.emerald,
  },
  warning: {
    label: 'Approaching the limit',
    icon: WarningAmberRounded,
    textColor: '#B45309',
    barColor: flowlyPalette.dashboard.amber,
  },
  exceeded: {
    label: 'Budget exceeded',
    icon: ErrorRounded,
    textColor: '#BE123C',
    barColor: flowlyPalette.dashboard.rose,
  },
};

export const formatMoney = (
  amountMinorUnits: number,
  currency: CurrencyCode,
): string => {
  try {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(amountMinorUnits / 100);
  } catch {
    return `${(amountMinorUnits / 100).toFixed(2)} ${currency}`;
  }
};

export const formatPercentage = (percentage: number): string => {
  return `${percentage.toFixed(2)}%`;
};

export const formatShortDate = (iso: string): string => {
  try {
    const d = new Date(iso);
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()}`;
  } catch {
    return iso;
  }
};

export const formatDateTime = (iso: string): string => {
  try {
    const d = new Date(iso);
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()}, ${pad(d.getHours())}:${pad(d.getMinutes())}`;
  } catch {
    return iso;
  }
};

export const formatBudgetSubtitle = (
  periodType: BudgetPeriodType,
  startDate: string,
  endDate: string,
): string => {
  return `${BUDGET_PERIOD_LABELS[periodType]} · ${formatShortDate(startDate)} – ${formatShortDate(endDate)}`;
};
