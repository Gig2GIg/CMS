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
            <div class="columns" v-if="performers.length">
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
              :show-detail-icon="true"
              detail-key="id"
              detailed
              hoverable
            >
              <template slot-scope="props">
                <b-table-column
                  field="name"
                  label="Performer"
                  width="250"
                  sortable
                >{{ props.row.name }}</b-table-column>

                <b-table-column field="user_city" label="City" sortable>{{ props.row.user_city }}</b-table-column>

                <b-table-column field="actions" width="40">
                  <b-dropdown position="is-bottom-left">
                    <button class="button is-info" slot="trigger">
                      <b-icon icon="menu-down"></b-icon>
                    </button>
                    <b-dropdown-item has-link>
                      <a @click.prevent.stop="confirmSendPassword(props.row)">Send password</a>
                    </b-dropdown-item>
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
                  <div class="w-1/2 mx-4">
                    <div class="mb-4">
                      <img class="w-24 h-24" :src="props.row.image">
                    </div>
                    <div class="content">
                      <p>
                        <strong>Agency:</strong>
                        {{ props.row.details.agency_name }}
                      </p>
                      <p>
                        <strong>Profession:</strong>
                        {{ props.row.details.profesion }}
                      </p>
                      <p>
                        <strong>Subscription:</strong>
                        Plan {{ props.row.details.subscription }}
                      </p>
                      <p>
                        <strong>Address:</strong>
                        {{ props.row.details.address }}
                      </p>
                      <p>
                        <strong>Zip code:</strong>
                        {{ props.row.details.zip }}
                      </p>
                      <p>
                        <strong>City:</strong>
                        {{ props.row.user_city }}
                      </p>
                      <div class="border-grey-light border-t mb-4"></div>
                      <div>
                        <h4>Video</h4>
                        <video width="320" height="240" controls v-if="props.row.video">
                          <source :src="props.row.video" type="video/mp4">
                          Your browser does not support the video tag.
                        </video>
                        <p v-else>No video.</p>
                      </div>
                    </div>
                  </div>
                  <div class="w-1/2 mx-4">
                    <div class="content">
                      <h3>Feedback</h3>
                      <div v-if="props.row.feedback">
                        <p class="flex items-center">
                          <strong>Instant Feedback:</strong>
                          <img src="/storage/feedback/i2.png" class="w-6 h-6 mx-2 inline" alt>
                        </p>
                        <p>
                          <strong>Call Back:</strong>
                          {{props.row.feedback.callBack === '0' ? 'No' : 'Yes'}}
                        </p>
                        <p>
                          <strong>Work On:</strong>
                          {{ props.row.feedback.work }}
                        </p>
                        <p>
                          <strong>Comment:</strong>
                          <span v-html=" props.row.comment_feedback"></span>
                        </p>
                      </div>
                      <p v-else>No feedback.</p>
                    </div>
                  </div>
                </article>
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
  name: "Performers",
  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: "",
    selectedPerformer: {},
    isModalActive: false,
  }),
  computed: {
    ...mapState('performers', ['performers', 'isLoading']),
    ...mapGetters('performers', ['search']),

    filter: function() {
      return this.search(this.searchText);
    }
  },
  methods: {
    ...mapActions('performers', ['fetch', 'sendPassword', 'notify', 'destroy']),

    confirmSendPassword(performer) {
      this.selectedPerformer = performer;
      this.$dialog.confirm({
        message: `Are you sure you want to send a forgot password "${
          this.selectedPerformer.name
        }"?`,
        confirmText: "Yes, I'm sure",
        type: "is-success",
        hasIcon: true,
        onConfirm: this.sendNewPassword
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

    confirmDelete(performer) {
      this.selectedPerformer = performer;

      this.$dialog.confirm({
        message: `Are you sure you want to delete "${
          this.selectedPerformer.name
        }"?`,
        confirmText: "Yes, I'm sure",
        type: "is-info",
        hasIcon: true,
        onConfirm: this.deletePerformer
      });
    },

    async sendNewPassword() {
      await this.sendPassword(this.selectedPerformer);
    },

    async sendNotification(client, message) {
      await this.notify({ client, message });
    },

    async deletePerformer() {
      await this.destroy(this.selectedPerformer);
    }
  },
  async created() {
    await this.fetch(this.$route.params.id);
    this.loaded = true;
  }
};
</script>
