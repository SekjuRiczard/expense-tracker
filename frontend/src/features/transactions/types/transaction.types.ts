export type TransactionType =
  | 'income'
  | 'expense';

export interface Transaction {
  readonly id: number;
  readonly walletId: number;
  readonly walletName: string;
  readonly categoryId: number;
  readonly categoryName: string;
  readonly type: TransactionType;
  readonly amount: number;
  readonly currency: string;
  readonly title: string;
  readonly description: string | null;
  readonly transactionDate: string;
  readonly createdAt: string;
  readonly updatedAt: string;
}

export interface TransactionPagination {
  readonly page: number;
  readonly limit: number;
  readonly totalItems: number;
  readonly totalPages: number;
}

export interface TransactionList {
  readonly items: readonly Transaction[];
  readonly pagination: TransactionPagination;
}

export interface TransactionFilters {
  readonly page?: number;
  readonly limit?: number;
  readonly type?: TransactionType;
  readonly walletId?: number;
  readonly categoryId?: number;
  readonly from?: string;
  readonly to?: string;
}

export interface CreateTransactionPayload {
  readonly walletId: number;
  readonly categoryId: number;
  readonly type: TransactionType;
  readonly amount: number;
  readonly title: string;
  readonly transactionDate: string;
  readonly description?: string | null;
}

export interface UpdateTransactionPayload {
  readonly walletId?: number;
  readonly categoryId?: number;
  readonly type?: TransactionType;
  readonly amount?: number;
  readonly title?: string;
  readonly transactionDate?: string;
  readonly description?: string | null;
}
