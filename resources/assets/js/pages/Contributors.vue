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
            <div class="columns" v-if="contributors.length">
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
                <b-table-column field="contributor_info.details.first_name" label="Name" width="250" sortable>
                  {{ props.row.contributor_info.details.first_name }} {{ props.row.contributor_info.details.last_name }}
                </b-table-column>

                <b-table-column field="contributor_info.email" label="Email" sortable>
                  {{ props.row.contributor_info.email }}
                </b-table-column>

                <b-table-column field="actions" width="40">
                  <b-dropdown position="is-bottom-left">
                    <button class="button is-info" slot="trigger">
                      <b-icon icon="menu-down"></b-icon>
                    </button>
                    <b-dropdown-item has-link >
                      <a @click.prevent.stop="confirmSendPassword(props.row)">Send password</a>
                    </b-dropdown-item>
                    <b-dropdown-item has-link>
                      <a @click.prevent.stop="confirmDelete(props.row)">Delete</a>
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
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from "vuex";

export default {
  name: "Contributors",
  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: "",
    selectedContributor: {},
    isModalActive: false,
  }),
  computed: {
    ...mapState('contributors', ['contributors', 'isLoading']),
    ...mapGetters("contributors", ["search"]),

    filter: function() {
      return this.search(this.searchText);
    }
  },
  methods: {
    ...mapActions("contributors", ["fetch", 'sendPassword', 'destroy']),

    confirmSendPassword(contributor) {
      this.selectedContributor = contributor;
      this.$dialog.confirm({
        message: `Are you sure you want to send a forgot password "${
          this.selectedContributor.contributor_info.email
        }"?`,
        confirmText: "Yes, I'm sure",
        type: "is-success",
        hasIcon: true,
        onConfirm: this.sendNewPassword
      });
    },

    confirmDelete(contributor) {
      this.selectedContributor = contributor;

      this.$dialog.confirm({
        message: `Are you sure you want to delete "${
          this.selectedContributor.contributor_info.email
        }"?`,
        confirmText: "Yes, I'm sure",
        type: "is-info",
        hasIcon: true,
        onConfirm: this.deleteContributor
      });
    },

    async sendNewPassword() {
      await this.sendPassword(this.selectedContributor);
    },

    async deleteContributor() {
      await this.destroy(this.selectedContributor);
    }
  },

  async created() {
    await this.fetch(this.$route.params.id);
    this.loaded = true;
  }
};
</script>
