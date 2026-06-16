import {
  Box,
  Stack,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  TableSortLabel,
  Typography,
} from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import { formatDate } from '../../../shared/lib';
import type { Transaction } from '../types';
import { TransactionActionsMenu } from './TransactionActionsMenu';
import { TransactionTypeIcon } from './TransactionTypeIcon';
import { TransactionTypePill } from './TransactionTypePill';
import {
  formatSignedAmount,
  type SortableColumn,
  type SortDirection,
} from './transactionHelpers';

export interface TransactionsTableProps {
  readonly transactions: readonly Transaction[];
  readonly sortBy: SortableColumn;
  readonly sortDirection: SortDirection;
  readonly onSortChange: (column: SortableColumn) => void;
  readonly onViewDetails: (transaction: Transaction) => void;
  readonly onEdit: (transaction: Transaction) => void;
  readonly onDelete: (transaction: Transaction) => void;
}

interface ColumnDef {
  readonly id: SortableColumn;
  readonly label: string;
  readonly align: 'left' | 'right';
}

const COLUMNS: readonly ColumnDef[] = [
  { id: 'transactionDate', label: 'Date', align: 'left' },
  { id: 'title', label: 'Title', align: 'left' },
  { id: 'categoryName', label: 'Category', align: 'left' },
  { id: 'walletName', label: 'Wallet', align: 'left' },
  { id: 'type', label: 'Type', align: 'left' },
  { id: 'amount', label: 'Amount', align: 'right' },
];

const headCellSx = {
  py: 1.25,
  borderColor: flowlyPalette.dashboard.borderSoft,
  backgroundColor: 'rgba(248, 250, 252, 0.5)',
  color: flowlyPalette.dashboard.textSecondary,
  fontSize: '0.68rem',
  fontWeight: 600,
  letterSpacing: '0.04em',
  textTransform: 'uppercase',
} as const;

export const TransactionsTable = ({
  transactions,
  sortBy,
  sortDirection,
  onSortChange,
  onViewDetails,
  onEdit,
  onDelete,
}: TransactionsTableProps) => {
  return (
    <Box sx={{ overflowX: 'auto' }}>
      <Table sx={{ minWidth: 760 }}>
        <TableHead>
          <TableRow>
            {COLUMNS.map((column) => (
              <TableCell
                align={column.align}
                key={column.id}
                sortDirection={sortBy === column.id ? sortDirection : false}
                sx={headCellSx}
              >
                <TableSortLabel
                  active={sortBy === column.id}
                  direction={sortBy === column.id ? sortDirection : 'asc'}
                  onClick={() => { onSortChange(column.id); }}
                  sx={{ color: 'inherit !important' }}
                >
                  {column.label}
                </TableSortLabel>
              </TableCell>
            ))}

            <TableCell align="right" sx={headCellSx} />
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
                  transition: 'background-color 150ms ease',
                  '& td': { borderColor: flowlyPalette.dashboard.borderSoft },
                }}
              >
                <TableCell>
                  <Typography
                    sx={{
                      color: flowlyPalette.dashboard.textSecondary,
                      fontSize: '0.8rem',
                    }}
                  >
                    {formatDate(transaction.transactionDate)}
                  </Typography>
                </TableCell>

                <TableCell>
                  <Stack
                    sx={{
                      alignItems: 'center',
                      flexDirection: 'row',
                      gap: 1.25,
                    }}
                  >
                    <TransactionTypeIcon type={transaction.type} />

                    <Box sx={{ minWidth: 0 }}>
                      <Typography
                        sx={{
                          color: flowlyPalette.dashboard.textPrimary,
                          fontSize: '0.82rem',
                          fontWeight: 600,
                        }}
                      >
                        {transaction.title}
                      </Typography>

                      {transaction.description && (
                        <Typography
                          sx={{
                            color: flowlyPalette.dashboard.textMuted,
                            fontSize: '0.7rem',
                          }}
                        >
                          {transaction.description}
                        </Typography>
                      )}
                    </Box>
                  </Stack>
                </TableCell>

                <TableCell>
                  <Typography
                    sx={{
                      color: flowlyPalette.dashboard.textSecondary,
                      fontSize: '0.8rem',
                    }}
                  >
                    {transaction.categoryName}
                  </Typography>
                </TableCell>

                <TableCell>
                  <Typography
                    sx={{
                      color: flowlyPalette.dashboard.textSecondary,
                      fontSize: '0.8rem',
                    }}
                  >
                    {transaction.walletName}
                  </Typography>
                </TableCell>

                <TableCell>
                  <TransactionTypePill type={transaction.type} />
                </TableCell>

                <TableCell align="right">
                  <Typography
                    sx={{
                      color: isIncome
                        ? flowlyPalette.dashboard.emerald
                        : flowlyPalette.dashboard.textPrimary,
                      fontSize: '0.82rem',
                      fontWeight: 600,
                      fontVariantNumeric: 'tabular-nums',
                    }}
                  >
                    {formatSignedAmount(
                      transaction.amount,
                      transaction.currency,
                      transaction.type,
                    )}
                  </Typography>
                </TableCell>

                <TableCell align="right">
                  <TransactionActionsMenu
                    onDelete={() => { onDelete(transaction); }}
                    onEdit={() => { onEdit(transaction); }}
                    onViewDetails={() => { onViewDetails(transaction); }}
                  />
                </TableCell>
              </TableRow>
            );
          })}
        </TableBody>
      </Table>
    </Box>
  );
};
