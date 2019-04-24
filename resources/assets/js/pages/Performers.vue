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
            @click="confirmBroadcast"
          >
            Broadcast notification
          </button>
        </div>
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
              :data="performers"
              :per-page="perPage"
              :loading="isLoading"
              :paginated="!!performers.length"
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
                >{{ props.row.name }}</b-table-column>

                <b-table-column field="date" label="City" sortable>{{ props.row.city }}</b-table-column>

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
                      <img class="w-24 h-24" :src="props.row.avatar">
                    </div>
                    <div class="content">
                      <p>
                        <strong>City:</strong>
                        {{ props.row.city }}
                      </p>
                      <div class="border-grey-light border-t mb-4"></div>
                      <div>
                        <h4>Media files</h4>    
                         <a
                          class="block mb"
                          v-for="(item, index) in props.row.files"
                          :href="item.url"
                          :key="index"
                          download
                        >{{item.name}}</a>
                      </div>
                    </div>
                  </div>
                  <div class="w-1/2 mx-4">
                    <div class="content">
                      <h3>Feedback</h3>
                      <p class="flex items-center">
                        <strong>Instant Feedback:</strong>
                        <img src="/storage/feedback/i2.png" class="w-6 h-6 mx-2 inline" alt>
                      </p>
                      <p>
                        <strong>Call Back:</strong>
                        {{props.row.callBack === '0' ? 'No' : 'Yes'}}
                      </p>
                      <p>
                        <strong>Work On:</strong>
                        {{ props.row.workOn }}
                      </p>
                      <p>
                        <strong>Comment:</strong>
                        <span v-html=" props.row.comment_feedback"></span>
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
    <!-- <b-modal :active.sync="isModalActive" has-modal-card :canCancel="!isLoading">
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
    </b-modal>-->
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
    selectedClient: {},
    isModalActive: false,
    performers: [
      {
        id: "p01",
        name: "Greg Smith",
        avatar:
          "https://cactusthemes.com/blog/wp-content/uploads/2018/01/tt_avatar_small.jpg",
        city: "New York, NY",
        instantFeedback: "1",
        comment_feedback: "<p>We love your energy and enthusiasm!!</p>",
        callBack: "0",
        workOn: "Vocals",
        photo:
          "https://cdn.evbstatic.com/s3-build/perm_001/7e2eb7/django/images/homepage/no-text/bg-desktop-generationdiy.jpg",
        files:[
          {name:'pdf example', url:'/storage/example.pdf'},
          {name: 'img example', url:'/storage/logo.png'},          
        ]
      },
      {
        id: "p02",
        name: "David Doe",
        avatar:
          "https://cactusthemes.com/blog/wp-content/uploads/2018/01/tt_avatar_small.jpg",
        city: "New York, NY",
        instantFeedback: "1",
        comment_feedback: "<p>We love your energy and enthusiasm!!</p>",
        callBack: "0",
        workOn: "Vocals",
        photo:
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
        message: 'Type a message',
        inputAttrs: {
          placeholder: 'Message',
          maxlenght: 2000
        },
        onConfirm: (value) => this.sendNotification(client, value),
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
      await this.destroy(this.selectedClient);
    }
  },
  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>
