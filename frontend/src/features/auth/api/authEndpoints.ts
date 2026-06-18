export const AUTH_ENDPOINTS = {
  login: '/login',
  register: '/register',
  currentUser: '/me',
  logout: '/logout',
  refreshSession: '/token/refresh',
  setupPin: '/pin/setup',
  verifyPin: '/pin/verify',
} as const;