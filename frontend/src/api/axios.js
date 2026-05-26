import axios from 'axios';

let accessToken = null;
let axiosConfig = {
    baseURL: import.meta.env.VITE_API_URL,
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
}
export const setAccessToken = (token) =>{
    accessToken = token;
}

const apiClient = axios.create(axiosConfig);

apiClient.interceptors.request.use((config) =>{
    if(accessToken){
        config.headers.Authorization = `Bearer ${accessToken}`;
    }

    return config;
})
