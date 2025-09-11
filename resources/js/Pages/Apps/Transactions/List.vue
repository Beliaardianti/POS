<template>
    <Head title="My Transactions" />

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa fa-receipt"></i> My Transactions</span>
                    <Link href="/apps/transactions" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> New Transaction
                    </Link>
                </div>
                <div class="card-body">

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h4>{{ completedCount }}</h4>
                                    <small>Completed</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h4>{{ pendingCount }}</h4>
                                    <small>Pending Approval</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h4>{{ refundedCount }}</h4>
                                    <small>Refunded</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-dark text-white">
                                <div class="card-body">
                                    <h4>{{ voidedCount }}</h4>
                                    <small>Voided</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div v-if="transactions.data.length === 0" class="text-center py-5">
                        <i class="fa fa-receipt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Transactions Found</h5>
                        <p class="text-muted">You haven't made any transactions yet.</p>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="transaction in transactions.data" :key="transaction.id">
                                    <td>
                                        <strong>{{ transaction.invoice }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ formatDate(transaction.created_at) }}</small>
                                    </td>
                                    <td>
                                        {{ transaction.customer ? transaction.customer.name : 'Walk-in Customer' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ transaction.details ? transaction.details.length : 0 }} items
                                        </span>
                                    </td>
                                    <td>
                                        <strong>Rp {{ formatNumber(transaction.grand_total) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge" :class="getStatusBadgeClass(transaction.status)">
                                            <i :class="getStatusIcon(transaction.status)" class="me-1"></i>
                                            {{ getStatusText(transaction.status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Actions for completed transactions -->
                                        <div v-if="transaction.status === 'completed'" class="btn-group">
                                            <button @click="requestRefund(transaction.id)"
                                                    class="btn btn-warning btn-sm"
                                                    title="Request Refund">
                                                <i class="fa fa-undo"></i> Refund
                                            </button>
                                            <button @click="requestVoid(transaction.id)"
                                                    class="btn btn-danger btn-sm"
                                                    title="Request Void">
                                                <i class="fa fa-ban"></i> Void
                                            </button>
                                        </div>

                                        <!-- Status message for other statuses -->
                                        <div v-else-if="transaction.status === 'pending_approval'" class="text-warning small">
                                            <i class="fa fa-hourglass-half"></i> Waiting for approval...
                                        </div>
                                        <div v-else-if="transaction.status === 'refunded'" class="text-info small">
                                            <i class="fa fa-check"></i> Refunded
                                        </div>
                                        <div v-else-if="transaction.status === 'voided'" class="text-dark small">
                                            <i class="fa fa-check"></i> Voided
                                        </div>
                                        <div v-else class="text-muted small">
                                            No actions available
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <pagination :links="transactions.links" align="center" />
                </div>
            </div>
        </div>
    </div>

    <!-- Refund Modal -->
    <div class="modal fade" id="refundModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Refund</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitRefundRequest">
                        <div class="mb-3">
                            <label class="form-label">Reason for Refund:</label>
                            <textarea v-model="refundForm.reason"
                                     class="form-control"
                                     rows="3"
                                     placeholder="Please explain why you need to refund this transaction..."
                                     required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Refund Amount (Optional):</label>
                            <input type="number"
                                   v-model="refundForm.amount"
                                   class="form-control"
                                   :placeholder="'Full amount: Rp ' + formatNumber(selectedTransaction?.grand_total || 0)">
                            <small class="text-muted">Leave empty for full refund</small>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fa fa-undo me-1"></i> Request Refund
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Void Modal -->
    <div class="modal fade" id="voidModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Void</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitVoidRequest">
                        <div class="mb-3">
                            <label class="form-label">Reason for Void:</label>
                            <textarea v-model="voidForm.reason"
                                     class="form-control"
                                     rows="3"
                                     placeholder="Please explain why you need to void this transaction..."
                                     required></textarea>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> Voiding will cancel this transaction completely and restore stock.
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-ban me-1"></i> Request Void
                            </button>
                        </div>
                    </form>
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
        transactions: Object
    },

    data() {
        return {
            selectedTransaction: null,
            refundForm: {
                reason: '',
                amount: ''
            },
            voidForm: {
                reason: ''
            }
        }
    },

    computed: {
        completedCount() {
            return this.transactions.data.filter(t => t.status === 'completed').length;
        },
        pendingCount() {
            return this.transactions.data.filter(t => t.status === 'pending_approval').length;
        },
        refundedCount() {
            return this.transactions.data.filter(t => t.status === 'refunded').length;
        },
        voidedCount() {
            return this.transactions.data.filter(t => t.status === 'voided').length;
        }
    },

    methods: {
        requestRefund(transactionId) {
            this.selectedTransaction = this.transactions.data.find(t => t.id === transactionId);
            this.refundForm.reason = '';
            this.refundForm.amount = '';

            // Show modal (assuming Bootstrap 5)
            const modal = new bootstrap.Modal(document.getElementById('refundModal'));
            modal.show();
        },

        submitRefundRequest() {
            if (!this.refundForm.reason.trim()) {
                alert('Please provide a reason for the refund');
                return;
            }

            const data = {
                transaction_id: this.selectedTransaction.id,
                reason: this.refundForm.reason,
            };

            if (this.refundForm.amount) {
                data.amount = parseFloat(this.refundForm.amount);
            }

            Inertia.post('/apps/transactions/request-refund', data, {
                onSuccess: () => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('refundModal'));
                    modal.hide();
                }
            });
        },

        requestVoid(transactionId) {
            this.selectedTransaction = this.transactions.data.find(t => t.id === transactionId);
            this.voidForm.reason = '';

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('voidModal'));
            modal.show();
        },

        submitVoidRequest() {
            if (!this.voidForm.reason.trim()) {
                alert('Please provide a reason for void');
                return;
            }

            Inertia.post('/apps/transactions/request-void', {
                transaction_id: this.selectedTransaction.id,
                reason: this.voidForm.reason
            }, {
                onSuccess: () => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('voidModal'));
                    modal.hide();
                }
            });
        },

        formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getStatusBadgeClass(status) {
            const classes = {
                'completed': 'bg-success',
                'pending_approval': 'bg-warning',
                'refunded': 'bg-info',
                'voided': 'bg-dark'
            };
            return classes[status] || 'bg-secondary';
        },

        getStatusIcon(status) {
            const icons = {
                'completed': 'fa fa-check',
                'pending_approval': 'fa fa-hourglass-half',
                'refunded': 'fa fa-undo',
                'voided': 'fa fa-ban'
            };
            return icons[status] || 'fa fa-question';
        },

        getStatusText(status) {
            const texts = {
                'completed': 'Completed',
                'pending_approval': 'Pending Approval',
                'refunded': 'Refunded',
                'voided': 'Voided'
            };
            return texts[status] || status;
        }
    }
}
</script>

<style scoped>
.table th {
    border-top: none;
    font-weight: 600;
}

.btn-group .btn {
    margin-right: 0;
}

.card:hover {
    transform: translateY(-1px);
    transition: all 0.3s ease;
}
</style>
