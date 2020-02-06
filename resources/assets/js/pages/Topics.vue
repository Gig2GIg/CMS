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
            @click="showCreateModal"
          >
            Create topic
          </button>
        </div>

        <div class="card">
          <div class="card-content">
            <div class="columns" v-if="topics.length">
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
            >
              <template slot-scope="props">
                <b-table-column field="title" label="Title" width="250" sortable>{{ props.row.title }}</b-table-column>

                <b-table-column field label sortable>{{ }}</b-table-column>
                <b-table-column field="actions" width="40">
                  <b-dropdown position="is-bottom-left">
                    <button class="button is-info" slot="trigger">
                      <b-icon icon="menu-down"></b-icon>
                    </button>
                    <b-dropdown-item has-link>
                      <a @click.prevent.stop="showUpdateModal(props.row)">Edit</a>
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
    <b-modal :active.sync="isModalActive" has-modal-card :canCancel="!isLoading">
      <form @submit.prevent="selectedTopic.id ? updateTopic() : createTopic()">
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">{{ modalTitle }} Topic</p>
          </header>

          <section class="modal-card-body">
            <b-field
              label="Title"
              :type="{'is-info': errors.has('title')}"
              :message="errors.first('title')"
            >
              <b-input
                v-model="selectedTopic.title"
                v-validate="'required|max:100'"
                name="title"
                autofocus
              />
            </b-field>
          </section>
          <footer class="modal-card-foot">
            <button
              class="button"
              type="button"
              :disabled="isLoading"
              @click="isModalActive = false"
            >Close</button>
            <button class="button is-primary" :disabled="isLoading">{{ modalTitle }} topic</button>
          </footer>
        </div>
      </form>
    </b-modal>
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from "vuex";

export default {
  name: "Topics",
  data: () => ({
    loaded: false,
    perPage: 10,
    isModalActive: false,
    selectedTopic: {},
    searchText: "",
  }),
  computed: {
    ...mapState('topics', ['topics', 'isLoading']),
    ...mapGetters('topics', ['search']),

    filter: function() {
      return this.search(this.searchText);
    },

    modalTitle: function() {
      return this.selectedTopic.id ? "Update" : "Create";
    }
  },
  methods: {
    ...mapActions('topics', ['fetch', 'store', 'update', 'destroy']),
    ...mapActions('toast', ['showError']),

    confirmDelete(topic) {
      this.selectedTopic = topic;
      this.$dialog.confirm({
        message: `Are you sure you want to delete "${this.selectedTopic.title}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-success',
        hasIcon: true,
        onConfirm: this.deleteTopic,
      });
    },

    showCreateModal() {
      this.selectedTopic = {};
      this.isModalActive = true;
    },

    showUpdateModal(topic) {
      this.selectedTopic = Object.assign({}, topic);
      this.isModalActive = true;
    },

    async createTopic() {
      try {
        let valid = await this.$validator.validateAll();

        if (! valid) {
          this.showError('Please check the fields.');
          return;
        }
        this.selectedTopic.status = 'on';
        await this.store(this.selectedTopic);

        this.isModalActive = false;
      } catch(e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async updateTopic() {
      try {
        let valid = await this.$validator.validateAll();
        if (!valid) {
          this.showError("Please check the fields.");
          return;
        }

        await this.update(this.selectedTopic);

        this.isModalActive = false;
      } catch (e) {
        console.log("TCL: updateTopic -> e", e)
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async deleteTopic() {
      await this.destroy(this.selectedTopic);
    }
  },

  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>
