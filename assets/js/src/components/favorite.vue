<template>
  <a class="btn-favorite" href="" v-on:click.prevent="setFavorite" :class="{ 'favorited': favorited}"><img :src="template_url + '/assets/images/heart.svg'" alt=""></a>
</template>

<script>
export default {
  props: {
    productId: Number,
    width: Number,
    height: Number,
    favorites: Array,
    color:{
      type: String,
      required: false,
      default: '000'
    },
    textbutton:{
      type: String,
      required: false,
      default: ''
    }
  },
  mounted(){
    if (this.favorites.includes(String(this.productId))) {
      this.$refs.favoriteButton.parentNode.classList.add('show');
    }
  },
  data() {
        return {
            template_url: SITEDATA.themepath,
            is_home: SITEDATA.is_home == "true",
        };
    },
  computed: {
      colorSVG: function () {
        return `#${this.color}`;
      },
			favorited: function () {
          if (this.favorites) {
            if (this.favorites.includes(String(this.productId))) {
              return true;
            }
            else{
              return false;
            }
          }
          else{
            return false;
          }
      }
  },
  methods: {
      removeFavorite(dataFavorite, vm){
        //Заглушка для добавления в базу
      },
      addFavorite(dataFavorite, vm){
        //Заглушка для добавления в базу
      },
      setFavorite(){
        let vm = this;
        let dataFavorite = new FormData();
        dataFavorite.append('product_id', this.productId);
        dataFavorite.append('user_id', SITEDATA.current_user_id); 
        if(this.favorited){
          this.$store.commit('removeFavorites', this.productId.toString());
          this.removeFavorite(dataFavorite, vm);
        }
        else{
          this.$store.commit('addFavorites', this.productId.toString());
          if (this.favorites.includes(String(this.productId))) {
            this.addFavorite(dataFavorite, vm);
          }
        }
      },
    
  }
};
</script>

<style lang="scss" scoped>
.product-variations-title__favorite {
  padding: 0;
  border: none;
  font: inherit;
  color: inherit;
  background-color: transparent;
  cursor: pointer;
  text-decoration: none;
  outline: none;
  font-size: 14px;
  line-height: 16px;
  color: #808D9A;
  display: inline-flex;
  justify-content: flex-start;
  align-items: center;
  span{
    padding-left: 5px;
  }
  svg{
	  path{
		  transition: 0.3s;
	  }
  }
  &.favorited {
    opacity: 1;
    svg {
		path{
			fill: #EA2336;
		}
	}
  }
}
</style>