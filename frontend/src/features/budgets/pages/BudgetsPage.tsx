import { AddRounded } from '@mui/icons-material';
import { Alert, Box, Button, Snackbar } from '@mui/material';
import { useEffect, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import type { AppLayoutOutletContext } from '../../../app/layouts/AppLayout';
import {
  BudgetDetailsDialog,
  BudgetFormDialog,
  BudgetsGrid,
  DeleteBudgetDialog,
} from '../components';
import { useBudgets } from '../hooks';
import type { BudgetWithUsage } from '../types';

interface SnackbarState {
  readonly open: boolean;
  readonly message: string;
  readonly severity: 'success' | 'error';
}

export const BudgetsPage = () => {
  const { setHeaderOverride } = useOutletContext<AppLayoutOutletContext>();

  const budgetsQuery = useBudgets();
  const budgets = budgetsQuery.data ?? [];

  const [createDialogOpen, setCreateDialogOpen] = useState(false);
  const [editingBudget, setEditingBudget] = useState<BudgetWithUsage | null>(
    null,
  );
  const [viewingBudget, setViewingBudget] = useState<BudgetWithUsage | null>(
    null,
  );
  const [deletingBudget, setDeletingBudget] = useState<BudgetWithUsage | null>(
    null,
  );

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

  useEffect(() => {
    const count = budgets.length;
    const subtitle = budgetsQuery.isPending
      ? 'Loading budgets...'
      : `${count} active ${count === 1 ? 'budget' : 'budgets'}`;

    setHeaderOverride({
      subtitle,
      action: (
        <Button
          onClick={() => { setCreateDialogOpen(true); }}
          startIcon={<AddRounded sx={{ fontSize: 18 }} />}
          variant="contained"
          sx={{
            display: { xs: 'none', sm: 'inline-flex' },
            minHeight: 38,
            px: 1.6,
            borderRadius: '12px',
            background: 'linear-gradient(135deg, #4F46E5, #4338CA)',
            boxShadow: 'none',
            color: '#FFFFFF',
            fontSize: '0.8rem',
            fontWeight: 700,
            textTransform: 'none',
            '&:hover': {
              background: 'linear-gradient(135deg, #6366F1, #4F46E5)',
              boxShadow: 'none',
            },
          }}
        >
          Add budget
        </Button>
      ),
    });

    return () => {
      setHeaderOverride(null);
    };
  }, [budgets.length, budgetsQuery.isPending, setHeaderOverride]);

  return (
    <Box>
      <BudgetsGrid
        budgets={budgets}
        error={budgetsQuery.isError}
        loading={budgetsQuery.isPending}
        onCreateClick={() => { setCreateDialogOpen(true); }}
        onDelete={(b) => { setDeletingBudget(b); }}
        onEdit={(b) => { setEditingBudget(b); }}
        onViewDetails={(b) => { setViewingBudget(b); }}
      />

      <BudgetFormDialog
        onClose={() => { setCreateDialogOpen(false); }}
        open={createDialogOpen}
        showToast={showToast}
      />

      <BudgetFormDialog
        budget={editingBudget}
        onClose={() => { setEditingBudget(null); }}
        open={Boolean(editingBudget)}
        showToast={showToast}
      />

      <BudgetDetailsDialog
        budget={viewingBudget}
        onClose={() => { setViewingBudget(null); }}
        open={Boolean(viewingBudget)}
      />

      <DeleteBudgetDialog
        budget={deletingBudget}
        onClose={() => { setDeletingBudget(null); }}
        open={Boolean(deletingBudget)}
        showToast={showToast}
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
