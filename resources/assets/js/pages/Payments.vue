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
            <div class="columns" v-if="payments.length">
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
              :data="payments"
              :per-page="perPage"
              :loading="isLoading"
              :paginated="!!payments.length"
              :show-detail-icon="true"
              detail-key="id"
              detailed
              hoverable
            >
              <template slot-scope="props">
                <b-table-column
                  field="name"
                  label="Name"
                  width="250"
                  sortable
                >{{ props.row.performer }}</b-table-column>

                <b-table-column field="amount" label="Amount" sortable>${{ props.row.amount }}</b-table-column>
                <b-table-column field="date" label="Date" sortable>{{ props.row.date }}</b-table-column>

               
              </template>

              <template slot="detail" slot-scope="props">
                <article class="media is-top">
                  <div class="w-1/2 mx-4">
                    <div class="content">
                      <p>
                        <strong>Performer: </strong>
                        {{ props.row.performer}}
                      </p>
                      <p>
                        <strong>Stripe ID: </strong>
                        {{ props.row.stripe_id}}
                      </p>
                      <p>
                        <strong>Amount: </strong>
                        ${{ props.row.amount}}
                      </p>
                      <p>
                        <strong>Date: </strong>
                        {{ props.row.date}}
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
            <p class="modal-card-title">Update Subscription</p>
          </header>

          <section class="modal-card-body">
            <b-field
              label="Title"
              :type="{'is-danger': errors.has('title')}"
              :message="errors.first('title')"
            >
              <b-input
                v-model="selectedSubscription.performer"
                v-validate="'required|max:255'"
                name="title"
                autofocus
              />
            </b-field>
            <b-field
              label="Tier"
              :type="{'is-danger': errors.has('tier')}"
              :message="errors.first('tier')"
            >
              <b-select
                placeholder="Select a name"
                name="tier"
                v-model="selectedSubscription.tier"
                v-validate="'required'"
                autofocus
                expanded
              >
                <option
                  v-for="option in options"
                  :value="option.name"
                  :key="option.id"
                >{{ option.name }}</option>
              </b-select>
            </b-field>
          </section>
          <footer class="modal-card-foot">
            <button
              class="button"
              type="button"
              :disabled="isLoading"
              @click="isModalActive = false"
            >Close</button>
            <button class="button is-primary" :disabled="isLoading">Update subscription</button>
          </footer>
        </div>
      </form>
    </b-modal>
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from "vuex";

export default {
  name: "Subscriptions",
  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: "",
    isModalActive: false,
    selectedSubscription: {},
    options: [{ id: "1", name: "Paid" }, { id: "2", name: "Free" }],
    payments: [
      {
        id: "1",
        performer: "David Doe",
        date: "2019-06-01",
        amount: "30.00",
        stripe_id: "us_090991"
      },
      {
        id: "2",
        performer: "Greg Smith",
        date: "2019-06-01",
        amount: "30.00",
        stripe_id: "us_090992"
      },
      {
        id: "3",
        performer: "Performer",
        date: "2019-06-01",
        amount: "30.00",
        stripe_id: "us_090993"
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
    showUpdateModal(category) {
      this.selectedSubscription = Object.assign({}, category);
      this.isModalActive = true;
    },
    showSubscription() {
      this.selectedFile = {};
      this.selectedCategory = {
        name: null,
        url_img: null
      };
      this.isModalActive = true;
    }
  },
  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>