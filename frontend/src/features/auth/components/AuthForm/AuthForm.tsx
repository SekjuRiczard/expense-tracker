import {
  Alert,
  Stack,
  Typography,
} from '@mui/material';
import type {
  FieldError,
  FieldValues,
} from 'react-hook-form';
import { flowlyPalette, } from '../../../../app/theme';
import { AuthFormField, } from './AuthFormField';
import { AuthSubmitButton, } from './AuthSubmitButton';
import type {
  AuthFormProps,
} from './authForm.types';

const getFieldErrorMessage = <
  TFieldValues extends FieldValues,
>(
  errors: AuthFormProps<TFieldValues>['errors'],
  fieldName: string,
): string | undefined => {
  const fieldError = errors[fieldName] as FieldError | undefined;

  return fieldError?.message;
};

export const AuthForm = <
  TFieldValues extends FieldValues,
>({
  title,
  subtitle,
  fields,
  submitLabel,
  isSubmitting,
  register,
  errors,
  onSubmit,
  errorMessage,
  footer,
}: AuthFormProps<TFieldValues>) => {
  return (
    <Stack spacing={3.2}>
      <Stack
        alignItems="center"
        spacing={0.7}
      >
        <Typography
          component="h1"
          sx={{
            color: flowlyPalette.auth.textPrimary,
            fontSize: 'clamp(2rem, 4vw, 2.3rem)',
            fontWeight: 850,
            letterSpacing: '-0.052em',
            lineHeight: 1.12,
            textAlign: 'center',
          }}
        >
          {title}
        </Typography>

        <Typography
          sx={{
            maxWidth: 440,
            color: flowlyPalette.auth.textSecondary,
            fontSize: '0.975rem',
            lineHeight: 1.65,
            textAlign: 'center',
          }}
        >
          {subtitle}
        </Typography>
      </Stack>

      <Stack
        component="form"
        noValidate
        onSubmit={onSubmit}
        spacing={2.1}
      >
        {errorMessage && (
          <Alert severity="error">
            {errorMessage}
          </Alert>
        )}

        {fields.map((field) => (
          <AuthFormField
            key={field.name}
            field={field}
            register={register}
            errorMessage={getFieldErrorMessage(
              errors,
              field.name,
            )}
          />
        ))}

        <AuthSubmitButton
          isSubmitting={isSubmitting}
          label={submitLabel}
        />

        {footer}
      </Stack>
    </Stack>
  );
};