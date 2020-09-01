import Vue from 'vue';
import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate';
Vue.use(Vuex);

const set = key => (state, val) => {
    state[key] = val
}

const store = new Vuex.Store({
    state: {
        catalogCategory: SITEDATA.category_slug,
        productCount: 1,
        pageNum: 1,
        showLoader: false,
        loadingProducts: false,
        product: {},
        products: [],
        category_count: '',
        category_count_page: 21,
        searchString: SITEDATA.search_query,
        
        catalogSort: '',
        catalogPrices: [],
        catalogColors: [],
        catalogSizes: [],
        catalogWidths: [],
        catalogMaterials: [],
        favorites: [],
        // max_price_per_product_cat: parseInt(SITEDATA.max_price_per_product_cat),
        // min_price_per_product_cat: parseInt(SITEDATA.min_price_per_product_cat),

    },
    plugins: [createPersistedState({
        reducer: state => ({
            favorites: state.favorites,          
        })
    })],
    getters: {
        getProductCount: state => state.productCount,
        allProducts: (state, getters) => {
            return state.products
        },
    },
    mutations: {
        ALL_PRODUCTS(state) {
            state.showLoader = true
        },
        ALL_PRODUCTS_SUCCESS(state, payload) {
            state.showLoader = false;
            state.products = payload;
        },

        updateCatalogSort: set('catalogSort'),
        updateSearchString: set('searchString'),
        updateCategoryCount: set('category_count'),
        updateCategoryCountPage: set('category_count_page'),
        updatePageNum: set('pageNum'),
        updateLoadingProducts: set('loadingProducts'),
        updateProductCount: set('productCount'),
        updateCatalogCategory: set('catalogCategory'),
        updateCatalogColors: set('catalogColors'),
        updateCatalogPrices: set('catalogPrices'),
        updateCatalogSizes: set('catalogSizes'),
        updateCatalogWidths: set('catalogWidths'),
        updateCatalogMaterials: set('catalogMaterials'),
        updateFavorites: set('favorites'),

        clearFavorites(state) {
            state.favorites = [];
        },  
        removeFavorites(state, value) {
            var index = state.favorites.indexOf(value);
            if (index > -1) {
                state.favorites.splice(index, 1);
            }
        },   
        addFavorites(state, value) {
            if (state.favorites) {
                state.favorites.push(value);
            }
            else{
                state.favorites = [];
                state.favorites.push(value);
            }
        },

    },
    actions: {
        async allProducts ({commit}, vm ) {
            try {
            commit('ALL_PRODUCTS');
            let catalogCategory = this.state.catalogCategory !=='null' ? this.state.catalogCategory: '';
            let rangePrice = this.state.catalogPrices;
            let catalogColors = this.state.catalogColors;
            let catalogSizes = this.state.catalogSizes;
            let catalogMaterials = this.state.catalogMaterials;
            let catalogPaged = this.state.pageNum ?  this.state.pageNum : SITEDATA.paged;
            let catalogWidths = this.state.catalogWidths;
            let searchString = this.state.searchString;
            let catalogSort = this.state.catalogSort;
            let searchData = `product-cat=${catalogCategory}&sizes=${catalogSizes}&materials=${catalogMaterials}&colors=${catalogColors}&paged=${catalogPaged}&widths=${catalogWidths}&range_price=${rangePrice}&sort=${catalogSort}`;
            let responseProducts = "";


            function isAttibute(element) {
                //TODO: получать атрибуты из wp
                let AttibuteParametrNames = [
                    'colors',
                    'materials',
                    'patterns',
                    'sizes',
                ];
                if (AttibuteParametrNames.includes(element)) {
                    return element;
                }
                else{
                    return false;
                }
            }

            let pathArray = window.location.pathname.split('/');
            let pathArrayFiltered = pathArray.filter((el) => {return el != ''});
            let AttibuteParametr = pathArrayFiltered[pathArrayFiltered.findIndex(isAttibute)];
            let AttibuteValue = pathArrayFiltered[pathArrayFiltered.findIndex(isAttibute) + 1];
            
            commit('updateLoadingProducts', true);
            if(SITEDATA.category_slug || (SITEDATA.is_shop === 'true')){
                console.log('glasse branch');
                responseProducts = await fetch(`${SITEDATA.url}/wp-json/amadreh/v1/get-products/?${searchData}`);
            }
            else if (SITEDATA.category_slug && !(SITEDATA.is_filter === 'true')) {
                console.log('filter branch');
                responseProducts = await fetch(`${SITEDATA.url}/wp-json/amadreh/v1/get-products/?${searchData}&product-cat=${SITEDATA.category_slug}&paged=${catalogPaged}`);
            }
            else if(SITEDATA.current_brand){
                console.log('current_brand branch');
                responseProducts = await fetch(`${SITEDATA.url}/wp-json/amadreh/v1/get-products/?brands=${SITEDATA.current_brand}&paged=${catalogPaged}&order_by=${catalogItemsOrderBy}`);
            }
            else if(AttibuteParametr && AttibuteValue){
                console.log('AttibuteParametr branch');
                responseProducts = await fetch(`${SITEDATA.url}/wp-json/amadreh/v1/get-products/?${AttibuteParametr}=${AttibuteValue}&paged=${catalogPaged}&order_by=${catalogItemsOrderBy}`);
            }
            else if(SITEDATA.is_search === 'true'){
                console.log('search branch');
                let product_name = searchString;
                responseProducts = await fetch(`${SITEDATA.url}/wp-json/amadreh/v1/get-products?search=${product_name}&${searchData}`);
            }
            
            if(responseProducts){
                const dataProducts = await responseProducts.json();
                commit('ALL_PRODUCTS_SUCCESS', dataProducts.data.posts);
                commit('updateCategoryCount', dataProducts.data.found_posts);
                commit('updateCategoryCountPage', Math.ceil(dataProducts.data.found_posts / 21));
            }
            commit('updateLoadingProducts', false);

            } catch (error) {
                console.error(error);
            }
        },
    },
});

export default store;