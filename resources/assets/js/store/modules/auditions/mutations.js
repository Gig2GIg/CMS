import * as types from '@/store/types';

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
};
