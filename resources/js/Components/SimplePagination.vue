<template>
    <nav v-if="hasPagination">
        <ul :class="`pagination justify-content-${align} mb-0`">
            <!-- Previous Button -->
            <li class="page-item" :class="{ 'disabled': !hasPrevious }">
                <Link
                    class="page-link"
                    :href="previousUrl || '#'"
                    :class="{ 'disabled': !hasPrevious }"
                >
                    <i class="fas fa-chevron-left"></i>
                    <span class="d-none d-sm-inline ms-1">Previous</span>
                </Link>
            </li>

            <!-- Current Page Info -->
            <li class="page-item active">
                <span class="page-link">
                    {{ currentPage }} of {{ totalPages }}
                </span>
            </li>

            <!-- Next Button -->
            <li class="page-item" :class="{ 'disabled': !hasNext }">
                <Link
                    class="page-link"
                    :href="nextUrl || '#'"
                    :class="{ 'disabled': !hasNext }"
                >
                    <span class="d-none d-sm-inline me-1">Next</span>
                    <i class="fas fa-chevron-right"></i>
                </Link>
            </li>
        </ul>
    </nav>
</template>

<script>
    //import Link
    import { Link } from '@inertiajs/inertia-vue3';
    import { computed } from 'vue';

    export default {
        props: {
            links: Array,
            align: String
        },

        components: {
            Link,
        },

        setup(props) {
            // Check if pagination exists
            const hasPagination = computed(() => {
                return props.links && props.links.length > 1;
            });

            // Get previous page info
            const hasPrevious = computed(() => {
                return props.links && props.links.length > 0 && props.links[0].url !== null;
            });

            const previousUrl = computed(() => {
                return props.links && props.links.length > 0 ? props.links[0].url : null;
            });

            // Get next page info
            const hasNext = computed(() => {
                if (!props.links || props.links.length === 0) return false;
                const lastIndex = props.links.length - 1;
                return props.links[lastIndex].url !== null;
            });

            const nextUrl = computed(() => {
                if (!props.links || props.links.length === 0) return null;
                const lastIndex = props.links.length - 1;
                return props.links[lastIndex].url;
            });

            // Get current page and total pages
            const currentPage = computed(() => {
                if (!props.links) return 1;
                const activeLink = props.links.find(link => link.active);
                return activeLink ? activeLink.label : '1';
            });

            const totalPages = computed(() => {
                if (!props.links || props.links.length < 3) return 1;
                // Exclude first (Previous) and last (Next) links
                return props.links.length - 2;
            });

            return {
                hasPagination,
                hasPrevious,
                previousUrl,
                hasNext,
                nextUrl,
                currentPage,
                totalPages
            };
        }
    }
</script>

<style scoped>
.page-link.disabled {
    pointer-events: none;
    cursor: default;
    opacity: 0.6;
}

.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
}
</style>
