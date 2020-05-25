import App from '@/components/App';
import VeeValidate from 'vee-validate';
import VeeValidateLaravel from '@/plugins/vee-validate-laravel';
import CKEditor from '@ckeditor/ckeditor5-vue';
import Vue from 'vue';
import VueMoment from 'vue-moment';
import router from '@/router';
import store from '@/store';
import firebase from 'firebase/app';

import '@/plugins';
import '@/components';

Vue.use(VeeValidate);
Vue.use(VeeValidateLaravel);
Vue.use(VueMoment);
Vue.use(CKEditor);

Vue.config.productionTip = false;


Vue.filter("dateTimeFormatBlogs", function (value) {
  if (value && value != '' && Vue.moment(value).isValid()) {
    let newValue = value.replace(/[-,.]/g, "/"); // safari not ssupported formate so replace it from - to / and make new format
    return Vue.moment(new Date(newValue + ' UTC').toISOString()).format("YYYY-MM-DD hh:mm:ss A");
  } else {
    return '';
  }
});

Vue.filter("dateFormat", function (value) {
  if (value && value != '' && Vue.moment(value).isValid()) {
    let newValue = value.replace(/[-,.]/g, "/"); // safari not ssupported formate so replace it from - to / and make new format
    return Vue.moment(new Date(newValue + ' UTC').toISOString()).format("YYYY-MM-DD");
  } else {
    return '';
  }
});

Vue.filter("birthDateFormat", function (value) {
  if (value && value != '' && Vue.moment(value).isValid()) {
    let newValue = value.replace(/[-,.]/g, "/"); // safari not ssupported formate so replace it from - to / and make new format
    return Vue.moment(new Date(newValue).toISOString()).format("YYYY-MM-DD");
  } else {
    return '';
  }
});

firebase.initializeApp({
  apiKey: 'AIzaSyDTrKkhJCM4ZNbFXRTq0AE2uKzNlpo3_i4',
  projectId: 'dd-gig2gi',
  storageBucket: 'dd-gig2gig.appspot.com',
});

Array.prototype.search = function (needle) {
  const match = item => Object.values(item).some(value => {
    if (value && typeof value === 'object') {
      return match(value);
    }
    return String(value).toLowerCase().indexOf(needle.toLowerCase()) !== -1;
  });

  return this.filter(item => match(item));
};

new Vue({
  router,
  store,
  ...App
});
