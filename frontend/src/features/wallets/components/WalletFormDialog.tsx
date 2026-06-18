import { zodResolver } from '@hookform/resolvers/zod';
import {
  Box,
  Button,
  CircularProgress,
  Dialog,
  DialogContent,
  DialogTitle,
  FormControl,
  FormHelperText,
  InputLabel,
  MenuItem,
  Select,
  TextField,
  Typography,
} from '@mui/material';
import { useEffect } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { flowlyPalette } from '../../../app/theme';
import { useCreateWallet, useUpdateWallet } from '../hooks';
import {
  createWalletFormSchema,
  updateWalletFormSchema,
} from '../schemas';
import type { CreateWalletFormData, UpdateWalletFormData } from '../schemas';
import type { Wallet } from '../types';
import { WALLET_TYPE_LABELS, CURRENCY_OPTIONS } from './walletHelpers';

export interface WalletFormDialogProps {
  readonly open: boolean;
  readonly wallet?: Wallet | null;
  readonly onClose: () => void;
  readonly showToast: (message: string, severity: 'success' | 'error') => void;
}

const walletTypeOptions = Object.entries(WALLET_TYPE_LABELS) as [
  string,
  string,
][];

const DialogFooter = ({
  isPending,
  onClose,
  submitLabel,
}: {
  readonly isPending: boolean;
  readonly onClose: () => void;
  readonly submitLabel: string;
}) => (
  <Box sx={{ display: 'flex', gap: 1.5, justifyContent: 'flex-end', mt: 0.5 }}>
    <Button
      disabled={isPending}
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
      Cancel
    </Button>

    <Button
      disabled={isPending}
      startIcon={
        isPending ? (
          <CircularProgress size={15} sx={{ color: '#fff' }} />
        ) : null
      }
      type="submit"
      variant="contained"
      sx={{
        borderRadius: '12px',
        background: 'linear-gradient(135deg, #4F46E5, #4338CA)',
        boxShadow: 'none',
        fontWeight: 700,
        textTransform: 'none',
        '&:hover': {
          background: 'linear-gradient(135deg, #6366F1, #4F46E5)',
          boxShadow: 'none',
        },
        '&.Mui-disabled': {
          background: flowlyPalette.dashboard.indigoSoft,
          color: flowlyPalette.dashboard.indigo,
        },
      }}
    >
      {submitLabel}
    </Button>
  </Box>
);

interface CreateFormProps {
  readonly onClose: () => void;
  readonly showToast: (message: string, severity: 'success' | 'error') => void;
  readonly open: boolean;
}

const CreateWalletForm = ({ onClose, showToast, open }: CreateFormProps) => {
  const form = useForm<CreateWalletFormData>({
    resolver: zodResolver(createWalletFormSchema),
    defaultValues: { name: '', type: 'bank_account', currency: 'PLN', balance: 0 },
  });

  useEffect(() => {
    if (open) form.reset({ name: '', type: 'bank_account', currency: 'PLN', balance: 0 });
  }, [open]); // eslint-disable-line react-hooks/exhaustive-deps

  const mutation = useCreateWallet({
    onSuccess: () => {
      showToast('Wallet has been created.', 'success');
      onClose();
      form.reset();
    },
    onError: () => {
      showToast('Failed to create wallet.', 'error');
    },
  });

  const onSubmit = (data: CreateWalletFormData) => {
    mutation.mutate({
      name: data.name,
      type: data.type,
      currency: data.currency,
      balanceAmount: Math.round(data.balance * 100),
    });
  };

  return (
    <Box
      component="form"
      noValidate
      onSubmit={(e) => { void form.handleSubmit(onSubmit)(e); }}
      sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}
    >
      <TextField
        error={Boolean(form.formState.errors.name)}
        fullWidth
        helperText={form.formState.errors.name?.message}
        label="Name"
        size="small"
        {...form.register('name')}
      />

      <FormControl
        error={Boolean(form.formState.errors.type)}
        fullWidth
        size="small"
      >
        <InputLabel id="create-type-label">Type</InputLabel>
        <Controller
          control={form.control}
          name="type"
          render={({ field }) => (
            <Select label="Type" labelId="create-type-label" {...field}>
              {walletTypeOptions.map(([value, label]) => (
                <MenuItem key={value} value={value}>
                  {label}
                </MenuItem>
              ))}
            </Select>
          )}
        />
        {form.formState.errors.type && (
          <FormHelperText>{form.formState.errors.type.message}</FormHelperText>
        )}
      </FormControl>

      <FormControl
        error={Boolean(form.formState.errors.currency)}
        fullWidth
        size="small"
      >
        <InputLabel id="create-currency-label">Currency</InputLabel>
        <Controller
          control={form.control}
          name="currency"
          render={({ field }) => (
            <Select label="Currency" labelId="create-currency-label" {...field}>
              {CURRENCY_OPTIONS.map((opt) => (
                <MenuItem key={opt.value} value={opt.value}>
                  {opt.label}
                </MenuItem>
              ))}
            </Select>
          )}
        />
        {form.formState.errors.currency && (
          <FormHelperText>
            {form.formState.errors.currency.message}
          </FormHelperText>
        )}
      </FormControl>

      <TextField
        error={Boolean(form.formState.errors.balance)}
        fullWidth
        helperText={
          form.formState.errors.balance?.message ??
          'Enter balance in major units, e.g. 500.00'
        }
        label="Initial balance"
        size="small"
        slotProps={{ htmlInput: { min: 0, step: 0.01 } }}
        type="number"
        {...form.register('balance', { valueAsNumber: true })}
      />

      <DialogFooter
        isPending={mutation.isPending}
        onClose={onClose}
        submitLabel="Create wallet"
      />
    </Box>
  );
};

