import { zodResolver } from '@hookform/resolvers/zod';
import {
  Alert,
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
} from '@mui/material';
import { useEffect, useState } from 'react';
import {
  Controller,
  useForm,
  useWatch,
  type Control,
  type UseFormReturn,
} from 'react-hook-form';
import { flowlyPalette } from '../../../app/theme';
import { isApiError } from '../../../shared/api';
import type { Category } from '../../categories';
import type { Wallet } from '../../wallets';
import { useCreateTransaction, useUpdateTransaction } from '../hooks';
import { transactionFormSchema } from '../schemas';
import type { TransactionFormData } from '../schemas';
import type { Transaction } from '../types';
import {
  dateInputToIso,
  toDateInputValue,
} from './transactionHelpers';

export interface TransactionFormDialogProps {
  readonly open: boolean;
  readonly transaction?: Transaction | null;
  readonly wallets: readonly Wallet[];
  readonly categories: readonly Category[];
  readonly onClose: () => void;
  readonly showToast: (message: string, severity: 'success' | 'error') => void;
}

const FORM_FIELDS: readonly (keyof TransactionFormData)[] = [
  'type',
  'walletId',
  'categoryId',
  'amount',
  'title',
  'description',
  'transactionDate',
];

const todayInputValue = (): string => toDateInputValue(new Date().toISOString());

const buildDefaults = (
  transaction?: Transaction | null,
): TransactionFormData => {
  if (transaction) {
    return {
      type: transaction.type,
      walletId: transaction.walletId,
      categoryId: transaction.categoryId,
      amount: transaction.amount / 100,
      title: transaction.title,
      description: transaction.description ?? '',
      transactionDate: toDateInputValue(transaction.transactionDate),
    };
  }

  return {
    type: 'expense',
    walletId: 0,
    categoryId: 0,
    amount: 0,
    title: '',
    description: '',
    transactionDate: todayInputValue(),
  };
};

const CategorySelect = ({
  control,
  form,
  categories,
  error,
  helperText,
}: {
  readonly control: Control<TransactionFormData>;
  readonly form: UseFormReturn<TransactionFormData>;
  readonly categories: readonly Category[];
  readonly error: boolean;
  readonly helperText?: string;
}) => {
  const selectedType = useWatch({ control, name: 'type' });
  const filtered = categories.filter((c) => c.type === selectedType);
  const selectedCategoryId = useWatch({ control, name: 'categoryId' });

  useEffect(() => {
    if (
      selectedCategoryId &&
      !filtered.some((c) => c.id === selectedCategoryId)
    ) {
      form.setValue('categoryId', 0, { shouldValidate: false });
    }
  }, [selectedType]); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <FormControl error={error} fullWidth size="small">
      <InputLabel id="tx-category-label">Category</InputLabel>
      <Controller
        control={control}
        name="categoryId"
        render={({ field }) => (
          <Select
            label="Category"
            labelId="tx-category-label"
            {...field}
            value={field.value === 0 ? '' : String(field.value)}
            onChange={(e) => { field.onChange(Number(e.target.value)); }}
          >
            {filtered.length === 0 && (
              <MenuItem disabled value="">
                No categories for this type
              </MenuItem>
            )}
            {filtered.map((category) => (
              <MenuItem key={category.id} value={String(category.id)}>
                {category.name}
              </MenuItem>
            ))}
          </Select>
        )}
      />
      {helperText && <FormHelperText>{helperText}</FormHelperText>}
    </FormControl>
  );
};

