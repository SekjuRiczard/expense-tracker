import type { z } from 'zod';
import type {
  settingsSessionSchema,
  changePasswordSchema,
  changePinSchema,
  setupPinSchema,
} from '../schemas';

export type SettingsSession = z.infer<typeof settingsSessionSchema>;

export type ChangePasswordFormData = z.infer<typeof changePasswordSchema>;
export type ChangePinFormData = z.infer<typeof changePinSchema>;
export type SetupPinFormData = z.infer<typeof setupPinSchema>;

export interface SettingsUserInfo {
  readonly id: string;
  readonly username: string;
  readonly email: string;
  readonly hasPin: boolean;
  readonly hasPassword: true;
  readonly roleLabel: string;
}
