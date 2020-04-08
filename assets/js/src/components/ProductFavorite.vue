<template>
<div class="products-items-container">
       
        <transition-group name="products" tag="section" class="catalog-content-inner" >
            <template v-if="favorite_products.length > 0" v-for="(product, index) in favorite_products">
                <product-item :product="product"  :key="index"></product-item>
            </template>
        </transition-group>
    </div>
</template>
<script>
  export default {
    name: 'product-favorite',
    async created () {
      if (this.$root.favorite_products.length === 0) {
            let productIDs = this.favorites.join();
            if (productIDs) {
                const responseFavoriteProducts = await fetch(`${SITEDATA.url}//wp-json/amadreh/v1/get-products/?include=${productIDs}`);
                const dataFavoriteProducts = await responseFavoriteProducts.json();
                this.$root.favorite_products = dataFavoriteProducts.data.posts;  
                
            }
      }
    },
    computed: {
      favorites () {
        return  this.$store.state.favorites;
      },
      favorite_products () {
        return  this.$root.favorite_products;
      }
    },
  }
</script>