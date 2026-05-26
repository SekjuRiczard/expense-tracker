import api from '../../../api/axios';
import { ENDPOINTS } from '../../../api/endpoints';

export const setupPinApi = (pin) => api.post(ENDPOINTS.PIN_SETUP, { pin });
export const verifyPinApi = (pin) => api.post(ENDPOINTS.PIN_VERIFY, { pin });