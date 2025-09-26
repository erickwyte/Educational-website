<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thank You - Dasaplus</title>
</head>
<body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">
  <?php $donor = isset($_GET['donor']) ? htmlspecialchars($_GET['donor']) : "Friend"; ?>
  <h1>Thank You, <?php echo $donor; ?>!</h1>
  <p>Your generous donation will help us continue providing educational resources.</p>
  <a href="donate.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: green; color: white; text-decoration: none; border-radius: 5px;">Donate Again</a>
</body>
</html>
