<template>
  <div>
    <nav class="breadcrumb" aria-label="breadcrumbs">
      <ul>
        <li class="is-active">
          <a href="#" aria-current="page">Marketplace Vendors</a>
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
            Create vendor
          </button>
        </div>

        <div class="card">
          <div class="card-content">
            <div class="columns" v-if="vendors.length">
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
                  label="Performer"
                  width="250"
                  sortable
                >{{ props.row.title }}</b-table-column>

                <b-table-column field="phone_number" label="Contact" sortable>{{ props.row.phone_number }}</b-table-column>
                <b-table-column field="featured" label="Featured" sortable>{{ props.row.featured }}</b-table-column>

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
                    <div v-if="props.row.featured == 'no'">
                      <b-dropdown-item has-link>
                      <a @click.prevent.stop="confirmFeatured(props.row)">Make Featured</a>
                    </b-dropdown-item>
                    </div>
                     <div v-else>
                      <b-dropdown-item has-link>
                      <a @click.prevent.stop="confirmNotFeatured(props.row)">Make Not Featured</a>
                    </b-dropdown-item>
                    </div>
                  </b-dropdown>
                </b-table-column>
              </template>

              <template slot="detail" slot-scope="props">
                <article class="media is-top">
                  <div class="w-1/2 mx-4">
                    <div class="mb-4">
                      <img class="max-w-xs h-24" :src="props.row.image.url">
                    </div>
                    <div class="content">
                      <p>
                        <strong>Address:</strong>
                        {{props.row.address}}
                      </p>
                      <p>
                        <strong>Contact:</strong>
                        {{props.row.phone_number}}
                      </p>
                      <p>
                        <strong>Website:</strong>
                        {{ props.row.url_web }}
                      </p>
                    </div>
                  </div>
                  <div class="w-1/2 mx-4">
                    <div class="content">
                      <p>
                        <strong>Services:</strong>
                        <span v-html=" props.row.services"></span>
                      </p>
                      <p>
                        <strong>Featured:</strong>
                        <span v-html=" props.row.featured"></span>
                      </p>
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
    <b-modal :active.sync="isModalActive" has-modal-card :canCancel="!isLoading">
      <form @submit.prevent="selectedVendor.id ? updateVendor() : createVendor()">
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">{{ modalTitle }} Vendor</p>
          </header>

          <section class="modal-card-body">
            <b-field
              label="Title"
              :type="{'is-danger': errors.has('title')}"
              :message="errors.first('title')"
            >
              <b-input
                v-model="selectedVendor.title"
                v-validate="'required|max:255'"
                name="title"
                autofocus
              />
            </b-field>

            <b-field
              label="Email"
              :type="{'is-danger': errors.has('email')}"
              :message="errors.first('email')"
            >
              <b-input
                v-model="selectedVendor.email"
                v-validate="'email'"
                name="email"
              />
            </b-field>

            <b-field
              label="Category"
              :type="{'is-danger': errors.has('category')}"
              :message="errors.first('category')"
            >
              <b-select
                name="category"
                v-model="selectedVendor.marketplace_category_id"
                v-validate="''"
                placeholder="Select a category"
              >
                <option
                  v-for="category in categories"
                  :value="category.id"
                  :key="category.id">
                  {{ category.name }}
                </option>
              </b-select>
            </b-field>

            <b-field
              label="Featured"
              :type="{'is-danger': errors.has('featured')}"
              :message="errors.first('featured')"
            >
              <b-select
                name="featured"
                v-model="selectedVendor.featured"
                v-validate="''"
                placeholder="Select a "
              >
                <option
                  v-for="f in ['yes', 'no']"
                  :value="f"
                  :key="f">
                  {{ f }}
                </option>
              </b-select>
            </b-field>

            <label class="label">Cover image</label>
            <div class="columns is-vcentered pb-2">
              <p class="image vendor-image">
                <img
                  :src="selectedFile.preview ? selectedFile.preview : (selectedVendor.image ? selectedVendor.image.url : 'images/default.jpg')"
                >
              </p>
              <b-field
                class="column file"
                :type="{'is-danger': errors.has('image')}"
                :message="errors.first('image')"
              >
                <b-upload
                  v-model="selectedFile.file"
                  accept=".png, .jpg, .jpeg"
                  :required="!selectedVendor.id"
                  @input="fileChanged"
                >
                  <a class="button is-primary">
                    <b-icon icon="upload"></b-icon>
                    <span>Click to upload</span>
                  </a>
                </b-upload>
                <span class="file-name" v-if="selectedFile.file">{{ selectedFile.file.name }}</span>
              </b-field>
            </div>
            <b-field
              label="Address"
              :type="{'is-danger': errors.has('address')}"
              :message="errors.first('address')"
            >
              <b-input
                v-model="selectedVendor.address"
                v-validate="'max:255'"
                name="address"
              />
            </b-field>
            <b-field
              label="Contact"
              :type="{'is-danger': errors.has('phone')}"
              :message="errors.first('phone')"
            >
              <b-input
                v-model="selectedVendor.phone_number"
                name="phone"
              />
            </b-field>
            <b-field
              label="Website"
              :type="{'is-danger': errors.has('website')}"
              :message="errors.first('website')"
            >
              <b-input
                v-model="selectedVendor.url_web"
                v-validate="'required|url'"
                name="website"
              />
            </b-field>
            <b-field
              label="Services"
              :type="{'is-info': errors.has('services')}"
              :message="errors.first('services')"
            >
              <b-input
                v-model="selectedVendor.services"
                v-validate="'required|max:500'"
                type="textarea"
                name="services"
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
            <button class="button is-primary" :disabled="isLoading">{{ modalTitle }} vendor</button>
          </footer>
        </div>
      </form>
    </b-modal>
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from "vuex";

