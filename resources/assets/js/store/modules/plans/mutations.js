import * as types from '@/store/types';

export default {
  [types.FETCH_PLANS_SUCCESS] (state, plans) {
    state.plans = plans;
  },

  [types.FETCH_PLANS_FAILURE] (state) {
    state.plans = [];
  },
};
