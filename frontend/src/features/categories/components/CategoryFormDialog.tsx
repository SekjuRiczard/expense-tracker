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
import { Controller, useForm } from 'react-hook-form';
import { flowlyPalette } from '../../../app/theme';
import { isApiError } from '../../../shared/api';
import { useCreateCategory, useUpdateCategory } from '../hooks';
import { categoryFormSchema } from '../schemas';
import type { CategoryFormData } from '../schemas';
import type { Category, CategoryType } from '../types';

export interface CategoryFormDialogProps {
  readonly open: boolean;
  readonly category?: Category | null;
  readonly defaultType?: CategoryType;
  readonly onClose: () => void;
  readonly showToast: (message: string, severity: 'success' | 'error') => void;
}

export const CategoryFormDialog = ({
  open,
  category,
  defaultType = 'expense',
  onClose,
  showToast,
}: CategoryFormDialogProps) => {
  const isEditMode = Boolean(category);
  const [formError, setFormError] = useState<string | null>(null);

  const form = useForm<CategoryFormData>({
    resolver: zodResolver(categoryFormSchema),
    defaultValues: {
      name: category?.name ?? '',
      type: category?.type ?? defaultType,
    },
  });

  useEffect(() => {
    if (open) {
      form.reset({
        name: category?.name ?? '',
        type: category?.type ?? defaultType,
      });
      // eslint-disable-next-line react-hooks/set-state-in-effect
      setFormError(null);
    }
  }, [open, category, defaultType]); // eslint-disable-line react-hooks/exhaustive-deps

  const handleError = (error: unknown, fallback: string) => {
    if (isApiError(error)) {
      if (error.status === 422 && error.violations.length > 0) {
        let matched = false;
        for (const violation of error.violations) {
          if (violation.propertyPath === 'name' || violation.propertyPath === 'type') {
            matched = true;
            form.setError(violation.propertyPath, { message: violation.message });
          }
        }
        if (!matched) setFormError(error.message);
        return;
      }
      setFormError(error.message);
      return;
    }
    showToast(fallback, 'error');
  };

  const createMutation = useCreateCategory({
    onSuccess: () => {
      showToast('Category has been created.', 'success');
      onClose();
      form.reset();
    },
    onError: (error) => { handleError(error, 'Failed to create category.'); },
  });

  const updateMutation = useUpdateCategory({
    onSuccess: () => {
      showToast('Category has been updated.', 'success');
      onClose();
      form.reset();
    },
    onError: (error) => { handleError(error, 'Failed to update category.'); },
  });

  const isPending = createMutation.isPending || updateMutation.isPending;

  const handleClose = () => {
    if (isPending) return;
    onClose();
  };

  const onSubmit = (data: CategoryFormData) => {
    setFormError(null);
    const payload = { name: data.name.trim(), type: data.type };

    if (isEditMode && category) {
      updateMutation.mutate({ id: category.id, payload });
    } else {
      createMutation.mutate(payload);
    }
  };

  const errors = form.formState.errors;

  return (
    <Dialog
      maxWidth="xs"
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
        {isEditMode ? 'Edit category' : 'Add category'}
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
          <TextField
            error={Boolean(errors.name)}
            fullWidth
            helperText={errors.name?.message}
            label="Name"
            size="small"
            {...form.register('name')}
          />

          <FormControl error={Boolean(errors.type)} fullWidth size="small">
            <InputLabel id="category-type-label">Type</InputLabel>
            <Controller
              control={form.control}
              name="type"
              render={({ field }) => (
                <Select label="Type" labelId="category-type-label" {...field}>
                  <MenuItem value="expense">Expense</MenuItem>
                  <MenuItem value="income">Income</MenuItem>
                </Select>
              )}
            />
            {errors.type && <FormHelperText>{errors.type.message}</FormHelperText>}
          </FormControl>

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
              {isEditMode ? 'Save changes' : 'Create category'}
            </Button>
          </Box>
        </Box>
      </DialogContent>
    </Dialog>
  );
};
