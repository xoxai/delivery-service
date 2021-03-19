<!DOCTYPE html>
<html>
<head>
  <title>Cart</title>
  <style type="text/css">
    body {
      padding-top: 80px;
    }

    .show-cart li {
      display: flex;
    }
    .card {
      margin-bottom: 20px;
    }
    .card-img-top {
      width: 200px;
      height: 200px;
      align-self: center;
    }
  </style>
 <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
</head>
<body>

<!-- Nav -->
<nav class="navbar navbar-inverse bg-inverse fixed-top bg-faded">
    <div class="row">
        <div class="col">
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#cart">Cart (<span class="total-count"></span>)</button> <button class="clear-cart btn btn-danger">Clear Cart</button></div>
    </div>
</nav>


<!-- Main Screen -->
<div class="container">
  <a href="#" data-name="Lemon underwater" data-price="5" class="add-to-cart btn btn-primary">Add to cart</a>
   <a href="#" data-name="Lemon2" data-price="15" class="add-to-cart btn btn-primary">Add to cart</a>
    <a href="#" data-name="Lemon3" data-price="35" class="add-to-cart btn btn-primary">Add to cart</a>
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
        <button type="button" class="btn btn-primary">Order now</button>
      </div>
    </div>
  </div>
</div> 

<!-- Cart Main JS -->
<script type="text/javascript" src="./js/cart.js"></script>

</body>
</html>