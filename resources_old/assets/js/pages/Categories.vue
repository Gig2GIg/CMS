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
            Create category
          </button>
        </div>

        <div class="card">
          <div class="card-content">
            <div class="columns" v-if="categories.length">
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
              :paginated="!!categories.length"
              :show-detail-icon="true"
              detail-key="id"
              detailed
              hoverable
            >
              <template slot-scope="props">
                <b-table-column field="name" label="Name" sortable>
                  {{ props.row.name }}
                </b-table-column>

                <b-table-column
                  field="status"
                  label="Status"
                  class="is-capitalized"
                  width="150"
                  sortable
                >
                  {{ props.row.status }}
                </b-table-column>

                <b-table-column field="actions" width="40">
                  <b-dropdown>
                    <button class="button is-info" slot="trigger">
                      <b-icon icon="menu-down"></b-icon>
                    </button>

                    <b-dropdown-item has-link>
                      <a @click.prevent.stop="showUpdateModal(props.row)">Edit</a>
                    </b-dropdown-item>

                    <b-dropdown-item has-link>
                      <a @click.prevent.stop="confirmStatus(props.row)">
                        {{ props.row.status === 'active' ? 'Disable' : 'Enable' }}
                      </a>
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
                      <img :src="props.row.url_img">
                    </p>
                  </figure>
                  <div class="media-content">
                    <div class="content">
                      <p>
                        <strong>Name:</strong>
                        {{ props.row.name }}
                      </p>
                      <p>
                        <strong>Created:</strong>
                        {{ props.row.created_at | moment('hh:mm A - DD MMM YYYY') }}
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

    <b-modal :active.sync="isModalActive" has-modal-card :canCancel="!isLoading">
      <form @submit.prevent="selectedCategory.id ? updateCategory() : createCategory()">
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">{{ modalTitle }} Category</p>
          </header>

          <section class="modal-card-body">
            <b-field
              label="Name"
              :type="{'is-danger': errors.has('name')}"
              :message="errors.first('name')"
            >
              <b-input
                v-model="selectedCategory.name"
                v-validate="'required|max:255'"
                name="name"
                autofocus
              />
            </b-field>

            <label class="label">Image</label>

            <div class="columns is-vcentered pb-2">
              <p class="image is-64x64">
                <img :src="selectedFile.preview ? selectedFile.preview : (selectedCategory.url_img ? selectedCategory.url_img : 'test.png')">
              </p>

              <b-field
                class="column file"
                :type="{'is-danger': errors.has('url_img')}"
                :message="errors.first('url_img')"
              >
                <b-upload
                  v-model="selectedFile.file"
                  accept=".png,.jpg,.jpeg"
                  :required="!selectedCategory.id"
                  @input="fileChanged"
                >
                  <a class="button is-primary">
                    <b-icon icon="upload"></b-icon>
                    <span>Click to upload</span>
                  </a>
                </b-upload>
                <span class="file-name" v-if="selectedFile.file">
                  {{ selectedFile.file.name }}
                </span>
              </b-field>
            </div>
          </section>

          <footer class="modal-card-foot">
            <button class="button" type="button" :disabled="isLoading" @click="isModalActive = false">Close</button>
            <button class="button is-primary" :disabled="isLoading">{{ modalTitle }} category</button>
          </footer>
        </div>
      </form>
    </b-modal>
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from 'vuex';

export default {
  name: 'Categories',
  data: () => ({
    isModalActive: false,
    loaded: false,
    perPage: 10,
    searchText: '',
    selectedCategory: {},
    selectedFile: {},
  }),
  computed: {
    ...mapState('categories', ['categories', 'isLoading']),
    ...mapGetters('categories', ['search']),

    modalTitle: function() {
      return this.selectedCategory.id ? 'Update' : 'Create';
    },

    filter: function() {
      return this.search(this.searchText);
    },
  },
  methods: {
    ...mapActions('categories', ['fetch', 'store', 'update', 'destroy']),
    ...mapActions('toast', ['showError']),

    showCreateModal() {
      this.selectedFile = {};
      this.selectedCategory = {
        name: null,
        url_img: null,
      };
      this.isModalActive = true;
    },

    showUpdateModal(category) {
      this.selectedFile = {};
      this.selectedCategory = Object.assign({}, category);
      this.isModalActive = true;
    },

    confirmStatus(category) {
      this.selectedCategory = Object.assign({}, category);

      const status = this.selectedCategory.status === 'active';
      const action = status ? 'disable' : 'enable';
      const type = status ? 'is-danger' : 'is-success';

      this.selectedCategory.status =
        this.selectedCategory.status === 'active' ? 'inactive' : 'active';

      this.$dialog.confirm({
        message: `Are you sure you want to ${action} "${this.selectedCategory.name}"?`,
        confirmText: "Yes, I'm sure",
        type,
        hasIcon: true,
        onConfirm: this.toggleCategoryStatus,
      });
    },

    confirmDelete(category) {
      this.selectedCategory = category;

      this.$dialog.confirm({
        message: `Are you sure you want to delete "${this.selectedCategory.name}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-danger',
        hasIcon: true,
        onConfirm: this.deleteCategory,
      });
    },

    async createCategory() {
      try {
        let valid = await this.$validator.validateAll();

        if (! valid) {
          this.showError('Please check the fields.');
          return;
        }

        await this.store({
          category: this.selectedCategory,
          fileData: this.selectedFile,
        });

        this.isModalActive = false;
      } catch(e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async updateCategory() {
      try {
        let valid = await this.$validator.validateAll();

        if (! valid) {
          this.showError('Please check the fields.');
          return;
        }

        await this.update({
          category: this.selectedCategory,
          fileData: this.selectedFile,
        });

        this.isModalActive = false;
      } catch(e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async toggleCategoryStatus() {
      await this.update({
        category: this.selectedCategory,
        fileData: this.selectedFile,
      });
    },

    async deleteCategory() {
      await this.destroy(this.selectedCategory);
    },

    fileChanged(file) {
      if (! file || file.size > 4000000) {
        this.selectedFile = {};
        return;
      }

      this.selectedFile.extension = file.name.split('.').pop();
      this.selectedFile.preview = URL.createObjectURL(file);
    },
  },
  async created() {
    await this.fetch();
    this.loaded = true;
  },
};
</script>
