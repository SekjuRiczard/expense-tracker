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
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useEffect, useState } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { flowlyPalette } from '../../../app/theme';
import { isApiError } from '../../../shared/api';
import { useCreateBudget, useUpdateBudget } from '../hooks';
import { budgetFormSchema, type BudgetFormData } from '../schemas';
import type { BudgetWithUsage } from '../types';
import { BUDGET_PERIOD_LABELS, CURRENCY_OPTIONS } from './budgetHelpers';

export interface BudgetFormDialogProps {
  readonly open: boolean;
  readonly budget?: BudgetWithUsage | null;
  readonly onClose: () => void;
  readonly showToast: (message: string, severity: 'success' | 'error') => void;
}

const periodOptions = Object.entries(BUDGET_PERIOD_LABELS) as [
  BudgetFormData['periodType'],
  string,
][];

const toDateInputValue = (date: Date): string => {
  const pad = (n: number) => String(n).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
};

const buildDefaultValues = (
  budget?: BudgetWithUsage | null,
): BudgetFormData => {
  if (budget) {
    return {
      name: budget.name,
      amount: budget.amount / 100,
      currency: budget.currency,
      periodType: budget.periodType,
      startDate: budget.startDate,
      endDate: budget.endDate,
    };
  }

  const now = new Date();
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

  return {
    name: '',
    amount: 0,
    currency: 'PLN',
    periodType: 'monthly',
    startDate: toDateInputValue(firstDay),
    endDate: toDateInputValue(lastDay),
  };
};

export const BudgetFormDialog = ({
  open,
  budget,
  onClose,
  showToast,
}: BudgetFormDialogProps) => {
  const isEditMode = Boolean(budget);
  const [formError, setFormError] = useState<string | null>(null);

  const form = useForm<BudgetFormData>({
    resolver: zodResolver(budgetFormSchema),
    defaultValues: buildDefaultValues(budget),
  });

  useEffect(() => {
    if (open) {
      form.reset(buildDefaultValues(budget));
      // eslint-disable-next-line react-hooks/set-state-in-effect
      setFormError(null);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, budget]);

  const handleApiError = (error: unknown) => {
    if (isApiError(error)) {
      if (error.status === 409) {
        setFormError('A budget for this period already exists.');
        return;
      }

      if (error.violations.length > 0) {
        error.violations.forEach((violation) => {
          form.setError(
            violation.propertyPath as keyof BudgetFormData,
            { message: violation.message },
          );
        });
        setFormError('Please correct the highlighted fields.');
        return;
      }
    }

    setFormError('Something went wrong. Please try again.');
  };

  const createMutation = useCreateBudget({
    onSuccess: () => {
      showToast('Budget has been created.', 'success');
      onClose();
      form.reset(buildDefaultValues());
    },
    onError: handleApiError,
  });

  const updateMutation = useUpdateBudget({
    onSuccess: () => {
      showToast('Budget has been updated.', 'success');
      onClose();
    },
    onError: handleApiError,
  });

  const isPending = createMutation.isPending || updateMutation.isPending;

  const onSubmit = (data: BudgetFormData) => {
    setFormError(null);

    const payload = {
      name: data.name,
      amount: Math.round(data.amount * 100),
      currency: data.currency,
      periodType: data.periodType,
      startDate: data.startDate,
      endDate: data.endDate,
    };

    if (isEditMode && budget) {
      updateMutation.mutate({ id: budget.id, payload });
      return;
    }

    createMutation.mutate(payload);
  };

  return (
    <Dialog
      maxWidth="xs"
      onClose={isPending ? undefined : onClose}
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
        {isEditMode ? 'Edit budget' : 'Add budget'}
      </DialogTitle>

      <DialogContent sx={{ px: 3, pt: '16px !important', pb: 3 }}>
        {formError && (
          <Typography
            sx={{
              mb: 2,
              color: flowlyPalette.dashboard.rose,
              fontSize: '0.8rem',
              fontWeight: 600,
            }}
          >
            {formError}
          </Typography>
        )}

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

          <TextField
            error={Boolean(form.formState.errors.amount)}
            fullWidth
            helperText={
              form.formState.errors.amount?.message ??
              'Enter amount in major units, e.g. 3000.00'
            }
            label="Amount"
            size="small"
            slotProps={{ htmlInput: { min: 0, step: 0.01 } }}
            type="number"
            {...form.register('amount', { valueAsNumber: true })}
          />

          <FormControl
            error={Boolean(form.formState.errors.currency)}
            fullWidth
            size="small"
          >
            <InputLabel id="budget-currency-label">Currency</InputLabel>
            <Controller
              control={form.control}
              name="currency"
              render={({ field }) => (
                <Select
                  label="Currency"
                  labelId="budget-currency-label"
                  {...field}
                >
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

          <FormControl
            error={Boolean(form.formState.errors.periodType)}
            fullWidth
            size="small"
          >
            <InputLabel id="budget-period-label">Period type</InputLabel>
            <Controller
              control={form.control}
              name="periodType"
              render={({ field }) => (
                <Select
                  label="Period type"
                  labelId="budget-period-label"
                  {...field}
                >
                  {periodOptions.map(([value, label]) => (
                    <MenuItem key={value} value={value}>
                      {label}
                    </MenuItem>
                  ))}
                </Select>
              )}
            />
            {form.formState.errors.periodType && (
              <FormHelperText>
                {form.formState.errors.periodType.message}
              </FormHelperText>
            )}
          </FormControl>

          <Stack
            sx={{
              flexDirection: { xs: 'column', sm: 'row' },
              gap: 2,
            }}
          >
            <TextField
              error={Boolean(form.formState.errors.startDate)}
              fullWidth
              helperText={form.formState.errors.startDate?.message}
              label="Start date"
              size="small"
              slotProps={{ inputLabel: { shrink: true } }}
              type="date"
              {...form.register('startDate')}
            />

            <TextField
              error={Boolean(form.formState.errors.endDate)}
              fullWidth
              helperText={form.formState.errors.endDate?.message}
              label="End date"
              size="small"
              slotProps={{ inputLabel: { shrink: true } }}
              type="date"
              {...form.register('endDate')}
            />
          </Stack>

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
              {isEditMode ? 'Save changes' : 'Create budget'}
            </Button>
          </Box>
        </Box>
      </DialogContent>
    </Dialog>
  );
};
