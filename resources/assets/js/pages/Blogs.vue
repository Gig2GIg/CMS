<template>
  <div>
    <nav class="breadcrumb" aria-label="breadcrumbs">
      <ul>
        <li class="is-active">
          <a href="#" aria-current="page">{{ $options.title }}</a>
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
            Create Blog
          </button>
        </div>
        <div class="card">
          <div class="card-content">
            <div class="columns"  v-if="blogs">
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
                  sortable>{{ props.row.title }}</b-table-column>

                <b-table-column 
                  field="date"
                  label="Date"
                  sortable>{{ props.row.created_at }}</b-table-column>

                <!-- <b-table-column 
                  field="type"
                  label="Type"
                  sortable>{{ props.row.type }}</b-table-column> -->

                <b-table-column 
                  field="search_to"
                  label="Search to"
                  sortable>{{ props.row.search_to }}</b-table-column>

                <b-table-column field="actions" width="40">
                  <b-dropdown position="is-bottom-left">
                    <button class="button is-info" slot="trigger">
                      <b-icon icon="menu-down"></b-icon>
                    </button>
                    <div >  
                      <b-dropdown-item has-link >
                          <a @click.prevent.stop="showUpdateModal(props.row)">Edit</a>
                      </b-dropdown-item>
                    </div>
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
                      <img class="w-full" :src="props.row && props.row.url_media ? props.row.url_media : defaultImg" @error="defaultImg">
                    </div>
                    <div class="content">
                      <!-- <p>
                        <strong>Type:</strong>
                        {{ props.row.type }}
                      </p> -->
                      <p>
                        <strong>Body:</strong>
                        <span v-html=" props.row.body"></span>
                      </p>
                    </div>
                  </div>
                  <div class="w-1/2 mx-4">
                    <div class="content">
                      <p>
                        <strong>Time ago:</strong>
                        {{ props.row.time_ago }}
                      </p>
                    
                      <p>
                        <strong>By:</strong>
                        {{ props.row.name }}
                      </p>
                      <div>
                        <strong>Search To:</strong>
                        <span> {{ props.row.search_to }}</span>
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

    <b-modal :active.sync="isModalActive" has-modal-card :canCancel="!isLoading">
      <form @submit.prevent="selectedBlog.id ? updateBlog() : createBlog()">
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">{{ modalTitle }} Blog</p>
          </header>

          <section class="modal-card-body">
            <b-field
              label="Title"
              :type="{'is-info': errors.has('title')}"
              :message="errors.first('title')"
            >
              <b-input
                v-model="selectedBlog.title"
                v-validate="'required|max:255'"
                name="title"
                autofocus
              />
            </b-field>
            <b-field
              label="URl Media"
              :type="{'is-info': errors.has('url_media')}"
              :message="errors.first('url_media')"
            >
              <b-input
                v-model="selectedBlog.url_media"
                v-validate="'required|max:255'"
                name="url_media"
                autofocus
              />
            </b-field>
            <b-field
              label="Topic"
              :type="{'is-danger': errors.has('topic')}"
              :message="errors.first('topic')"
            >
              <b-select
                name="topics"
                v-model="selectedBlog.topic_id"
                v-validate="'required'"
                placeholder="Select a topic"
              >
                <option
                  v-for="topic in topics"
                  :value="topic.id"
                  :key="topic.id">
                  {{ topic.title }}
                </option>
              </b-select>
            </b-field>

            <!-- <b-field
              label="type"
              :type="{'is-danger': errors.has('type')}"
              :message="errors.first('type')"
            >
              <b-select
                name="types"
                v-model="selectedBlog.type"
                v-validate="'required'"
                placeholder="Select a type"
              >
                <option
                  v-for="type in ['blog', 'forum']"
                  :value="type"
                  :key="type">
                  {{ type }}
                </option>
              </b-select>
            </b-field> -->


            <b-field
              label="Search to"
              :type="{'is-danger': errors.has('type')}"
              :message="errors.first('type')"
            >
              <b-select
                name="search_to"
                v-model="selectedBlog.search_to"
                v-validate="'required'"
                placeholder="Select a search to"
              >
                <option
                  v-for="to in ['performance', 'director', 'both']"
                  :value="to"
                  :key="to">
                  {{ to }}
                </option>
              </b-select>
            </b-field>

            <div class="mb-6">
              <label class="label">Body</label>
              <ckeditor :editor="editor" v-model="selectedBlog.body" :config="editorConfig"></ckeditor>
            </div>

          </section>
          <footer class="modal-card-foot">
            <button
              class="button"
              type="button"
              :disabled="isLoading"
              @click="isModalActive = false"
            >Close</button>
            <button 
              class="button is-primary"
              :disabled="isLoading">{{ modalTitle }} blog</button>
          </footer>
        </div>
      </form>
    </b-modal>
  </div>
</template>

<script>
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import { mapActions, mapState, mapGetters } from "vuex";
import DEFINE from '../constant.js';

export default {
  name: "Blogs",
  data: () => ({
    loaded: false,
    perPage: 10,
    isModalActive: false,
    

    selectedBlog: {},
    searchText: "",
    

    editor: ClassicEditor,
    editorConfig: {}
  }),
  computed: {
    ...mapState('blogs', ['blogs', 'isLoading']),
    ...mapState('topics', ['topics']),
    ...mapGetters('blogs', ['search']),

    filter: function() {
      return this.search(this.searchText);
    },

    modalTitle: function() {
      return this.selectedBlog.id ? "Update" : "Create";
    }
  },
  methods: {
    ...mapActions('blogs', ['fetch', 'store', 'update', 'destroy']),
    ...mapActions('topics', {'fetchTopics' : 'fetch'}),
    ...mapActions('toast', ['showError']),

    confirmDelete(blog) {
      this.selectedBlog = blog;
      this.$dialog.confirm({
        message: `Are you sure you want to delete "${this.selectedBlog.title}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-success',
        hasIcon: true,
        onConfirm: this.deleteBlog,
      });
    },

    showCreateModal() {
      this.selectedBlog = {};
      this.isModalActive = true;
    },

    showUpdateModal(blog) {
      this.selectedBlog = Object.assign({}, blog);
      this.isModalActive = true;
    },

    

    async createBlog() {
      try {
        this.selectedBlog.type = "blog"; // store forum type blogs
        await this.store(this.selectedBlog);

        this.isModalActive = false;
      } catch(e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async updateBlog() {
      try {
        this.selectedBlog.type = "blog"; // store forum type blogs
        await this.update(this.selectedBlog);

        this.isModalActive = false;
      } catch (e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async deleteBlog() {
      console.log(this.selectedBlog);
      await this.destroy(this.selectedBlog);
    },
    defaultImg(event){
      event.target.src = DEFINE.no_img_placeholder;
      
    }
  },

  async created() {
    await this.fetch();
    await this.fetchTopics();
    this.loaded = true;
  }
};
</script>
