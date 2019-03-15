<template>
  <nav class="navbar is-white is-shadowless">
    <div class="container is-fluid">
      <div class="navbar-brand">
        <a class="navbar-item" href="/">
          <img src="/storage/logo.png" alt="Logo">
        </a>
        <navbar-burger/>
      </div>
      <template v-if="user">
        <navbar-menu :user="user"></navbar-menu>
      </template>
    </div>
  </nav>
</template>

<script>
import NavbarMenu from "@/components/NavbarMenu";
import { mapState } from "vuex";
export default {
  name: "Navbar",
  data: () => ({
    appName: window.config.appName
  }),

  components: {
    NavbarMenu
  },

  computed: mapState({
    user: state => state.auth.user
  })
};
</script>
