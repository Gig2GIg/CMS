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
            <div class="columns" v-if="rentals.length">
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
              :paginated="!!rentals.length"
              :show-detail-icon="true"
              detail-key="id"
              detailed
              hoverable
            >
              <template slot-scope="props">
                <b-table-column field="product.name" label="Product" width="250" sortable>
                  {{ props.row.product.name }}
                </b-table-column>

                <b-table-column field="client_name" label="Client" sortable>
                  {{ props.row.client_name }}
                </b-table-column>

                <b-table-column field="product.store_name" label="Seller" sortable>
                  {{ props.row.product.store_name }}
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

                <b-table-column field="created_at" label="Created" sortable>
                  {{ props.row.created_at.date | moment('DD MMM YYYY') }}
                </b-table-column>
              </template>

              <template slot="detail" slot-scope="props">
                <article class="media is-top">
                  <figure class="media-left">
                    <p class="image is-64x64 overflow-hidden">
                      <img :src="props.row.product.image.url">
                    </p>
                  </figure>
                  <div class="media-content">
                    <div class="content">
                      <p>
                        <strong>Total Amount:</strong>
                        $ {{ props.row.total_amount }}
                      </p>
                      <p>
                        <strong>Tax:</strong>
                        $ {{ props.row.tax }}
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
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from 'vuex';

export default {
  name: 'Rentals',
  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: '',
    selectedRental: {},
  }),
  computed: {
    ...mapState('rentals', ['rentals', 'isLoading']),
    ...mapGetters('rentals', ['search']),

    filter: function() {
      return this.search(this.searchText);
    },
  },
  methods: {
    ...mapActions('rentals', ['fetch', 'destroy']),

    confirmDelete(rental) {
      this.selectedRental = rental;

      this.$dialog.confirm({
        message: `Are you sure you want to delete "${this.selectedRental.name}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-danger',
        hasIcon: true,
        onConfirm: this.deleteRental,
      });
    },

    async deleteRental() {
      await this.destroy(this.selectedRental);
    },
  },
  async created() {
    await this.fetch();
    this.loaded = true;
  },
};
</script>
