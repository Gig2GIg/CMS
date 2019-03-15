import getters from './getters';
import actions from './actions';
import mutations from './mutations';

const state = {
  products: [],
  isLoading: false,
};

export default {
  state,
  getters,
  actions,
  mutations,
};
