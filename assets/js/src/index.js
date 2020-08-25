import 'babel-polyfill'
import Vue from 'vue'
import Vuex from 'vuex'
import { mapState, mapGetters } from 'vuex'
Vue.use(Vuex)
Vue.use(mapState)
Vue.use(mapGetters)
import store from './store'
import Vuetify from 'vuetify'
Vue.use(Vuetify)

import ProductList from './components/ProductList.vue'
Vue.component('product-list', ProductList)

import ProductItem from './components/ProductItem.vue'
Vue.component('product-item', ProductItem)

import favorite from './components/favorite.vue'
Vue.component('favorite', favorite)

import ProductFavorite from './components/ProductFavorite.vue'
Vue.component('product-favorite', ProductFavorite)

import Paginate from 'vuejs-paginate'
Vue.component('paginate', Paginate)

import numeral from 'numeral'
numeral.register('locale', 'ru', {
    delimiters: {
        thousands: ' ',
        decimal: ','
    },
    abbreviations: {
        thousand: 'тыс.',
        million: 'млн.',
        billion: 'млрд.',
        trillion: 'трлн.'
    },
    ordinal: function() {
        return '.'
    },
    currency: {
        symbol: 'руб.'
    }
})

numeral.locale('ru')

import { modal } from './components/mixins/modal'

Vue.filter('clearText', function (text) {
    return text.replace("Размер", "")
})

document.addEventListener('DOMContentLoaded', () => {
    let elVue = '#app'
    let elVueQuery = document.querySelector(elVue)

    if (elVueQuery) {
        const app = new Vue({
            el: '#app',
            vuetify: new Vuetify(),
            mixins: [modal],
            store,
            delimiters: ['((', '))'],
            data() {
                return {
                    sortItems: [
                        { label: 'По популярности', value: 'popular' },
                        { label: 'По убыванию цены', value: 'price_desc' },
                        { label: 'По возрастанию цены', value: 'price_asc' },
                        { label: 'По умолчанию', value: 'date_desc' },
                    ],
                    materialItems: [
                        { label: '', value: '' },
                        { label: 'хлопок', value: 'popular' },
                        { label: 'шерсть', value: 'price_desc' },
                        { label: 'шелк', value: 'price_asc' },
                        { label: 'вискоза', value: 'date_desc' },
                    ],
                    sort: 'date_desc',
                    material: '',
                    price: [100, 15000],
                    chips: [],
                    items: SITEDATA.sizes,
                    loading: true,
                    adding: false,
                    favorite_products: [],
                    productVariations: [],

                    is_product: SITEDATA.is_product == 'true',
                    productID: SITEDATA.product_id,
                    selectedProductSize: ''
                }
            },
            computed: {
                ...mapState(['category_count_page', 'category_count', 'pageNum', 'productCount', 'favorites', 'max_price_per_product_cat', 'min_price_per_product_cat']),
                currentProductVariation() {
                    if (this.productVariations.length !== 0) {
                        let object = this.productVariations.find((x) => x.value === this.productType)
                        this.productID = object.id
                        this.currentProductSize = object.attribute_razmer
                        return object
                    }
                },
                uniqueProductVariations() {
                    let result = this.productVariations.filter(
                        variation => variation.label !== 'образец' && variation.value.toLowerCase() !== 'образец' && variation.value !== null && variation.stock_quality !== null
                    )
                    return result.sort((a, b) => parseFloat(a.value) - parseFloat(b.value));
                },
            },
            methods: {
                remove(item) {
                    this.chips.splice(this.chips.indexOf(item), 1)
                    this.chips = [...this.chips]
                },
                clearFavorites() {
                    this.$store.commit('clearFavorites')
                    this.favorite_products = []
                },
                selectPage(pageNum) {
                    this.pageNum = pageNum
                    this.$store.commit('updatePageNum', pageNum)
                    store.dispatch('allProducts')
                },
                applyFilter(storevalue, value) {
                    let methodName = 'update' + storevalue.charAt(0).toUpperCase() + storevalue.slice(1)
                    this.$store.commit(methodName, value)
                    store.dispatch('allProducts')
                },
                async addCart(ID) {
                    this.adding = true
                    let formProduct = new FormData()
                    formProduct.append('action', 'add_one_product')
                    formProduct.append('product_id', ID)
                    formProduct.append('quantity', 1)

                    this.$refs.button_cart.innerText = 'Товар добавляется'

                    // extra options
                    formProduct.append('size', this.selectedProductSize)

                    let fetchData = {
                        method: 'POST',
                        body: formProduct
                    }
                    let response = await fetch(wc_add_to_cart_params.ajax_url, fetchData)
                    let jsonResponse = await response.json()
                    if (jsonResponse.error != 'undefined' && jsonResponse.error || jsonResponse.success === false) {
                        this.$refs.button_cart.innerText = 'Ошибка добавления товара'
                        console.error('ошибка добавления товара')
                    } else if (jsonResponse.success) {
                        this.$refs.button_cart.innerText = 'Товар добавлен'
                        setTimeout(() => {this.showModal('modal-window--added-to-cart')}, 0)
                        this.updateFragment()
                    }
                    this.adding = false
                },
                async updateFragment() {
                    let response = await fetch(wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'), {
                        method: 'POST'
                    })
                    let data = await response.json()
                    if (data && data.fragments) {
                        $.each(data.fragments, function(key, value) {
                            $(key).replaceWith(value)
                        })
                        $(document.body).trigger('wc_fragments_refreshed')
                    }
                },
                selectSize(size) {
                    this.selectedProductSize = size
                }
            },
            async mounted() {
                //favorites
                const fetchDataFavorite = SITEDATA.current_user_id
                if (fetchDataFavorite == true) {
                    const responseFavorite = await fetch(`${SITEDATA.url}/wp-json/amadreh/v1/get-favorite?user_id=${fetchDataFavorite}`)
                    const dataFavorite = await responseFavorite.json()
                    this.favorites = Object.values(dataFavorite.data)
                }
                if (this.is_product) {
                    const requestDataProductVariations = {
                        url: SITEDATA.url + '/wp-json/amadreh/v1/get-variations/?post_parent=' + SITEDATA.product_id,
                        method: 'GET'
                    }
                    const urlProduct = requestDataProductVariations.url
                    const responseProductVariations = await fetch(urlProduct)
                    const dataProductVariations = await responseProductVariations.json()
                    this.productVariations = dataProductVariations.data
                    if (dataProductVariations.data) {
                        this.productType = dataProductVariations.data[0].value
                    }
                }
            }
        })
    }
})

