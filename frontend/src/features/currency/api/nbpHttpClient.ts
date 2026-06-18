import axios from 'axios';

export const nbpHttpClient = axios.create({
  baseURL: 'https://api.nbp.pl/api',
  timeout: 8_000,
  headers: {
    Accept: 'application/json',
  },
});