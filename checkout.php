<?php

include('ligar_bd.php');

session_start();
  //se ouver um submit reserva faz a reserva 
 if(isset($_POST['reserve'])){
  //so faz a reserva se primeiro tiver sessao iniciada
   if(isset($_SESSION['user'])){
    //defenição das variaves a serem introduzidas na query de inserção de reserva e do cartao de crédito
    $id_activity = $_GET['id'];
    $user_id = $_SESSION['user']['idUser'];

    $cardnumber = $_POST['cardnumber'];
    $cardholdername = $_POST['cardholdername'];
    $expirydate = $_POST['expirydate'];
    $securitynumber = $_POST['securitynumber'];
    $state = 'Standby';

    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
  
    $key = "teste";

    //encriptação dos valores
    $cardnumber = openssl_encrypt($cardnumber, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $cardnumber, $key, $as_binary=true);
    $cardnumber = base64_encode( $iv.$hmac.$cardnumber );

    $securitynumber = openssl_encrypt($securitynumber, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $securitynumber, $key, $as_binary=true);
    $securitynumber = base64_encode( $iv.$hmac.$securitynumber );


    $expirydate = openssl_encrypt($expirydate, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $expirydate, $key, $as_binary=true);
    $expirydate = base64_encode( $iv.$hmac.$expirydate );
    

    $expirydate = htmlspecialchars($expirydate, ENT_QUOTES, 'UTF-8');
    $cardholdername = htmlspecialchars($cardholdername, ENT_QUOTES, 'UTF-8');
    $cardnumber = htmlspecialchars($cardnumber, ENT_QUOTES, 'UTF-8');
    $securitynumber = htmlspecialchars($securitynumber, ENT_QUOTES, 'UTF-8');  
    
    //query que pega tudo do cartão de credito
    $sql = $db->prepare(" SELECT `cardNumber` FROM `CreditCard` ");
    $sql->execute();
    $row = $sql->fetchAll(PDO::FETCH_ASSOC);
    $count = $sql->rowCount();
    
    //se tiver resultados ve em cada row da tabela e desencripta os dados anteriormente encriptdos
    // e compara se existe cartão de credito igual ao que vai ser inserido de momento, isto para que não crie sempre
    // um cartao de credito no momento que faz reserva mesmo que ja exista um igual na BD, isto aconteçe porque no momento de encriptção fica diferente 
    // ao valor que vai ser inserido, para isso temos de buscar tudo desencriptar e so depois conseguimos comparar
    if ($count > 0)
    {
        foreach( $row as $value)
      {
    
          $key = "teste";
                              
          $cardnumber = $value['cardNumber'];
          $c = base64_decode($cardnumber);
          $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
          $iv = substr($c, 0, $ivlen);
          $hmac = substr($c, $ivlen, $sha2len=32);
          $ciphertext_raw = substr($c, $ivlen+$sha2len);
          $newcardnumber = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
          $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
            
       
          if($newcardnumber == $_POST['cardnumber'])
            {
              $already_exists_db = "";
            }else
              {
                                        
              $sql = $db->prepare(" INSERT INTO `CreditCard` (`cardNumber`,`cardHolderName`,`expiryDate`,`securityNumber`)
              VALUES (:cardNumber,:cardHolderName,:expiryDate,:securityNumber)");
      
              $sql->bindParam(':cardNumber', $cardnumber);
              $sql->bindParam(':cardHolderName', $cardholdername);
              $sql->bindParam(':expiryDate', $expirydate);
              $sql->bindParam(':securityNumber', $securitynumber);
      
              $sql->execute();  
              } 
        } 
    } else {
              $sql = $db->prepare(" INSERT INTO `CreditCard` (`cardNumber`,`cardHolderName`,`expiryDate`,`securityNumber`)
              VALUES (:cardNumber,:cardHolderName,:expiryDate,:securityNumber)");
          
                $sql->bindParam(':cardNumber', $cardnumber);
                $sql->bindParam(':cardHolderName', $cardholdername);
                $sql->bindParam(':expiryDate', $expirydate);
                $sql->bindParam(':securityNumber', $securitynumber);
          
                $sql->execute();
            }
      $sql = $db->prepare(" SELECT idActivity FROM `Reservation` where `idUser` = :idUser AND `idActivity` = :idActivity ");
      $sql->bindParam(':idUser', $user_id);
      $sql->bindParam(':idActivity', $id_activity);

      $sql->execute();

     $count = $sql->rowCount();

     if ($count > 0){

        $erro_reserva_user = "Already did a reserve to this Activity ! ";
        
    }else{
      
      $sql = $db->prepare(" INSERT INTO `Reservation` (`idUser`, `idActivity`,`cardNumber`,`state`)
      VALUES (:idUser,:idActivity,:cardNumber,:state)");

      $sql->bindParam(':idUser', $user_id);
      $sql->bindParam(':idActivity', $id_activity);
      $sql->bindParam(':cardNumber', $cardnumber);
      $sql->bindParam(':state', $state);

      $sql->execute();

      $count = $sql->rowCount();

      if ($count > 0){
        $success = "Reserve Done !";
      }
      else{
        $error = "erro na reserva !";
      }

    }
           
  }
  else{
    $error_session = "Need to be logged in first to reserve !";
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
  <form class='modal' method="POST">
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
            <input name="securitynumber" maxlenght='3' placeholder='' type='number'>
          </div>
        </div>
      </div>
    </div>
    <footer class='footer'>
      <input type="submit" name="reserve" value="Complete Payment" class='button'>
      <?php echo $already_exists_db ?>
     <?php echo $erro_reserva_user ?>
     <?php echo $success ?>
    <?php echo  $error_session ?> 
    </footer>
  </form>
</div>






</body>

</html>