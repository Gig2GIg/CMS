import { Toast } from 'buefy/dist/components/toast';

export default {
  showMessage(_, message) {
    if (typeof message !== 'object') {
      message = {
        message,
      };
    }

    Toast.open({
      position: 'is-bottom',
      ...message
    });
  },

  showError({ dispatch }, message) {
    dispatch('showMessage', { message, type: 'is-danger' });
  },
};
