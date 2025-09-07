<template>
    <Head title="Approval History" />

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa fa-history"></i> Approval History</span>
                    <Link href="/apps/approvals" class="btn btn-primary btn-sm">
                        <i class="fa fa-arrow-left me-1"></i> Back to Pending
                    </Link>
                </div>
                <div class="card-body">

                    <!-- Filter Section -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <select v-model="filterType" @change="applyFilter" class="form-select">
                                <option value="">All Types</option>
                                <option value="refund">Refund</option>
                                <option value="void">Void</option>
                                <option value="discount">Discount</option>
                                <option value="large_transaction">Large Transaction</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select v-model="filterStatus" @change="applyFilter" class="form-select">
                                <option value="">All Status</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" v-model="filterStartDate" @change="applyFilter" class="form-control" placeholder="Start Date">
                        </div>
                        <div class="col-md-3">
                            <input type="date" v-model="filterEndDate" @change="applyFilter" class="form-control" placeholder="End Date">
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h4>{{ approvedCount }}</h4>
                                    <small>Total Approved</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h4>{{ rejectedCount }}</h4>
                                    <small>Total Rejected</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- History List -->
                    <div v-if="approvals.data.length === 0" class="text-center py-5">
                        <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Approval History</h5>
                        <p class="text-muted">No approvals have been processed yet.</p>
                    </div>

                    <div v-else>
                        <div v-for="approval in approvals.data" :key="approval.id" class="card mb-3" :class="getCardClass(approval.status)">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5>
                                            <span class="badge me-2" :class="getTypeBadgeClass(approval.type)">
                                                {{ formatType(approval.type) }}
                                            </span>
                                            Rp {{ formatNumber(approval.amount) }}
                                            <span class="badge ms-2" :class="getStatusBadgeClass(approval.status)">
                                                {{ approval.status.toUpperCase() }}
                                            </span>
                                        </h5>
                                        <p class="mb-2"><strong>Reason:</strong> {{ approval.reason }}</p>
                                        <div class="approval-details">
                                            <small class="text-muted d-block">
                                                <i class="fa fa-user me-1"></i>
                                                Requested by: <strong>{{ approval.requested_by.name }}</strong>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fa fa-calendar me-1"></i>
                                                Requested: {{ formatDate(approval.requested_at) }}
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fa fa-check me-1"></i>
                                                Processed by: <strong>{{ approval.approved_by.name }}</strong>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fa fa-clock me-1"></i>
                                                Processed: {{ formatDate(approval.processed_at) }}
                                            </small>
                                        </div>
                                        <div v-if="approval.approval_reason" class="mt-3 p-2 bg-light rounded">
                                            <strong>{{ approval.status === 'approved' ? 'Approval' : 'Rejection' }} Note:</strong><br>
                                            {{ approval.approval_reason }}
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="d-flex flex-column align-items-end">
                                            <span class="badge fs-6 mb-2" :class="getStatusBadgeClass(approval.status)">
                                                <i :class="getStatusIcon(approval.status)"></i>
                                                {{ approval.status.toUpperCase() }}
                                            </span>
                                            <small class="text-muted">
                                                {{ getDaysAgo(approval.processed_at) }}
                                            </small>
                                        </div>
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
import { Head, Link } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';
import Pagination from '../../../Components/Pagination.vue';

export default {
    layout: LayoutApp,

    components: {
        Head,
        Link,
        Pagination
    },

    props: {
        approvals: Object,
        filters: Object
    },

    data() {
        return {
            filterType: this.filters?.type || '',
            filterStatus: this.filters?.status || '',
            filterStartDate: this.filters?.start_date || '',
            filterEndDate: this.filters?.end_date || ''
        }
    },

    computed: {
        approvedCount() {
            return this.approvals.data.filter(approval => approval.status === 'approved').length;
        },
        rejectedCount() {
            return this.approvals.data.filter(approval => approval.status === 'rejected').length;
        }
    },

    methods: {
        applyFilter() {
            const params = {};
            if (this.filterType) params.type = this.filterType;
            if (this.filterStatus) params.status = this.filterStatus;
            if (this.filterStartDate) params.start_date = this.filterStartDate;
            if (this.filterEndDate) params.end_date = this.filterEndDate;

            Inertia.get('/apps/approvals/history', params, {
                preserveState: true,
                preserveScroll: true
            });
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
        },

        formatType(type) {
            const types = {
                'refund': 'Refund',
                'void': 'Void Transaction',
                'discount': 'Large Discount',
                'large_transaction': 'Large Transaction'
            };
            return types[type] || type;
        },

        getCardClass(status) {
            return status === 'approved' ? 'border-success' : 'border-danger';
        },

        getTypeBadgeClass(type) {
            const classes = {
                'refund': 'bg-warning',
                'void': 'bg-secondary',
                'discount': 'bg-info',
                'large_transaction': 'bg-primary'
            };
            return classes[type] || 'bg-secondary';
        },

        getStatusBadgeClass(status) {
            return status === 'approved' ? 'bg-success' : 'bg-danger';
        },

        getStatusIcon(status) {
            return status === 'approved' ? 'fa fa-check' : 'fa fa-times';
        },

        getDaysAgo(date) {
            const days = Math.floor((new Date() - new Date(date)) / (1000 * 60 * 60 * 24));
            if (days === 0) return 'Today';
            if (days === 1) return 'Yesterday';
            return `${days} days ago`;
        }
    }
}
</script>

<style scoped>
.approval-details small {
    line-height: 1.5;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>
