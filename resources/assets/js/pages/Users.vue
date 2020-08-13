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
            <!-- <div class="columns" v-if="userList.data.length"> -->
            <div class="columns">
              <b-field class="column">
                <b-input v-model="searchText" placeholder="Search..." icon="magnify" type="search" @input="searchUsers" />
              </b-field>

              <b-field class="column" position="is-right" grouped>
                <b-select v-model="perPage" @input="perPageChage">
                  <option value="5">5 per page</option>
                  <option value="10">10 per page</option>
                  <option value="15">15 per page</option>
                  <option value="20">20 per page</option>
                </b-select>
              </b-field>
            </div>

            <b-table
              :data="userList.data"
              :loading="!loaded"
              paginated
              detailed
              hoverable
              backend-pagination
              :total="total"
              :per-page="perPage"
              :current-page="page"
              @page-change="onPageChange"              
              backend-sorting
              :default-sort-direction="defaultSortOrder"
              :default-sort="[sortField, sortOrder]"
              @sort="onSort"
            >
            <!-- aria-next-label="Next page"
              aria-previous-label="Previous page"
              aria-page-label="Page"
              aria-current-label="Current page" -->
              <template slot-scope="props" v-if="props.row.details">
                <b-table-column
                  field="email"
                  label="Email"
                  width="60"
                  sortable
                >{{ props.row.email }}</b-table-column>
                <b-table-column
                  field="user_details.first_name"
                  label="First Name"
                  width="100"
                  sortable
                >{{ props.row.details.first_name ? props.row.details.first_name : ""}}</b-table-column>
                <b-table-column
                  field="user_details.last_name"
                  label="Last Name"
                  width="100"
                  sortable
                >{{ props.row.details.last_name ? props.row.details.last_name : "" }}</b-table-column>

                <b-table-column
                  field="is_active"
                  label="Status"
                  width="100"
                  sortable
                >{{ props.row.is_active ? 'Active' : 'De-active' }}</b-table-column>

                <b-table-column
                  field="user_details.type"
                  label="Type"
                  width="100"
                  sortable
                >{{ user_type[props.row.details.type] ? user_type[props.row.details.type] : '' }}</b-table-column>

                <b-table-column width="100" field="user_details.created_at" label="Date" sortable>{{ props.row.details.created_at | dateFormat}}</b-table-column>

                <b-table-column field="actions" width="40">
                  <b-dropdown position="is-bottom-left">
                    <button class="button is-info" slot="trigger">
                      <b-icon icon="menu-down"></b-icon>
                    </button>
                    <b-dropdown-item has-link >
                          <a @click.prevent.stop="showUpdateModal(props.row)">Edit</a>
                    </b-dropdown-item>
                    <b-dropdown-item has-link>
                      <a @click.prevent.stop="confirmDelete(props.row)">Delete</a>
                    </b-dropdown-item>
                    <b-dropdown-item has-link>
                      <a @click.prevent.stop="confirmstatusChange(props.row)">{{ props.row.is_active ? 'De-active' : 'Active' }}</a>
                    </b-dropdown-item>
                  </b-dropdown>
                </b-table-column>
              </template>

              <template slot="detail" slot-scope="props">
                <article class="media is-top">
                  <div class="w-1/2 mx-4">
                    <div class="mb-4 user-avatar">
                      <img class="w-full " :src="props.row.image && props.row.image.url ? props.row.image.url : props.row.image ? props.row.image : defaultImg" @error="defaultImg" >
                    </div>
                    <div class="content">
                      <p>
                        <strong>Birth Date:</strong>
                        {{ props.row.details.birth | birthDateFormat }}
                      </p>                     
                      
                    </div>
                  </div>
                  <div class="w-1/2 mx-4">
                    <div class="content">
                      <template v-if="props.row.details.type == 1">
                        <p>
                          <strong>Job Title:</strong>
                          <span v-html=" props.row.details.profesion"></span>
                        </p>
                         <p>
                          <strong>Agency Name:</strong>
                          <span v-html=" props.row.details.agency_name"></span>
                        </p>     
                      </template>
                      <template v-if="props.row.details.type == 2">
                        <p>
                          <strong>Stage Name:</strong>
                          <span v-html=" props.row.details.stage_name"></span>
                        </p>
                        <p>
                          <strong>Profesion:</strong>
                          <span v-html=" props.row.details.profesion"></span>
                        </p>
                        <p>
                          <strong>URL:</strong>
                          <span v-html=" props.row.details.url"></span>
                        </p>
                         <p>
                            <strong>Gender:</strong>
                            <span v-html=" props.row.details.gender"></span>
                          </p>
                          <p v-if="props.row.details && props.row.details.gender == 'self describe'">
                            <strong>Self Describe:</strong>
                            <span v-html=" props.row.details.gender_desc"></span>
                          </p>
                      </template>
                      
                                      
                      <p>
                        <strong>Address:</strong>
                        <span v-html=" props.row.details.address"></span>
                      </p>
                      <p>
                        <strong>city:</strong>
                        <span v-html=" props.row.details.city"></span>
                      </p>
                      <p>
                        <strong>State:</strong>
                        <span v-html=" getStateName(props.row.details.state)"></span>
                      </p>
                      <p>
                        <strong>Zip:</strong>
                        <span v-html=" props.row.details.zip"></span>
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
      <form @submit.prevent="updateUser()" v-if="isModalActive">
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">Update User</p>
          </header>

          <section class="modal-card-body">
            <b-field
              label="Profile Picture"
            >
            </b-field>
            <b-field
              :type="{'is-info': errors.has('image')}"
              :message="errors.first('image')"              
            >
              <b-upload v-model="profile_file" accept=".png, .jpg, .jpeg">
                  <a class="button is-primary">
                      <b-icon icon="upload"></b-icon>
                      <span>Click to upload</span>
                  </a>
              </b-upload>
              <span class="file-name" v-if="profile_file">
                  {{ profile_file.name ? profile_file.name : image.name ? image.name : '' }}
              </span>
            </b-field>

            <b-field
              label="First Name"
              :type="{'is-info': errors.has('first_name')}"
              :message="errors.first('first_name')"              
            >
              <b-input
                v-model="selectedUser.details.first_name"
                v-validate="'required|max:255'"
                name="first_name"
                data-vv-as="first name"
                autofocus
              />
            </b-field>
            
            <b-field
              label="Last Name"
              :type="{'is-info': errors.has('last_name')}"
              :message="errors.first('last_name')"
            >
              <b-input
                v-model="selectedUser.details.last_name"
                v-validate="'required|max:255'"
                name="last_name"
                data-vv-as="last name"
                autofocus
              />
            </b-field>

            <b-field
              label="Email"
              :type="{'is-info': errors.has('email')}"
              :message="errors.first('email')"
            >
              <b-input
                v-model="selectedUser.email"
                v-validate="'required'"
                name="email"
                v-bind:disabled="true"
                autofocus
              />
            </b-field>

            <b-field
              label="Birth date"
              :type="{'is-info': errors.has('birth')}"
              :message="errors.first('birth')"
            >
              <b-datepicker
                :show-week-number="showWeekNumber"
                v-model="selectedUser.details.birth"
                v-validate="'required'"
                name="birth"
                autofocus
                placeholder="Click to select..."
                icon="calendar-today"
                data-vv-as="birth date"
                >
              </b-datepicker>
            </b-field>

            <!-- Casting user fields update only when caster user -->
            <template v-if="selectedUser.details.type == 1">
              <b-field              
                label="Job Title"
                :type="{'is-info': errors.has('profesion')}"
                :message="errors.first('profesion')"
              >
                <b-input
                  v-model="selectedUser.details.profesion"
                  v-validate="'required|max:255'"
                  name="profesion"
                  data-vv-as="job title"
                  autofocus
                />
              </b-field>

              <b-field              
                  label="Agency Name"
                  :type="{'is-info': errors.has('agency_name')}"
                  :message="errors.first('agency_name')"
              >
                <b-input
                  v-model="selectedUser.details.agency_name"
                  v-validate="'required|max:255'"
                  name="agency_name"
                  data-vv-as="agency name"
                  autofocus
                />
              </b-field>                

            </template>

            <!-- Performer user fields update only when Performer user -->
            <template v-if="selectedUser.details.type == 2">

              <b-field              
                  label="Stage Name"
                  :type="{'is-info': errors.has('stage_name')}"
                  :message="errors.first('stage_name')"
              >
                <b-input
                  v-model="selectedUser.details.stage_name"
                  v-validate="'required|max:255'"
                  name="stage_name"
                  data-vv-as="stage name"
                  autofocus
                />
              </b-field>

              <b-field              
                  label="Professional / Working Title"
                  :type="{'is-info': errors.has('profesion')}"
                  :message="errors.first('profesion')"
              >
                <b-input
                  v-model="selectedUser.details.profesion"
                  v-validate="'required|max:255'"
                  name="profesion"
                  data-vv-as="working title"
                  autofocus
                />
              </b-field>   

              <b-field              
                  label="Personal Website"
                  :type="{'is-info': errors.has('url')}"
                  :message="errors.first('url')"
              >
                <b-input
                  v-model="selectedUser.details.url"
                  v-validate="'required|url'"
                  name="url"
                  data-vv-as="personal website"
                  autofocus
                />
              </b-field>  

              <b-field
                label="Gender"
                :type="{'is-danger': errors.has('gender')}"
                :message="errors.first('gender')"
              >
                <b-select
                  name="gender"
                  v-model="selectedUser.details.gender"
                  v-validate="'required'"
                  placeholder="Select a gender"
                >
                  <option
                    v-for="gender in genderList"
                    :key="gender.id"
                    :value="gender.id"
                  >
                    {{ gender.value }}
                  </option>
                </b-select>
              </b-field>

              <b-field           
                  v-if="selectedUser.details.gender == 'self describe'"   
                  label="Self Describe"
                  :type="{'is-info': errors.has('gender_desc')}"
                  :message="errors.first('gender_desc')"
              >
                <b-input
                  v-model="selectedUser.details.gender_desc"
                  v-validate="'required|max:255'"
                  name="gender_desc"
                  data-vv-as="self describe"
                  autofocus
                />
              </b-field>

            </template>

            <b-field
              label="Address"
              :type="{'is-info': errors.has('address')}"
              :message="errors.first('address')"
            >
              <b-input
                v-model="selectedUser.details.address"
                v-validate="'required|max:300'"
                name="address"
                data-vv-as="address"
                autofocus
              />
            </b-field>

            <b-field
              label="City"
              :type="{'is-info': errors.has('city')}"
              :message="errors.first('city')"
            >
              <b-input
                v-model="selectedUser.details.city"
                v-validate="'required|max:255'"
                name="city"
                data-vv-as="city"
                autofocus
              />
            </b-field>

            <b-field
              label="State"
              :type="{'is-danger': errors.has('state')}"
              :message="errors.first('state')"
            >
              <b-select
                name="state"
                v-model="selectedUser.details.state"
                v-validate="'required'"
                placeholder="Select a state"
              >
               <option
                  v-for="state in states"
                  :key="state.value"
                  :value="state.value"
                >
                  {{ state.label }}
                </option>
              </b-select>
            </b-field>

            <b-field
              label="Zip Code"
              :type="{'is-info': errors.has('zip')}"
              :message="errors.first('zip')"
            >
              <b-input
                v-model="selectedUser.details.zip"
                v-validate="'required|integer|max:5'"
                name="zip"
                data-vv-as="zip code"
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
            <button 
              class="button is-primary"
              :disabled="isLoading" type="submit">Update User</button>
          </footer>
        </div>
      </form>
    </b-modal>

    
  </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from "vuex";
