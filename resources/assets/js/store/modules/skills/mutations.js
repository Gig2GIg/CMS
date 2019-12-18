import * as types from '@/store/types';
import Vue from 'vue';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_SKILLS_SUCCESS] (state, skills) {
    state.skills = skills;
  },

  [types.FETCH_SKILLS_FAILURE] (state) {
    state.skills = [];
  },

  [types.CREATE_SKILL] (state, skill) {
    state.skills.push(skill);
  },

  [types.UPDATE_SKILL] (state, skill) {
    let currentSkill = state.skills.find(x => x.id === skill.id);
    let index = state.skills.indexOf(currentSkill);

    Vue.set(state.skills, index, skill);
  },

  [types.DELETE_SKILL] (state, skill) {
    let index = state.skills.indexOf(skill);
    state.skills.splice(index, 1);
  },
};
