<template>
    <Head>
        <title>Permissions - Aplikasi POS</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-purple">
                            <div class="card-header">
                                <span class="font-weight-bold"><i class="fa fa-key"></i> PERMISSIONS</span>
                            </div>
                            <div class="card-body">
                                <!-- Search & Per Page Controls -->
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <form @submit.prevent="handleSearch">
                                            <div class="input-group">
                                                <input type="text" class="form-control" v-model="search" placeholder="search by permission name...">
                                                <button class="btn btn-primary input-group-text" type="submit">
                                                    <i class="fa fa-search me-2"></i> SEARCH
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <label class="form-label me-2 mb-0 text-nowrap">Show:</label>
                                            <select class="form-select" v-model="perPage" @change="handlePerPageChange">
                                                <option value="15">15</option>
                                                <option value="20">20</option>
                                                <option value="50">50</option>
                                                <option value="all">All</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Info -->
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Showing {{ showingText }}
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">
                                            Total: {{ permissions.total || permissions.data.length }} permissions
                                        </small>
                                    </div>
                                </div>

                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col" width="80">#</th>
                                            <th scope="col">Permission Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(permission, index) in permissions.data" :key="index">
                                            <td>{{ getRowNumber(index) }}</td>
                                            <td>{{ permission.name }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Pagination - hanya tampil jika bukan 'all' -->
                                <div v-if="perPage !== 'all'" class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            Page {{ currentPage }} of {{ totalPages }}
                                        </small>
                                    </div>
                                    <SimplePagination :links="permissions.links" align="end"/>
                                </div>

                                <!-- Info untuk mode 'all' -->
                                <div v-else class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-list me-1"></i>
                                        Displaying all {{ permissions.data.length }} permissions
                                    </small>
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
    //import layout
    import LayoutApp from '../../../Layouts/App.vue';

    //import component simple pagination
    import SimplePagination from '../../../Components/SimplePagination.vue';

    //import Heade and Link from Inertia
    import { Head, Link } from '@inertiajs/inertia-vue3';

    //import ref and computed from vue
    import { ref, computed } from 'vue';

    //import inertia adapter
    import { Inertia } from '@inertiajs/inertia';

    export default {
        //layout
        layout: LayoutApp,

        //register component
        components: {
            Head,
            Link,
            SimplePagination
        },

        //props
        props: {
            permissions: Object,
        },

        setup(props) {
            // Get URL params
            const urlParams = new URL(document.location);

            //define state search
            const search = ref(urlParams.searchParams.get('q') || '');

            //define state per page
            const perPage = ref(urlParams.searchParams.get('per_page') || '15');

            //define method search
            const handleSearch = () => {
                Inertia.get('/apps/permissions', {
                    q: search.value,
                    per_page: perPage.value,
                }, {
                    preserveState: true,
                    replace: true
                });
            }

            //define method per page change
            const handlePerPageChange = () => {
                Inertia.get('/apps/permissions', {
                    q: search.value,
                    per_page: perPage.value,
                }, {
                    preserveState: true,
                    replace: true
                });
            }

            // Computed properties
            const currentPage = computed(() => {
                return props.permissions.current_page || 1;
            });

            const totalPages = computed(() => {
                return props.permissions.last_page || 1;
            });

            const showingText = computed(() => {
                if (perPage.value === 'all') {
                    return `all ${props.permissions.data.length} entries`;
                }

                const from = props.permissions.from || 1;
                const to = props.permissions.to || props.permissions.data.length;
                const total = props.permissions.total || props.permissions.data.length;

                return `${from}-${to} of ${total} entries`;
            });

            // Method to get row number
            const getRowNumber = (index) => {
                if (perPage.value === 'all') {
                    return index + 1;
                }

                const from = props.permissions.from || 1;
                return from + index;
            };

            //return
            return {
                search,
                perPage,
                handleSearch,
                handlePerPageChange,
                currentPage,
                totalPages,
                showingText,
                getRowNumber
            }
        }
    }
</script>

<style scoped>
.form-select {
    min-width: 80px;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.table tbody tr:hover {
    background-color: rgba(111, 66, 193, 0.05);
}

/* Custom styling for controls */
.input-group .btn {
    border-left: 0;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

/* Info text styling */
.text-muted {
    color: #6c757d !important;
}

.fas.fa-info-circle,
.fas.fa-list {
    color: #6f42c1;
}

/* Responsive */
@media (max-width: 768px) {
    .row .col-md-8,
    .row .col-md-4 {
        margin-bottom: 1rem;
    }

    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }

    .d-flex.justify-content-between > div {
        text-align: center;
    }
}
</style>
