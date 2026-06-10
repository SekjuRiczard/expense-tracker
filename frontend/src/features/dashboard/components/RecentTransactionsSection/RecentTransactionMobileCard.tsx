import {
  Box,
  Stack,
  Typography,
} from '@mui/material';
import { flowlyPalette, } from '../../../../app/theme';
import {
  formatCurrency,
  formatDate,
} from '../../../../shared/lib';
import type {
  Transaction,
} from '../../../transactions';
import { TransactionTypeIcon, } from './TransactionTypeIcon';

export interface RecentTransactionMobileCardProps {
  readonly transaction: Transaction;
}

export const RecentTransactionMobileCard = ({
  transaction,
}: RecentTransactionMobileCardProps) => {
  const isIncome = transaction.type === 'income';

  return (
    <Stack
      sx={{
        alignItems: 'center',
        flexDirection: 'row',
        gap: 1.1,
        justifyContent: 'space-between',
        px: 2,
        py: 1.5,
        borderBottom: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
      }}
    >
      <Stack
        sx={{
          alignItems: 'center',
          flexDirection: 'row',
          gap: 1.1,
          minWidth: 0,
        }}
      >
        <TransactionTypeIcon type={transaction.type} />

        <Box
          sx={{
            minWidth: 0,
          }}
        >
          <Typography
            noWrap
            sx={{
              color: flowlyPalette.dashboard.textPrimary,
              fontSize: '0.84rem',
              fontWeight: 850,
            }}
          >
            {transaction.title}
          </Typography>

          <Typography
            noWrap
            sx={{
              mt: 0.3,
              color: flowlyPalette.dashboard.textSecondary,
              fontSize: '0.72rem',
            }}
          >
            {transaction.categoryName} · {transaction.walletName}
          </Typography>

          <Typography
            sx={{
              mt: 0.2,
              color: flowlyPalette.dashboard.textMuted,
              fontSize: '0.7rem',
            }}
          >
            {formatDate(transaction.transactionDate)}
          </Typography>
        </Box>
      </Stack>

      <Typography
        sx={{
          flexShrink: 0,
          color: isIncome
            ? flowlyPalette.dashboard.emerald
            : flowlyPalette.dashboard.textPrimary,
          fontSize: '0.82rem',
          fontWeight: 850,
          fontVariantNumeric: 'tabular-nums',
        }}
      >
        {isIncome ? '+' : '−'}
        {formatCurrency(
          transaction.amount,
          transaction.currency,
        )}
      </Typography>
    </Stack>
  );
};
