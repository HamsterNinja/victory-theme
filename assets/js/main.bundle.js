!function(e){function o(t){if(i[t])return i[t].exports;var n=i[t]={i:t,l:!1,exports:{}};return e[t].call(n.exports,n,n.exports,o),n.l=!0,n.exports}var i={};o.m=e,o.c=i,o.d=function(e,i,t){o.o(e,i)||Object.defineProperty(e,i,{configurable:!1,enumerable:!0,get:t})},o.n=function(e){var i=e&&e.__esModule?function(){return e.default}:function(){return e};return o.d(i,"a",i),i},o.o=function(e,o){return Object.prototype.hasOwnProperty.call(e,o)},o.p="",o(o.s=142)}({142:function(e,o){$(".btn-hamburger").click(function(e){$(this).toggleClass("active"),$(".hidden-menu_block").toggleClass("active"),$(".overlay").toggleClass("active")}),$(".overlay").click(function(e){$(this).removeClass("active"),$(".btn-hamburger").removeClass("active"),$(".hidden-menu_block").removeClass("active")}),$(".choice-button").click(function(e){e.preventDefault(),$(".choice-button").removeClass("active"),$(this).addClass("active");var o=$(this).attr("data-id");o&&$(".collections-content-inner:visible").fadeOut(0,function(){$(".collections-content").find("#"+o).fadeIn("slow",function(){$(".collections-slick").slick("reinit")})})}),$(".collections-slick").slick({infinite:!0,slidesToShow:5,slidesToScroll:5,arrows:!0,dots:!1,responsive:[{breakpoint:1280,settings:{slidesToShow:4,slidesToScroll:4,infinite:!0,dots:!1}},{breakpoint:600,settings:{slidesToShow:2,slidesToScroll:2}},{breakpoint:480,settings:{slidesToShow:1,slidesToScroll:1}}]}),$(".product-slick-for").slick({slidesToShow:1,slidesToScroll:1,arrows:!1,fade:!0,asNavFor:".product-slick-nav"}),$(".product-slick-nav").slick({slidesToShow:6,slidesToScroll:1,asNavFor:".product-slick-for",focusOnSelect:!0,vertical:!0}),$(window).on("resize",function(){$(window).width>1025&&onepagescroll(".main-content.pages",{pageContainer:"section",animationType:"ease-in-out",animationTime:500,infinite:!1,pagination:!0,keyboard:!0,direction:"vertical"})}),void 0==window.getComputedStyle(document.body).mixBlendMode&&$(".ops-navigation").addClass("curtain")}});