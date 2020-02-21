import axios from 'axios';
import store from '@/store';
import router from "../router/index";
import * as types from '@/store/types';

let isRetryTokenRefresh = true;

axios.interceptors.request.use(request => {
  const token = store.getters['auth/token'];

  if (token) {
    request.headers.common['Authorization'] = `Bearer ${token}`;
  }

  return request;
});

axios.interceptors.response.use(
  response => response,
  (error) => {
    const is_remember = localStorage.getItem('is_remember');
    if (error.response.status === 401 && is_remember == 1 && isRetryTokenRefresh) {
      isRetryTokenRefresh = false;
      return axios.post('/api/admin/refresh')
        .then(res => {
          if (res.status === 200) {
            isRetryTokenRefresh = true;
            // 1) save token client browser
            let updateTokenDetails = {
              token: res.data.token,
              remember: true,
              is_remember: true
            }
            store.dispatch('auth/saveToken', updateTokenDetails);

            // 2) Change Authorization header
            error.response.config.headers['Authorization'] = `Bearer ${res.data.token}`;

            // 3) return originalRequest object with Axios.
            return axios(error.response.config);
          }
        }).catch((refresh_token_error) => {
          isRetryTokenRefresh = false;
        });
    } else {
      store.dispatch('auth/removeToken');
      router.replace({ name: 'login' });
    }
    // return Error object with Promise
    return Promise.reject(error);
  });