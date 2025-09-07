<template>
    <Head title="Pending Approvals" />

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header">
                    <span><i class="fa fa-check-circle"></i> Pending Approvals</span>
                </div>
                <div class="card-body">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h4>{{ stats.pending_count }}</h4>
                                    <small>Pending Approvals</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approvals List -->
                    <div v-if="approvals.data.length === 0" class="text-center py-5">
                        <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Pending Approvals</h5>
                        <p class="text-muted">All approval requests have been processed.</p>
                    </div>

                    <div v-else>
                        <div v-for="approval in approvals.data" :key="approval.id" class="card mb-3 border-warning">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5 class="text-capitalize">
                                            <span class="badge bg-warning me-2">{{ approval.type }}</span>
                                            Rp {{ formatNumber(approval.amount) }}
                                        </h5>
                                        <p class="mb-2">{{ approval.reason }}</p>
                                        <small class="text-muted">
                                            Requested by: <strong>{{ approval.requested_by.name }}</strong>
                                            on {{ formatDate(approval.requested_at) }}
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button @click="approve(approval.id)" class="btn btn-success me-2">
                                            <i class="fa fa-check"></i> Approve
                                        </button>
                                        <button @click="reject(approval.id)" class="btn btn-danger">
                                            <i class="fa fa-times"></i> Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <pagination :links="approvals.links" align="center" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LayoutApp from '../../../Layouts/App.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';
import Pagination from '../../../Components/Pagination.vue';

export default {
    layout: LayoutApp,

    components: {
        Head,
        Pagination
    },

    props: {
        approvals: Object,
        stats: Object
    },

    methods: {
        approve(approvalId) {
            if(confirm('Are you sure you want to approve this request?')) {
                Inertia.post(`/apps/approvals/${approvalId}/approve`, {}, {
                    onSuccess: () => {
                        // Success handler
                    },
                    onError: (errors) => {
                        console.error('Approval error:', errors);
                    }
                });
            }
        },

        reject(approvalId) {
            const reason = prompt('Reason for rejection:');
            if(reason) {
                Inertia.post(`/apps/approvals/${approvalId}/reject`, {
                    reason: reason
                }, {
                    onSuccess: () => {
                        // Success handler
                    },
                    onError: (errors) => {
                        console.error('Reject error:', errors);
                    }
                });
            }
        },

        formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
