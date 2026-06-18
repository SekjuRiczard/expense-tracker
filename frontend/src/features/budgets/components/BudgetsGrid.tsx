import { Alert, Box } from '@mui/material';
import type { BudgetWithUsage } from '../types';
import { BudgetCard } from './BudgetCard';
import { BudgetsEmptyState } from './BudgetsEmptyState';
import { BudgetsSkeleton } from './BudgetsSkeleton';

export interface BudgetsGridProps {
  readonly budgets: readonly BudgetWithUsage[];
  readonly loading: boolean;
  readonly error: boolean;
  readonly onCreateClick: () => void;
  readonly onViewDetails: (budget: BudgetWithUsage) => void;
  readonly onEdit: (budget: BudgetWithUsage) => void;
  readonly onDelete: (budget: BudgetWithUsage) => void;
}

export const BudgetsGrid = ({
  budgets,
  loading,
  error,
  onCreateClick,
  onViewDetails,
  onEdit,
  onDelete,
}: BudgetsGridProps) => {
  if (loading) {
    return <BudgetsSkeleton />;
  }

  if (error) {
    return (
      <Alert severity="error" sx={{ borderRadius: '16px' }}>
        Failed to load budgets.
      </Alert>
    );
  }

  if (budgets.length === 0) {
    return <BudgetsEmptyState onCreateClick={onCreateClick} />;
  }

  return (
    <Box
      sx={{
        display: 'grid',
        gap: '20px',
        gridTemplateColumns: { xs: '1fr', md: 'repeat(2, 1fr)' },
      }}
    >
      {budgets.map((budget) => (
        <BudgetCard
          budget={budget}
          key={budget.id}
          onDelete={onDelete}
          onEdit={onEdit}
          onViewDetails={onViewDetails}
        />
      ))}
    </Box>
  );
};
