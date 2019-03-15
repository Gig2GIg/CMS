import getters from './getters';
import actions from './actions';
import mutations from './mutations';

const state = {
  rentals: [],
  isLoading: false,
};

export default {
  state,
  getters,
  actions,
  mutations,
};