export default {
  name: "Vendors",
  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: "",
    selectedVendor: {},
    isModalActive: false,
    selectedFile: {},
  }),
  computed: {
    ...mapState('vendors', ['vendors', 'isLoading']),
    ...mapState('categories', ['categories']),
    ...mapGetters('vendors', ['search']),

    modalTitle: function() {
      return this.selectedVendor.id ? "Update" : "Create";
    },

    filter: function() {
      return this.search(this.searchText);
    }
  },
  methods: {
    ...mapActions('vendors', ['fetch', 'store', 'update', 'destroy', 'updateFeatured', 'updateNotFeatured']),
    ...mapActions({getCategories : 'categories/fetch'}),
    ...mapActions('toast', ['showError']),

    confirmDelete(vendor) {
      this.selectedVendor = vendor;
      this.$dialog.confirm({
        message: `Are you sure you want to delete "${this.selectedVendor.title}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-success',
        hasIcon: true,
        onConfirm: this.deleteVendor,
      });
    },

  confirmFeatured(vendor) {
      this.selectedVendor = vendor;
      this.$dialog.confirm({
        message: `Are you sure you want to make featured  "${this.selectedVendor.title}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-success',
        hasIcon: true,
        onConfirm: this.makeFaturedVendor,
      });
    },

  confirmNotFeatured(vendor) {
      this.selectedVendor = vendor;
      this.$dialog.confirm({
        message: `Are you sure you want to make not featured  "${this.selectedVendor.title}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-success',
        hasIcon: true,
        onConfirm: this.makeFaturedNotVendor,
      });
    },


    showCreateModal() {
      this.selectedFile = {};
      this.selectedVendor = {};
      this.isModalActive = true;
    },

    showUpdateModal(vendor) {
      this.selectedVendor = Object.assign({}, vendor);
      this.isModalActive = true;
    },

    fileChanged(file) {
      if (!file || file.size > 4000000) {
        this.selectedFile = {};
        return;
      }

      this.selectedFile.extension = file.name.split(".").pop();
      this.selectedFile.preview = URL.createObjectURL(file);
    },

    async makeFaturedVendor() {
      await this.updateFeatured(this.selectedVendor);
    },

   async makeFaturedNotVendor() {
      await this.updateNotFeatured(this.selectedVendor);
    },


    async createVendor() {
      try {
        let valid = await this.$validator.validateAll();

        if (! valid) {
          this.showError('Please check the fields.');
          return;
        }

        await this.store({
          vendor: this.selectedVendor,
          imageData: this.selectedFile,
        });

        this.isModalActive = false;
      } catch(e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async updateVendor() {
      try {
        let valid = await this.$validator.validateAll();

        if (! valid) {
          this.showError('Please check the fields.');
          return;
        }

        await this.update({
          vendor: this.selectedVendor,
          imageData: this.selectedFile,
        });

        this.isModalActive = false;
      } catch(e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async deleteVendor() {
      await this.destroy(this.selectedVendor);
      await this.destroyFirebase(this.selectedVendor);
    }
  },

  async created() {
    await this.fetch();
    await this.getCategories();    
    this.loaded = true;
  }
};
</script>
