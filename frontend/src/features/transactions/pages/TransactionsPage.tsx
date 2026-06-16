import { AddRounded } from '@mui/icons-material';
import { Alert, Box, Button, Paper, Snackbar } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import type { AppLayoutOutletContext } from '../../../app/layouts/AppLayout';
import { flowlyPalette } from '../../../app/theme';
import { useCategories } from '../../categories';
import { useWallets } from '../../wallets';
import {
  DeleteTransactionDialog,
  TransactionDetailsDialog,
  TransactionFilters,
  TransactionFormDialog,
  TransactionsEmptyState,
  TransactionsPagination,
  TransactionsSkeleton,
  TransactionsTable,
  type SortableColumn,
  type SortDirection,
} from '../components';
import { useTransactions } from '../hooks';
import type { Transaction, TransactionFilters as Filters } from '../types';

interface SnackbarState {
  readonly open: boolean;
  readonly message: string;
  readonly severity: 'success' | 'error';
}

const DEFAULT_FILTERS: Filters = {
  page: 1,
  limit: 20,
};

const compareValues = (
  a: Transaction,
  b: Transaction,
  column: SortableColumn,
): number => {
  switch (column) {
    case 'amount':
      return a.amount - b.amount;
    case 'transactionDate':
      return (
        new Date(a.transactionDate).getTime() -
        new Date(b.transactionDate).getTime()
      );
    case 'title':
      return a.title.localeCompare(b.title);
    case 'categoryName':
      return a.categoryName.localeCompare(b.categoryName);
    case 'walletName':
      return a.walletName.localeCompare(b.walletName);
    case 'type':
      return a.type.localeCompare(b.type);
    default:
      return 0;
  }
};

