import {
  AccountBalanceWalletRounded,
  CreditCardRounded,
  PaymentsRounded,
  SavingsRounded,
} from '@mui/icons-material';
import type { SvgIconComponent } from '@mui/icons-material';
import type { WalletType, CurrencyCode } from '../types';

export const WALLET_TYPE_LABELS: Record<WalletType, string> = {
  cash: 'Cash',
  bank_account: 'Bank account',
  credit_card: 'Credit card',
  savings_account: 'Savings account',
};

export const WALLET_TYPE_ICONS: Record<WalletType, SvgIconComponent> = {
  cash: PaymentsRounded,
  bank_account: AccountBalanceWalletRounded,
  credit_card: CreditCardRounded,
  savings_account: SavingsRounded,
};

export const CURRENCY_OPTIONS: readonly { value: CurrencyCode; label: string }[] = [
  { value: 'PLN', label: 'PLN — Polish Zloty' },
  { value: 'EUR', label: 'EUR — Euro' },
  { value: 'USD', label: 'USD — US Dollar' },
  { value: 'GBP', label: 'GBP — British Pound' },
];

export const formatBalance = (
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
