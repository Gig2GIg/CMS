import axios from 'axios';
import store from '@/store';

axios.interceptors.request.use(request => {
  const token = store.getters['auth/token'];

  if (token) {
    request.headers.common['Authorization'] = `Bearer ${token}`;
  }

  return request;
});

axios.interceptors.response.use(
  response => response,
  error => {
    let errorResponse = error.response;

    if (errorResponse.status === 500) {
      store.dispatch('toast/showError', 'Something went wrong.');
    }

    return Promise.reject(error);
  }
)
