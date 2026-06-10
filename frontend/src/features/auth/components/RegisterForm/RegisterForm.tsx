import {
  LockOutlined,
  MailOutlineRounded,
  PersonOutlineRounded,
} from '@mui/icons-material';
import {
  Link,
  Typography,
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
  registerSchema,
  type RegisterFormValues,
} from './register.schema';

export interface RegisterFormProps {
  readonly onSignInClick: () => void;
}

const registerFields: readonly AuthFormFieldConfig<RegisterFormValues>[] = [
  {
    name: 'username',
    label: 'Full name',
    placeholder: 'John Doe',
    type: 'text',
    autoComplete: 'name',
    icon: (
      <PersonOutlineRounded
        sx={{
          color: flowlyPalette.auth.textMuted,
          fontSize: 21,
        }}
      />
    ),
  },
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
    autoComplete: 'new-password',
    icon: (
      <LockOutlined
        sx={{
          color: flowlyPalette.auth.textMuted,
          fontSize: 21,
        }}
      />
    ),
  },
  {
    name: 'confirmPassword',
    label: 'Confirm password',
    placeholder: '••••••••',
    type: 'password',
    autoComplete: 'new-password',
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

export const RegisterForm = ({
  onSignInClick,
}: RegisterFormProps) => {
  const {
    register: registerUser,
  } = useAuth();

  const [errorMessage, setErrorMessage,] = useState<string | null>(null);

  const {
    formState: {
      errors,
      isSubmitting,
    },
    handleSubmit,
    register,
  } = useForm<RegisterFormValues>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      username: '',
      email: '',
      password: '',
      confirmPassword: '',
    },
  });

  const submitRegistration: SubmitHandler<RegisterFormValues> = async ({
    username,
    email,
    password,
  }) => {
    setErrorMessage(null);

    try {
      await registerUser({
        username,
        email,
        password,
      });
    } catch (error: unknown) {
      setErrorMessage(getAuthErrorMessage(error));
    }
  };

  return (
    <AuthForm
      title="Join us today"
      subtitle="Create an account and start controlling your finances"
      fields={registerFields}
      submitLabel="Create account"
      isSubmitting={isSubmitting}
      register={register}
      errors={errors}
      errorMessage={errorMessage}
      onSubmit={handleSubmit(submitRegistration)}
      footer={(
        <Typography
          sx={{
            pt: 0.5,
            color: flowlyPalette.auth.textSecondary,
            fontSize: '0.875rem',
            textAlign: 'center',
          }}
        >
          Already have an account?{' '}

          <Link
            component="button"
            onClick={onSignInClick}
            type="button"
            underline="hover"
            sx={{
              color: flowlyPalette.auth.focus,
              fontSize: '0.875rem',
              fontWeight: 700,
            }}
          >
            Sign in
          </Link>
        </Typography>
      )}
    />
  );
};