<template>
    <Head>
        <title>Login Account - Aplikasi POS</title>
    </Head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <div class="col-md-4">

        <div class="fade-in">
            <!-- Logo Section - Modern touch -->
            <div class="text-center mb-4 logo-section">
                <a href="" class="text-dark text-decoration-none">
                    <div class="logo-wrapper">
                        <img src="/images/cash-machine.png" width="70">
                    </div>
                    <h3 class="mt-3 font-weight-bold app-title">APLIKASI KASIR</h3>
                </a>
            </div>

            <!-- Card with modern styling -->
            <div class="card-group">
                <div class="card modern-card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4">

                        <!-- Header Section -->
                        <div class="text-start mb-4">
                            <h5 class="login-title">LOGIN ACCOUNT</h5>
                            <p class="text-muted login-subtitle">Sign In to your account</p>
                        </div>

                        <hr class="modern-divider">

                        <!-- Success Alert -->
                        <div v-if="session.status" class="alert modern-alert-success mt-2">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session.status }}
                        </div>

                        <!-- Login Form -->
                        <form @submit.prevent="submit">

                            <!-- Email Input -->
                            <div class="input-group mb-3 modern-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text modern-input-icon">
                                        <i class="bi bi-envelope text-white"></i>
                                    </span>
                                </div>
                                <input
                                    class="form-control modern-input"
                                    v-model="form.email"
                                    :class="{ 'is-invalid': errors.email }"
                                    type="email"
                                    placeholder="Email Address"
                                >
                            </div>
                            <div v-if="errors.email" class="alert modern-alert-danger mb-3">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ errors.email }}
                            </div>

                            <!-- Password Input -->
                            <div class="input-group mb-3 modern-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text modern-input-icon">
                                       <i class="bi bi-lock text-white"></i>
                                    </span>
                                </div>
                                <input
                                    class="form-control modern-input"
                                    v-model="form.password"
                                    :class="{ 'is-invalid': errors.password }"
                                    type="password"
                                    placeholder="Password"
                                >
                            </div>
                            <div v-if="errors.password" class="alert modern-alert-danger mb-3">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ errors.password }}
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12 mb-3 text-end">
                                    <Link href="/forgot-password" class="modern-link">
                                        Forgot Password?
                                    </Link>
                                </div>
                                <div class="col-12">
                                    <button class="btn modern-btn w-100" type="submit">
                                        <span>LOGIN</span>

                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
//import layout
import LayoutAuth from '../../Layouts/Auth.vue';

//import reactive
import { reactive } from 'vue';

//import inertia adapter
import { Inertia } from '@inertiajs/inertia';

//import Head and useForm from Inertia
import {
    Head,
    Link,
} from '@inertiajs/inertia-vue3';

export default {
    //layout
    layout: LayoutAuth,

    //register component
    components: {
        Head,
        Link
    },

    props: {
        errors: Object,
        session: Object
    },

    //define composition API
    setup() {
        //define form state
        const form = reactive({
            email: '',
            password: '',
        });

        //submit method
        const submit = () => {
            //send data to server
            Inertia.post('/login', {
                //data
                email: form.email,
                password: form.password,
            });
        }

        //return form state and submit method
        return {
            form,
            submit,
        };
    }
}
</script>

<style scoped>
/* Import modern font */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

/* Global styles */
* {
    font-family: 'Inter', sans-serif;
}

/* Fade in animation - keeping original */
.fade-in {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Logo section modern touch */
.logo-section .logo-wrapper {
    display: inline-block;
    padding: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 18px;
    box-shadow:
        0 8px 25px rgba(102, 126, 234, 0.25),
        0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.logo-section .logo-wrapper:hover {
    transform: translateY(-2px);
}

.logo-section .logo-wrapper img {
    filter: brightness(0) invert(1);
}

.app-title {
    color: #2d3748;
    letter-spacing: -0.025em;
    margin-bottom: 0;
}

/* Modern card styling */
.modern-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow:
        0 20px 40px rgba(0, 0, 0, 0.08),
        0 8px 16px rgba(0, 0, 0, 0.04);
    transition: all 0.3s ease;
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow:
        0 25px 50px rgba(0, 0, 0, 0.12),
        0 12px 24px rgba(0, 0, 0, 0.08);
}

/* Header styling */
.login-title {
    font-weight: 600;
    color: #2d3748;
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.login-subtitle {
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 0;
}

/* Modern divider */
.modern-divider {
    border: none;
    height: 1px;
    background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
    margin: 1.5rem 0;
}

/* Input group modern styling */
.modern-input-group {
    position: relative;
}

.modern-input-icon {
    background: linear-gradient(135deg, #0e83d0, #135ea9);
    border: 2px solid #0f50a5;
    border-right: none;
    color: #718096;
    border-radius: 12px 0 0 12px;
    transition: all 0.3s ease;
}

.modern-input {
    border: 2px solid #e2e8f0;
    border-left: none;
    border-radius: 0 12px 12px 0;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.8);
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.modern-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    background: rgba(255, 255, 255, 1);
}

.modern-input:focus + .modern-input-icon,
.modern-input-group:focus-within .modern-input-icon {
    border-color: #667eea;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

/* Modern alerts */
.modern-alert-success {
    background: linear-gradient(135deg, #48bb78, #38a169);
    border: none;
    border-radius: 12px;
    color: white;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    font-weight: 500;
}

.modern-alert-danger {
    background: linear-gradient(135deg, #f56565, #e53e3e);
    border: none;
    border-radius: 12px;
    color: white;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Modern link */
.modern-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    position: relative;
}

.modern-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -2px;
    left: 0;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.3s ease;
}

.modern-link:hover {
    color: #290ea1;
}

.modern-link:hover::after {
    width: 100%;
}

/* Modern button */
.modern-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    border-radius: 12px;
    padding: 0.875rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.modern-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s;
}

.modern-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.modern-btn:hover::before {
    left: 100%;
}

.modern-btn:active {
    transform: translateY(0);
}

/* Error states */
.is-invalid {
    border-color: #e53e3e !important;
}

.is-invalid + .modern-input-icon,
.modern-input-group:has(.is-invalid) .modern-input-icon {
    border-color: #e53e3e !important;
    background: linear-gradient(135deg, #f56565, #e53e3e) !important;
    color: white !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modern-card {
        margin: 1rem;
    }

    .app-title {
        font-size: 1.25rem;
    }

    .logo-section .logo-wrapper {
        padding: 12px;
    }
}

/* Accessibility */
.modern-btn:focus,
.modern-input:focus,
.modern-link:focus {
    outline: 2px solid #0a2bc1;
    outline-offset: 2px;
}

/* Subtle animation for card */
.card-group {
    animation: slideInUp 0.8s ease-out 0.2s both;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
