import { formatCurrency } from '../../../shared/lib';
import type { TransactionType } from '../types';

export const TRANSACTION_TYPE_LABELS: Record<TransactionType, string> = {
  income: 'Income',
  expense: 'Expense',
};

export const formatSignedAmount = (
  amount: number,
  currency: string,
  type: TransactionType,
): string => {
  const sign = type === 'income' ? '+' : '\u2212';
  return `${sign}${formatCurrency(amount, currency)}`;
};

export const toDateInputValue = (iso: string): string => {
  try {
    const d = new Date(iso);
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  } catch {
    return '';
  }
};

export const dateInputToIso = (value: string): string => {
  return new Date(`${value}T00:00:00`).toISOString();
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

export type SortableColumn =
  | 'transactionDate'
  | 'title'
  | 'categoryName'
  | 'walletName'
  | 'type'
  | 'amount';

export type SortDirection = 'asc' | 'desc';
