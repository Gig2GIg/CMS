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
        <!-- <div class="mb-6">
          <button
            class="button is-primary shadow"
            :disabled="isLoading"
            @click="confirmBroadcast"
          >
            Broadcast notification
          </button>
        </div>-->
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
              :data="contributors"
              :per-page="perPage"
              :loading="isLoading"
              :paginated="!!contributors.length"
              :show-detail-icon="true"
              detail-key="id"
              hoverable
            >
              <template slot-scope="props">
                <b-table-column field="name" label="Name" width="250" sortable>
                  {{ props.row.name }}
                  <span
                    class="text-xs font-bold text-grey-dark"
                    v-if="props.row.director"
                  >(Casting director)</span>
                </b-table-column>

                <b-table-column field="email" label="Email" sortable>{{ props.row.email }}</b-table-column>

                <b-table-column field="actions" width="40">
                  <b-dropdown position="is-bottom-left">
                    <button class="button is-info" slot="trigger">
                      <b-icon icon="menu-down"></b-icon>
                    </button>
                    <b-dropdown-item has-link  >
                      <a @click.prevent.stop="confirmSendPassword(props.row)">Send password</a>
                    </b-dropdown-item>
                    <b-dropdown-item has-link v-if="!props.row.director">
                      <a @click.prevent.stop="confirmDelete(props.row)">Delete</a>
                    </b-dropdown-item>
                  </b-dropdown>
                </b-table-column>
              </template>

              <!-- <template slot="detail" slot-scope="props">
                <article class="media is-top">                 
                  <div class="w-1/2 mx-4">                 
                    <div class="content">
                      <p>
                        <strong>email:</strong>
                        {{ props.row.email }}
                      </p>                                       
                    </div>
                  </div>                
                </article>
              </template>-->
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
    contributors: [
      {
        id: "p01",
        name: "Greg Smith",
        email: "gregsmith@test.com",
        director: true
      },
      {
        id: "p01",
        name: "David Smith",
        email: "gregsmith@test.com",
        director: false
      },
      {
        id: "p01",
        name: "David Doe",
        email: "gregsmith@test.com",
        director: false
      }
    ]
  }),
  computed: {
    //...mapState('clients', ['clients', 'isLoading']),
    ...mapGetters("clients", ["search"]),

    filter: function() {
      return this.search(this.searchText);
    }
  },
  methods: {
    ...mapActions("clients", ["fetch", "broadcast", "notify", "destroy"]),

    confirmSendPassword(contributor) {
      this.selectedContributor = contributor;
      this.$dialog.confirm({
        message: `Are you sure you want to send a forgot password "${
          this.selectedContributor.email
        }"?`,
        confirmText: "Yes, I'm sure",
        type: "is-success",
        hasIcon: true,
        //onConfirm: this.deleteClient
      });
    },
    confirmDelete(contributor) {
      this.selectedContributor = contributor;

      this.$dialog.confirm({
        message: `Are you sure you want to delete "${
          this.selectedContributor.email
        }"?`,
        confirmText: "Yes, I'm sure",
        type: "is-info",
        hasIcon: true,
        //onConfirm: this.deleteClient
      });
    },

    // async sendBroadcast(message) {
    //   await this.broadcast(message);
    // },

    // async sendNotification(client, message) {
    //   await this.notify({ client, message });
    // },

    // async deleteClient() {
    //   await this.destroy(this.selectedClient);
    // }
  },
  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>
