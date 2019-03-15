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
            <div class="columns" v-if="submissions.length">
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
              :data="submissions"
              :per-page="perPage"
              :loading="isLoading"
              :paginated="!!submissions.length"
              :show-detail-icon="true"
              detail-key="id"
              detailed
              hoverable
            >
              <template slot-scope="props">
                <b-table-column
                  field="name"
                  label="Business name"
                  width="250"
                  sortable
                >{{ props.row.business_name }}</b-table-column>
                <b-table-column field="phone" label="Phone" sortable>{{ props.row.phone_number }}</b-table-column>
                <b-table-column field="email" label="Email" sortable>{{ props.row.email }}</b-table-column>
                <b-table-column field="actions" width="40">
                  <b-dropdown position="is-bottom-left">
                    <button class="button is-info" slot="trigger">
                      <b-icon icon="menu-down"></b-icon>
                    </button>
                    <b-dropdown-item has-link>
                      <a @click.prevent.stop="confirmAccepted(props.row)">Accepted</a>
                    </b-dropdown-item>
                    <b-dropdown-item has-link>
                      <a @click.prevent.stop="confirmDenied(props.row)">Denied</a>
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
                        <strong>Address:</strong>
                        {{ props.row.address }}
                      </p>
                      <p>
                        <strong>Phone:</strong>
                        {{props.row.phone_number}}
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
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from "vuex";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

export default {
  name: "Business Submissions",
  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: "",
    selectedSubmission: {},
    isModalActive: false,
    selectedFile: {},
    editor: ClassicEditor,
    editorConfig: {},
    submissions: [
      {
        id: "1",
        business_name: "Professional Photography, Inc.",
        address: "123 Main Street, New York, NY 10018",
        phone_number: "(123) 456-7890",
        website_url: "professionalphotographyinc.com",
        email: "professional@photographyinc.com",
        services:
          "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p><br><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>",
        url_img:
          "https://cdn.evbstatic.com/s3-build/perm_001/7e2eb7/django/images/homepage/no-text/bg-desktop-generationdiy.jpg"
      },
      {
        id: "2",
        business_name: "Professional Photography, Inc.",
        address: "123 Main Street, New York, NY 10018",
        phone_number: "(123) 456-7890",
        website_url: "professionalphotographyinc.com",
        email: "professional@photographyinc.com",
        services:
          "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p><br><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>",
        url_img:
          "https://cdn.evbstatic.com/s3-build/perm_001/7e2eb7/django/images/homepage/no-text/bg-desktop-generationdiy.jpg"
      },
      {
        id: "3",
        business_name: "Professional Photography, Inc.",
        address: "123 Main Street, New York, NY 10018",
        phone_number: "(123) 456-7890",
        website_url: "professional@photographyinc.com",
        email: "professional@photographyinc.com",
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
    filter: function() {
      return this.search(this.searchText);
    }
  },
  methods: {
    ...mapActions("clients", ["fetch", "broadcast", "notify", "destroy"]),
   
    confirmAccepted(vendor) {
      this.selectedSubmission = vendor;
      this.$dialog.confirm({
        message: `Are you sure you want to accept "${this.selectedSubmission.title}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-success',
        hasIcon: true,
        onConfirm: this.deleteCategory,
      });
    },
    confirmDenied(vendor) {
      this.selectedSubmission = vendor;
      this.$dialog.confirm({
        message: `Are you sure you want to denied "${this.selectedSubmission.title}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-success',
        hasIcon: true,
        onConfirm: this.deniedSubmission,
      });
    },

    async deniedSubmission() {
      await this.destroy(this.deniedSubmission);
    }
  },
  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>
