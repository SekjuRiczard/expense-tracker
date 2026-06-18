import {
  z,
} from 'zod';

export const registerSchema = z
  .object({
    username: z
      .string()
      .trim()
      .min(2, 'Full name must contain at least 2 characters.')
      .max(100, 'Full name cannot exceed 100 characters.'),

    email: z
      .string()
      .trim()
      .min(1, 'Email address is required.')
      .email('Enter a valid email address.'),

    password: z
      .string()
      .min(8, 'Password must contain at least 8 characters.'),

    confirmPassword: z
      .string()
      .min(1, 'Confirm your password.'),
  })
  .refine(
    ({
      password,
      confirmPassword,
    }) => password === confirmPassword,
    {
      path: ['confirmPassword',],
      message: 'Passwords do not match.',
    },
  );

export type RegisterFormValues = z.infer<typeof registerSchema>;