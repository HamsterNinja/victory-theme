<template>
    <div class="products-slider" :class="classContainer">
        <div class="container">
            <transition name="fade">
                <div class="loader-overlay" v-if="loading">
                    <div class="loader"></div>
                </div>
            </transition>
            <div class="main-page-title">
                <a :href="link">{{ linkTitle }}</a>
            </div>
            <transition-group name="products" tag="section" class="products-slick">
                <template v-if="products.length > 0" v-for="(product, index) in products">
                    <product-item-slide :product="product" :key='index'></product-item-slide>
                </template>
            </transition-group>
        </div>
    </div>
</template>

<script>
export default {
    props: ["ids", "classContainer", "link", "linkTitle"],
    data() {
        return {
            loading: true,
            products: [],
            site_url: SITEDATA.url
        };
    },
    async mounted () {
        this.loading = true;
        const requestData = {
            url: SITEDATA.url + `/wp-json/amadreh/v1/get-products/`,
            method: 'GET'
        };   
        const postGet = requestData.url + '?include=' + this.ids;
        const response = await fetch(postGet);
        try{
            const json = await response.json();
            this.products = json.data.posts;
        }
        catch (e) { 
            console.log(`Failed to retrieve product informations: (${e.message})`);
        };
        this.loading = false;
    },
    updated() {
        const settingsDefaultSlider = {
            infinite: false,
            slidesToShow: 6,
            slidesToScroll: 1,
            arrows: true,
            swipe: false,
            dots: false,
            responsive: [{
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 1,
                    }
                },
                {
                    breakpoint: 840,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1,
                    }
                },
                {
                    breakpoint: 580,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                    }
                },
                {
                    breakpoint: 420,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    }
                }
            ]
        };
        $(`.${this.classContainer} .products-slick`).slick(settingsDefaultSlider);
    },
};
</script>