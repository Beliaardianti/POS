<template>
  <Head title="My Profile" />

  <main class="c-main">
    <div class="container-fluid">
      <div class="fade-in">
        <div class="row justify-content-center">
          <div class="col-md-8">
            <div class="card border-0 rounded-3 shadow border-top-purple">
              <div class="card-header">
                <span class="font-weight-bold text-uppercase">
                  <i class="fa fa-user"></i> MY PROFILE
                </span>
              </div>
              <div class="card-body">

                <!-- Avatar Section -->
                <div class="text-center mb-4">
                  <div class="c-avatar" style="width: 120px; height: 120px; margin: 0 auto;">
                    <img class="c-avatar-img"
                         :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=6f42c1&color=ffffff&size=120`"
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
                          <td class="font-weight-bold">Role:</td>
                          <td>
                            <span class="badge bg-primary">{{ $page.props.auth.user.roles?.[0]?.name || 'User' }}</span>
                          </td>
                        </tr>

                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-4">

                  <button type="button"
                          class="btn btn-outline-secondary"
                          @click="changePassword = !changePassword">
                    <i class="fa fa-key"></i> Change Password
                  </button>
                  <Link href="/apps/dashboard"
                        class="btn btn-secondary ms-2">
                    <i class="fa fa-arrow-left"></i> Back to Dashboard
                  </Link>
                </div>

                <!-- Change Password Form -->
                <div v-if="changePassword" class="mt-4">
                  <div class="row justify-content-center">
                    <div class="col-md-6">
                      <div class="card border-0 shadow-sm">
                        <div class="card-header bg-warning text-dark">
                          <strong><i class="fa fa-key"></i> Change Password</strong>
                        </div>
                        <div class="card-body">
                          <form @submit.prevent="updatePassword">
                            <div class="form-group mb-3">
                              <label class="fw-bold">Current Password</label>
                              <input type="password"
                                     class="form-control"
                                     v-model="passwordForm.current_password"
                                     :class="{ 'is-invalid': passwordErrors.current_password }"
                                     placeholder="Enter current password"
                                     required>
                              <div v-if="passwordErrors.current_password" class="invalid-feedback">
                                {{ passwordErrors.current_password }}
                              </div>
                            </div>
                            <div class="form-group mb-3">
                              <label class="fw-bold">New Password</label>
                              <input type="password"
                                     class="form-control"
                                     v-model="passwordForm.password"
                                     :class="{ 'is-invalid': passwordErrors.password }"
                                     placeholder="Enter new password"
                                     required>
                              <div v-if="passwordErrors.password" class="invalid-feedback">
                                {{ passwordErrors.password }}
                              </div>
                              <small class="text-muted">Password must be at least 8 characters long</small>
                            </div>
                            <div class="form-group mb-3">
                              <label class="fw-bold">Confirm New Password</label>
                              <input type="password"
                                     class="form-control"
                                     v-model="passwordForm.password_confirmation"
                                     placeholder="Confirm new password"
                                     required>
                            </div>
                            <div class="text-end">
                              <button type="button"
                                      class="btn btn-secondary me-2"
                                      @click="cancelPasswordChange">
                                <i class="fa fa-times"></i> Cancel
                              </button>
                              <button type="submit"
                                      class="btn btn-primary"
                                      :disabled="passwordForm.processing">
                                <i class="fa fa-save"></i>
                                {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                              </button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Success/Error Messages -->
                <div v-if="$page.props.flash?.success" class="alert alert-success mt-3" role="alert">
                  <i class="fa fa-check-circle"></i> {{ $page.props.flash.success }}
                </div>

                <div v-if="$page.props.flash?.error" class="alert alert-danger mt-3" role="alert">
                  <i class="fa fa-exclamation-circle"></i> {{ $page.props.flash.error }}
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import { Head, Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import LayoutApp from '../../../Layouts/App.vue'
import Swal from 'sweetalert2'

export default {
  layout: LayoutApp,

  components: {
    Head,
    Link
  },

  props: {
    errors: Object
  },

  data() {
    return {
      changePassword: false,
      passwordForm: {
        current_password: '',
        password: '',
        password_confirmation: '',
        processing: false
      },
      passwordErrors: {}
    }
  },

  methods: {
    formatDate(dateString) {
      if (!dateString) return '-'
      const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }
      return new Date(dateString).toLocaleDateString('id-ID', options)
    },

    updatePassword() {
      this.passwordForm.processing = true
      this.passwordErrors = {}

      Inertia.put('/apps/profile/password', {
        current_password: this.passwordForm.current_password,
        password: this.passwordForm.password,
        password_confirmation: this.passwordForm.password_confirmation
      }, {
        onSuccess: () => {
          Swal.fire({
            title: 'Success!',
            text: 'Password updated successfully.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          })
          this.cancelPasswordChange()
        },
        onError: (errors) => {
          this.passwordErrors = errors
          Swal.fire({
            title: 'Error!',
            text: 'Please check your input and try again.',
            icon: 'error'
          })
        },
        onFinish: () => {
          this.passwordForm.processing = false
        }
      })
    },

    cancelPasswordChange() {
      this.changePassword = false
      this.passwordForm = {
        current_password: '',
        password: '',
        password_confirmation: '',
        processing: false
      }
      this.passwordErrors = {}
    }
  }
}
</script>

<style scoped>
.fade-in {
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.border-top-purple {
  border-top: 3px solid #6f42c1 !important;
}

.c-avatar-img {
  object-fit: cover;
  border: 4px solid #fff;
  box-shadow: 0 4px 15px rgba(111, 66, 193, 0.2);
  transition: transform 0.3s ease;
}

.c-avatar-img:hover {
  transform: scale(1.05);
}

.table td {
  padding: 12px 8px;
  border: none;
}

.btn {
  border-radius: 6px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card {
  transition: transform 0.2s ease;
}

.badge {
  font-size: 0.8rem;
  padding: 6px 12px;
}

.alert {
  border-radius: 8px;
  border: none;
}

.form-control:focus {
  border-color: #6f42c1;
  box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
}

.text-muted {
  color: #6c757d !important;
}
</style>
