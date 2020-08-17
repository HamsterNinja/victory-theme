$(document).ready(function () {
 $('.category-list-menu-name').click(function (e) {
     $(this).toggleClass('active');
     $(this).parent().find('.category-list-menu-content').first().slideToggle();
 })
})