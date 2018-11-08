<?php

 if(isset($_POST['reserve'])){
   
   if(isset($_SESSION['user'])){

    $user_id = $_SESSION['user']['idUser'];

    $cardholdername = $_POST['cardholdername'];

    $cardnumber = $_POST['cardnumber'];

    $expirydate = $_POST['expirydate'];

    $securitynumber = $_POST['securitynumber'];

    $cipher = "aes-128-gcm";

    $key="ola";

    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);

    $expirydate = openssl_encrypt($expirydate, $cipher, $key, $options=0, $iv, $tag);
    $securitynumber = openssl_encrypt($securitynumber, $cipher, $key, $options=0, $iv, $tag);

    $expirydate = htmlspecialchars($expirydate, ENT_QUOTES, 'UTF-8');
    $cardholdername = htmlspecialchars($cardholdername, ENT_QUOTES, 'UTF-8');
    $cardnumber = htmlspecialchars($cardnumber, ENT_QUOTES, 'UTF-8');
    $securitynumber = htmlspecialchars($securitynumber, ENT_QUOTES, 'UTF-8');
    


    $sql = $db->prepare(" INSERT INTO `creditcard` (`idUser`,`cardNumber`, `cardHolderName`,`expiryDate`
    `securityNumber`)
    VALUES (:idUser,:cardNumber,:cardHolderName,:expiryDate,:securityNumber)");

      $sql->bindParam(':idUser', $user_id);
      $sql->bindParam(':cardNumber', $cardnumber);
      $sql->bindParam(':cardHolderName', $cardholdername);
      $sql->bindParam(':expiryDate', $expirydate);
      $sql->bindParam(':securityNumber', $securitynumber);

      $sql->execute();


      $sql = $db->prepare("SELECT idCreditCard where cardNumber = :cardnumber");

      $sql->bindParam(':cardNumber', $cardnumber);

      $sql->execute();




     
  }

 }


?>

<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title>Checkout</title>
  

        <link href="https://fonts.googleapis.com/css?family=Lato:100,300,300i,400" rel="stylesheet">

      <link rel="stylesheet" href="css/checkout.css">

  
</head>

<body>

  <div class='container'>
  <form class='modal'>
    <header class='header'>
      <div class='card-type'>
        <a class='card' href='#'>
          <img src='https://s3-us-west-2.amazonaws.com/s.cdpn.io/169963/Amex.png'>
        </a>
        <a class='card' href='#'>
          <img src='https://s3-us-west-2.amazonaws.com/s.cdpn.io/169963/Discover.png'>
        </a>
        <a class='card active' href='#'>
          <img src='https://s3-us-west-2.amazonaws.com/s.cdpn.io/169963/Visa.png'>
        </a>
        <a class='card' href='#'>
          <img src='https://s3-us-west-2.amazonaws.com/s.cdpn.io/169963/MC.png'>
        </a>
      </div>
    </header>
    <div class='content'>
      <div class='form'>
        <div class='form-row'>
          <div class='input-group'>
            <label for=''>Name on card</label>
            <input name="cardholdername" placeholder='' type='text'>
          </div>
        </div>
        <div class='form-row'>
          <div class='input-group'>
            <label for=''>Card Number</label>
            <input name="cardnumber" maxlength='16' placeholder='' type='number'>
          </div>
        </div>
        <div class='form-row'>
          <div class='input-group'>
            <label for=''>Expiry Date</label>
            <input name="expirydate" placeholder='' type='month'>
          </div>
          <div class='input-group'>
            <label for=''>CVS</label>
            <input name="securitynumber" maxlenght='4' placeholder='' type='number'>
          </div>
        </div>
      </div>
    </div>
    <footer class='footer'>
      <input type="submit" name="reserve" value="Complete Payment" class='button'>
    </footer>
  </form>
</div>






</body>

</html>