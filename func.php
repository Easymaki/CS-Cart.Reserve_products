<?php

function fn_easymaki_reserve_products_add_product_to_cart_get_price($product_data, $cart, $auth, $update, $_id, $data, $product_id, $amount, $price, $zero_price_action, $allow_add)
{
	$max_amount = db_get_field("SELECT amount FROM ?:products WHERE product_id = ?i", $product_id);
	if ($amount > $max_amount) {
			$amount = $max_amount;
	}
	$_SESSION['auth']['reserve_data'][$product_id]['amount'] = $amount;
}

function fn_easymaki_reserve_products_add_to_cart(&$cart, $product_id, $_id)
{
    $amount = $_SESSION['auth']['reserve_data'][$_id]['amount'];

    $current_amount = db_get_field("SELECT amount FROM ?:products WHERE product_id = ?i", $product_id);

    if (empty($_SESSION['auth']['reserve_data'][$product_id]['old_amount'])) {
        $_SESSION['auth']['reserve_data'][$product_id]['old_amount'] = $current_amount;
    }

    $_SESSION['auth']['reserve_data'][$product_id]['reserve'] = $_SESSION['auth']['reserve_data'][$product_id]['old_amount'];
}

function fn_easymaki_reserve_products_check_amount_in_stock_before_check($product_id, $amount, $product_options, $cart_id, $is_edp, &$original_amount, $cart, $update_id, $product, &$current_amount)
{
    if (!empty($_SESSION['auth']['reserve_data'][$product_id]['reserve'])) {
        $current_amount = $_SESSION['auth']['reserve_data'][$product_id]['reserve'];
    }
}

function fn_easymaki_reserve_products_delete_cart_product($cart, $cart_id)
{   
   	$unikey = $_SESSION['auth']['reserve_data']['unikey'] . $cart['products'][$cart_id]['product_id'];

    $reserve_data = db_get_row("SELECT amount FROM ?:reserve_products WHERE user_id = ?i AND product_id = ?i", $_SESSION['auth']['user_id'], $cart['products'][$cart_id]['product_id']);

    if (!empty($reserve_data['amount'])) {
        $current_amount = db_get_field("SELECT amount FROM ?:products WHERE product_id = ?i", $cart['products'][$cart_id]['product_id']);
        $new_amount = $current_amount + $reserve_data['amount'];

        db_query('UPDATE ?:products SET amount = ?i WHERE product_id = ?i', $new_amount, $cart['products'][$cart_id]['product_id']); 
        db_query("DELETE FROM ?:reserve_products WHERE user_id = ?i AND product_id = ?i", $_SESSION['auth']['user_id'], $cart['products'][$cart_id]['product_id']);
    }
}

function fn_easymaki_reserve_products_get_cart_product_data($product_id, &$_pdata, $product, $auth, &$cart, $hash)
{
  $reserve_data = db_get_row("SELECT * FROM ?:reserve_products WHERE user_id = ?i AND product_id = ?i", $_SESSION['auth']['user_id'], $product_id);

  if (!empty($reserve_data)) {
        if ($reserve_data['amount'] != $product['amount']) {
            db_query("UPDATE ?:reserve_products SET amount = ?i WHERE user_id = ?i AND product_id = ?i", $product['amount'], $_SESSION['auth']['user_id'], $product_id);

            $current_product_amount = db_get_field("SELECT amount FROM ?:products WHERE product_id = ?i", $product_id);
            if ($reserve_data['amount'] < $product['amount']) {
                $difference = $product['amount'] - $reserve_data['amount'];
                $new_product_amount = $current_product_amount - $difference;

                db_query("UPDATE ?:products SET amount = ?i WHERE product_id = ?i", $new_product_amount, $product_id);
            } else {
                $difference = $reserve_data['amount'] - $product['amount'];
                $new_product_amount = $current_product_amount + $difference;

                db_query("UPDATE ?:products SET amount = ?i WHERE product_id = ?i", $new_product_amount, $product_id);
            }
        }
    } else {
        if ($product['amount'] > 0) {
            $current_product_amount = db_get_field("SELECT amount FROM ?:products WHERE product_id = ?i", $product_id);
            $new_product_amount = $current_product_amount - $product['amount'];

            db_query("UPDATE ?:products SET amount = ?i WHERE product_id = ?i", $new_product_amount, $product_id);

            $reserve_data = [
                'user_id' => $_SESSION['auth']['user_id'],
                'product_id' => $product_id,
                'amount' => $product['amount'],
                'endtime' => time() + 1200,
            ];

            db_query("REPLACE INTO ?:reserve_products ?e", $reserve_data);
        }
    }
}
