import type {
  FormEventHandler,
  ReactNode,
} from 'react';
import type {
  FieldErrors,
  FieldValues,
  Path,
  UseFormRegister,
} from 'react-hook-form';

export interface AuthFormFieldConfig<
  TFieldValues extends FieldValues,
> {
  readonly name: Path<TFieldValues>;
  readonly label: string;
  readonly placeholder: string;
  readonly type?: 'email' | 'password' | 'text';
  readonly autoComplete?: string;
  readonly icon: ReactNode;
}

export interface AuthFormProps<
  TFieldValues extends FieldValues,
> {
  readonly title: string;
  readonly subtitle: string;
  readonly fields: readonly AuthFormFieldConfig<TFieldValues>[];
  readonly submitLabel: string;
  readonly isSubmitting: boolean;
  readonly register: UseFormRegister<TFieldValues>;
  readonly errors: FieldErrors<TFieldValues>;
  readonly onSubmit: FormEventHandler<HTMLFormElement>;
  readonly errorMessage?: string | null;
  readonly footer?: ReactNode;
}