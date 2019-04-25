import * as types from '@/store/types';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_CLIENTS_SUCCESS] (state, clients) {
    state.clients = clients;
  },

  [types.FETCH_CLIENTS_FAILURE] (state) {
    state.clients = [];
  },

  [types.DELETE_CLIENT] (state, client) {
    let index = state.clients.indexOf(client);
    state.clients.splice(index, 1);
  },
};
