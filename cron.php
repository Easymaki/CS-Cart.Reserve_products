<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$items_in_reserve = db_get_array("SELECT * FROM ?:reserve_products");

if (!empty($items_in_reserve)) {
    foreach ($items_in_reserve as $item) {
    $current_time = time();
        if ($current_time > $item['endtime']) {
            $current_product_amount = db_get_field("SELECT amount FROM ?:products WHERE product_id = ?i", $item['product_id']);
            if ($current_product_amount == 0) {
                db_query("DELETE FROM ?:user_session_products WHERE product_id = ?i", $item['product_id']);
            }

            $new_amount = $current_product_amount + $item['amount'];
            db_query('UPDATE ?:products SET amount = ?i WHERE product_id = ?i', $new_amount, $item['product_id']);
            db_query("DELETE FROM ?:reserve_products WHERE reserve_id = ?i", $item['reserve_id']);
        }
    }
}

