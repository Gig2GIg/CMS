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
            <div class="mb-6">
              <label class="label">Terms of Use</label>
              <ckeditor :editor="editor" v-model="settings.term_of_use" :config="editorConfig"></ckeditor>
            </div>

            <div class="mb-6">
              <label class="label">Privacy Policy</label>
              <ckeditor :editor="editor" v-model="settings.privacy_policy" :config="editorConfig"></ckeditor>
            </div>

            <div class="mb-6">
              <label class="label">About</label>
              <ckeditor :editor="editor" v-model="settings.help" :config="editorConfig"></ckeditor>
            </div>

            <div class="mb-6">
              <label class="label">App info</label>
              <ckeditor :editor="editor" v-model="settings.app_info" :config="editorConfig"></ckeditor>
            </div>

            <button
              class="button is-primary shadow"
              :disabled="updating"
              @click="updateSettings"
            >Update settings</button>
          </div>
        </div>
      </section>
    </transition>
  </div>
</template>

<script>
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import { mapActions } from "vuex";
import axios from "axios";

export default {
  name: "Settings",
  data: () => ({
    loaded: false,
    updating: false,
    settings: {},
    editor: ClassicEditor,
    editorConfig: {}
  }),
  methods: {
    ...mapActions("toast", ["showMessage"]),

    async updateSettings() {
      this.updating = true;

      await axios.put("/api/cms/content-settings/update", this.settings);
      this.showMessage("Settings updated.");

      this.updating = false;
    }
  },
  async created() {
    const { data: { data } } = await axios.get("/api/cms/content-settings");

    this.settings = data[0];
    this.loaded = true;
  }
};
</script>
