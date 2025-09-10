<template>
  <v-app>
    <!-- Login Page -->
    <div v-if="!isAuthenticated" class="login-container">
      <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
          <v-col cols="12" sm="8" md="4">
            <v-card class="elevation-12">
              <v-toolbar color="primary" dark flat>
                <v-toolbar-title>WordPress Back Office</v-toolbar-title>
              </v-toolbar>
              <v-card-text>
                <v-form @submit.prevent="login">
                  <v-text-field
                    v-model="loginForm.username"
                    label="WordPress Username"
                    prepend-icon="mdi-account"
                    type="text"
                    required
                  ></v-text-field>
                  <v-text-field
                    v-model="loginForm.password"
                    label="Password"
                    prepend-icon="mdi-lock"
                    type="password"
                    required
                  ></v-text-field>
                </v-form>
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  @click="login"
                  :loading="loading"
                  :disabled="!loginForm.username || !loginForm.password"
                >
                  Login
                </v-btn>
              </v-card-actions>
              <v-alert v-if="error" type="error" class="ma-4">
                {{ error }}
              </v-alert>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </div>

    <!-- Dashboard -->
    <div v-else>
      <v-app-bar color="primary" dark>
        <v-toolbar-title>WordPress Back Office</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon @click="logout">
          <v-icon>mdi-logout</v-icon>
        </v-btn>
      </v-app-bar>

      <v-main>
        <v-container>
          <v-row>
            <v-col cols="12">
              <h1>Blog Posts Management</h1>
              <v-btn color="primary" @click="showCreateDialog = true" class="mb-4">
                <v-icon left>mdi-plus</v-icon>
                Create New Post
              </v-btn>
              
              <!-- Posts Table -->
              <v-data-table
                :headers="headers"
                :items="posts"
                :loading="loadingPosts"
                class="elevation-1"
              >
                <template v-slot:item.actions="{ item }">
                  <v-btn icon small @click="editPost(item)" class="mr-2">
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                  <v-btn icon small @click="deletePost(item)" color="red">
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </template>
              </v-data-table>
            </v-col>
          </v-row>
        </v-container>
      </v-main>

      <!-- Create/Edit Dialog -->
      <v-dialog v-model="showCreateDialog" max-width="600">
        <v-card>
          <v-card-title>
            {{ editingPost ? 'Edit Post' : 'Create New Post' }}
          </v-card-title>
          <v-card-text>
            <v-text-field
              v-model="postForm.title"
              label="Title"
              required
            ></v-text-field>
            <v-textarea
              v-model="postForm.content"
              label="Content"
              required
            ></v-textarea>
            <v-select
              v-model="postForm.status"
              :items="['draft', 'publish', 'private']"
              label="Status"
            ></v-select>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn @click="showCreateDialog = false">Cancel</v-btn>
            <v-btn color="primary" @click="savePost" :loading="saving">
              {{ editingPost ? 'Update' : 'Create' }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
  </v-app>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      isAuthenticated: false,
      loading: false,
      loadingPosts: false,
      saving: false,
      error: '',
      loginForm: {
        username: '',
        password: ''
      },
      posts: [],
      showCreateDialog: false,
      editingPost: null,
      postForm: {
        title: '',
        content: '',
        status: 'draft'
      },
      headers: [
        { title: 'Title', key: 'title.rendered' },
        { title: 'Status', key: 'status' },
        { title: 'Date', key: 'date' },
        { title: 'Actions', key: 'actions', sortable: false }
      ]
    }
  },
  
  async mounted() {
    await this.checkAuth()
    if (this.isAuthenticated) {
      await this.loadPosts()
    }
  },
  
  methods: {
    async checkAuth() {
      try {
        const response = await axios.get('/api/auth/check')
        this.isAuthenticated = response.data.authenticated
      } catch (error) {
        console.error('Auth check failed:', error)
      }
    },
    
    async login() {
      this.loading = true
      this.error = ''
      
      try {
        const response = await axios.post('/api/auth/login', this.loginForm)
        if (response.data.success) {
          this.isAuthenticated = true
          await this.loadPosts()
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
      } finally {
        this.loading = false
      }
    },
    
    async logout() {
      await axios.post('/api/auth/logout')
      this.isAuthenticated = false
      this.posts = []
    },
    
    async loadPosts() {
      this.loadingPosts = true
      try {
        const response = await axios.get('/api/posts')
        this.posts = response.data
      } catch (error) {
        console.error('Failed to load posts:', error)
      } finally {
        this.loadingPosts = false
      }
    },
    
    editPost(post) {
      this.editingPost = post
      this.postForm = {
        title: post.title.rendered,
        content: post.content.rendered,
        status: post.status
      }
      this.showCreateDialog = true
    },
    
    async savePost() {
      this.saving = true
      try {
        if (this.editingPost) {
          await axios.put(`/api/posts/${this.editingPost.id}`, this.postForm)
        } else {
          await axios.post('/api/posts', this.postForm)
        }
        
        this.showCreateDialog = false
        this.resetForm()
        await this.loadPosts()
      } catch (error) {
        console.error('Failed to save post:', error)
      } finally {
        this.saving = false
      }
    },
    
    async deletePost(post) {
      if (confirm('Are you sure you want to delete this post?')) {
        try {
          await axios.delete(`/api/posts/${post.id}`)
          await this.loadPosts()
        } catch (error) {
          console.error('Failed to delete post:', error)
        }
      }
    },
    
    resetForm() {
      this.editingPost = null
      this.postForm = {
        title: '',
        content: '',
        status: 'draft'
      }
    }
  }
}
</script>

<style>
.login-container {
  min-height: 100vh;
  background: linear-gradient(45deg, #1976d2, #42a5f5);
}
</style>