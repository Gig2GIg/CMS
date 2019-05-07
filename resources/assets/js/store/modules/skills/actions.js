import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/cms/skill-suggestions');
      commit(types.FETCH_SKILLS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_SKILLS_FAILURE);
    }
  },

  async store({ dispatch, commit }, skill) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      const { data: { data } } = await axios.post('/api/cms/skill-suggestions/create', skill);
      commit(types.CREATE_SKILL, data);

      dispatch('toast/showMessage', 'Skill created.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async update({ dispatch, commit }, skill) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      await axios.put(`/api/cms/skill-suggestions/update/${skill.id}`, skill);
      commit(types.UPDATE_SKILL, skill);

      dispatch('toast/showMessage', 'Skill updated.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroy({ dispatch, commit }, skill) {
    try {
      dispatch('toggleSpinner');

      // Delete skill
      await axios.delete(`/api/cms/skill-suggestions/delete/${skill.id}`);
      commit(types.DELETE_SKILL, skill);

      dispatch('toast/showMessage', 'Skill deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
