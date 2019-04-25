<template>
  <div class="column is-one-third-desktop is-one-mobile">
    <article class="card rounded shadow">
      <div class="card-content has-text-centered">
        <h1 class="title">Page not Found</h1>
        <div class="links">
          <router-link to="home">
            Go Home
          </router-link>
        </div>
      </div>
    </article>
  </div>
</template>

<script>
export default {
  name: 'not-found',
  layout: 'default'
};
</script>
