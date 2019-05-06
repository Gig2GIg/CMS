<template>
  <div>
    <nav class="breadcrumb" aria-label="breadcrumbs">
      <ul>
        <li class="is-active">
          <a href="#" aria-current="page">{{ $options.name }}</a>
        </li>
      </ul>
    </nav>

    <transition name="page">
      <section v-if="loaded">
        <div class="card">
          <div class="card-content">
            <div class="columns" v-if="subscriptions.length">
              <b-field class="column">
                <b-input v-model="searchText" placeholder="Search..." icon="magnify" type="search"/>
              </b-field>

              <b-field class="column" position="is-right" grouped>
                <b-select v-model="perPage">
                  <option value="5">5 per page</option>
                  <option value="10">10 per page</option>
                  <option value="15">15 per page</option>
                  <option value="20">20 per page</option>
                </b-select>
              </b-field>
            </div>

            <b-table
              :data="filter"
              :per-page="perPage"
              :loading="isLoading"
              :paginated="!!filter.length"
              hoverable
            >
              <template slot-scope="props">
                <b-table-column
                  field="user.first_name"
                  label="Name"
                  width="250"
                  sortable
                >{{ props.row.user.first_name }} {{ props.row.user.last_name }}</b-table-column>

                <b-table-column field="plan" label="Plan" sortable>Plan {{ props.row.plan }}</b-table-column>
                <b-table-column field="subscription.ends_at" label="Expiration" sortable>{{ props.row.subscription ? props.row.subscription.ends_at : '' }}</b-table-column>

                <b-table-column field="actions" width="40">
                  <b-dropdown position="is-bottom-left">
                    <button class="button is-info" slot="trigger">
                      <b-icon icon="menu-down"></b-icon>
                    </button>
                    <b-dropdown-item has-link>
                       <a @click.prevent.stop="showUpdateModal(props.row)">Edit</a>
                    </b-dropdown-item>
                  </b-dropdown>
                </b-table-column>
              </template>

              <template slot="empty">
                <section class="section">
                  <div class="content has-text-grey has-text-centered">
                    <p>
                      <b-icon icon="emoticon-sad" size="is-large"/>
                    </p>
                    <p>Nothing here.</p>
                  </div>
                </section>
              </template>
            </b-table>
          </div>
        </div>
      </section>
    </transition>
       <b-modal :active.sync="isModalActive" has-modal-card :canCancel="!isLoading">
      <form @submit.prevent="updateSubscription">
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">Update Subscription</p>
          </header>

          <section class="modal-card-body">
            <b-field
              label="Plan"
              :type="{'is-danger': errors.has('plan')}"
              :message="errors.first('plan')"
            >
             <b-select placeholder="Select a plan" name="plan"
             v-model="selectedSubscription.plan"  v-validate="'required'"
             autofocus expanded>
              <option
                    v-for="option in options"
                    :value="option.id"
                    :key="option.id">
                    {{ option.name }}
                </option>
              </b-select>
            </b-field>

          </section>
          <footer class="modal-card-foot">
            <button
              class="button"
              type="button"
              :disabled="isLoading"
              @click="isModalActive = false"
            >Close</button>
            <button class="button is-primary" :disabled="isLoading">Update subscription</button>
          </footer>
        </div>
      </form>
    </b-modal>
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from "vuex";

export default {
  name: "Subscriptions",
  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: "",
    isModalActive: false,
    selectedSubscription: {},
    options : [
      {id: "1", name:'Plan 1'},
      {id: "2", name:'Plan 2'},
      {id: "3", name:'Plan 3'},
    ],
  }),
  computed: {
    ...mapState('subscriptions', ['subscriptions', 'isLoading']),
    ...mapGetters('subscriptions', ['search']),

    filter: function() {
      return this.search(this.searchText);
    }
  },
  methods: {
    ...mapActions('subscriptions', ['fetch', 'update']),
    ...mapActions('toast', ['showError']),

    showUpdateModal(subscription) {
      this.selectedSubscription = Object.assign({}, subscription);
      this.isModalActive = true;
    },

    async updateSubscription() {
      try {
        let valid = await this.$validator.validateAll();
        if (!valid) {
          this.showError("Please check the fields.");
          return;
        }

        await this.update(this.selectedSubscription);

        this.isModalActive = false;
      } catch (e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },
  },

  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>
