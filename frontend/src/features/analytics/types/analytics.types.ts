export type AnalyticsCurrency = 'PLN' | 'EUR' | 'USD';

export interface AnalyticsDashboardParams {
  readonly from: string;
  readonly to: string;
  readonly currency: AnalyticsCurrency;
}

export interface AnalyticsSummary {
  readonly currency: string;
  readonly from: string;
  readonly to: string;
  readonly income: number;
  readonly expense: number;
  readonly balance: number;
  readonly transactionCount: number;
}

export interface CategoryBreakdownItem {
  readonly categoryId: number;
  readonly categoryName: string;
  readonly amount: number;
  readonly percentage: number;
}

export interface CashFlowPoint {
  readonly period: string;
  readonly income: number;
  readonly expense: number;
  readonly balance: number;
}

export interface AnalyticsDashboard {
  readonly summary: AnalyticsSummary;
  readonly categoryBreakdown: readonly CategoryBreakdownItem[];
  readonly cashFlow: readonly CashFlowPoint[];
}
