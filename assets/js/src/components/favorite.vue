<template>
  <a class="btn-favorite" href="" v-on:click.prevent="setFavorite" :class="{ 'favorited': favorited}"><svg data-v-50394372="" viewBox="0 -45 540 540" xmlns="http://www.w3.org/2000/svg" style="width: 22px;height: 18px;"><path data-v-50394372="" d="M256 455.516c-7.29 0-14.316-2.641-19.793-7.438-20.684-18.086-40.625-35.082-58.219-50.074l-.09-.078c-51.582-43.957-96.125-81.918-127.117-119.313C16.137 236.81 0 197.172 0 153.871c0-42.07 14.426-80.883 40.617-109.293C67.121 15.832 103.488 0 143.031 0c29.555 0 56.621 9.344 80.446 27.77C235.5 37.07 246.398 48.453 256 61.73c9.605-13.277 20.5-24.66 32.527-33.96C312.352 9.344 339.418 0 368.973 0c39.539 0 75.91 15.832 102.414 44.578C497.578 72.988 512 111.801 512 153.871c0 43.3-16.133 82.938-50.777 124.738-30.993 37.399-75.532 75.356-127.106 119.309-17.625 15.016-37.597 32.039-58.328 50.168a30.046 30.046 0 01-19.789 7.43zM143.031 29.992c-31.066 0-59.605 12.399-80.367 34.914-21.07 22.856-32.676 54.45-32.676 88.965 0 36.418 13.535 68.988 43.883 105.606 29.332 35.394 72.961 72.574 123.477 115.625l.093.078c17.66 15.05 37.68 32.113 58.516 50.332 20.961-18.254 41.012-35.344 58.707-50.418 50.512-43.051 94.137-80.223 123.469-115.617 30.344-36.618 43.879-69.188 43.879-105.606 0-34.516-11.606-66.11-32.676-88.965-20.758-22.515-49.3-34.914-80.363-34.914-22.758 0-43.653 7.235-62.102 21.5-16.441 12.719-27.894 28.797-34.61 40.047-3.452 5.785-9.53 9.238-16.261 9.238s-12.809-3.453-16.262-9.238c-6.71-11.25-18.164-27.328-34.61-40.047-18.448-14.265-39.343-21.5-62.097-21.5zm0 0" fill="#fff"></path> <path data-v-50394372="" d="m471.382812 44.578125c-26.503906-28.746094-62.871093-44.578125-102.410156-44.578125-29.554687 0-56.621094 9.34375-80.449218 27.769531-12.023438 9.300781-22.917969 20.679688-32.523438 33.960938-9.601562-13.277344-20.5-24.660157-32.527344-33.960938-23.824218-18.425781-50.890625-27.769531-80.445312-27.769531-39.539063 0-75.910156 15.832031-102.414063 44.578125-26.1875 28.410156-40.613281 67.222656-40.613281 109.292969 0 43.300781 16.136719 82.9375 50.78125 124.742187 30.992188 37.394531 75.535156 75.355469 127.117188 119.3125 17.613281 15.011719 37.578124 32.027344 58.308593 50.152344 5.476563 4.796875 12.503907 7.4375 19.792969 7.4375 7.285156 0 14.316406-2.640625 19.785156-7.429687 20.730469-18.128907 40.707032-35.152344 58.328125-50.171876 51.574219-43.949218 96.117188-81.90625 127.109375-119.304687 34.644532-41.800781 50.777344-81.4375 50.777344-124.742187 0-42.066407-14.425781-80.878907-40.617188-109.289063zm0 0" fill="transparent" class="favorited"></path></svg></a>
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