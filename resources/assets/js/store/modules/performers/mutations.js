import * as types from '@/store/types';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_PERFORMERS_SUCCESS] (state, performers) {
    state.performers = performers;
  },

  [types.FETCH_PERFORMERS_FAILURE] (state) {
    state.performers = [];
  },

  [types.DELETE_PERFORMER] (state, performer) {
    let index = state.performers.indexOf(performer);
    state.performers.splice(index, 1);
  },
};
