import { DeleteForeverRounded } from '@mui/icons-material';
import {
  Box,
  Button,
  CircularProgress,
  Dialog,
  DialogContent,
  DialogTitle,
  Typography,
} from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import { useDeleteWallet } from '../hooks';
import type { Wallet } from '../types';

export interface DeleteWalletDialogProps {
  readonly open: boolean;
  readonly wallet: Wallet | null;
  readonly onClose: () => void;
  readonly showToast: (message: string, severity: 'success' | 'error') => void;
}

export const DeleteWalletDialog = ({
  open,
  wallet,
  onClose,
  showToast,
}: DeleteWalletDialogProps) => {
  const { mutate, isPending } = useDeleteWallet({
    onSuccess: () => {
      showToast('Wallet has been deleted.', 'success');
      onClose();
    },
    onError: () => {
      showToast('Failed to delete wallet.', 'error');
    },
  });

  const handleClose = () => {
    if (isPending) return;
    onClose();
  };

  const handleConfirm = () => {
    if (!wallet) return;
    mutate(wallet.id);
  };

  return (
    <Dialog
      maxWidth="xs"
      onClose={handleClose}
      open={open}
      slotProps={{
        paper: {
          sx: { borderRadius: '20px', width: '100%' },
        },
      }}
    >
      <DialogTitle
        sx={{
          pb: 0,
          pt: 3,
          px: 3,
          color: flowlyPalette.dashboard.textPrimary,
          fontSize: '1.1rem',
          fontWeight: 800,
          letterSpacing: '-0.02em',
        }}
      >
        Delete wallet?
      </DialogTitle>

      <DialogContent sx={{ px: 3, pt: '16px !important', pb: 3 }}>
        <Typography
          sx={{
            mb: 3,
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.88rem',
            lineHeight: 1.6,
          }}
        >
          Are you sure you want to delete{' '}
          <strong>{wallet?.name}</strong>? This action cannot be undone.
        </Typography>

        <Box sx={{ display: 'flex', gap: 1.5, justifyContent: 'flex-end' }}>
          <Button
            disabled={isPending}
            onClick={handleClose}
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
            Cancel
          </Button>

          <Button
            aria-busy={isPending}
            disabled={isPending}
            onClick={handleConfirm}
            startIcon={
              isPending
                ? <CircularProgress size={15} sx={{ color: '#fff' }} />
                : <DeleteForeverRounded sx={{ fontSize: 17 }} />
            }
            variant="contained"
            sx={{
              borderRadius: '12px',
              backgroundColor: '#E11D48',
              boxShadow: 'none',
              fontWeight: 700,
              textTransform: 'none',
              '&:hover': {
                backgroundColor: '#BE123C',
                boxShadow: 'none',
              },
              '&.Mui-disabled': {
                backgroundColor: '#FECDD3',
                color: '#FDA4AF',
              },
            }}
          >
            {isPending ? 'Deleting...' : 'Delete wallet'}
          </Button>
        </Box>
      </DialogContent>
    </Dialog>
  );
};
