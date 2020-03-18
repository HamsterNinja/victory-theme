<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * My Account navigation.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_navigation' ); ?>

<div class="woocommerce-MyAccount-content">
	<?php $current_user = wp_get_current_user();?>
	<p style="font-size: 34px; font-weight: 600; color: #000; text-indent: 0; text-align: center; margin-bottom: 25px;">Добро пожаловать, <?php echo $current_user->user_firstname;?> <?php echo $current_user->user_lastname;?></p>
	<p>Добро пожаловать, <strong><?php echo $current_user->user_firstname;?> <?php echo $current_user->user_lastname;?></strong> (не <strong><?php echo $current_user->user_firstname;?> <?php echo $current_user->user_lastname;?></strong>? <a href="<?php echo get_site_url();?>/my-account/customer-logout/?_wpnonce=14fad161f4">Выйти</a>)</p>
	<p>Из главной страницы аккаунта вы можете посмотреть ваши <a href="<?php echo get_site_url();?>/my-account/orders/">недавние заказы</a>, а также <a href="<?php echo get_site_url();?>/my-account/edit-account/">изменить пароль и основную информацию</a>.</p>
</div>
