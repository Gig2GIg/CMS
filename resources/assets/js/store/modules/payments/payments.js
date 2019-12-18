import mutations from './mutations';
import actions from './actions';
import getters from './getters';

const state = {
  payments: [],
  isLoading: false,
};

export default {
  state,
  mutations,
  actions,
  getters,
};
