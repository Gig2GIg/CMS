import * as types from '@/store/types';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_RENTERS_SUCCESS] (state, renters) {
    state.renters = renters;
  },

  [types.FETCH_RENTERS_FAILURE] (state) {
    state.renters = [];
  },

  [types.DELETE_RENTER] (state, renter) {
    let index = state.renters.indexOf(renter);
    state.renters.splice(index, 1);
  },
};
