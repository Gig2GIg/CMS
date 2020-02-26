import * as types from '@/store/types';
import Vue from 'vue';

export default {
  [types.TOGGLE_SPINNER](state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_USERS_SUCCESS](state, users) {
    state.userList = users;
  },

  [types.FETCH_USERS_FAILURE](state) {
    state.userList = [];
  },

  [types.UPDATE_USER](state, user) {
    let current = state.userList.find(x => x.id === user.id);
    let index = state.userList.indexOf(current);

    Vue.set(state.userList, index, user);
  },

  [types.DELETE_TOPIC](state, user) {
    let index = state.userList.indexOf(user);
    state.userList.splice(index, 1);
  },
};
