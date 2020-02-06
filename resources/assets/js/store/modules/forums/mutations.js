import * as types from '@/store/types';
import Vue from 'vue';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_FORUMS_SUCCESS] (state, forums) {
    state.forums = forums;
  },

  [types.FETCH_FORUMS_FAILURE] (state) {
    state.forums = [];
  },

  [types.CREATE_FORUM] (state, forum) {
    // state.forums.push(forum);
    state.forums.splice(0,0,forum)
  },

  [types.UPDATE_FORUM] (state, forum) {
    let current = state.forums.find(x => x.id === forum.id);
    let index = state.forums.indexOf(current);

    Vue.set(state.forums, index, forum);
  },

  [types.DELETE_FORUM] (state, forum) {
    let index = state.forums.indexOf(forum);
    state.forums.splice(index, 1);
  },
};
