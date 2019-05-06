<template>
  <div>
    <nav class="breadcrumb" aria-label="breadcrumbs">
      <ul>
        <li class="is-active">
          <a href="#" aria-current="page">Production Types</a>
        </li>
      </ul>
    </nav>

    <transition name="page">
      <section v-if="loaded">
        <div class="card">
          <div class="card-content">
            <div class="columns" v-if="productionTypes.length">
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
              hoverable
            >
              <template slot-scope="props">
                <b-table-column field="name" label="Name" width="250" sortable>{{ props.row.name }}</b-table-column>

                <b-table-column field label sortable>{{ }}</b-table-column>
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
      <form @submit.prevent="selectedProductionType.id ? updateProductionType() : createType()">
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">{{ modalTitle }} Type</p>
          </header>

          <section class="modal-card-body">
            <b-field
              label="Name"
              :type="{'is-info': errors.has('name')}"
              :message="errors.first('name')"
            >
              <b-input
                v-model="selectedProductionType.name"
                v-validate="'required|max:255'"
                name="name"
                autofocus
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
            <button class="button is-primary" :disabled="isLoading">{{ modalTitle }} Production Type</button>
          </footer>
        </div>
      </form>
    </b-modal>
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from "vuex";

export default {
  name: "ProductionTypes",
  data: () => ({
    loaded: false,
    perPage: 10,
    isModalActive: false,
    selectedProductionType: {},
    searchText: "",
  }),
  computed: {
    ...mapState('productionTypes', ['productionTypes', 'isLoading']),
    ...mapGetters('productionTypes', ['search']),

    filter: function() {
      return this.search(this.searchText);
    },

    modalTitle: function() {
      return this.selectedProductionType.id ? "Update" : "Create";
    }
  },
  methods: {
    ...mapActions('productionTypes', ['fetch', 'update', 'destroy']),
    ...mapActions('toast', ['showError']),

    confirmDelete(productionType) {
      this.selectedProductionType = productionType;
      this.$dialog.confirm({
        message: `Are you sure you want to delete "${this.selectedProductionType.name}"?`,
        confirmText: "Yes, I'm sure",
        type: 'is-success',
        hasIcon: true,
        onConfirm: this.deleteProductionType,
      });
    },

    showUpdateModal(productionType) {
      this.selectedProductionType = Object.assign({}, productionType);
      this.isModalActive = true;
    },

    async updateProductionType() {
      try {
        let valid = await this.$validator.validateAll();
        if (!valid) {
          this.showError("Please check the fields.");
          return;
        }

        await this.update(this.selectedProductionType);

        this.isModalActive = false;
      } catch (e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async deleteProductionType() {
      await this.destroy(this.selectedProductionType);
    }
  },

  async created() {
    await this.fetch();
    this.loaded = true;
  }
};
</script>
