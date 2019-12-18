import * as types from '@/store/types';

export default {
  [types.FETCH_PAYMENTS_SUCCESS] (state, payments) {
    state.payments = payments;
  },

  [types.FETCH_PAYMENTS_FAILURE] (state) {
    state.payments = [];
  },
};
