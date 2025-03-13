import axios from 'axios';

const BASE_URL = import.meta.env.VITE_API_URL;

const baseApi = axios.create({
  baseURL: BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'X-Client-Type': 'web'
  }
});

export default baseApi;