export const TransactionFormDialog = ({
  open,
  transaction,
  wallets,
  categories,
  onClose,
  showToast,
}: TransactionFormDialogProps) => {
  const isEditMode = Boolean(transaction);
  const [formError, setFormError] = useState<string | null>(null);

  const form = useForm<TransactionFormData>({
    resolver: zodResolver(transactionFormSchema),
    defaultValues: buildDefaults(transaction),
  });

  const { control } = form;

  useEffect(() => {
    if (open) {
      form.reset(buildDefaults(transaction));
      // eslint-disable-next-line react-hooks/set-state-in-effect
      setFormError(null);
    }
  }, [open, transaction]); // eslint-disable-line react-hooks/exhaustive-deps

  const handleMutationError = (error: unknown, fallback: string) => {
    if (isApiError(error)) {
      if (error.status === 404) {
        showToast('Transaction was not found.', 'error');
        onClose();
        return;
      }
      if (error.status === 422 && error.violations.length > 0) {
        let matched = false;
        for (const violation of error.violations) {
          if (
            FORM_FIELDS.includes(violation.propertyPath as keyof TransactionFormData)
          ) {
            matched = true;
            form.setError(violation.propertyPath as keyof TransactionFormData, {
              message: violation.message,
            });
          }
        }
        if (!matched) {
          setFormError(error.message);
        }
        return;
      }
      setFormError(error.message);
      return;
    }
    showToast(fallback, 'error');
  };

  const createMutation = useCreateTransaction({
    onSuccess: () => {
      showToast('Transaction has been created.', 'success');
      onClose();
      form.reset();
    },
    onError: (error) => {
      handleMutationError(error, 'Failed to create transaction.');
    },
  });

  const updateMutation = useUpdateTransaction({
    onSuccess: () => {
      showToast('Transaction has been updated.', 'success');
      onClose();
      form.reset();
    },
    onError: (error) => {
      handleMutationError(error, 'Failed to update transaction.');
    },
  });

  const isPending = createMutation.isPending || updateMutation.isPending;

  const handleClose = () => {
    if (isPending) return;
    onClose();
  };

  const onSubmit = (data: TransactionFormData) => {
    setFormError(null);

    const description = data.description.trim();
    const payload = {
      walletId: data.walletId,
      categoryId: data.categoryId,
      type: data.type,
      amount: Math.round(data.amount * 100),
      title: data.title.trim(),
      transactionDate: dateInputToIso(data.transactionDate),
      description: description.length > 0 ? description : null,
    };

    if (isEditMode && transaction) {
      updateMutation.mutate({ id: transaction.id, payload });
    } else {
      createMutation.mutate(payload);
    }
  };

  const errors = form.formState.errors;

  return (
    <Dialog
      maxWidth="sm"
      onClose={handleClose}
      open={open}
      slotProps={{ paper: { sx: { borderRadius: '20px', width: '100%' } } }}
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
        {isEditMode ? 'Edit transaction' : 'Add transaction'}
      </DialogTitle>

      <DialogContent sx={{ px: 3, pt: '16px !important', pb: 3 }}>
        {formError && (
          <Alert severity="error" sx={{ mb: 2, borderRadius: '12px' }}>
            {formError}
          </Alert>
        )}

        <Box
          component="form"
          noValidate
          onSubmit={(e) => { void form.handleSubmit(onSubmit)(e); }}
          sx={{ display: 'flex', flexDirection: 'column', gap: 2, mt: 1 }}
        >
          <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap' }}>
            <FormControl
              error={Boolean(errors.type)}
              size="small"
              sx={{ flex: 1, minWidth: 160 }}
            >
              <InputLabel id="tx-type-label">Type</InputLabel>
              <Controller
                control={control}
                name="type"
                render={({ field }) => (
                  <Select label="Type" labelId="tx-type-label" {...field}>
                    <MenuItem value="income">Income</MenuItem>
                    <MenuItem value="expense">Expense</MenuItem>
                  </Select>
                )}
              />
              {errors.type && (
                <FormHelperText>{errors.type.message}</FormHelperText>
              )}
            </FormControl>

            <FormControl
              error={Boolean(errors.walletId)}
              size="small"
              sx={{ flex: 1, minWidth: 160 }}
            >
              <InputLabel id="tx-wallet-label">Wallet</InputLabel>
              <Controller
                control={control}
                name="walletId"
                render={({ field }) => (
                  <Select
                    label="Wallet"
                    labelId="tx-wallet-label"
                    {...field}
                    value={field.value === 0 ? '' : String(field.value)}
                    onChange={(e) => { field.onChange(Number(e.target.value)); }}
                  >
                    {wallets.map((wallet) => (
                      <MenuItem key={wallet.id} value={String(wallet.id)}>
                        {wallet.name}
                      </MenuItem>
                    ))}
                  </Select>
                )}
              />
              {errors.walletId && (
                <FormHelperText>{errors.walletId.message}</FormHelperText>
              )}
            </FormControl>
          </Box>

          <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap' }}>
            <Box sx={{ flex: 1, minWidth: 160 }}>
              <CategorySelect
                categories={categories}
                control={control}
                error={Boolean(errors.categoryId)}
                form={form}
                helperText={errors.categoryId?.message}
              />
            </Box>

            <TextField
              error={Boolean(errors.amount)}
              helperText={
                errors.amount?.message ?? 'Major units, e.g. 129.99'
              }
              label="Amount"
              size="small"
              slotProps={{ htmlInput: { min: 0, step: 0.01 } }}
              sx={{ flex: 1, minWidth: 160 }}
              type="number"
              {...form.register('amount', { valueAsNumber: true })}
            />
          </Box>

          <TextField
            error={Boolean(errors.title)}
            fullWidth
            helperText={errors.title?.message}
            label="Title"
            size="small"
            {...form.register('title')}
          />

          <TextField
            error={Boolean(errors.description)}
            fullWidth
            helperText={errors.description?.message}
            label="Description"
            multiline
            minRows={2}
            size="small"
            {...form.register('description')}
          />

          <TextField
            error={Boolean(errors.transactionDate)}
            fullWidth
            helperText={errors.transactionDate?.message}
            label="Transaction date"
            size="small"
            slotProps={{ inputLabel: { shrink: true } }}
            type="date"
            {...form.register('transactionDate')}
          />

          <Box
            sx={{
              display: 'flex',
              gap: 1.5,
              justifyContent: 'flex-end',
              mt: 0.5,
            }}
          >
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
              {isEditMode ? 'Save changes' : 'Create transaction'}
            </Button>
          </Box>
        </Box>
      </DialogContent>
    </Dialog>
  );
};
