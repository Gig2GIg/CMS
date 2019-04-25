import getters from './getters';
import actions from './actions';
import mutations from './mutations';

const state = {
  clients: [],
  isLoading: false,
};

export default {
  state,
  getters,
  actions,
  mutations,
};
