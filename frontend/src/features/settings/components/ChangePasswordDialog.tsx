import { zodResolver } from '@hookform/resolvers/zod';
import { VisibilityOffRounded, VisibilityRounded } from '@mui/icons-material';
import {
  Alert,
  Box,
  Button,
  CircularProgress,
  Dialog,
  DialogContent,
  DialogTitle,
  IconButton,
  InputAdornment,
  TextField,
  Typography,
} from '@mui/material';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { flowlyPalette } from '../../../app/theme';
import { useChangePassword } from '../hooks';
import { changePasswordSchema } from '../schemas';
import type { ChangePasswordFormData } from '../types';

export interface ChangePasswordDialogProps {
  readonly open: boolean;
  readonly onClose: () => void;
  readonly onSuccess: (message: string) => void;
}

export const ChangePasswordDialog = ({
  open,
  onClose,
  onSuccess,
}: ChangePasswordDialogProps) => {
  const [showOld, setShowOld] = useState(false);
  const [showNew, setShowNew] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<ChangePasswordFormData>({
    resolver: zodResolver(changePasswordSchema),
  });

  const { mutate, isPending, error: mutationError } = useChangePassword(() => {
    onSuccess('Password has been changed.');
    onClose();
    reset();
  });

  const handleClose = () => {
    if (isPending) return;
    onClose();
    reset();
  };

  const onSubmit = (data: ChangePasswordFormData) => {
    mutate(data);
  };

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
        Change password
      </DialogTitle>

      <DialogContent sx={{ px: 3, pt: '16px !important', pb: 3 }}>
        <Typography
          sx={{
            mb: 2.5,
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.82rem',
          }}
        >
          Enter your current password and choose a new one.
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

          <TextField
            autoComplete="current-password"
            error={Boolean(errors.oldPassword)}
            fullWidth
            helperText={errors.oldPassword?.message}
            label="Current password"
            size="small"
            slotProps={{
              input: {
                endAdornment: (
                  <InputAdornment position="end">
                    <IconButton
                      aria-label={showOld ? 'Hide password' : 'Show password'}
                      edge="end"
                      onClick={() => { setShowOld((p) => !p); }}
                      size="small"
                    >
                      {showOld
                        ? <VisibilityOffRounded sx={{ fontSize: 18 }} />
                        : <VisibilityRounded sx={{ fontSize: 18 }} />}
                    </IconButton>
                  </InputAdornment>
                ),
              },
            }}
            type={showOld ? 'text' : 'password'}
            {...register('oldPassword')}
          />

          <TextField
            autoComplete="new-password"
            error={Boolean(errors.newPassword)}
            fullWidth
            helperText={errors.newPassword?.message}
            label="New password"
            size="small"
            slotProps={{
              input: {
                endAdornment: (
                  <InputAdornment position="end">
                    <IconButton
                      aria-label={showNew ? 'Hide password' : 'Show password'}
                      edge="end"
                      onClick={() => { setShowNew((p) => !p); }}
                      size="small"
                    >
                      {showNew
                        ? <VisibilityOffRounded sx={{ fontSize: 18 }} />
                        : <VisibilityRounded sx={{ fontSize: 18 }} />}
                    </IconButton>
                  </InputAdornment>
                ),
              },
            }}
            type={showNew ? 'text' : 'password'}
            {...register('newPassword')}
          />

          <TextField
            autoComplete="new-password"
            error={Boolean(errors.confirmNewPassword)}
            fullWidth
            helperText={errors.confirmNewPassword?.message}
            label="Confirm new password"
            size="small"
            slotProps={{
              input: {
                endAdornment: (
                  <InputAdornment position="end">
                    <IconButton
                      aria-label={showConfirm ? 'Hide password' : 'Show password'}
                      edge="end"
                      onClick={() => { setShowConfirm((p) => !p); }}
                      size="small"
                    >
                      {showConfirm
                        ? <VisibilityOffRounded sx={{ fontSize: 18 }} />
                        : <VisibilityRounded sx={{ fontSize: 18 }} />}
                    </IconButton>
                  </InputAdornment>
                ),
              },
            }}
            type={showConfirm ? 'text' : 'password'}
            {...register('confirmNewPassword')}
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
