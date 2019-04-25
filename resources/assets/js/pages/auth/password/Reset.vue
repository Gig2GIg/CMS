<template>
  <div class="column is-one-third-desktop is-one-mobile">
    <article class="card rounded shadow">
      <form @submit.prevent="submit">
        <div class="card-content">
          <h1 class="title has-text-centered">
            <a href="/">
              <img src="/storage/logo.png" alt="Logo" class="">
            </a>
          </h1>

          <b-field
            label="Email"
            :type="{'is-danger': errors.has('email')}"
            :message="errors.first('email')"
          >
            <b-input
              v-model="form.email"
              v-validate="'required|email'"
              name="email"
              readonly
            />
          </b-field>

          <b-field
            label="Password"
            :type="{'is-danger': errors.has('password')}"
            :message="errors.first('password')"
          >
            <b-input
              v-model="form.password"
              v-validate="'required|min:6'"
              name="password"
              type="password"
              autofocus
            />
          </b-field>

          <b-field
            label="Confirm password"
            :type="{'is-danger': errors.has('confirm-password')}"
            :message="errors.first('confirm-password')"
          >
            <b-input
              v-model="form.password_confirmation"
              v-validate="{ required: true, is: form.password }"
              name="confirm-password"
              type="password"
            />
          </b-field>

          <div class="field">
            <button class="button is-info is-medium is-fullwidth rounded shadow" :disabled="isLoading">
              <i class="fa fa-user"></i>
              Change password
            </button>
          </div>
        </div>
      </form>
    </article>
  </div>
</template>

<script>
import { mapActions, mapState } from 'vuex';

export default {
  layout: 'auth',
  data: () => ({
    form: {
      token: null,
      email: null,
      password: null,
      password_confirmation: null,
    },
  }),
  computed: {
    ...mapState('auth', ['isLoading']),
  },
  methods: {
    ...mapActions('auth', ['reset']),
    ...mapActions('toast', ['showError']),

    async submit() {
      try {
        let valid = await this.$validator.validateAll();

        if (! valid) {
          this.showError('Please check the fields.');
          return;
        }

        if (await this.reset(this.form)) {
          this.$router.replace({ name: 'login' });
        }
      } catch(e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },
  },
  created() {
    this.form.email = this.$route.query.email;
    this.form.token = this.$route.params.token;
  },
};
</script>
