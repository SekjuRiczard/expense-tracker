import {
  LockOutlined,
  MailOutlineRounded,
} from '@mui/icons-material';
import {
  Box,
  Link,
  Stack,
} from '@mui/material';
import {
  zodResolver,
} from '@hookform/resolvers/zod';
import {
  useState,
} from 'react';
import {
  useForm,
  type SubmitHandler,
} from 'react-hook-form';
import { flowlyPalette, } from '../../../../app/theme';
import { useAuth, } from '../../hooks/useAuth';
import { getAuthErrorMessage, } from '../../utils';
import {
  AuthForm,
  type AuthFormFieldConfig,
} from '../AuthForm';
import {
  loginSchema,
  type LoginFormValues,
} from './login.schema';

export interface LoginFormProps {
  readonly onSignUpClick: () => void;
}

const loginFields: readonly AuthFormFieldConfig<LoginFormValues>[] = [
  {
    name: 'email',
    label: 'Email address',
    placeholder: 'you@example.com',
    type: 'email',
    autoComplete: 'email',
    icon: (
      <MailOutlineRounded
        sx={{
          color: flowlyPalette.auth.textMuted,
          fontSize: 21,
        }}
      />
    ),
  },
  {
    name: 'password',
    label: 'Password',
    placeholder: '••••••••',
    type: 'password',
    autoComplete: 'current-password',
    icon: (
      <LockOutlined
        sx={{
          color: flowlyPalette.auth.textMuted,
          fontSize: 21,
        }}
      />
    ),
  },
];

export const LoginForm = ({
  onSignUpClick,
}: LoginFormProps) => {
  const {
    login,
  } = useAuth();

  const [errorMessage, setErrorMessage,] = useState<string | null>(null);

  const {
    formState: {
      errors,
      isSubmitting,
    },
    handleSubmit,
    register,
  } = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: '',
      password: '',
    },
  });

  const submitLogin: SubmitHandler<LoginFormValues> = async (values) => {
    setErrorMessage(null);

    try {
      await login(values);
    } catch (error: unknown) {
      setErrorMessage(getAuthErrorMessage(error));
    }
  };

  return (
    <AuthForm
      title="Welcome back"
      subtitle="Sign in to your account"
      fields={loginFields}
      submitLabel="Sign in"
      isSubmitting={isSubmitting}
      register={register}
      errors={errors}
      errorMessage={errorMessage}
      onSubmit={handleSubmit(submitLogin)}
      footer={(
        <Stack
          alignItems="center"
          direction="row"
          justifyContent="center"
          spacing={1.5}
          sx={{
            pt: 0.5,
          }}
        >
          <Link
            component="button"
            onClick={onSignUpClick}
            type="button"
            underline="hover"
            sx={{
              color: flowlyPalette.auth.focus,
              fontSize: '0.875rem',
              fontWeight: 700,
            }}
          >
            Sign up
          </Link>

          <Box
            aria-hidden="true"
            sx={{
              width: '1px',
              height: 16,
              backgroundColor: flowlyPalette.auth.border,
            }}
          />

          <Link
            component="button"
            onClick={() => {
              window.alert('Password recovery will be available soon.');
            }}
            type="button"
            underline="hover"
            sx={{
              color: flowlyPalette.auth.focus,
              fontSize: '0.875rem',
              fontWeight: 700,
            }}
          >
            Forgot password?
          </Link>
        </Stack>
      )}
    />
  );
};