import * as types from '@/store/types';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_RENTALS_SUCCESS] (state, rentals) {
    state.rentals = rentals;
  },

  [types.FETCH_RENTALS_FAILURE] (state) {
    state.rentals = [];
  }
};
