<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Donate with PayPal - Dasaplus</title>
</head>
<body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">
  <h1>Support Our Educational Platform</h1>
  <p>Your donation helps us provide free learning resources to students worldwide.</p>

  <!-- Container for PayPal button -->
  <div id="paypal-button-container"></div>

  <!-- PayPal JavaScript SDK -->
  <script src="https://www.paypal.com/sdk/js?client-id=YOUR_CLIENT_ID&currency=USD"></script>
  <script>
    paypal.Buttons({
      createOrder: function(data, actions) {
        return actions.order.create({
          purchase_units: [{
            amount: {
              value: '5.00' // default donation amount (can be made dynamic)
            },
            description: "Donation to Dasaplus Educational Platform"
          }]
        });
      },
      onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
          // Redirect to thank-you page
          window.location.href = "success.php?donor=" + details.payer.name.given_name;
        });
      },
      onCancel: function(data) {
        window.location.href = "cancel.php";
      },
      onError: function(err) {
        console.error(err);
        alert("Something went wrong with PayPal. Please try again.");
      }
    }).render('#paypal-button-container');
  </script>
</body>
</html>