$(document).ready(function() {
    $('.btn-hamburger').click(function(e) {
        $(this).toggleClass('active')
        $('.hidden-menu_block').toggleClass('active')
        $('.overlay').toggleClass('active')
        $('.hidden-search_block').removeClass('active')
    })
    $('.overlay').click(function(e) {
        $(this).removeClass('active')
        $('.btn-hamburger').removeClass('active')
        $('.hidden-menu_block').removeClass('active')
    })

    $('.js-search').click(function(e) {
        $('.btn-hamburger').removeClass('active')
        $('.btn-hamburger')
            .parent()
            .removeClass('active')
        $('.hidden-menu_block').removeClass('active')
        $('.hidden-search_block').toggleClass('active')
        $('.overlay').removeClass('active')
    })

    $('.choice-button').click(function(event) {
        event.preventDefault()
        $('.choice-button').removeClass('active')
        $(this).addClass('active')

        var id = $(this).attr('data-id')
        if (id) {
            $('.collections-content-inner:visible').fadeOut(0, function() {
                $('.collections-content')
                    .find('#' + id)
                    .fadeIn('slow', function() {
                        $('.collections-slick').slick('reinit')
                    })
            })
        }
    })

    $('.collections-slick').slick({
        infinite: true,
        slidesToShow: 4,
        slidesToScroll: 4,
        arrows: true,
        dots: false,
        responsive: [
            {
                breakpoint: 1280,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 4,
                    infinite: true,
                    dots: false
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    })

    $('.product-slick-for').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: true,
        asNavFor: '.product-slick-nav'
    })
    $('.product-slick-nav').slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        asNavFor: '.product-slick-for',
        focusOnSelect: true,
        vertical: true
    })
    if ($('.home').length) {
        if ($(window).outerWidth() > 1025) {
            onepagescroll('.main-content.pages', {
                pageContainer: 'section',
                animationType: 'ease-in-out',
                animationTime: 500,
                infinite: false,
                pagination: true,
                keyboard: true,
                direction: 'vertical'
            })
        }

        if (window.getComputedStyle(document.body).mixBlendMode == undefined) $('.ops-navigation').addClass('curtain')
    }
})
