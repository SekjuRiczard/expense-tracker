import { zodResolver } from '@hookform/resolvers/zod';
import {
  Alert,
  Box,
  Button,
  CircularProgress,
  Dialog,
  DialogContent,
  DialogTitle,
  TextField,
  Typography,
} from '@mui/material';
import { useEffect } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { flowlyPalette } from '../../../app/theme';
import { useChangePin } from '../hooks';
import { changePinSchema, setupPinSchema } from '../schemas';
import type { ChangePinFormData, SetupPinFormData } from '../types';

export interface ChangePinDialogProps {
  readonly open: boolean;
  readonly hasPin: boolean;
  readonly onClose: () => void;
  readonly onSuccess: (message: string) => void;
}

type PinFormData = ChangePinFormData | SetupPinFormData;

const onlyDigits = (value: string) =>
  value.replace(/\D/g, '').slice(0, 6);

const pinInputProps = {
  inputMode: 'numeric' as const,
  maxLength: 6,
  pattern: '[0-9]*',
};

export const ChangePinDialog = ({
  open,
  hasPin,
  onClose,
  onSuccess,
}: ChangePinDialogProps) => {
  const {
    control,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<PinFormData>({
    resolver: zodResolver(hasPin ? changePinSchema : setupPinSchema),
    defaultValues: hasPin
      ? { oldPin: '', newPin: '', confirmPin: '' }
      : { newPin: '', confirmPin: '' },
  });

  useEffect(() => {
    if (!open) reset();
  }, [open, reset]);

  const { mutate, isPending, error: mutationError } = useChangePin(() => {
    onSuccess(hasPin ? 'PIN has been changed.' : 'PIN has been set.');
    onClose();
    reset();
  });

  const handleClose = () => {
    if (isPending) return;
    onClose();
    reset();
  };

  const onSubmit = (data: PinFormData) => {
    if (hasPin) {
      const d = data as ChangePinFormData;
      mutate({ hasPin: true, oldPin: d.oldPin, newPin: d.newPin });
    } else {
      const d = data as SetupPinFormData;
      mutate({ hasPin: false, newPin: d.newPin });
    }
  };

  const changePinErrors = hasPin
    ? (errors as Partial<Record<keyof ChangePinFormData, { message?: string }>>)
    : null;

  const setupPinErrors = !hasPin
    ? (errors as Partial<Record<keyof SetupPinFormData, { message?: string }>>)
    : null;

  return (
    <Dialog
      maxWidth="xs"
      onClose={handleClose}
      open={open}
      slotProps={{
        paper: {
          sx: {
            borderRadius: '20px',
            width: '100%',
          },
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
        {hasPin ? 'Change PIN' : 'Set PIN'}
      </DialogTitle>

      <DialogContent sx={{ px: 3, pt: '16px !important', pb: 3 }}>
        <Typography
          sx={{
            mb: 2.5,
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.82rem',
          }}
        >
          {hasPin
            ? 'Enter your current PIN and choose a new 6-digit PIN.'
            : 'Set a 6-digit numeric PIN for your account.'}
        </Typography>

        <Box
          component="form"
          noValidate
          onSubmit={(e) => { void handleSubmit(onSubmit)(e); }}
          sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}
        >
          {mutationError && (
            <Alert severity="error" sx={{ borderRadius: '12px', fontSize: '0.82rem' }}>
              {mutationError.message}
            </Alert>
          )}

          {hasPin && (
            <Controller
              control={control}
              name={'oldPin' as keyof PinFormData}
              render={({ field }) => (
                <TextField
                  autoComplete="off"
                  error={Boolean(changePinErrors?.oldPin)}
                  fullWidth
                  helperText={changePinErrors?.oldPin?.message}
                  label="Current PIN"
                  size="small"
                  slotProps={{ htmlInput: pinInputProps }}
                  type="text"
                  {...field}
                  onChange={(e) => {
                    field.onChange(onlyDigits(e.target.value));
                  }}
                />
              )}
            />
          )}

          <Controller
            control={control}
            name="newPin"
            render={({ field }) => (
              <TextField
                autoComplete="off"
                error={hasPin
                  ? Boolean(changePinErrors?.newPin)
                  : Boolean(setupPinErrors?.newPin)}
                fullWidth
                helperText={hasPin
                  ? changePinErrors?.newPin?.message
                  : setupPinErrors?.newPin?.message}
                label="New PIN"
                size="small"
                slotProps={{ htmlInput: pinInputProps }}
                type="text"
                {...field}
                onChange={(e) => {
                  field.onChange(onlyDigits(e.target.value));
                }}
              />
            )}
          />

          <Controller
            control={control}
            name="confirmPin"
            render={({ field }) => (
              <TextField
                autoComplete="off"
                error={hasPin
                  ? Boolean(changePinErrors?.confirmPin)
                  : Boolean(setupPinErrors?.confirmPin)}
                fullWidth
                helperText={hasPin
                  ? changePinErrors?.confirmPin?.message
                  : setupPinErrors?.confirmPin?.message}
                label="Confirm new PIN"
                size="small"
                slotProps={{ htmlInput: pinInputProps }}
                type="text"
                {...field}
                onChange={(e) => {
                  field.onChange(onlyDigits(e.target.value));
                }}
              />
            )}
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
                isPending
                  ? <CircularProgress size={15} sx={{ color: '#fff' }} />
                  : null
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
              Save
            </Button>
          </Box>
        </Box>
      </DialogContent>
    </Dialog>
  );
};
