<template>
    <div class="products-items-container">
        <transition name="fade">
            <div class="loader-overlay" v-if="loadingProducts">
                <div class="loader"></div>
            </div>
        </transition>
        <transition-group name="products" tag="section" class="catalog-content-inner" :style="[products.length == 0 ? {marginBottom: '0px'} : {marginBottom: '60px'}]">
            <template v-if="products.length > 0" v-for="(product, index) in products">
                <product-item :product="product" :class="classItem" :key="index"></product-item>
            </template>
        </transition-group>
        <template v-if="products.length == 0 && !loadingProducts">
            <div class="not-content">Категория пуста</div>
        </template>
    </div>
</template>

<script>
export default {
    name: "product-list",
    props: ["classItem"],
    created() {
        this.$store.dispatch("allProducts");
    },
    data() {
        return {
            template_url: SITEDATA.themepath
        };
    },
    computed: {
        products() {
            return this.$store.getters.allProducts;
        },
        loadingProducts () {
            return this.$store.state.loadingProducts
        }
    },
};
</script>