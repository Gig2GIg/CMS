<template>
  <div>
    <section class="hero is-info welcome is-small">
      <div class="hero-body">
        <div class="container">
          <h1 class="title">Hi, {{ user.name }}.</h1>
          <h2 class="subtitle">We hope you are having a great day!</h2>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { mapState } from "vuex";

export default {
  name: 'Home',
  computed: mapState('auth', ['user']),
};
</script>
