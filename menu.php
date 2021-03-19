<?php

$menuString = file_get_contents('./chinese_menu.txt');

$menuItems = [];

foreach (explode(PHP_EOL, $menuString) as $line) {
    $item = explode(' ', $line);
    $code = $item[0];
    $categoryId = $item[1];
    $chineseName = $item[2];
    $russianName = implode(' ', explode('_', $item[3]));
    $price = $item[4];
    if (!isset($item[4])) {
        echo $code;
    }
    $menuItems[] = ['code' => $code,
                    'category_id' => $categoryId,
                    'chinese_name' => $chineseName,
                    'russian_name' => $russianName,
                    'price' => $price];
}

function getCartLink($name, $price) {
     $link = "<a href=\"#\" data-name=\"$name\" data-price=\"$price\" class=\"add-to-cart btn btn-primary\">Add to cart</a>";
     return $link;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Menu</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

   <!-- Extra CSS -->
    <style type="text/css">
        .table-header {
            font-weight: bold;
        }

        .show-cart li {
          display: flex;
        }

        .form-control {
            margin-left: 5px;
            margin-right: 5px;
        }

        .table td, .table th {
            vertical-align: middle;
        }

        .centered {
            margin: 0 auto;
            margin-top: 10px;
            margin-bottom: 10px;
        }
    </style>

    <script type="text/javascript" src="./js/filter.js"></script>
</head>
<body>
        <nav class="navbar navbar-light bg-light justify-content-between">
            <div class="col">
          <a class="navbar-brand">Chinese Restaurant Menu</a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#cart">Cart (<span class="total-count"></span>)</button> 
            <button class="clear-cart btn btn-danger">Clear Cart</button>
        </div>
        </nav>



    <div class="container">
        <!-- <h2>Menu</h2> -->
        <div class="centered">
    <form class="form-inline" id="searchForm" style="text-align: right;">
            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" id="search" onkeyup="filter();">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit" onsubmit="filter();">Search</button>
          </form>
      </div>
    <?php
        echo "<table id=\"menu\" class=\"table table-striped\">";
        echo "<tr class=\"table-header\"><!--<td>Code</td><td>Category</td>--><td>Name (chinese)</td><td>Name (russian)</td><td>Price (RUR)</td><td></td></tr>";
        foreach($menuItems as $item) {
            echo "<tr>";
            // echo "<td>" . $item['code'] . "</td>";
            // echo "<td>" . $item['category_id'] . "</td>";
            echo "<td>" . $item['chinese_name'] . "</td>";
            echo "<td>" . $item['russian_name'] . "</td>";
            echo "<td>" . $item['price'] . "</td>";
            // echo "<td><button class=\"btn btn-success\">Add to cart</td>";
            $fullName = "[". $item['code'] . "." . $item['category_id'] . "] " . $item['chinese_name'] ."<br>(".$item['russian_name'].")";
            echo "<td>" . getCartLink($fullName, $item['price']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    ?>
    </div>

    <!-- Modal Window (Cart Preview) -->
    <div class="modal fade" id="cart" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cart</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">

            
            <table class="show-cart table">
              
            </table>
            <div>Total price: <span class="total-cart"></span>₽</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#userinfo" data-dismiss="modal">Order now</button>
          </div>
        </div>
      </div>
    </div> 

     <!-- User Info Modal Window -->

      <div class="modal fade" id="userinfo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel2">Client Information</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
             <form action="order.php" method="post" id="orderForm">
              <div class="form-group">
                <label for="phone">Phone</label>
                <input type="phone" class="form-control" id="phone" name="phone" placeholder="+79997750577" required="">
              </div>
              <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Alex" required="">
              </div>
              <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" name="address" id="address" placeholder="Enter your address" required="">
              </div>
              <div class="form-group">
                <label for="notes">Additional information</label>
                <textarea class="form-control" id="notes" rows="3" name="notes" placeholder="Let us know if you have something to add: number of flat, building specifications and so on"></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Order</button>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div> 

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script type="text/javascript">
        $("form#searchForm").submit(function(event){
            event.preventDefault();
        });
    </script>

    <!-- Cart Main JS -->
    <script type="text/javascript" src="./js/cart.js"></script>
    <script type="text/javascript" src="./js/sendOrder.js"></script>

</body>
</html>