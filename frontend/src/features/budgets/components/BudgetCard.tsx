import { Box, Stack, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { BudgetWithUsage } from '../types';
import { BudgetActionsMenu } from './BudgetActionsMenu';
import {
  BUDGET_STATUS_META,
  formatBudgetSubtitle,
  formatMoney,
  formatPercentage,
} from './budgetHelpers';

export interface BudgetCardProps {
  readonly budget: BudgetWithUsage;
  readonly onViewDetails: (budget: BudgetWithUsage) => void;
  readonly onEdit: (budget: BudgetWithUsage) => void;
  readonly onDelete: (budget: BudgetWithUsage) => void;
}

export const BudgetCard = ({
  budget,
  onViewDetails,
  onEdit,
  onDelete,
}: BudgetCardProps) => {
  const statusMeta = BUDGET_STATUS_META[budget.status];
  const StatusIcon = statusMeta.icon;
  const fillWidth = Math.min(budget.percentage, 100);
  const isExceeded = budget.status === 'exceeded';

  const rightStatusText = isExceeded
    ? `Exceeded by ${formatMoney(Math.abs(budget.remainingAmount), budget.currency)} · ${formatPercentage(budget.percentage)}`
    : `Remaining ${formatMoney(budget.remainingAmount, budget.currency)} · ${formatPercentage(budget.percentage)}`;

  return (
    <Box
      sx={{
        backgroundColor: '#FFFFFF',
        border: '1px solid #F1F5F9',
        borderRadius: '20px',
        p: 3,
        display: 'flex',
        flexDirection: 'column',
        gap: 2.75,
      }}
    >
      <Stack
        sx={{
          alignItems: 'flex-start',
          flexDirection: 'row',
          justifyContent: 'space-between',
          gap: 1.5,
        }}
      >
        <Box sx={{ minWidth: 0 }}>
          <Typography
            sx={{
              color: flowlyPalette.dashboard.textPrimary,
              fontSize: '1rem',
              fontWeight: 600,
              lineHeight: 1.3,
            }}
          >
            {budget.name}
          </Typography>

          <Typography
            sx={{
              color: flowlyPalette.dashboard.textSecondary,
              fontSize: '0.75rem',
              mt: 0.5,
            }}
          >
            {formatBudgetSubtitle(
              budget.periodType,
              budget.startDate,
              budget.endDate,
            )}
          </Typography>
        </Box>

        <BudgetActionsMenu
          onDelete={() => { onDelete(budget); }}
          onEdit={() => { onEdit(budget); }}
          onViewDetails={() => { onViewDetails(budget); }}
        />
      </Stack>

      <Stack
        sx={{
          alignItems: 'baseline',
          flexDirection: 'row',
          gap: 1,
          flexWrap: 'wrap',
        }}
      >
        <Typography
          sx={{
            color: flowlyPalette.dashboard.textPrimary,
            fontSize: '1.5rem',
            fontWeight: 700,
            fontVariantNumeric: 'tabular-nums',
          }}
        >
          {formatMoney(budget.spentAmount, budget.currency)}
        </Typography>

        <Typography
          sx={{
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.8125rem',
          }}
        >
          of {formatMoney(budget.amount, budget.currency)}
        </Typography>
      </Stack>

      <Box
        sx={{
          width: '100%',
          height: 8,
          backgroundColor: '#F1F5F9',
          borderRadius: '999px',
          overflow: 'hidden',
        }}
      >
        <Box
          sx={{
            height: '100%',
            width: `${fillWidth}%`,
            borderRadius: '999px',
            backgroundColor: statusMeta.barColor,
            transition: 'width 0.3s ease',
          }}
        />
      </Box>

      <Stack
        sx={{
          alignItems: 'center',
          flexDirection: 'row',
          justifyContent: 'space-between',
          gap: 1.5,
          flexWrap: 'wrap',
        }}
      >
        <Stack
          sx={{
            alignItems: 'center',
            flexDirection: 'row',
            gap: 0.75,
            color: statusMeta.textColor,
          }}
        >
          <StatusIcon sx={{ fontSize: 18 }} />
          <Typography
            sx={{ fontSize: '0.8125rem', fontWeight: 600, color: 'inherit' }}
          >
            {statusMeta.label}
          </Typography>
        </Stack>

        <Typography
          sx={{
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.75rem',
            textAlign: 'right',
          }}
        >
          {rightStatusText}
        </Typography>
      </Stack>
    </Box>
  );
};
