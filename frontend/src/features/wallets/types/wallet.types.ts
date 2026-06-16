export type WalletType =
  | 'cash'
  | 'bank_account'
  | 'credit_card'
  | 'savings_account';

export type CurrencyCode =
  | 'PLN'
  | 'EUR'
  | 'USD'
  | 'GBP';

export interface Wallet {
  readonly id: number;
  readonly name: string;
  readonly type: WalletType;
  readonly currency: CurrencyCode;
  readonly balanceAmount: number;
  readonly createdAt: string;
  readonly updatedAt: string;
}

export interface CreateWalletPayload {
  readonly name: string;
  readonly type: WalletType;
  readonly currency: CurrencyCode;
  readonly balanceAmount: number;
}

export interface UpdateWalletPayload {
  readonly name?: string;
  readonly type?: WalletType;
}
