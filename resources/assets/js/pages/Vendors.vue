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
              :data="vendors"
              :per-page="perPage"
              :loading="isLoading"
              :paginated="!!vendors.length"
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

                <b-table-column field="contact" label="Contact" sortable>{{ props.row.phoneNumber }}</b-table-column>
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
                <article class="media is-top">
                  <div class="w-1/2 mx-4">
                    <div class="mb-4">
                      <img class="max-w-xs h-24" :src="props.row.url_img">
                    </div>
                    <div class="content">
                      <p>
                        <strong>Location:</strong>
                        {{ props.row.location }}
                      </p>
                      <p>
                        <strong>Contact:</strong>
                        {{props.row.phoneNumber}}
                      </p>
                      <p>
                        <strong>Website:</strong>
                        {{ props.row.website_url }}
                      </p>
                    </div>
                  </div>
                  <div class="w-1/2 mx-4">
                    <div class="content">
                      <p>
                        <strong>Services:</strong>
                        <span v-html=" props.row.services"></span>
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
      <form @submit.prevent="selectedCategory.id ? updateCategory() : createCategory()">
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
            <label class="label">Cover image</label>
            <div class="columns is-vcentered pb-2">
              <p class="image">
                <img
                  :src="selectedFile.preview ? selectedFile.preview : (selectedVendor.url_img ? selectedVendor.url_img : 'test.png')"
                >
              </p>
              <b-field
                class="column file"
                :type="{'is-danger': errors.has('url_img')}"
                :message="errors.first('url_img')"
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
              label="Location"
              :type="{'is-danger': errors.has('location')}"
              :message="errors.first('location')"
            >
              <b-input
                v-model="selectedVendor.location"
                v-validate="'required'"
                name="location"
                autofocus
              />
            </b-field>
            <b-field
              label="Contact"
              :type="{'is-danger': errors.has('phoneNumber')}"
              :message="errors.first('phoneNumber')"
            >
              <b-input
                v-model="selectedVendor.phoneNumber"
                v-validate="'required'"
                name="phoneNumber"
                autofocus
              />
            </b-field>
            <b-field
              label="Website"
              :type="{'is-danger': errors.has('website_url')}"
              :message="errors.first('website_url')"
            >
              <b-input
                v-model="selectedVendor.website_url"
                v-validate="'required'"
                name="website_url"
                autofocus
              />
            </b-field>
            <div>
              <strong>Servicios</strong>
              <ckeditor :editor="editor" v-model="selectedVendor.services" :config="editorConfig"></ckeditor>
            </div>
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
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

export default {
  name: "Vendors",
  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: "",
    selectedVendor: {},
    isModalActive: false,
    selectedFile: {},
    editor: ClassicEditor,
    editorConfig: {},
    vendors: [
      {
        id: "1",
        title: "Professional Photography, Inc.",
        location: "123 Main Street, New York, NY 10018",
        phoneNumber: "(123) 456-7890",
        website_url: "professionalphotographyinc.com",
        services:
          "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p><br><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>",
        url_img:
          "https://cdn.evbstatic.com/s3-build/perm_001/7e2eb7/django/images/homepage/no-text/bg-desktop-generationdiy.jpg"
      },
      {
        id: "2",
        title: "Professional Photography, Inc.",
        location: "123 Main Street, New York, NY 10018",
        phoneNumber: "(123) 456-7890",
        websiteURL: "professionalphotographyinc.com",
        services:
          "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p><br><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>",
        url_img:
          "https://cdn.evbstatic.com/s3-build/perm_001/7e2eb7/django/images/homepage/no-text/bg-desktop-generationdiy.jpg"
      },
      {
        id: "3",
        title: "Professional Photography, Inc.",
        location: "123 Main Street, New York, NY 10018",
        phoneNumber: "(123) 456-7890",
        websiteURL: "professionalphotographyinc.com",
        services:
          "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p><br><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>",
        url_img:
          "https://cdn.evbstatic.com/s3-build/perm_001/7e2eb7/django/images/homepage/no-text/bg-desktop-generationdiy.jpg"
      }
    ]
  }),
  computed: {
    //...mapState('clients', ['clients', 'isLoading']),
    ...mapGetters("clients", ["search"]),
    modalTitle: function() {
      return this.selectedVendor.id ? "Update" : "Create";
    },

    filter: function() {
      return this.search(this.searchText);
    }
  },
  methods: {
    ...mapActions("clients", ["fetch", "broadcast", "notify", "destroy"]),

    // confirmBroadcast() {
    //   this.$dialog.prompt({
    //     message: "Type a message",
    //     inputAttrs: {
    //       placeholder: "Message",
    //       maxlenght: 2000
    //     },
    //     onConfirm: value => this.sendBroadcast(value)
    //   });
    // },
    // confirmNotification(client) {
    //   this.$dialog.prompt({
    //     message: "Type a message",
    //     inputAttrs: {
    //       placeholder: "Message",
    //       maxlenght: 2000
    //     },
    //     onConfirm: value => this.sendNotification(client, value)
    //   });
    // },
    confirmDelete(vendor) {
      this.selectedVendor = vendor;
      this.$dialog.confirm({
        message: `Are you sure you want to delete "${this.selectedVendor.title}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-success',
        hasIcon: true,
        onConfirm: this.deleteCategory,
      });
    },

    showUpdateModal(category) {
      this.selectedVendor = Object.assign({}, category);
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

    async deleteVendor() {
      await this.destroy(this.selectedVendor);
    }
  },
  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>
