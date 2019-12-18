import mutations from './mutations';
import actions from './actions';
import getters from './getters';

const state = {
  isLoading: false,
  productionTypes: [],
};

export default {
  state,
  mutations,
  actions,
  getters,
};
