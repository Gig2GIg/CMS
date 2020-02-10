import * as types from '@/store/types';
import Vue from 'vue';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_BLOGS_SUCCESS] (state, blogs) {
    state.blogs = blogs;
  },

  [types.FETCH_BLOGS_FAILURE] (state) {
    state.blogs = [];
  },

  [types.CREATE_BLOG] (state, blog) {
    // state.blogs.push(blog);
    state.blogs.splice(0,0,blog)
  },

  [types.UPDATE_BLOG] (state, blog) {
    let current = state.blogs.find(x => x.id === blog.id);
    let index = state.blogs.indexOf(current);

    Vue.set(state.blogs, index, blog);
  },

  [types.DELETE_BLOG] (state, blog) {
    let index = state.blogs.indexOf(blog);
    state.blogs.splice(index, 1);
  },
};