import DEFINE from '../constant.js';
import states from '@/utils/states';
import Vue from 'vue';

import firebase,{ functions } from 'firebase/app';
import 'firebase/storage';
import uuid from 'uuid/v1';

export default {
  name: "Users",
  data: () => ({
    loaded: false,
    perPage: 10,
    isModalActive: false,
    searchText: "",
    selectedUser: {},
    user_type : DEFINE.user_type,
    states,
    genderList: [
      { value : 'Agender', id: 'agender' },
      { value : 'Female', id: 'female' },
      { value : 'Gender diverse', id: 'gender diverse' },
      { value : 'Gender expansive', id: 'gender expansive' },
      { value : 'Gender fluid', id: 'gender fluid' },
      { value : 'Genderqueer', id: 'genderqueer' },
      { value : 'Intersex', id: 'intersex' },
      { value : 'Male', id: 'male' },
      { value : 'Non-binary', id: 'non-binary' },
      { value : 'Transfemale/transfeminine', id: 'transfemale/transfeminine' },
      { value : 'Transmale/transmasculine', id: 'transmale/transmasculine' },
      { value : 'Two-spirit', id: 'two-spirit' },
      { value : 'Self describe', id: 'self describe' },
      { value : 'Prefer not to answer', id: 'Prefer not to answer' },
    ],
    showWeekNumber : false,
    profile_file : null,
    data: [],
    total: 0,    
    sortField: "user_details.created_at",
    sortOrder: "desc",
    defaultSortOrder: "desc",
    page: 1,
    searchTimeout : null
  }),
  computed: {
    ...mapState('users', ['userList', 'isLoading']),
    ...mapGetters('users', ['search']),

    // filter: function() {
    //   return this.search(this.searchText);
    // }
  },
  methods: {
    // ...mapActions('users', ['fetch', 'getlist', 'update', 'destroy', 'status_change', 'dateChange']),
    ...mapActions('users', ['getlist', 'update', 'destroy', 'status_change', 'dateChange']),
    ...mapActions('toast', ['showError']),
    confirmDelete(user) {
      this.selectedUser = Object.assign({}, user);

      this.$dialog.confirm({
        message: `Are you sure you want to delete "${this.selectedUser.details.first_name ? this.selectedUser.details.first_name : ''} ${this.selectedUser.details.last_name ? this.selectedUser.details.last_name : ''}"?`,
        confirmText: "Yes, I'm sure",
        type: "is-info",
        hasIcon: true,
        onConfirm: this.deleteUser
      });
    },

    confirmstatusChange(user) {
      this.selectedUser = Object.assign({}, user);
      let msg =
      this.$dialog.confirm({
        message: `Are you sure you want to ${user.is_active ? 'De-active' : 'Active'} "${this.selectedUser.details.first_name ? this.selectedUser.details.first_name : ''} ${this.selectedUser.details.last_name ? this.selectedUser.details.last_name : ''}"?`,
        confirmText: "Yes, I'm sure",
        type: "is-info",
        hasIcon: true,
        onConfirm: this.statusChangeUser
      });
    },

    async deleteUser() {
      await this.destroy(this.selectedUser);
      this.selectedUser = {};
      await this.loadAsyncData();
    },
    async statusChangeUser() {
      await this.status_change(this.selectedUser);
      this.selectedUser = {};
      await this.loadAsyncData();
    },
    defaultImg(event){
      event.target.src = DEFINE.user_default_img;      
    },
    async showUpdateModal(user) {
      this.profile_file = null;
      this.selectedUser = JSON.parse(JSON.stringify(user));
      if(this.selectedUser.details.birth){
        this.selectedUser.details.birth = Vue.moment(this.selectedUser.details.birth).toDate();
      } else {
        this.selectedUser.details.birth = null
      }
      
      this.isModalActive = true;
    },
    async updateUser() {
      try {        
        let valid = await this.$validator.validateAll();

        if (! valid) {
          this.showError('Please check the fields.');
          return;
        }
        let requestParam = JSON.parse(JSON.stringify(this.selectedUser));
        if(this.profile_file){
          const imageName = this.profile_file.name;
          const snapshot = await firebase.storage()
            .ref(`profileImage/${uuid()}.${imageName.split('.').pop()}`)
            .put(this.profile_file);

          requestParam.image = await snapshot.ref.getDownloadURL();          
        } else if(requestParam.image && requestParam.image.url) {
          requestParam.image = requestParam.image.url;
        }
        if(requestParam.details.birth){
          requestParam.details.birth = Vue.moment(requestParam.details.birth).format("YYYY-MM-DD");
        }
        
        await this.update(requestParam);        
        this.isModalActive = false;
        // this.selectedUser = {};
        await this.loadAsyncData();
      } catch (e) {
        console.log("TCL: updateUser -> e", e)
        this.$setErrorsFromResponse(e.response.data);
      }
    },
    getStateName(state_id) {
      let foundState = states.find(x => x.value == state_id);
      return foundState ? foundState.label : '';
    },
    /*
     * Load async data
     */
    async loadAsyncData() {      
      const params = [        
        `per_page=${this.perPage}`,
        `page=${this.page}`,
        `order_by=${this.sortField}`,
        `order_type=${this.sortOrder.toUpperCase()}`,
        `search=${this.searchText}`        
      ].join("&");
      await this.getlist(params);      
      this.total = this.userList.total ?? 0;      
    },
    /*
     * Handle page-change event
     */
    onPageChange(page) {
      this.page = page;
      this.loadAsyncData();
    },
    /*
     * Handle per-page-change event
     */
    perPageChage() {
      this.page = 1;
      this.loadAsyncData();
    },
    /*
     * Handle search box event
     */
    searchUsers() {
      if (this.searchTimeout) {  
        this.page = 1;
        clearTimeout(this.searchTimeout);
      }
      this.searchTimeout = setTimeout(() => {
        this.loadAsyncData();
      }, 100);
      
    },
    /*
     * Handle sort event
     */
    onSort(field, order) {
      this.sortField = field;
      this.sortOrder = order;
      this.loadAsyncData();
    },
  },

  async mounted() {
    await this.loadAsyncData();
    this.loaded = true;
  },
  // async created() {
  //   await this.loadAsyncData();
  //   // await this.fetch();    
  //   
  //   this.loaded = true;
  // }
};
</script>
<style>
.user-avatar, .user-avatar > img{
  width: 185px;
  height: 185px;
  display: inline-block;
  border-radius: 50%;
  object-fit: cover;
}
.datepicker.dropdown-menu {
  z-index: 99999 !important;
}
</style>