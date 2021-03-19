// jquery-based order sending
$('#orderForm').on('submit', function(e) {
    // get fresh one shopping cart using session storage
    orderJson = sessionStorage.getItem("shoppingCart");
    e.preventDefault();
    // make an ajax
    $.ajax({
      url: 'order.php',
      data: {name: $('#name').val(),
             phone: $('#phone').val(),
             notes: $('#notes').val(),
             address: $('#address').val(),
             order: orderJson},
      type: 'POST',
      success: function(response) {
        alert('Success! Please wait, we will contact you via phone!');
        $('#userinfo').modal('hide');
      }
    });
});