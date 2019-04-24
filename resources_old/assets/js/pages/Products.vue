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
            <div class="columns" v-if="products.length">
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
              :paginated="!!products.length"
              :show-detail-icon="true"
              detail-key="id"
              detailed
              hoverable
            >
              <template slot-scope="props">
                <b-table-column field="name" label="Name" width="250" sortable>
                  {{ props.row.name }}
                </b-table-column>

                <b-table-column field="store_name" label="Seller" sortable>
                  {{ props.row.store_name }}
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
                  <div class="media-content address-content">
                    <div class="content">
                      <p>
                        <strong>Description:</strong>
                        {{ props.row.description }}
                      </p>
                      <p>
                        <strong>Condition:</strong>
                        {{ props.row.condition }}
                      </p>
                      <p>
                        <strong>Size:</strong>
                        {{ props.row.size }}
                      </p>
                    </div>
                  </div>
                  <div class="media-content address-content">
                    <div class="content">
                      <p>
                        <strong>Favorites count:</strong>
                        {{ props.row.favorites_count }}
                      </p>
                      <strong>Images:</strong>
                      <div class="flex images-content">
                        <p class="image is-64x64 overflow-hidden" v-for="image in props.row.images" v-bind:key="image.id">
                          <img :src="image.url">
                        </p>
                      </div>
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
  name: 'Products',
  data: () => ({
    loaded: false,
    perPage: 10,
    searchText: '',
    selectedProduct: {},
  }),
  computed: {
    ...mapState('products', ['products', 'isLoading']),
    ...mapGetters('products', ['search']),

    filter: function() {
      return this.search(this.searchText);
    },
  },
  methods: {
    ...mapActions('products', ['fetch', 'update', 'destroy']),

    confirmStatus(product) {
      this.selectedProduct = Object.assign({}, product);

      const status = this.selectedProduct.status === 'active';
      const action = status ? 'disable' : 'enable';
      const type = status ? 'is-danger' : 'is-success';

      this.selectedProduct.status =
        this.selectedProduct.status === 'active' ? 'down' : 'active';

      this.$dialog.confirm({
        message: `Are you sure you want to ${action} "${this.selectedProduct.name}"?`,
        confirmText: "Yes, I'm sure",
        type,
        hasIcon: true,
        onConfirm: this.toggleProductStatus,
      });
    },

    confirmDelete(product) {
      this.selectedProduct = product;

      this.$dialog.confirm({
        message: `Are you sure you want to delete "${this.selectedProduct.name}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-danger',
        hasIcon: true,
        onConfirm: this.deleteProduct,
      });
    },

    async toggleProductStatus() {
      await this.update(this.selectedProduct);
    },

    async deleteProduct() {
      await this.destroy(this.selectedProduct);
    },
  },
  async created() {
    await this.fetch();
    this.loaded = true;
  },
};
</script>
