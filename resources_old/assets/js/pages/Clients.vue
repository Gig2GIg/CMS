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
        <div class="mb-6">
          <button
            class="button is-primary shadow"
            :disabled="isLoading"
            @click="confirmBroadcast"
          >
            Broadcast notification
          </button>
        </div>

        <div class="card">
          <div class="card-content">
            <div class="columns" v-if="clients.length">
              <b-field class="column">
                <b-input
                  v-model="searchText"
                  placeholder="Search..."
                  icon="magnify"
                  type="search"
                />
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
              :paginated="!!clients.length"
              :show-detail-icon="true"
              detail-key="id"
              detailed
              hoverable
            >
              <template slot-scope="props">
                <b-table-column field="name" label="Name" width="250" sortable>
                  {{ props.row.name }}
                </b-table-column>

                <b-table-column field="email" label="Email" sortable>
                  {{ props.row.email }}
                </b-table-column>

                <b-table-column field="actions" width="40">
                  <b-dropdown>
                    <button class="button is-info" slot="trigger">
                      <b-icon icon="menu-down"></b-icon>
                    </button>

                    <b-dropdown-item has-link>
                      <a @click.prevent.stop="confirmNotification(props.row)">Send notification</a>
                    </b-dropdown-item>

                    <b-dropdown-item has-link>
                      <a @click.prevent.stop="confirmDelete(props.row)">Delete</a>
                    </b-dropdown-item>
                  </b-dropdown>
                </b-table-column>
              </template>

              <template slot="detail" slot-scope="props">
                <article class="media is-top">
                  <figure class="media-left">
                    <p class="image is-64x64 overflow-hidden">
                      <img :src="props.row.client.avatar">
                    </p>
                  </figure>
                  <div class="media-content address-content">
                    <div class="content">
                      <p>
                        <strong>Street Address:</strong>
                        {{ props.row.street_address }}
                      </p>
                      <p>
                        <strong>Apt. Suite:</strong>
                        {{ props.row.apt_suite }}
                      </p>
                      <p>
                        <strong>Zipcode:</strong>
                        {{ props.row.zip_code }}
                      </p>
                    </div>
                  </div>
                  <div class="media-content address-content">
                    <div class="content">
                      <p>
                        <strong>State:</strong>
                        {{ props.row.state }}
                      </p>
                      <p>
                        <strong>City:</strong>
                        {{ props.row.city }}
                      </p>
                    </div>
                  </div>
                </article>
              </template>

              <template slot="empty">
                <section class="section">
                  <div class="content has-text-grey has-text-centered">
                    <p>
                      <b-icon
                        icon="emoticon-sad"
                        size="is-large"
                      />
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
import { mapActions, mapState, mapGetters } from 'vuex';

export default {
  name: 'Clients',
  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: '',
    selectedClient: {},
  }),
  computed: {
    ...mapState('clients', ['clients', 'isLoading']),
    ...mapGetters('clients', ['search']),

    filter: function() {
      return this.search(this.searchText);
    },
  },
  methods: {
    ...mapActions('clients', ['fetch', 'broadcast', 'notify', 'destroy']),

    confirmBroadcast() {
      this.$dialog.prompt({
        message: 'Type a message',
        inputAttrs: {
          placeholder: 'Message',
          maxlenght: 2000
        },
        onConfirm: (value) => this.sendBroadcast(value),
      });
    },

    confirmNotification(client) {
      this.$dialog.prompt({
        message: 'Type a message',
        inputAttrs: {
          placeholder: 'Message',
          maxlenght: 2000
        },
        onConfirm: (value) => this.sendNotification(client, value),
      });
    },

    confirmDelete(client) {
      this.selectedClient = client;

      this.$dialog.confirm({
        message: `Are you sure you want to delete "${this.selectedClient.name}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-danger',
        hasIcon: true,
        onConfirm: this.deleteClient,
      });
    },

    async sendBroadcast(message) {
      await this.broadcast(message);
    },

    async sendNotification(client, message) {
      await this.notify({ client, message });
    },

    async deleteClient() {
      await this.destroy(this.selectedClient);
    },
  },
  async created() {
    await this.fetch();
    this.loaded = true;
  },
};
</script>
