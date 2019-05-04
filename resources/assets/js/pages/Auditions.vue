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
            <div class="columns" v-if="auditions.length">
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
                      <img class="w-full" :src="props.row.cover">
                    </div>
                    <div class="content">
                      <p>
                        <strong>Time:</strong>
                        {{ props.row.time }}
                      </p>
                      <p>
                        <strong>Location:</strong>
                        {{ props.row.agency }}
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
                        {{ props.row.url }}
                      </p>
                      <div v-if="props.row.dates.find(x => x.type === 'contract')">
                        <strong>Contract dates:</strong>
                        <span class="flex mb-2">{{props.row.dates.find(x => x.type === 'contract').from}}</span>
                        <span class="flex mb-2">{{props.row.dates.find(x => x.type === 'contract').to}}</span>
                      </div>
                      <div v-if="props.row.dates.find(x => x.type === 'rehearsal')">
                        <strong>Rehearsal dates:</strong>
                        <span class="flex mb-2">{{props.row.dates.find(x => x.type === 'rehearsal').from}}</span>
                        <span class="flex mb-2">{{props.row.dates.find(x => x.type === 'rehearsal').to}}</span>
                      </div>
                      <p>
                        <strong>Manage appointments:</strong>
                        {{ props.row.manageAppointments }}
                      </p>
                      <p>
                        <strong>Union status:</strong>
                        {{ props.row.union.toUpperCase() }}
                      </p>
                      <p>
                        <strong>Contract Type:</strong>
                        {{ props.row.contract }}
                      </p>
                      <div>
                        <strong>Production type:</strong>
                        <span
                          class="flex mb-2"
                          v-for="(item, index) in props.row.production"
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
  }),
  computed: {
    ...mapState('auditions', ['auditions', 'isLoading']),
    ...mapGetters('auditions', ['search']),

    filter: function() {
      return this.search(this.searchText);
    }
  },
  methods: {
    ...mapActions('auditions', ['fetch', 'destroy']),

    confirmDelete(audition) {
      this.selectedAudition = audition;

      this.$dialog.confirm({
        message: `Are you sure you want to delete "${
          this.selectedAudition.title
        }"?`,
        confirmText: "Yes, I'm sure",
        type: "is-info",
        hasIcon: true,
        onConfirm: this.deleteAudition
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

    async deleteAudition() {
      await this.destroy(this.selectedAudition);
    }
  },
  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>
