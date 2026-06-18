export type CategoryType = 'income' | 'expense';

export interface Category {
  readonly id: number;
  readonly name: string;
  readonly type: CategoryType;
  readonly isDefault: boolean;
  readonly createdAt: string;
  readonly updatedAt: string;
}

export interface CreateCategoryPayload {
  readonly name: string;
  readonly type: CategoryType;
}

export interface UpdateCategoryPayload {
  readonly name?: string;
  readonly type?: CategoryType;
}
