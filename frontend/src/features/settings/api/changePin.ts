import { httpClient } from '../../../shared/api';

export interface ChangePinPayload {
  readonly hasPin: boolean;
  readonly oldPin?: string;
  readonly newPin: string;
}

export const changePinOrSetup = async ({
  hasPin,
  oldPin,
  newPin,
}: ChangePinPayload): Promise<void> => {
  if (hasPin) {
    await httpClient.put('/pin/change', { oldPin, newPin });
  } else {
    await httpClient.post('/pin/setup', { pin: newPin });
  }
};
