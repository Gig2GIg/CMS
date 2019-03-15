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
            <div class="columns" v-if="clients.length">
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
              :data="clients"
              :per-page="perPage"
              :loading="isLoading"
              :paginated="!!clients.length"
              :show-detail-icon="true"
              detail-key="id"
              detailed
              hoverable
            >
              <template slot-scope="props">
                <b-table-column
                  field="title"
                  label="Title"
                  width="250"
                  sortable
                >{{ props.row.title }}</b-table-column>

                <b-table-column field="date" label="Date" sortable>{{ props.row.date }}</b-table-column>

                <b-table-column field="actions" width="40">
                  <b-dropdown position="is-bottom-left">
                    <button class="button is-info" slot="trigger">
                      <b-icon icon="menu-down"></b-icon>
                    </button>
                    <b-dropdown-item has-link>
                      <router-link :to="{name: 'contributors', params: { id: props.row.id }}" >View director/contributors</router-link>
                    </b-dropdown-item>
                    <b-dropdown-item has-link>
                      <router-link :to="{name: 'performers', params: { id: props.row.id }}" >View performers</router-link>
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
                      <img class="w-full" :src="props.row.coverImage">
                    </div>
                    <div class="content">
                      <p>
                        <strong>Time:</strong>
                        {{ props.row.time }}
                      </p>
                      <p>
                        <strong>Location:</strong>
                        {{ props.row.location }}
                      </p>
                      <p>
                        <strong>Description:</strong>
                        <span v-html=" props.row.description"></span>
                      </p>
                    </div>
                  </div>
                  <div class="w-1/2 mx-4">
                    <div class="content">
                      <p>
                        <strong>Audition Url:</strong>
                        {{ props.row.auditionURL }}
                      </p>
                      <div>
                        <strong>Contract dates:</strong>
                        <span
                          class="flex mb-2"
                          v-for="(item, index) in props.row.contractDates"
                          :key="index"
                        >{{item}}</span>
                      </div>
                      <div>
                        <strong>Rehearsal dates:</strong>
                        <span
                          class="flex mb-2"
                          v-for="(item, index) in props.row.contractDates"
                          :key="index"
                        >{{item}}</span>
                      </div>
                      <p>
                        <strong>Manage appointments:</strong>
                        {{ props.row.manageAppointments }}
                      </p>
                      <p>
                        <strong>Union status:</strong>
                        {{ props.row.unionStatus }}
                      </p>
                      <p>
                        <strong>Contract Type:</strong>
                        {{ props.row.contractType }}
                      </p>
                      <div>
                        <strong>Production type:</strong>
                        <span
                          class="flex mb-2"
                          v-for="(item, index) in props.row.productionType"
                          :key="index"
                        >{{item}}</span>
                      </div>
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
  name: "Auditions",
  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: "",
    selectedAudition: {},
    clients: [
      {
        id: "au-01",
        title: "Audition test",
        description:
          "<p>Now in its third hit year on Broadway, ALADDIN is “Exactly what you wish for!” –NBC-TV</p><br><p>It currently plays 8 times a week at the historic New Amsterdam Theatre on 42nd Street in the heart of Times Square.</p>",
        auditionURL: "audition.test.co",
        date: "2019-06-01",
        time: "4 h",
        location: "Casting House, Inc.",
        auditionURL: "auditiontest-gig2gig.co",
        contractDates: ["2019-06-01", "2019-06-02"],
        rehearsalDates: ["2019-06-01", "2019-06-02"],
        unionStatus: "UNION",
        contractType: "paid",
        productionType: ["theater", "tv & video", "film"],
        manageAppointments: "Lorem ipsum",
        coverImage:
          "https://cdn.evbstatic.com/s3-build/perm_001/7e2eb7/django/images/homepage/no-text/bg-desktop-generationdiy.jpg"
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

    confirmBroadcast() {
      this.$dialog.prompt({
        message: "Type a message",
        inputAttrs: {
          placeholder: "Message",
          maxlenght: 2000
        },
        onConfirm: value => this.sendBroadcast(value)
      });
    },

    confirmNotification(client) {
      this.$dialog.prompt({
        message: "Type a message",
        inputAttrs: {
          placeholder: "Message",
          maxlenght: 2000
        },
        onConfirm: value => this.sendNotification(client, value)
      });
    },

    confirmDelete(client) {
      this.selectedAudition = client;

      this.$dialog.confirm({
        message: `Are you sure you want to delete "${
          this.selectedAudition.title
        }"?`,
        confirmText: "Yes, I'm sure",
        type: "is-info",
        hasIcon: true,
        onConfirm: this.deleteClient
      });
    },
    showPerformers() {
      this.selectedFile = {};
      this.selectedCategory = {
        name: null,
        url_img: null
      };
      this.isModalActive = true;
    },
    async sendBroadcast(message) {
      await this.broadcast(message);
    },

    async sendNotification(client, message) {
      await this.notify({ client, message });
    },

    async deleteClient() {
      await this.destroy(this.selectedAudition);
    }
  },
  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>
