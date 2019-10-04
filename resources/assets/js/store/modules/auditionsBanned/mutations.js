import * as types from '@/store/types';
import Vue from 'vue'

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_AUDITIONS_SUCCESS] (state, auditions) {
    state.auditions = auditions;
  },

  [types.FETCH_AUDITIONS_FAILURE] (state) {
    state.auditions = [];
  },

  [types.DELETE_AUDITION] (state, audition) {
    let index = state.auditions.indexOf(audition);
    state.auditions.splice(index, 1);
  },

  [types.ACCEPT_BAN] (state, audition) {

    let currenaudition = state.auditions.find(x => x.id == audition.data.data.id)
    let index = state.auditions.indexOf(currenaudition);
    Vue.set(state.auditions, index, audition);
  },

  [types.REMOVE_BAN] (state, audition) {
    let currentaudition = state.auditionsBanned.find(x => x.id == audition.id)
    let index = state.auditions.indexOf(currentaudition);
    Vue.set(state.auditions, index, audition);
  },
};
