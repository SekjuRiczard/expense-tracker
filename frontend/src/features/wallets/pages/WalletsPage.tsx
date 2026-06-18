import { AddRounded } from '@mui/icons-material';
import { Alert, Box, Button, Snackbar } from '@mui/material';
import { useEffect, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import type { AppLayoutOutletContext } from '../../../app/layouts/AppLayout';
import {
  DeleteWalletDialog,
  WalletDetailsDialog,
  WalletFormDialog,
  WalletsGrid,
} from '../components';
import { useWallets } from '../hooks';
import type { Wallet } from '../types';

interface SnackbarState {
  readonly open: boolean;
  readonly message: string;
  readonly severity: 'success' | 'error';
}

export const WalletsPage = () => {
  const { setHeaderOverride } = useOutletContext<AppLayoutOutletContext>();

  const walletsQuery = useWallets();
  const wallets = walletsQuery.data ?? [];

  const [createDialogOpen, setCreateDialogOpen] = useState(false);
  const [editingWallet, setEditingWallet] = useState<Wallet | null>(null);
  const [viewingWallet, setViewingWallet] = useState<Wallet | null>(null);
  const [deletingWallet, setDeletingWallet] = useState<Wallet | null>(null);

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
    const count = wallets.length;
    const subtitle = walletsQuery.isPending
      ? 'Loading wallets...'
      : `You have ${count} ${count === 1 ? 'wallet' : 'wallets'}`;

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
          Add wallet
        </Button>
      ),
    });

    return () => {
      setHeaderOverride(null);
    };
  }, [wallets.length, walletsQuery.isPending, setHeaderOverride]);

  return (
    <Box>
      <WalletsGrid
        error={walletsQuery.isError}
        loading={walletsQuery.isPending}
        onCreateClick={() => { setCreateDialogOpen(true); }}
        onDelete={(w) => { setDeletingWallet(w); }}
        onEdit={(w) => { setEditingWallet(w); }}
        onViewDetails={(w) => { setViewingWallet(w); }}
        wallets={wallets}
      />

      <WalletFormDialog
        onClose={() => { setCreateDialogOpen(false); }}
        open={createDialogOpen}
        showToast={showToast}
      />

      <WalletFormDialog
        onClose={() => { setEditingWallet(null); }}
        open={Boolean(editingWallet)}
        showToast={showToast}
        wallet={editingWallet}
      />

      <WalletDetailsDialog
        onClose={() => { setViewingWallet(null); }}
        open={Boolean(viewingWallet)}
        wallet={viewingWallet}
      />

      <DeleteWalletDialog
        onClose={() => { setDeletingWallet(null); }}
        open={Boolean(deletingWallet)}
        showToast={showToast}
        wallet={deletingWallet}
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
