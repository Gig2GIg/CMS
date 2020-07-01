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
            <div class="columns" v-if="userList.length">
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
            
            <p class="mt-3">Current Page: {{ currentPage }}</p>


            <b-table
              :data="filter"
              :loading="isLoading"
              :show-detail-icon="true"
              :per-page="0"
              detail-key="id"
             
            >
             <!-- detailed
              hoverable -->
            <!-- :data="filter" -->
            <!-- :per-page="perPage" -->
            <!-- :paginated="!!filter.length" -->
              <template slot-scope="props" v-if="props.row.details">
                <b-table-column
                  field="email"
                  label="Email"
                  width="60"
                  sortable
                >{{ props.row.email }}</b-table-column>
                <b-table-column
                  field="first_name"
                  label="First Name"
                  width="100"
                  sortable
                >{{ props.row.details.first_name ? props.row.details.first_name : ""}}</b-table-column>
                <b-table-column
                  field="last_name"
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
                  field="type"
                  label="Type"
                  width="100"
                  sortable
                >{{ user_type[props.row.details.type] ? user_type[props.row.details.type] : '' }}</b-table-column>

                <b-table-column width="100" field="created_at" label="Date" sortable>{{ props.row.details.created_at | dateFormat}}</b-table-column>

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
            <b-pagination
              v-on:change="onPageChange" 
              v-model="currentPage"
              :total-rows="rows"
              :per-page="5"              
            ></b-pagination>
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
    // perPage: 5,
    isModalActive: false,
    searchText: "",
    selectedUser: {},
    user_type : DEFINE.user_type,
    states,
    genderList: [{
        id: 'male',
        value: 'Male',
      }, {
        id: 'female',
        value: 'Female',
      }, {
        id: 'other',
        value: 'Other',
      }],
    showWeekNumber : false,
    profile_file : null,
    currentPage: 1,
    perPage: 10,
    items : []
  }),
  computed: {
    ...mapState('users', ['userList', 'isLoading']),
    ...mapGetters('users', ['search']),

    filter: function() {
      // return this.search(this.searchText);
      const items = this.search(this.searchText);
      // Return just page of items needed
      return items.slice(
        (this.currentPage - 1) * this.perPage,
        this.currentPage * this.perPage
      )
    },
    rows() {
      console.log("rows -> this.search(this.searchText).length", this.search(this.searchText).length)
      return this.search(this.searchText).length
    }
  },
  methods: {
    ...mapActions('users', ['fetch', 'update', 'destroy', 'status_change', 'dateChange']),
    ...mapActions('toast', ['showError']),
    onPageChange($event) {
    console.log("onPageChange -> $event", $event)

    },
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
    },
    async statusChangeUser() {
      await this.status_change(this.selectedUser);
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

        if(this.profile_file){
          const imageName = this.profile_file.name;
          const snapshot = await firebase.storage()
            .ref(`profileImage/${uuid()}.${imageName.split('.').pop()}`)
            .put(this.profile_file);

          this.selectedUser.image = await snapshot.ref.getDownloadURL();          
        } else if(this.selectedUser.image && this.selectedUser.image.url) {
          this.selectedUser.image = this.selectedUser.image.url;
        }
        if(this.selectedUser.details.birth){
          this.selectedUser.details.birth = Vue.moment(this.selectedUser.details.birth).format("YYYY-MM-DD");
        }
        
        await this.update(this.selectedUser);

        this.isModalActive = false;
      } catch (e) {
        console.log("TCL: updateUser -> e", e)
        this.$setErrorsFromResponse(e.response.data);
      }
    },
    getStateName(state_id) {
      let foundState = states.find(x => x.value == state_id);
      return foundState ? foundState.label : '';
    }
  },

  async created() {
    await this.fetch();
    this.items = this.userList;
    console.log("created -> this.items", this.items)
    this.loaded = true;
  }
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
