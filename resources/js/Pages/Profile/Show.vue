<template>
  <Head title="My Profile" />
  
  <div class="container-fluid">
    <div class="fade-in">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <span class="font-weight-bold text-uppercase">
                <i class="fa fa-user"></i> My Profile
              </span>
            </div>
            <div class="card-body">
              
              <!-- Avatar Section -->
              <div class="text-center mb-4">
                <div class="c-avatar" style="width: 120px; height: 120px; margin: 0 auto;">
                  <img class="c-avatar-img" 
                       :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=4e73df&color=ffffff&size=120`"
                       style="width: 120px; height: 120px; border-radius: 50%;">
                </div>
                <h4 class="mt-3 mb-0">{{ $page.props.auth.user.name }}</h4>
                <p class="text-muted">{{ $page.props.auth.user.email }}</p>
              </div>

              <!-- Profile Info Table -->
              <div class="row">
                <div class="col-md-8 mx-auto">
                  <table class="table table-borderless">
                    <tbody>
                      <tr>
                        <td width="30%" class="font-weight-bold">Name:</td>
                        <td>{{ $page.props.auth.user.name }}</td>
                      </tr>
                      <tr>
                        <td class="font-weight-bold">Email:</td>
                        <td>{{ $page.props.auth.user.email }}</td>
                      </tr>
                      <tr>
                        <td class="font-weight-bold">Member Since:</td>
                        <td>{{ formatDate($page.props.auth.user.created_at) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="text-center mt-4">
                <Link :href="route('apps.profile.edit')" 
                      class="btn btn-primary me-2">
                  <i class="fa fa-edit"></i> Edit Profile
                </Link>
                <button type="button" 
                        class="btn btn-outline-secondary"
                        @click="changePassword = !changePassword">
                  <i class="fa fa-key"></i> Change Password
                </button>
              </div>

              <!-- Change Password Form -->
              <div v-if="changePassword" class="mt-4">
                <div class="row justify-content-center">
                  <div class="col-md-6">
                    <div class="card">
                      <div class="card-header">
                        <strong>Change Password</strong>
                      </div>
                      <div class="card-body">
                        <form @submit.prevent="updatePassword">
                          <div class="form-group mb-3">
                            <label>Current Password</label>
                            <input type="password" 
                                   class="form-control"
                                   v-model="passwordForm.current_password"
                                   required>
                          </div>
                          <div class="form-group mb-3">
                            <label>New Password</label>
                            <input type="password" 
                                   class="form-control"
                                   v-model="passwordForm.password"
                                   required>
                          </div>
                          <div class="form-group mb-3">
                            <label>Confirm New Password</label>
                            <input type="password" 
                                   class="form-control"
                                   v-model="passwordForm.password_confirmation"
                                   required>
                          </div>
                          <div class="text-end">
                            <button type="button" 
                                    class="btn btn-secondary me-2"
                                    @click="changePassword = false">
                              Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                              Update Password
                            </button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'

export default {
  components: {
    Head,
    Link
  },

  data() {
    return {
      changePassword: false,
      passwordForm: {
        current_password: '',
        password: '',
        password_confirmation: ''
      }
    }
  },

  methods: {
    formatDate(dateString) {
      const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      }
      return new Date(dateString).toLocaleDateString('id-ID', options)
    },

    updatePassword() {
      Inertia.put(route('apps.profile.password'), this.passwordForm, {
        onSuccess: () => {
          this.changePassword = false
          this.passwordForm = {
            current_password: '',
            password: '',
            password_confirmation: ''
          }
        }
      })
    }
  }
}
</script>

<style scoped>
.fade-in {
  animation: fadeIn 0.5s;
}

@keyframes fadeIn {
  0% { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
}

.c-avatar-img {
  object-fit: cover;
  border: 4px solid #fff;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>