<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'add_product_to_cart_get_price',
    'add_to_cart',
    'check_amount_in_stock_before_check',
    'delete_cart_product',
    'update_product_amount_pre',
    'get_cart_product_data'
);
