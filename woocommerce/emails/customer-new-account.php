<?php
  /**
   * Customer new account email
   *
   * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
   *
   * HOWEVER, on occasion WooCommerce will need to update template files and you
   * (the theme developer) will need to copy the new files to your theme to
   * maintain compatibility. We try to do this as little as possible, but it does
   * happen. When this occurs the version of the template file will be bumped and
   * the readme will list any important changes.
   *
   * @see https://docs.woocommerce.com/document/template-structure/
   * @package WooCommerce/Templates/Emails
   * @version 3.7.0
   */

  defined('ABSPATH') || exit;

  do_action('woocommerce_email_header', $email_heading, $email); ?>

<?php /* translators: %s Customer username */ ?>
  <p><?php printf(esc_html__('Здравствуйте, %s,', 'woocommerce'), esc_html($user_login)); ?></p>
<?php /* translators: %1$s: Site title, получен и обрабатывается. В ближайшее время наш менеджер с Вами свяжется. : Username, %3$s: My account link */ ?>
  <p><?php printf(esc_html__('Вы успешно зарегистрировались в интернет-магазине %1$s. Ваши регистрационные данные: ', 'woocommerce'), esc_html($blogname)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
  <p><?php printf(esc_html__('Ваш логин: %s', 'woocommerce'), '<strong>' . esc_html($user_login) . '</strong>'); ?></p>
  <p><?php printf(esc_html__('Логин и пароль необходимы для оформления заказа, а так же для доступа в Ваш «ЛИЧНЫЙ КАБИНЕТ» %s.', 'woocommerce'), make_clickable(esc_url(wc_get_page_permalink('myaccount')))); ?></p>
  <p><?php printf(esc_html__('Контакты: Адрес: Москва, Кутузовский пр. 36 стр 4', 'woocommerce')); ?></p>
  <p>С уважением, менеджеры интернет- магазина elevainni.ru</p>
  <p><?php printf(esc_html__('Телефон: +7-915-060-77-22', 'woocommerce')); ?></p>
  <p><?php printf(esc_html__('E-mail: elevainni@mail.ru', 'woocommerce')); ?></p>
  <p><?php printf(esc_html__('Сайт: %s', 'woocommerce'), '<a href="http://elevainni.ru/">' . esc_html('elevainni.ru') . '</a>'); ?></p>


<?php if ('yes' === get_option('woocommerce_registration_generate_password') && $password_generated) : ?>
  <?php /* translators: %s Auto generated password */ ?>
  <p><?php printf(esc_html__('Your password has been automatically generated: %s', 'woocommerce'), '<strong>' . esc_html($user_pass) . '</strong>'); ?></p>
<?php endif; ?>

<?php
  /**
   * Show user-defined additional content - this is set in each email's settings.
   */
  if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
  }

  do_action('woocommerce_email_footer', $email);
