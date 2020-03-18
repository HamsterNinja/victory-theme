<template v-if="product.stock_status == 'instock'">
    <a class="catalog-content-item" :href="product.permalink">
		<div class="catalog-content-item-img">
			<img v-if="product.images[0].woocommerce_single" :src="product.images[0].woocommerce_single" :alt="product.name"/>
            <img v-else :src="template_url + '/assets/images/placeholder-images.webp'" :alt="product.name">
		</div>
		<div class="catalog-content-item-name">
            <h4>{{ product.name }}</h4>
        </div>
		<div class="catalog-content-item-price">
            <template v-if="product.sale_price">
                    <div class="product-item-price">
                        <span v-html="product.sale_price"></span>
                        <span>₽</span>
                    </div>
                    <div class="product-item-old_price" v-if="product.regular_price">
                        <span v-html="product.price_html[0]"></span>
                        <span>₽</span>
                    </div>
                </template>
                <template v-else-if="product.regular_price">
                    <div class="product-item-price" v-if="product.regular_price">
                        <span v-html="product.price_html[0]"></span>
                        <span>₽</span>
                    </div>
                </template>
                <template v-else>
                    <div class="product-item-price" v-if="product.price_html[0]">
                        <span v-html="product.price_html[0]"></span>
                        <span>₽</span>
                    </div>
                    <div class="product-item-old_price" v-if="product.price_html[1]">
                        <span v-html="product.price_html[1]"></span>
                        <span>₽</span>
                    </div>
                </template>
        </div>
	</a>
</template>

<script>
export default {
    name: "product-item",
    props: {
        product: Object
    },
    mounted() {},
    data() {
        return {
            template_url: SITEDATA.themepath,
            is_home: SITEDATA.is_home == "true",
        };
    },
    computed: {},
    methods: {}
};
</script>