export const TransactionsPage = () => {
  const { setHeaderOverride } = useOutletContext<AppLayoutOutletContext>();

  const [filters, setFilters] = useState<Filters>(DEFAULT_FILTERS);
  const [sortBy, setSortBy] = useState<SortableColumn>('transactionDate');
  const [sortDirection, setSortDirection] = useState<SortDirection>('desc');

  const [createOpen, setCreateOpen] = useState(false);
  const [editing, setEditing] = useState<Transaction | null>(null);
  const [viewing, setViewing] = useState<Transaction | null>(null);
  const [deleting, setDeleting] = useState<Transaction | null>(null);

  const [snackbar, setSnackbar] = useState<SnackbarState>({
    open: false,
    message: '',
    severity: 'success',
  });

  const showToast = (
    message: string,
    severity: 'success' | 'error' = 'success',
  ) => {
    setSnackbar({ open: true, message, severity });
  };

  const transactionsQuery = useTransactions(filters);
  const walletsQuery = useWallets();
  const categoriesQuery = useCategories();

  const wallets = walletsQuery.data ?? [];
  const categories = categoriesQuery.data ?? [];

  const pagination = transactionsQuery.data?.pagination;
  const totalItems = pagination?.totalItems ?? 0;

  const sortedItems = useMemo(() => {
    const copy = [...(transactionsQuery.data?.items ?? [])];
    copy.sort((a, b) => {
      const result = compareValues(a, b, sortBy);
      return sortDirection === 'asc' ? result : -result;
    });
    return copy;
  }, [transactionsQuery.data, sortBy, sortDirection]);

  const handleFilterChange = (partial: Partial<Filters>) => {
    setFilters((prev) => ({ ...prev, ...partial, page: 1 }));
  };

  const handlePageChange = (page: number) => {
    setFilters((prev) => ({ ...prev, page }));
  };

  const handleSortChange = (column: SortableColumn) => {
    if (column === sortBy) {
      setSortDirection((prev) => (prev === 'asc' ? 'desc' : 'asc'));
    } else {
      setSortBy(column);
      setSortDirection(column === 'transactionDate' ? 'desc' : 'asc');
    }
  };

  useEffect(() => {
    const subtitle = transactionsQuery.isPending
      ? 'Loading transactions...'
      : `${totalItems} ${totalItems === 1 ? 'transaction' : 'transactions'}`;

    setHeaderOverride({
      subtitle,
      action: (
        <Button
          onClick={() => { setCreateOpen(true); }}
          startIcon={<AddRounded sx={{ fontSize: 18 }} />}
          variant="contained"
          sx={{
            display: { xs: 'none', sm: 'inline-flex' },
            minHeight: 38,
            px: 1.6,
            borderRadius: 2,
            background: 'linear-gradient(135deg, #4F46E5, #4338CA)',
            boxShadow: 'none',
            fontSize: '0.8rem',
            fontWeight: 700,
            textTransform: 'none',
            '&:hover': {
              background: 'linear-gradient(135deg, #6366F1, #4F46E5)',
              boxShadow: 'none',
            },
          }}
        >
          Add transaction
        </Button>
      ),
    });

    return () => {
      setHeaderOverride(null);
    };
  }, [totalItems, transactionsQuery.isPending, setHeaderOverride]);

  const showEmpty =
    !transactionsQuery.isPending &&
    !transactionsQuery.isError &&
    sortedItems.length === 0;

  return (
    <Box>
      <TransactionFilters
        categories={categories}
        filters={filters}
        onChange={handleFilterChange}
        wallets={wallets}
      />

      <Paper
        elevation={0}
        sx={{
          overflow: 'hidden',
          border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
          borderRadius: '20px',
          backgroundColor: flowlyPalette.dashboard.surface,
        }}
      >
        {transactionsQuery.isError && (
          <Box sx={{ p: 2 }}>
            <Alert
              action={
                <Button
                  color="inherit"
                  onClick={() => { void transactionsQuery.refetch(); }}
                  size="small"
                >
                  Retry
                </Button>
              }
              severity="error"
              sx={{ borderRadius: '12px' }}
            >
              Failed to load transactions.
            </Alert>
          </Box>
        )}

        {!transactionsQuery.isError && transactionsQuery.isPending && (
          <TransactionsSkeleton />
        )}

        {showEmpty && <TransactionsEmptyState />}

        {!transactionsQuery.isError &&
          !transactionsQuery.isPending &&
          sortedItems.length > 0 && (
            <TransactionsTable
              onDelete={(t) => { setDeleting(t); }}
              onEdit={(t) => { setEditing(t); }}
              onSortChange={handleSortChange}
              onViewDetails={(t) => { setViewing(t); }}
              sortBy={sortBy}
              sortDirection={sortDirection}
              transactions={sortedItems}
            />
          )}

        {pagination && !transactionsQuery.isError && (
          <TransactionsPagination
            onPageChange={handlePageChange}
            pagination={pagination}
          />
        )}
      </Paper>

      <TransactionFormDialog
        categories={categories}
        onClose={() => { setCreateOpen(false); }}
        open={createOpen}
        showToast={showToast}
        wallets={wallets}
      />

      <TransactionFormDialog
        categories={categories}
        onClose={() => { setEditing(null); }}
        open={Boolean(editing)}
        showToast={showToast}
        transaction={editing}
        wallets={wallets}
      />

      <TransactionDetailsDialog
        onClose={() => { setViewing(null); }}
        open={Boolean(viewing)}
        transaction={viewing}
      />

      <DeleteTransactionDialog
        onClose={() => { setDeleting(null); }}
        open={Boolean(deleting)}
        showToast={showToast}
        transaction={deleting}
      />

      <Snackbar
        anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
        autoHideDuration={4000}
        onClose={() => { setSnackbar((s) => ({ ...s, open: false })); }}
        open={snackbar.open}
      >
        <Alert
          onClose={() => { setSnackbar((s) => ({ ...s, open: false })); }}
          severity={snackbar.severity}
          sx={{ borderRadius: '12px', fontWeight: 600 }}
          variant="filled"
        >
          {snackbar.message}
        </Alert>
      </Snackbar>
    </Box>
  );
};
