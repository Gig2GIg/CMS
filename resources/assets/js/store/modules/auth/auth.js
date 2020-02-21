import Cookies from "js-cookie";
import actions from './actions';
import getters from './getters';
import mutations from './mutations';
const user =  window.localStorage.getItem('user');
console.log("TCL: localStorage vuser", user)
const state = {
  user: user ? JSON.parse(user) : null,
  token: Cookies.get("token"),
  isLoading: false,
};

export default {
  state,
  getters,
  actions,
  mutations,
};
