<?php
defined('ABSPATH') || exit;

return apply_filters(
    'wc_tuyopay_settings',
    array(
        'enabled' => array(
            'title'       => __('Activar/Desactivar', 'tuyo-pay-gateway'),
            'label'       => __('Activar Tuyo Pay', 'tuyo-pay-gateway'),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'no',
        ),
        'title' => array(
            'title'       => __('Título', 'tuyo-pay-gateway'),
            'type'        => 'text',
            'description' => __('Esto controla el título que el usuario ve durante el pago.', 'tuyo-pay-gateway'),
            'default'     => 'Tuyo Pay',
            'desc_tip'    => true,
        ),
        'description' => array(
            'title'       => __('Descripción', 'tuyo-pay-gateway'),
            'type'        => 'text',
            'description' => __('Esto controla la descripción que el usuario ve durante el pago.', 'tuyo-pay-gateway'),
            'default'     => __('Pagar con Tuyo Pay', 'tuyo-pay-gateway'),
            'desc_tip'    => true,
        ),
        'icon_style' => array(
            'title'       => __('Diseño del logo', 'tuyo-pay-gateway'),
            'type'        => 'select',
            'description' => __('Esto controla el diseño del logo de Tuyo Pay que el usuario ve durante el pago.', 'tuyo-pay-gateway'),
            'default'     => 'tuyopay-logo',
            'required'    => true,
            'options'     => array(
                'tuyopay-logo' => __('Original'),
                'tuyopay-white' => __('Blanco'),
                'tuyopay-green' => __('Verde'),
                'tuyopay-full' => __('Con icono')
            ),
            'desc_tip'    => true,
        ),
        'webhook' => array(
            'title'       => __('Webhook URL', 'tuyo-pay-gateway'),
            'type'        => 'title',
            'description' => sprintf(__('Envíe la siguiente URL <strong class="wc_tuyopay-webhook-link">&nbsp;%s&nbsp;</strong> a su KAM asignado para habitar el botón de pago de Tuyo Pay (para ambos entornos Producción y Sandbox).', 'tuyo-pay-gateway'), add_query_arg('wc-api', 'wc_tuyopay', trailingslashit(get_home_url()))),
        ),
        'testmode' => array(
            'title'       => __('Modo Sandbox', 'tuyo-pay-gateway'),
            'label'       => __('Activar entorno de pruebas', 'tuyo-pay-gateway'),
            'type'        => 'checkbox',
            'description' => __('Coloque la pasarela de pago en modo de prueba usando API Key de prueba.', 'tuyo-pay-gateway'),
            'default'     => 'yes',
            'desc_tip'    => true,
        ),
        'test_api_key' => array(
            'title'       => __('API Key de Pruebas', 'tuyo-pay-gateway'),
            'type'        => 'password',
            'description' => __('Obtén tus API Keys contactándote con tu KAM asignado.', 'tuyo-pay-gateway'),
            'default'     => '',
            'desc_tip'    => true,
        ),
        'api_key' => array(
            'title'       => __('API Key de Producción', 'tuyo-pay-gateway'),
            'type'        => 'password',
            'description' => __('Obtén tus API Keys contactándote con tu KAM asignado.', 'tuyo-pay-gateway'),
            'default'     => '',
            'desc_tip'    => true,
        ),
        'logging' => array(
            'title'       => __('Logging', 'tuyo-pay-gateway'),
            'label'       => __('Registro de mensajes de depuración', 'tuyo-pay-gateway'),
            'type'        => 'checkbox',
            'description' => __('Guarda los mensajes de depuración en el Registro de estado del sistema de WooCommerce.', 'tuyo-pay-gateway'),
            'default'     => 'no',
            'desc_tip'    => true,
        ),
    )
);
