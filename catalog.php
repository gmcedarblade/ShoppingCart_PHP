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
        <title>Minis Mall Catalog</title>
        <link href="css/minismall.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <h2>Product Catalog</h2>
        <?php
        /**
         * Check to see if session contains a numItems variable. If it does not
         * create the session variable
         */
        if(!isset($_SESSION['numItems'])) {
            
            $_SESSION['numItems'] = 0;
            
        }
        
        echo "<p>Your shopping cart contains $_SESSION[numItems] item(s)</p>\n";
        ?>
    
        <a href="cart.php">View your cart</a> | <a href="index.html">Pick a new Category.</a>

        <?php

        require 'dbConnect.php';

        try {

            $sql = "SELECT catid FROM categories";

            $categoryResults = $pdo->query($sql);

        } catch (Exception $exception) {

            $error = 'Unable to select categories.';
            include 'error.html.php';
            exit();

        }

        $catIds = array();
        $counter = 0;
        while ($row = $categoryResults->fetch()) {

            $catIds[$counter] = $row['catid'];
            $counter++;
        }

        // check to see if category id is valid. If not set default
        if(isset($_GET['cat']) && in_array($_GET['cat'], $catIds)) {

            $cat = $_GET['cat'];

        } else {

            $cat = 1;

        }

        $_SESSION['cat'] = $cat; // remember the chosen category

        // Query for all the products in the chosen category
        try {

            $itemResult = $pdo->query("SELECT * FROM products WHERE category=$cat");

        } catch (Exception $exception) {

            $error = 'Unable to select products.';
            include 'error.html.php';
            exit();

        }

        ?>

        <br><br>
        <form action="cart.php" method="post">
            <table>
                <tr class="header">
                    <th>Image</th>
                    <th>Description</th>
                    <th>Price - US$</th>
                    <th style="background-color: white">&nbsp;</th>
                </tr>

                <?php
                /*
                 * step through the result set of products and display
                 * each product as a table row.
                 */
                while($itemRow = $itemResult->fetch()) {

                    // do some data sanitization
                    $imgLocation = htmlspecialchars(strip_tags($itemRow['loc']));
                    $desc = htmlspecialchars(strip_tags($itemRow['description']));
                    $price = htmlspecialchars(strip_tags($itemRow['price']));

                    $price = "$" . number_format($price, 2);

                    $productId = $itemRow['prodid'];

                    if(isset($_SESSION['cart'][$productId])) {

                        // the quantity has already been set in the cart so get it out
                        $qty = $_SESSION['cart'][$productId];

                    } else {

                        /*
                         * quantity has not been set in the cart,
                         * so set it to a default of zero
                         */
                        $qty = 0;

                    }

                    echo <<<TABLEROW

                        <tr>
                            <td><img src="$imgLocation" alt="$desc"</td>
                            <td class="desc">$desc</td>
                            <td class="price">$price</td>
                            <td class="qty">
                                <label for="quantityForProduct$productId">Qty</label>
                                <input type="text" name="$productId" id="quantityForProduct$productId" value="$qty" size="3">
                            </td>
                        </tr>

TABLEROW;


                }

                ?>

            </table>
            <input type="submit" name="submit" value="submit">
        </form>
    </body>
</html>

