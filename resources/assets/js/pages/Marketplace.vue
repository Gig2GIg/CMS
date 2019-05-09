<template>
  <div>
    <nav class="breadcrumb" aria-label="breadcrumbs">
      <ul>
        <li class="is-active">
          <a href="#" aria-current="page">Marketplace Categories</a>
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
            Create category
          </button>
        </div>

        <div class="card">
          <div class="card-content">
            <div class="columns" v-if="categories.length">
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
                <b-table-column field="name" label="Name" width="250" sortable>{{ props.row.name }}</b-table-column>

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

              <template slot="detail" slot-scope="props">
                <article class="media pl-4 is-top">
                  <div class="w-1/2 mx-4">
                    <div class="content">
                      <p>
                        <strong>Description:</strong>
                        <span v-html=" props.row.description"></span>
                      </p>
                    </div>
                  </div>
                  <div class="w-1/2 mx-4">
                    <div class="content"></div>
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
    <b-modal :active.sync="isModalActive" has-modal-card :canCancel="!isLoading">
      <form @submit.prevent="selectedCategory.id ? updateCategory() : createCategory()">
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">{{ modalTitle }} Category</p>
          </header>

          <section class="modal-card-body">
            <b-field
              label="Name"
              :type="{'is-info': errors.has('name')}"
              :message="errors.first('name')"
            >
              <b-input
                v-model="selectedCategory.name"
                v-validate="'required|max:255'"
                name="name"
                autofocus
              />
            </b-field>

            <b-field
              label="Description"
              :type="{'is-info': errors.has('description')}"
              :message="errors.first('description')"
            >
              <b-input
                v-model="selectedCategory.description"
                v-validate="'required'"
                type="textarea"
                name="description"
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
            <button class="button is-primary" :disabled="isLoading">{{ modalTitle }} category</button>
          </footer>
        </div>
      </form>
    </b-modal>
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from "vuex";

export default {
  name: "Marketplace",
  data: () => ({
    loaded: false,
    perPage: 10,
    isModalActive: false,
    selectedCategory: {},
    searchText: "",
  }),
  computed: {
    ...mapState('categories', ['categories', 'isLoading']),
    ...mapGetters('categories', ['search']),

    filter: function() {
      return this.search(this.searchText);
    },

    modalTitle: function() {
      return this.selectedCategory.id ? "Update" : "Create";
    }
  },
  methods: {
    ...mapActions('categories', ['fetch', 'store', 'update', 'destroy']),
    ...mapActions('toast', ['showError']),

    confirmDelete(category) {
      this.selectedCategory = category;
      this.$dialog.confirm({
        message: `Are you sure you want to delete "${this.selectedCategory.name}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-success',
        hasIcon: true,
        onConfirm: this.deleteCategory,
      });
    },

    showCreateModal() {
      this.selectedCategory = {};
      this.isModalActive = true;
    },

    showUpdateModal(category) {
      this.selectedCategory = Object.assign({}, category);
      this.isModalActive = true;
    },

    async createCategory() {
      try {
        let valid = await this.$validator.validateAll();

        if (! valid) {
          this.showError('Please check the fields.');
          return;
        }

        await this.store(this.selectedCategory);

        this.isModalActive = false;
      } catch(e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async updateCategory() {
      try {
        let valid = await this.$validator.validateAll();
        if (!valid) {
          this.showError("Please check the fields.");
          return;
        }

        await this.update(this.selectedCategory);

        this.isModalActive = false;
      } catch (e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async deleteCategory() {
      await this.destroy(this.selectedCategory);
    }
  },

  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>
