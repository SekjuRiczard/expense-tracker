export const supportedCurrencies = [
  {
    code: 'EUR',
    symbol: '€',
  },
  {
    code: 'USD',
    symbol: '$',
  },
  {
    code: 'CHF',
    symbol: '₣',
  },
] as const;

export type SupportedCurrencyCode =
  typeof supportedCurrencies[number]['code'];

export type CurrencyTrend =
  | 'up'
  | 'down'
  | 'neutral';

export interface NbpRate {
  readonly no: string;
  readonly effectiveDate: string;
  readonly mid: number;
}

export interface NbpCurrencyRateSeries {
  readonly table: string;
  readonly currency: string;
  readonly code: string;
  readonly rates: readonly NbpRate[];
}

export interface CurrencyRate {
  readonly code: SupportedCurrencyCode;
  readonly symbol: string;
  readonly pair: string;

  readonly rate: number;
  readonly formattedRate: string;

  readonly changePercentage: number;
  readonly formattedChange: string;
  readonly trend: CurrencyTrend;

  readonly effectiveDate: string;
  readonly history: readonly number[];
}