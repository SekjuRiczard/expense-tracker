import {
  Box,
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  Stack,
  Typography,
} from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { BudgetWithUsage } from '../types';
import {
  BUDGET_PERIOD_LABELS,
  BUDGET_STATUS_META,
  formatDateTime,
  formatMoney,
  formatPercentage,
  formatShortDate,
} from './budgetHelpers';

export interface BudgetDetailsDialogProps {
  readonly open: boolean;
  readonly budget: BudgetWithUsage | null;
  readonly onClose: () => void;
}

const DetailRow = ({
  label,
  value,
  valueColor,
}: {
  readonly label: string;
  readonly value: string;
  readonly valueColor?: string;
}) => (
  <Stack
    sx={{
      alignItems: 'center',
      flexDirection: 'row',
      justifyContent: 'space-between',
      gap: 2,
      py: 0.75,
      borderBottom: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
    }}
  >
    <Typography
      sx={{
        color: flowlyPalette.dashboard.textSecondary,
        fontSize: '0.8125rem',
      }}
    >
      {label}
    </Typography>
    <Typography
      sx={{
        color: valueColor ?? flowlyPalette.dashboard.textPrimary,
        fontSize: '0.8125rem',
        fontWeight: 600,
        textAlign: 'right',
      }}
    >
      {value}
    </Typography>
  </Stack>
);

export const BudgetDetailsDialog = ({
  open,
  budget,
  onClose,
}: BudgetDetailsDialogProps) => {
  return (
    <Dialog
      maxWidth="xs"
      onClose={onClose}
      open={open}
      slotProps={{ paper: { sx: { borderRadius: '20px', width: '100%' } } }}
    >
      <DialogTitle
        sx={{
          pb: 0.5,
          pt: 3,
          px: 3,
          color: flowlyPalette.dashboard.textPrimary,
          fontSize: '1.1rem',
          fontWeight: 800,
          letterSpacing: '-0.02em',
        }}
      >
        {budget?.name ?? 'Budget details'}
      </DialogTitle>

      <DialogContent sx={{ px: 3, pt: '12px !important', pb: 1 }}>
        {budget && (
          <Box>
            <DetailRow
              label="Period type"
              value={BUDGET_PERIOD_LABELS[budget.periodType]}
            />
            <DetailRow label="Currency" value={budget.currency} />
            <DetailRow
              label="Limit"
              value={formatMoney(budget.amount, budget.currency)}
            />
            <DetailRow
              label="Spent"
              value={formatMoney(budget.spentAmount, budget.currency)}
            />
            <DetailRow
              label={budget.status === 'exceeded' ? 'Exceeded by' : 'Remaining'}
              value={formatMoney(
                Math.abs(budget.remainingAmount),
                budget.currency,
              )}
            />
            <DetailRow
              label="Usage"
              value={formatPercentage(budget.percentage)}
              valueColor={BUDGET_STATUS_META[budget.status].textColor}
            />
            <DetailRow
              label="Start date"
              value={formatShortDate(budget.startDate)}
            />
            <DetailRow
              label="End date"
              value={formatShortDate(budget.endDate)}
            />
            <DetailRow
              label="Created"
              value={formatDateTime(budget.createdAt)}
            />
            <DetailRow
              label="Updated"
              value={formatDateTime(budget.updatedAt)}
            />
          </Box>
        )}
      </DialogContent>

      <DialogActions sx={{ px: 3, pb: 3, pt: 1 }}>
        <Button
          onClick={onClose}
          variant="outlined"
          sx={{
            borderRadius: '12px',
            borderColor: flowlyPalette.dashboard.border,
            color: flowlyPalette.dashboard.textSecondary,
            fontWeight: 600,
            textTransform: 'none',
          }}
        >
          Close
        </Button>
      </DialogActions>
    </Dialog>
  );
};
