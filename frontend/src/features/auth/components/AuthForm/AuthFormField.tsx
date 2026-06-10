import {
  InputAdornment,
  TextField,
  Typography,
} from '@mui/material';
import type {
  FieldValues,
  Path,
  UseFormRegister,
} from 'react-hook-form';
import { flowlyPalette, } from '../../../../app/theme';
import type {
  AuthFormFieldConfig,
} from './authForm.types';

export interface AuthFormFieldProps<
  TFieldValues extends FieldValues,
> {
  readonly field: AuthFormFieldConfig<TFieldValues>;
  readonly register: UseFormRegister<TFieldValues>;
  readonly errorMessage?: string;
}

export const AuthFormField = <
  TFieldValues extends FieldValues,
>({
  field,
  register,
  errorMessage,
}: AuthFormFieldProps<TFieldValues>) => {
  return (
    <div>
      <Typography
        component="label"
        htmlFor={field.name}
        sx={{
          display: 'block',
          mb: 0.75,
          color: flowlyPalette.auth.textPrimary,
          fontSize: '0.875rem',
          fontWeight: 650,
        }}
      >
        {field.label}
      </Typography>

      <TextField
        {...register(field.name as Path<TFieldValues>)}
        id={field.name}
        autoComplete={field.autoComplete}
        error={Boolean(errorMessage)}
        fullWidth
        helperText={errorMessage}
        placeholder={field.placeholder}
        type={field.type ?? 'text'}
        slotProps={{
          input: {
            startAdornment: (
              <InputAdornment position="start">
                {field.icon}
              </InputAdornment>
            ),
          },
        }}
        sx={{
          '& .MuiOutlinedInput-root': {
            minHeight: 54,
            borderRadius: 2.5,
            backgroundColor: flowlyPalette.auth.background,
            transition: 'transform 180ms ease, box-shadow 180ms ease',

            '& fieldset': {
              borderColor: flowlyPalette.auth.border,
            },

            '&:hover fieldset': {
              borderColor: flowlyPalette.auth.textMuted,
            },

            '&.Mui-focused': {
              transform: 'scale(1.012)',
              boxShadow: `0 0 0 4px ${flowlyPalette.auth.focusGlow}`,
            },

            '&.Mui-focused fieldset': {
              borderColor: flowlyPalette.auth.focus,
              borderWidth: 1.5,
            },
          },

          '& .MuiFormHelperText-root': {
            mx: 0,
          },
        }}
      />
    </div>
  );
};