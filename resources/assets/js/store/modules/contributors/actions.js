import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }, audition) {
    try {
      const { data: { data } } = await axios.get(`/api/cms/auditions/show/${audition}`);
      const contributors = data.contributors;
      const director = data.director;

      contributors.push({
        director: true,
        contributor_info: director,
      });

      commit(types.FETCH_CONTRIBUTORS_SUCCESS, contributors);
    } catch (e) {
      commit(types.FETCH_CONTRIBUTORS_FAILURE);
    }
  },

  async sendPassword({ dispatch }, contributor) {
    try {
      dispatch('toggleSpinner');

      await axios.post('/api/cms/remember', {
        email: contributor.contributor_info.email,
      });

      dispatch('toast/showMessage', 'Password sent.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroy({ dispatch, commit }, contributor) {
    try {
      dispatch('toggleSpinner');

      // Delete contributor
      await axios.delete(`/api/cms/contributors/${contributor.id}`);
      commit(types.DELETE_CONTRIBUTOR, contributor);

      dispatch('toast/showMessage', 'Contributor deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
