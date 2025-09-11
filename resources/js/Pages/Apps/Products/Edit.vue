<template>
    <Head>
        <title>Edit Product - Aplikasi POS</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-purple">
                            <div class="card-header">
                                <span class="font-weight-bold"><i class="fa fa-shopping-bag"></i> EDIT PRODUCT</span>
                            </div>
                            <div class="card-body">

                                <form @submit.prevent="submit">
                                    <div class="mb-3">
                                        <label class="fw-bold">Product Image</label>
                                        <input class="form-control" @input="form.image = $event.target.files[0]" :class="{ 'is-invalid': errors.image }" type="file">
                                        <small class="text-muted">Leave empty if you don't want to change the image</small>
                                    </div>
                                    <div v-if="errors.image" class="alert alert-danger">
                                        {{ errors.image }}
                                    </div>

                                    <!-- Current Image Preview -->
                                    <div class="mb-3" v-if="product.image">
                                        <label class="fw-bold">Current Image</label>
                                        <div>
                                            <img :src="product.image" alt="Current Product Image" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold">Barcode</label>
                                                <input class="form-control" v-model="form.barcode" :class="{ 'is-invalid': errors.barcode }" type="text" placeholder="Barcode / Code Product">
                                            </div>
                                            <div v-if="errors.barcode" class="alert alert-danger">
                                                {{ errors.barcode }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold">Category</label>
                                                <select class="form-select" :class="{ 'is-invalid': errors.category_id }" v-model="form.category_id">
                                                    <option value="">-- Select Category --</option>
                                                    <option v-for="(category, index) in categories" :key="index" :value="category.id">{{ category.name }}</option>
                                                </select>
                                            </div>
                                            <div v-if="errors.category_id" class="alert alert-danger">
                                                {{ errors.category_id }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold">Title Product</label>
                                                <input class="form-control" v-model="form.title" :class="{ 'is-invalid': errors.title }" type="text" placeholder="Title Product">
                                            </div>
                                            <div v-if="errors.title" class="alert alert-danger">
                                                {{ errors.title }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold">Stock</label>
                                                <input class="form-control" v-model="form.stock" :class="{ 'is-invalid': errors.stock }" type="number" placeholder="Stock" min="0">
                                            </div>
                                            <div v-if="errors.stock" class="alert alert-danger">
                                                {{ errors.stock }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">Description</label>
                                        <textarea class="form-control" v-model="form.description" :class="{ 'is-invalid': errors.description }" rows="4" placeholder="Description"></textarea>
                                    </div>
                                    <div v-if="errors.description" class="alert alert-danger">
                                        {{ errors.description }}
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold">Buy Price</label>
                                                <input class="form-control" v-model="form.buy_price" :class="{ 'is-invalid': errors.buy_price }" type="number" placeholder="Buy Price" min="0" step="0.01">
                                            </div>
                                            <div v-if="errors.buy_price" class="alert alert-danger">
                                                {{ errors.buy_price }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold">Sell Price</label>
                                                <input class="form-control" v-model="form.sell_price" :class="{ 'is-invalid': errors.sell_price }" type="number" placeholder="Sell Price" min="0" step="0.01">
                                            </div>
                                            <div v-if="errors.sell_price" class="alert alert-danger">
                                                {{ errors.sell_price }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- NEW FIELDS SECTION -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold">Minimum Stock</label>
                                                <input class="form-control" v-model="form.minimum_stock" :class="{ 'is-invalid': errors.minimum_stock }" type="number" placeholder="Minimum Stock Alert" min="0">
                                            </div>
                                            <div v-if="errors.minimum_stock" class="alert alert-danger">
                                                {{ errors.minimum_stock }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold">Expired Date</label>
                                                <input class="form-control" v-model="form.expired_date" :class="{ 'is-invalid': errors.expired_date }" type="date">
                                            </div>
                                            <div v-if="errors.expired_date" class="alert alert-danger">
                                                {{ errors.expired_date }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">Location</label>
                                        <select class="form-select" v-model="form.location" :class="{ 'is-invalid': errors.location }">
                                            <option value="">-- Select Location --</option>
                                            <option value="Gudang">Gudang</option>
                                            <option value="Display">Display</option>
                                        </select>
                                    </div>
                                    <div v-if="errors.location" class="alert alert-danger">
                                        {{ errors.location }}
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button class="btn btn-primary shadow-sm rounded-sm" type="submit" :disabled="form.processing">
                                                <i class="fa fa-save"></i> UPDATE
                                            </button>
                                            <button class="btn btn-warning shadow-sm rounded-sm ms-3" type="button" @click="resetForm">
                                                <i class="fa fa-refresh"></i> RESET
                                            </button>
                                            <Link :href="`/apps/products`" class="btn btn-secondary shadow-sm rounded-sm ms-3">
                                                <i class="fa fa-arrow-left"></i> BACK
                                            </Link>
                                        </div>
                                    </div>
                                </form>

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

    //import Heade and Link from Inertia
    import { Head, Link } from '@inertiajs/inertia-vue3';

    //import reactive from vue
    import { reactive } from 'vue';

    //import inerita adapter
    import { Inertia } from '@inertiajs/inertia';

    //import sweet alert2
    import Swal from 'sweetalert2';

    export default {
        //layout
        layout: LayoutApp,

        //register components
        components: {
            Head,
            Link
        },

        //props
        props: {
            errors: Object,
            categories: Array,
            product: Object
        },

        //composition API
        setup(props) {

            //define form with reactive - UPDATED WITH ALL FIELDS
            const form = reactive({
                image: '',
                barcode: props.product.barcode,
                category_id: props.product.category_id,
                title: props.product.title,
                description: props.product.description,
                buy_price: props.product.buy_price,
                sell_price: props.product.sell_price,
                stock: props.product.stock,

                // NEW FIELDS - ADDED
                minimum_stock: props.product.minimum_stock || 0,
                expired_date: props.product.expired_date || '',
                location: props.product.location || 'Gudang',
                processing: false
            });

            //method "submit" - UPDATED WITH ALL FIELDS
            const submit = () => {
                form.processing = true;

                //send data to server
                Inertia.post(`/apps/products/${props.product.id}`, {
                    //data - INCLUDING ALL NEW FIELDS
                    _method: 'PUT',
                    image: form.image,
                    barcode: form.barcode,
                    category_id: form.category_id,
                    title: form.title,
                    description: form.description,
                    buy_price: form.buy_price,
                    sell_price: form.sell_price,
                    stock: form.stock,

                    // NEW FIELDS DATA
                    minimum_stock: form.minimum_stock,
                    expired_date: form.expired_date,
                    location: form.location
                }, {
                    onSuccess: () => {
                        //show success alert
                        Swal.fire({
                            title: 'Success!',
                            text: 'Product updated successfully.',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    },
                    onError: (errors) => {
                        //show error alert
                        Swal.fire({
                            title: 'Error!',
                            text: 'Please check your input and try again.',
                            icon: 'error',
                            showConfirmButton: true,
                        });
                    },
                    onFinish: () => {
                        form.processing = false;
                    }
                });
            }

            //method "resetForm" - RESET TO ORIGINAL VALUES
            const resetForm = () => {
                form.image = '';
                form.barcode = props.product.barcode;
                form.category_id = props.product.category_id;
                form.title = props.product.title;
                form.description = props.product.description;
                form.buy_price = props.product.buy_price;
                form.sell_price = props.product.sell_price;
                form.stock = props.product.stock;
                form.minimum_stock = props.product.minimum_stock || 0;
                form.expired_date = props.product.expired_date || '';
                form.location = props.product.location || 'Gudang';
            }

            return {
                form,
                submit,
                resetForm
            }
        }
    }
</script>

<style>
.border-top-purple {
    border-top: 3px solid #6f42c1 !important;
}

.form-control:focus,
.form-select:focus {
    border-color: #6f42c1;
    box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
}

.btn-primary {
    background-color: #6f42c1;
    border-color: #6f42c1;
}

.btn-primary:hover {
    background-color: #5a32a3;
    border-color: #5a32a3;
}

.alert-danger {
    font-size: 0.875rem;
    margin-top: 0.25rem;
    margin-bottom: 0;
}

.img-thumbnail {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 0.25rem;
}
</style>
