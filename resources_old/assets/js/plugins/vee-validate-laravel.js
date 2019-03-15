// https://gist.github.com/RobertGlynWilliams/f31b55160af9df92a970ac5500a16469
export default {
  install(Vue, _options) {
    Vue.prototype.$setErrorsFromResponse = function(errorResponse, scope = null) {
      // Only allow this function to be run if the validator exists
      if(!this.hasOwnProperty('$validator')) {
        return;
      }

      // Clear errors
      this.$validator.errors.clear();

      // Check if errors exist
      if (!errorResponse.hasOwnProperty('errors')) {
        let errorFields = Object.keys(errorResponse);

        // Insert laravel errors
        errorFields.map(field => {
          let msg = errorResponse[field];
          this.$validator.errors.add({ field, msg });
        });
      } else {
        let errorFields = Object.keys(errorResponse.errors);

        // Insert laravel errors
        errorFields.map(field => {
          let msg = errorResponse.errors[field].join(', ');
          this.$validator.errors.add({ field, msg, scope });
        });
      }
    };
  }
};
