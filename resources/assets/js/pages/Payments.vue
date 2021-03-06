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
                  field="user.details.first_name"
                  label="Name"
                  width="250"
                  sortable
                >{{ props.row.user.details.first_name }} {{ props.row.user.details.last_name }}</b-table-column>

                <b-table-column field="amount" label="Amount" sortable>${{ props.row.amount }}</b-table-column>
                <b-table-column field="updated_at" label="Date" sortable>{{ props.row.updated_at }}</b-table-column>

              </template>

              <template slot="detail" slot-scope="props">
                <article class="media is-top">
                  <div class="w-1/2 mx-4">
                    <div class="content">
                      <p>
                        <strong>Performer: </strong>
                        {{ props.row.user.details.first_name }} {{ props.row.user.details.last_name }}
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
                        {{ props.row.updated_at}}
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
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from "vuex";

export default {
  name: "Payments",

  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: "",
    isModalActive: false,
  }),

  computed: {
    ...mapState('payments', ['payments', 'isLoading']),
    ...mapGetters('payments', ['search']),

    filter: function() {
      return this.search(this.searchText);
    }
  },

  methods: mapActions('payments', ['fetch']),

  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>
