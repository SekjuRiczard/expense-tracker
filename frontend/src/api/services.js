import api from '@/api/axios';

const BASE_URL = '/api';
export const ENDPOINTS = {
    LOGIN : `${BASE_URL}/login_check`,
    REGISTER : `${BASE_URL}/register`,
    REFRESH : `${BASE_URL}/token/refresh`,
    PIN_SETUP : `${BASE_URL}/pin/setup`,
    PIN_CHANGE : `${BASE_URL}/pin/change`,
    PIN_VERIFY : `${BASE_URL}/pin/verify`,
    SESSION : `${BASE_URL}/auth/session`,
    //SESSION_DELETE: (id) => `${BASE_URL}/auth/session${id}`
}

export const ApiService = {
    login: (credentials) => api.post(ENDPOINTS.LOGIN,credentials),
    register: (data) => api.post(ENDPOINTS.REGISTER,data),
    setupPin: (pin) => api.post(ENDPOINTS.PIN_SETUP,pin),
    pinVerify: (pin) => api.post(ENDPOINTS.PIN_VERIFY,pin),
    pinChage: (pin) => api.put(ENDPOINTS.PIN_CHANGE,pin),
}