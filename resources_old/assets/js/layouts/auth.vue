<template>
  <section class="hero is-fullheight is-primary is-bold" id="app">
    <div class="hero-body">
      <div class="container">
        <div class="columns is-centered">
          <child/>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: "auth-layout",
  data: () => ({
    drawer: null,
    items: [
      { title: "Welcome", icon: "dashboard", to: "welcome" },
      { title: "Login", icon: "question_answer", to: "login" },
      { title: "Register", icon: "question_answer", to: "register" }
    ],
    appName: window.config.appName
  })
};
</script>
