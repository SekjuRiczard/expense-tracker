import { httpClient } from '../../../shared/api';
import { settingsSessionsSchema } from '../schemas';
import type { SettingsSession } from '../types';

export const getSessions = async (): Promise<readonly SettingsSession[]> => {
  const response = await httpClient.get<unknown>('/auth/sessions');
  return settingsSessionsSchema.parse(response.data);
};
