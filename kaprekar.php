<?
  /*
  *  Kaprekar numbers - Coding exercise
  *
  *  by Jawad Mohyuddin - jmohyuddin@gmail.com
  *    
  */      


?>

<!DOCTYPE HTML>
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>Kaprekar Numbers</title>
  </head>
  <body>

  <h1>Kaprekar Numbers</h1>
  <form action="" method="POST" name="number_form" >
    Check Number: 
    <input name="number" value="<? if (isset($_POST['number'])) { echo htmlentities(trim($_POST['number'])); } ?>" >
    <input type="submit" value="Check Number" />
  </form>
  
  <? if (isset($_POST['number'])) { 
  
      $kaprekar = new Kaprekar();
      
      if ( $kaprekar->checkNumber($_POST['number']) ) {
  ?>
      <strong style="color: #11cc22;">Yes, Kaprekar Number!</strong>    
  <? } else { ?>
      <strong style="color: #cc1122;">Sorry, not a Kaprekar Number.</strong>    
  <? } } ?>


  <br /><br />
  <form action="" method="POST" name="list_form" >
    Maximum Number: 
    <input name="max_number" value="<? if (isset($_POST['max_number'])) { echo htmlentities(trim($_POST['max_number'])); } else { echo 1000; } ?>" >
    <input type="submit" value="List Kaprekar Numbers" />
  </form>
  
  <? if (isset($_POST['max_number'])) { 
  
      $kaprekar = new Kaprekar();
      $numbers = $kaprekar->getNumbers($_POST['max_number']);
      
      if ($numbers && count($numbers) > 0) {
  ?>
      <strong>Results:</strong>
      <div style="border:1px solid; padding: 5px; background: #ffccbb; ">
          <?=$kaprekar->arrayToString($numbers); ?>
      </div>
    
  <? } else { ?>
      <em>No results returned.</em>
  
  <? } } ?>


  <div id="footer" style="margin-top: 20px; font-size: 11px;">
    Jawad Mohyuddin | <a href="mailto:jmohyuddin@gmail.com">jmohyuddin@gmail.com</a>
  </div>
  
  </body>
</html>


<? 

class Kaprekar{

  public function __construct(){
  }

  public function checkNumber($number){
    $result = false;
    
    if (is_numeric($number) && intval($number) == $number ) {
      // get length 
      $num_length = strlen((string) $number);
      
      // calculate number based on kaprekar formula
      $num_squared = (string)($number*$number);
      $left_num = intval(substr($num_squared,0,-$num_length));
      $right_num = intval(substr($num_squared,-$num_length));
      $kap_um = $left_num+$right_num;
      
      // check if number matches original
      if ( $number == $kap_um && $right_num > 0 ) $result = true;
    }
    
    return $result;
  }

  public function getNumbers($max_number){
    $numbers = array();  
  
    if (is_numeric($max_number) && $max_number > 0 ) {
      //check each number in range 
      for ($i=1; $i<=$max_number; $i++ ) {
        if ( $this->checkNumber($i) ) {
          $numbers[] = $i;
        } 
      }
    }    
    
    return $numbers;
  }
  
  public function arrayToString($number_array=array()){
    if (is_array($number_array))
      return implode(',',$number_array);
    else
      return $number_array;
  }
}

?>