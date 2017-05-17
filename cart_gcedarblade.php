<?php

if(!session_id()) {

    session_start();

}

?>

<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <link href="css/minismall.css" rel="stylesheet" type="text/css"/>
</head>
<body>
    <h2>Shopping Cart</h2>
    <?php
    /**
     * Check if session cart variable exists and set shortcut
     */
    require 'dbConnect.php';

    if (isset($_SESSION['cart'])) {
        $cart = $_SESSION['cart'];
    } else { // $_SESSION['cart'] does NOT exist
        $cart = Array();
    }

    /*
     * Check if 'remove' form field exists and set shortcut
     */

    if (isset($_POST['remove'])) {
        $remove = $_POST['remove'];
    } else {
        $remove = Array();
    }

    $totalPrice = 0;
    $prodIDStr = '';

    if(!isset($_SESSION['numItems'])) {
        $_SESSION['numItems'] = 0;
    }

    /*
     *Using the appropriate loop choice, loop through the incoming form
     * data (this page is called from either catalog.php or cart_yourname.php).
     */

    foreach ($_POST as $prodId=>$quantity) {

        // start loop

        if ($quantity > 0 && is_numeric($quantity) && !isset($remove[$prodId])) {

            //Update the cart’s element for this
            // product with the product quantity from the form
            $cart[$prodId] = $quantity;

        } else if ($quantity == 0 || isset($remove[$prodId])) {

            //Remove this product from the cart array.
            // Which function can be used to remove or unset a variable?
            unset($cart[$prodId]);

        }

        // end loop
    }

    foreach ($cart as $productId=>$quantity) {

        $_SESSION['numItems'] += $quantity;
        $prodIDStr .= "$productId,";

    }

    $prodIDStr = rtrim($prodIDStr, ",");






    if(empty($cart)) {
        echo "<h3>Your shopping cart is empty!!</h3>";
    } else {
        try {

            $sql = "SELECT * FROM products WHERE prodid IN ($prodIDStr) ORDER BY category, prodid";

            $productResults = $pdo->query($sql);

        } catch (Exception $exception) {

            $error = 'Unable to select all from products';
            echo $exception;
            include 'error.html.php';
            exit();

        }

        ?>

        <form action="cart_gcedarblade.php" method="post">
            <table>
                <tr class="header">
                    <th>Image</th>
                    <th>Description</th>
                    <th>Price - US$</th>
                    <th>Subtotal</th>
                    <th>Quantity</th>
                    <th>Remove</th>
                </tr>
    <?php

            while($row = $productResults->fetch()){

            $quantity = $cart[$row['prodid']];

            $subTotal = $quantity * $row['price'];


            $totalPrice += $subTotal;


            $totalPrice = number_format($totalPrice, 2, '.', ",");

            $subTotal = number_format($subTotal, 2, '.', ',');
//            echo "$" . $price . "\n";
//            echo "$" . $subTotal . "\n";
            // do some data sanitization
            $imgLocation = htmlspecialchars(strip_tags($row['loc']));
            $desc = htmlspecialchars(strip_tags($row['description']));
            $price = htmlspecialchars(strip_tags($row['price']));


            $productId = $row['prodid'];
            echo <<<TABLEROW

                <tr>
                    <td><img src="$imgLocation" alt="$desc"</td>
                    <td class="desc">$desc</td>
                    <td class="price">$price</td>
                    <td class="price">$subTotal</td>
                    <td class="desc">
                        <label for="quantityForProduct$productId">Qty</label>
                        <input type="text" name="$productId" id="quantityForProduct$productId" value="$quantity" size="3">
                    </td>
                    <td class="desc">Remove<br><input type="checkbox" name="remove[$productId]" id="remove" ></td>
                </tr>
                
TABLEROW;



        }
    }
    $_SESSION['cart'] = $cart;
    ?>


        </table>
            <?php echo "Total:" . $totalPrice . "\n"?>
            <br><br><input type="submit" name="checkout" value="Check Out">
            <input type="submit" name="updateCart" value="Update Cart">
    </form>
    <br><br>
    <footer><a href="catalog.php">Continue shopping</a></footer>
</body>
</html>
