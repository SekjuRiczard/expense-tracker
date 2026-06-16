export type WalletType =
  | 'cash'
  | 'bank_account'
  | 'credit_card'
  | 'savings_account';

export interface Wallet {
  readonly id: number;
  readonly name: string;
  readonly type: WalletType;
  readonly currency: string;
  readonly balanceAmount: number;
  readonly createdAt: string;
  readonly updatedAt: string;
}
