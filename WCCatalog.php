<?php

class WCCatalog {

    private $option_name = 'wec_settings';

    public function __construct() {
        # Admin settings
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);

        # Campos no produto
        add_action('woocommerce_product_options_general_product_data', [$this, 'add_external_link_field']);
        add_action('woocommerce_process_product_meta', [$this, 'save_external_link_field']);

        # Substitui botões
        add_action('init', [$this, 'replace_add_to_cart_buttons']);

        # Ocultar preços e checkout conforme configuração
        add_action('wp', [$this, 'maybe_hide_prices']);
        add_action('template_redirect', [$this, 'maybe_disable_cart_checkout']);
    }

    /**
     * Cria página de configurações no menu WooCommerce
     */
    public function add_settings_page() {
        add_submenu_page(
            'woocommerce',
            'Modo Catálogo',
            'Modo Catálogo',
            'manage_woocommerce',
            'woocommerce-catalog',
            [$this, 'settings_page_html']
        );
    }

    /**
     * Registra configuração
     */
    public function register_settings() {
        register_setting($this->option_name, $this->option_name);
        add_settings_section('wc_main_section', '', null, $this->option_name);

        add_settings_field(
            'hide_prices',
            'Ocultar preços',
            [$this, 'field_hide_prices_html'],
            $this->option_name,
            'wc_main_section'
        );
    }

    public function field_hide_prices_html() {
        $options = get_option($this->option_name);
        $checked = isset($options['hide_prices']) ? 'checked' : '';
        echo '<label><input type="checkbox" name="' . $this->option_name . '[hide_prices]" value="1" ' . $checked . '> Esconder preços</label>';
    }

    /**
     * Página HTML das configurações
     */
    public function settings_page_html() {
        ?>
        <div class="wrap">
            <h1>WooCommerce Modo Catálogo</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_name);
                do_settings_sections($this->option_name);
                submit_button('Salvar alterações');
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Adiciona campo "Link Externo" no admin do produto
     */
    public function add_external_link_field() {
        woocommerce_wp_text_input([
            'id'          => 'link_externo',
            'label'       => 'Link Externo',
            'placeholder' => 'https://outra-loja.com/produto/...',
            'desc_tip'    => true,
            'description' => 'URL do produto na outra loja',
        ]);
    }

    /**
     * Salva o campo "Link Externo"
     */
    public function save_external_link_field($post_id) {
        $link_externo = isset($_POST['link_externo']) ? esc_url_raw($_POST['link_externo']) : '';
        update_post_meta($post_id, 'link_externo', $link_externo);
    }

    /**
     * Substitui o botão padrão de compra
     */
    public function replace_add_to_cart_buttons() {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);

        add_action('woocommerce_after_shop_loop_item', [$this, 'add_external_button'], 10);
        add_action('woocommerce_single_product_summary', [$this, 'add_external_button'], 30);
    }

    /**
     * Exibe botão de redirecionamento
     */
    public function add_external_button() {
        global $product;
        $link_externo = get_post_meta($product->get_id(), 'link_externo', true);

        if ($link_externo) {
            echo '<a href="' . esc_url($link_externo) . '" class="button alt" target="_blank" rel="noopener noreferrer">Comprar (Brasil)</a>';
            echo ' <a href="' . esc_url($link_externo) . '&pt=1" class="button alt" target="_blank" rel="noopener noreferrer">Comprar (Portugal)</a>';
        }
    }

    /**
     * Oculta preços se a opção estiver ativa
     */
    public function maybe_hide_prices() {
        $options = get_option($this->option_name);
        if (!empty($options['hide_prices'])) {
            remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
        }
    }

    /**
     * Desativa carrinho e checkout (mantendo navegação normal)
     */
    public function maybe_disable_cart_checkout() {
        if (is_cart() || is_checkout()) {
            wp_redirect(home_url());
            exit;
        }
    }
}
