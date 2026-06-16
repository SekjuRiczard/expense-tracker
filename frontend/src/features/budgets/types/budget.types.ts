export type BudgetPeriodType = 'monthly' | 'yearly' | 'custom';

export type CurrencyCode = 'PLN' | 'EUR' | 'USD' | 'GBP';

export type BudgetStatus = 'ok' | 'warning' | 'exceeded';

export interface Budget {
  readonly id: number;
  readonly name: string;
  readonly amount: number;
  readonly currency: CurrencyCode;
  readonly periodType: BudgetPeriodType;
  readonly startDate: string;
  readonly endDate: string;
  readonly createdAt: string;
  readonly updatedAt: string;
}

export interface BudgetWithUsage extends Budget {
  readonly spentAmount: number;
  readonly remainingAmount: number;
  readonly percentage: number;
  readonly status: BudgetStatus;
}

export interface CreateBudgetPayload {
  readonly name: string;
  readonly amount: number;
  readonly currency: CurrencyCode;
  readonly periodType: BudgetPeriodType;
  readonly startDate: string;
  readonly endDate: string;
}

export interface UpdateBudgetPayload {
  readonly name?: string;
  readonly amount?: number;
  readonly currency?: CurrencyCode;
  readonly periodType?: BudgetPeriodType;
  readonly startDate?: string;
  readonly endDate?: string;
}
