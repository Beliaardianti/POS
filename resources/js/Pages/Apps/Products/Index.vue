<template>
    <Head>
        <title>Products - Aplikasi POS</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-purple">
                            <div class="card-header">
                                <span class="font-weight-bold"><i class="fa fa-shopping-bag"></i> PRODUCTS</span>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="handleSearch">
                                    <div class="input-group mb-3">
                                        <Link href="/apps/products/create" v-if="hasAnyPermission(['products.create'])" class="btn btn-primary input-group-text">
                                            <i class="fa fa-plus-circle me-2"></i> NEW
                                        </Link>

                                        <!-- TOMBOL EXPORT DITAMBAHKAN -->
                                        <button @click.prevent="exportProducts" class="btn btn-success input-group-text" type="button">
                                            <i class="fa fa-download me-2"></i> EXPORT
                                        </button>

                                        <input type="text" class="form-control" v-model="search" placeholder="search by product title...">

                                        <button class="btn btn-primary input-group-text" type="submit">
                                            <i class="fa fa-search me-2"></i> SEARCH
                                        </button>
                                    </div>
                                </form>

                                <!-- ALERT SECTION UNTUK LOW STOCK & EXPIRED -->
                                <div v-if="lowStockAlert.length > 0" class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <strong><i class="fa fa-exclamation-triangle"></i> Low Stock Alert!</strong>
                                    <span v-for="(item, index) in lowStockAlert" :key="index">
                                        {{ item.title }} (Stock: {{ item.stock }}/{{ item.minimum_stock }}){{ index < lowStockAlert.length - 1 ? ', ' : '' }}
                                    </span>
                                    <button type="button" class="btn-close" @click="lowStockAlert = []"></button>
                                </div>

                                <div v-if="expiredAlert.length > 0" class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong><i class="fa fa-clock"></i> Expired Products!</strong>
                                    <span v-for="(item, index) in expiredAlert" :key="index">
                                        {{ item.title }} (Expired: {{ formatDate(item.expired_date) }}){{ index < expiredAlert.length - 1 ? ', ' : '' }}
                                    </span>
                                    <button type="button" class="btn-close" @click="expiredAlert = []"></button>
                                </div>

                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">Barcode</th>
                                            <th scope="col">Title</th>
                                            <th scope="col">Buy Price</th>
                                            <th scope="col">Sell Price</th>
                                            <th scope="col">Stock</th>
                                            <!-- KOLOM TAMBAHAN -->
                                            <th scope="col">Min Stock</th>
                                            <th scope="col">Location</th>
                                            <th scope="col">Expired</th>
                                            <th scope="col" style="width:20%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(product, index) in products.data" :key="index"
                                            :class="{
                                                'table-warning': product.stock <= product.minimum_stock,
                                                'table-danger': isExpired(product.expired_date)
                                            }">
                                            <td class="text-center">
                                                <Barcode
                                                    :value="product.barcode"
                                                    :format="'CODE39'"
                                                    :lineColor="'#000'"
                                                    :width="1"
                                                    :height="20"
                                                />
                                            </td>
                                            <td>
                                                {{ product.title }}
                                                <small v-if="product.stock <= product.minimum_stock" class="text-warning d-block">
                                                    <i class="fa fa-exclamation-triangle"></i> Low Stock
                                                </small>
                                                <small v-if="isExpired(product.expired_date)" class="text-danger d-block">
                                                    <i class="fa fa-clock"></i> Expired
                                                </small>
                                            </td>
                                            <td>Rp. {{ formatPrice(product.buy_price) }}</td>
                                            <td>Rp. {{ formatPrice(product.sell_price) }}</td>
                                            <td>
                                                <span :class="{'text-warning fw-bold': product.stock <= product.minimum_stock}">
                                                    {{ product.stock }}
                                                </span>
                                            </td>
                                            <!-- DATA TAMBAHAN -->
                                            <td>{{ product.minimum_stock || 0 }}</td>
                                            <td>
                                                <span class="badge" :class="product.location === 'Gudang' ? 'bg-secondary' : 'bg-info'">
                                                    {{ product.location || 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span v-if="product.expired_date" :class="{'text-danger': isExpired(product.expired_date)}">
                                                    {{ formatDate(product.expired_date) }}
                                                </span>
                                                <span v-else class="text-muted">-</span>
                                            </td>
                                            <td class="text-center">
                                                <Link :href="`/apps/products/${product.id}/edit`" v-if="hasAnyPermission(['products.edit'])" class="btn btn-success btn-sm me-2">
                                                    <i class="fa fa-pencil-alt me-1"></i> EDIT
                                                </Link>
                                                <button @click.prevent="destroy(product.id)" v-if="hasAnyPermission(['products.delete'])" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-trash"></i> DELETE
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <Pagination :links="products.links" align="end"/>
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

    //import component pagination
    import Pagination from '../../../Components/Pagination.vue';

    //import Heade and Link from Inertia
    import { Head, Link } from '@inertiajs/inertia-vue3';

    //import ref from vue
    import { ref, onMounted } from 'vue';

    //import inertia adapter
    import { Inertia } from '@inertiajs/inertia';

    //import sweet alert2
    import Swal from 'sweetalert2';

    //import component barcode
    import Barcode from '../../../Components/Barcode.vue';

    //import axios for API calls
    import axios from 'axios';

    export default {
        //layout
        layout: LayoutApp,

        //register components
        components: {
            Head,
            Link,
            Pagination,
            Barcode
        },

        //props
        props: {
            products: Object,
        },

        //composition API
        setup() {

            //define state search
            const search = ref('' || (new URL(document.location)).searchParams.get('q'));

            //define alert states
            const lowStockAlert = ref([]);
            const expiredAlert = ref([]);

            //define method search
            const handleSearch = () => {
                Inertia.get('/apps/products', {
                    //send params "q" with value from state "search"
                    q: search.value,
                });
            }

            //method "destroy"
            const destroy = (id) => {
                //show confirm
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        //send to server
                        Inertia.delete(`/apps/products/${id}`);

                        //show alert
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Product deleted successfully.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    }
                })
            }

            //method "exportProducts" - BARU DITAMBAHKAN
            const exportProducts = () => {
                Swal.fire({
                    title: 'Exporting...',
                    text: 'Please wait while we prepare your file.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Trigger download
               window.location.href = '/apps/products/export';

                setTimeout(() => {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Export completed successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                }, 2000);
            }

            //method "checkAlerts" - CEK LOW STOCK & EXPIRED
            const checkAlerts = async () => {
                try {
                    // Check low stock
                    const lowStockResponse = await axios.get('/apps/products/low-stock');
                    if (lowStockResponse.data.low_stock_products.length > 0) {
                        lowStockAlert.value = lowStockResponse.data.low_stock_products;
                    }

                    // Check expired
                    const expiredResponse = await axios.get('/apps/products/expired');
                    if (expiredResponse.data.expired_products.length > 0) {
                        expiredAlert.value = expiredResponse.data.expired_products;
                    }
                } catch (error) {
                    console.log('Error checking alerts:', error);
                }
            }

            //utility methods
            const formatPrice = (price) => {
                return new Intl.NumberFormat('id-ID').format(price);
            }

            const formatDate = (date) => {
                if (!date) return '-';
                return new Date(date).toLocaleDateString('id-ID');
            }

            const isExpired = (date) => {
                if (!date) return false;
                return new Date(date) <= new Date();
            }

            //lifecycle
            onMounted(() => {
                checkAlerts();
            });

            return {
                search,
                handleSearch,
                destroy,
                exportProducts,
                lowStockAlert,
                expiredAlert,
                formatPrice,
                formatDate,
                isExpired
            }
        }
    }
</script>

<style>
.border-top-purple {
    border-top: 3px solid #6f42c1 !important;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.table-danger {
    background-color: #dc35451a !important;
}

.alert {
    border-radius: 0.5rem;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}
</style>