interface EditFormProps {
  readonly wallet: Wallet;
  readonly onClose: () => void;
  readonly showToast: (message: string, severity: 'success' | 'error') => void;
  readonly open: boolean;
}

const EditWalletForm = ({
  wallet,
  onClose,
  showToast,
  open,
}: EditFormProps) => {
  const form = useForm<UpdateWalletFormData>({
    resolver: zodResolver(updateWalletFormSchema),
    defaultValues: { name: wallet.name, type: wallet.type },
  });

  useEffect(() => {
    if (open) form.reset({ name: wallet.name, type: wallet.type });
  }, [open, wallet.name, wallet.type]); // eslint-disable-line react-hooks/exhaustive-deps

  const mutation = useUpdateWallet({
    onSuccess: () => {
      showToast('Wallet has been updated.', 'success');
      onClose();
    },
    onError: () => {
      showToast('Failed to update wallet.', 'error');
    },
  });

  const onSubmit = (data: UpdateWalletFormData) => {
    mutation.mutate({ id: wallet.id, payload: { name: data.name, type: data.type } });
  };

  return (
    <Box
      component="form"
      noValidate
      onSubmit={(e) => { void form.handleSubmit(onSubmit)(e); }}
      sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}
    >
      <TextField
        error={Boolean(form.formState.errors.name)}
        fullWidth
        helperText={form.formState.errors.name?.message}
        label="Name"
        size="small"
        {...form.register('name')}
      />

      <FormControl
        error={Boolean(form.formState.errors.type)}
        fullWidth
        size="small"
      >
        <InputLabel id="edit-type-label">Type</InputLabel>
        <Controller
          control={form.control}
          name="type"
          render={({ field }) => (
            <Select label="Type" labelId="edit-type-label" {...field}>
              {walletTypeOptions.map(([value, label]) => (
                <MenuItem key={value} value={value}>
                  {label}
                </MenuItem>
              ))}
            </Select>
          )}
        />
        {form.formState.errors.type && (
          <FormHelperText>{form.formState.errors.type.message}</FormHelperText>
        )}
      </FormControl>

      <DialogFooter
        isPending={mutation.isPending}
        onClose={onClose}
        submitLabel="Save changes"
      />
    </Box>
  );
};

export const WalletFormDialog = ({
  open,
  wallet,
  onClose,
  showToast,
}: WalletFormDialogProps) => {
  const isEditMode = Boolean(wallet);

  const handleClose = () => {
    onClose();
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
        {isEditMode ? 'Edit wallet' : 'Add wallet'}
      </DialogTitle>

      <DialogContent sx={{ px: 3, pt: '16px !important', pb: 3 }}>
        <Typography
          sx={{
            mb: 2.5,
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.82rem',
          }}
        >
          {isEditMode
            ? 'Update the wallet name or type.'
            : 'Enter the wallet details to create a new account.'}
        </Typography>

        {isEditMode && wallet ? (
          <EditWalletForm
            onClose={handleClose}
            open={open}
            showToast={showToast}
            wallet={wallet}
          />
        ) : (
          <CreateWalletForm
            onClose={handleClose}
            open={open}
            showToast={showToast}
          />
        )}
      </DialogContent>
    </Dialog>
  );
};
