import * as types from '@/store/types';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_CONTRIBUTORS_SUCCESS] (state, contributors) {
    state.contributors = contributors;
  },

  [types.FETCH_CONTRIBUTORS_FAILURE] (state) {
    state.contributors = [];
  },
};
