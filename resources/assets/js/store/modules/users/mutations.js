import * as types from '@/store/types';
import Vue from 'vue';

export default {
  [types.TOGGLE_SPINNER](state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_USERS_SUCCESS](state, users) {
    state.users = users;
  },

  [types.FETCH_USERS_FAILURE](state) {
    state.users = [];
  },

  [types.UPDATE_USER](state, user) {
    let current = state.users.find(x => x.id === user.id);
    let index = state.users.indexOf(current);

    Vue.set(state.users, index, user);
  },

  [types.DELETE_TOPIC](state, user) {
    let index = state.users.indexOf(user);
    state.users.splice(index, 1);
  },
};
