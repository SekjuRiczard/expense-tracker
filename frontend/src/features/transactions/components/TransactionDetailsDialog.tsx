import {
  Box,
  Button,
  Dialog,
  DialogContent,
  DialogTitle,
  Typography,
} from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import { formatCurrency, formatDate } from '../../../shared/lib';
import type { Transaction } from '../types';
import { TRANSACTION_TYPE_LABELS, formatDateTime } from './transactionHelpers';
import { TransactionTypeIcon } from './TransactionTypeIcon';

export interface TransactionDetailsDialogProps {
  readonly open: boolean;
  readonly transaction: Transaction | null;
  readonly onClose: () => void;
}

const DetailRow = ({
  label,
  value,
}: {
  readonly label: string;
  readonly value: string;
}) => (
  <Box
    sx={{
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'flex-start',
      gap: 2,
      py: 1.15,
      borderBottom: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
      '&:last-of-type': { borderBottom: 'none', pb: 0 },
      '&:first-of-type': { pt: 0 },
    }}
  >
    <Typography
      sx={{
        color: flowlyPalette.dashboard.textSecondary,
        fontSize: '0.8rem',
        fontWeight: 500,
        flexShrink: 0,
      }}
    >
      {label}
    </Typography>

    <Typography
      sx={{
        color: flowlyPalette.dashboard.textPrimary,
        fontSize: '0.83rem',
        fontWeight: 600,
        textAlign: 'right',
      }}
    >
      {value}
    </Typography>
  </Box>
);

export const TransactionDetailsDialog = ({
  open,
  transaction,
  onClose,
}: TransactionDetailsDialogProps) => {
  if (!transaction) return null;

  const isIncome = transaction.type === 'income';

  return (
    <Dialog
      maxWidth="xs"
      onClose={onClose}
      open={open}
      slotProps={{ paper: { sx: { borderRadius: '20px', width: '100%' } } }}
    >
      <DialogTitle
        sx={{
          pb: 0,
          pt: 3,
          px: 3,
          display: 'flex',
          alignItems: 'center',
          gap: 1.5,
        }}
      >
        <TransactionTypeIcon type={transaction.type} />

        <Typography
          component="span"
          sx={{
            color: flowlyPalette.dashboard.textPrimary,
            fontSize: '1.1rem',
            fontWeight: 800,
            letterSpacing: '-0.02em',
          }}
        >
          {transaction.title}
        </Typography>
      </DialogTitle>

      <DialogContent sx={{ px: 3, pt: '20px !important', pb: 3 }}>
        <Typography
          sx={{
            mb: 2,
            color: isIncome
              ? flowlyPalette.dashboard.emerald
              : flowlyPalette.dashboard.textPrimary,
            fontSize: '1.5rem',
            fontWeight: 800,
            fontVariantNumeric: 'tabular-nums',
          }}
        >
          {isIncome ? '+' : '\u2212'}
          {formatCurrency(transaction.amount, transaction.currency)}
        </Typography>

        <Box component="dl" sx={{ m: 0 }}>
          <DetailRow
            label="Type"
            value={TRANSACTION_TYPE_LABELS[transaction.type]}
          />
          <DetailRow label="Currency" value={transaction.currency} />
          <DetailRow label="Wallet" value={transaction.walletName} />
          <DetailRow label="Category" value={transaction.categoryName} />
          <DetailRow
            label="Date"
            value={formatDate(transaction.transactionDate)}
          />
          {transaction.description && (
            <DetailRow label="Description" value={transaction.description} />
          )}
          <DetailRow
            label="Created"
            value={formatDateTime(transaction.createdAt)}
          />
          <DetailRow
            label="Last updated"
            value={formatDateTime(transaction.updatedAt)}
          />
        </Box>

        <Box sx={{ display: 'flex', justifyContent: 'flex-end', mt: 2.5 }}>
          <Button
            onClick={onClose}
            variant="outlined"
            sx={{
              borderRadius: '12px',
              borderColor: flowlyPalette.dashboard.border,
              color: flowlyPalette.dashboard.textSecondary,
              fontWeight: 600,
              textTransform: 'none',
              '&:hover': {
                borderColor: flowlyPalette.dashboard.textMuted,
                backgroundColor: flowlyPalette.dashboard.background,
              },
            }}
          >
            Close
          </Button>
        </Box>
      </DialogContent>
    </Dialog>
  );
};
