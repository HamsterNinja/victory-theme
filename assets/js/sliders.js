const slidersInit = {
    init: () => {
        $('.banner-items').slick(slidersInit.settingsHomeBannerSlider);
    },
    settingsHomeBannerSlider: {
        arrows: true,
        dots: false,
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 4000,
        responsive: [{
            breakpoint: 1340,
            settings: {
                arrows: false,
            }
        }]
    },
}

document.addEventListener('DOMContentLoaded', () => {
    slidersInit.init();
});