import {
  MoreHorizRounded,
} from '@mui/icons-material';
import {
  Box,
  IconButton,
  Stack,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
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

export interface RecentTransactionsTableProps {
  readonly transactions: readonly Transaction[];
}

export const RecentTransactionsTable = ({
  transactions,
}: RecentTransactionsTableProps) => {
  return (
    <Table>
      <TableHead>
        <TableRow
          sx={{
            backgroundColor: 'rgba(248, 250, 252, 0.72)',
          }}
        >
          {[
            'Title',
            'Category',
            'Wallet',
            'Date',
            'Amount',
            '',
          ].map((label) => (
            <TableCell
              align={label === 'Amount' ? 'right' : 'left'}
              key={label || 'actions'}
              sx={{
                py: 1.2,
                borderColor: flowlyPalette.dashboard.borderSoft,
                color: flowlyPalette.dashboard.textMuted,
                fontSize: '0.68rem',
                fontWeight: 850,
                letterSpacing: '0.04em',
                textTransform: 'uppercase',
              }}
            >
              {label}
            </TableCell>
          ))}
        </TableRow>
      </TableHead>

      <TableBody>
        {transactions.map((transaction) => {
          const isIncome = transaction.type === 'income';

          return (
            <TableRow
              hover
              key={transaction.id}
              sx={{
                '& td': {
                  borderColor: flowlyPalette.dashboard.borderSoft,
                },
              }}
            >
              <TableCell>
                <Stack
                  sx={{
                    alignItems: 'center',
                    flexDirection: 'row',
                    gap: 1.2,
                  }}
                >
                  <TransactionTypeIcon type={transaction.type} />

                  <Typography
                    sx={{
                      color: flowlyPalette.dashboard.textPrimary,
                      fontSize: '0.85rem',
                      fontWeight: 800,
                    }}
                  >
                    {transaction.title}
                  </Typography>
                </Stack>
              </TableCell>

              <TableCell>
                <Typography
                  sx={{
                    color: flowlyPalette.dashboard.textSecondary,
                    fontSize: '0.82rem',
                  }}
                >
                  {transaction.categoryName}
                </Typography>
              </TableCell>

              <TableCell>
                <Typography
                  sx={{
                    color: flowlyPalette.dashboard.textSecondary,
                    fontSize: '0.82rem',
                  }}
                >
                  {transaction.walletName}
                </Typography>
              </TableCell>

              <TableCell>
                <Typography
                  sx={{
                    color: flowlyPalette.dashboard.textSecondary,
                    fontSize: '0.82rem',
                  }}
                >
                  {formatDate(transaction.transactionDate)}
                </Typography>
              </TableCell>

              <TableCell align="right">
                <Typography
                  sx={{
                    color: isIncome
                      ? flowlyPalette.dashboard.emerald
                      : flowlyPalette.dashboard.textPrimary,
                    fontSize: '0.84rem',
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
              </TableCell>

              <TableCell align="right">
                <IconButton
                  aria-label={`Open actions for ${transaction.title}`}
                  size="small"
                  sx={{
                    color: flowlyPalette.dashboard.textMuted,
                  }}
                >
                  <MoreHorizRounded />
                </IconButton>
              </TableCell>
            </TableRow>
          );
        })}
      </TableBody>
    </Table>
  );
};
