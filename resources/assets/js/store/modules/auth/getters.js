export default {
  user: state => state.user,
  token: state => state.token,
  is_remember: state => state.is_remember,
  check: state => !!state.user,
};
