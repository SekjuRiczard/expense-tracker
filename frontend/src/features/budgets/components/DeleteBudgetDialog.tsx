import {
  Button,
  CircularProgress,
  Dialog,
  DialogActions,
  DialogContent,
  DialogContentText,
  DialogTitle,
} from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import { useDeleteBudget } from '../hooks';
import type { BudgetWithUsage } from '../types';

export interface DeleteBudgetDialogProps {
  readonly open: boolean;
  readonly budget: BudgetWithUsage | null;
  readonly onClose: () => void;
  readonly showToast: (message: string, severity: 'success' | 'error') => void;
}

export const DeleteBudgetDialog = ({
  open,
  budget,
  onClose,
  showToast,
}: DeleteBudgetDialogProps) => {
  const mutation = useDeleteBudget({
    onSuccess: () => {
      showToast('Budget has been deleted.', 'success');
      onClose();
    },
    onError: () => {
      showToast('Failed to delete budget.', 'error');
    },
  });

  const handleDelete = () => {
    if (budget) {
      mutation.mutate(budget.id);
    }
  };

  return (
    <Dialog
      maxWidth="xs"
      onClose={mutation.isPending ? undefined : onClose}
      open={open}
      slotProps={{ paper: { sx: { borderRadius: '20px', width: '100%' } } }}
    >
      <DialogTitle
        sx={{
          pt: 3,
          px: 3,
          color: flowlyPalette.dashboard.textPrimary,
          fontSize: '1.1rem',
          fontWeight: 800,
        }}
      >
        Delete budget?
      </DialogTitle>

      <DialogContent sx={{ px: 3 }}>
        <DialogContentText
          sx={{
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.875rem',
          }}
        >
          Are you sure you want to delete this budget? This action cannot be
          undone.
        </DialogContentText>
      </DialogContent>

      <DialogActions sx={{ px: 3, pb: 3, gap: 1 }}>
        <Button
          disabled={mutation.isPending}
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
          Cancel
        </Button>

        <Button
          disabled={mutation.isPending}
          onClick={handleDelete}
          startIcon={
            mutation.isPending ? (
              <CircularProgress size={15} sx={{ color: '#fff' }} />
            ) : null
          }
          variant="contained"
          sx={{
            borderRadius: '12px',
            backgroundColor: flowlyPalette.dashboard.rose,
            boxShadow: 'none',
            fontWeight: 700,
            textTransform: 'none',
            '&:hover': {
              backgroundColor: '#E11D48',
              boxShadow: 'none',
            },
          }}
        >
          Delete budget
        </Button>
      </DialogActions>
    </Dialog>
  );
};
