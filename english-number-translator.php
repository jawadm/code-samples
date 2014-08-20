<?
  /*
  *  English Number Translator - Coding exercise
  *
  *  by Jawad Mohyuddin - jmohyuddin@gmail.com
  *    
  */      


?>

<!DOCTYPE HTML>
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>English Number Translator</title>
  </head>
  <body>

  <h1>English Number Translator</h1>
  <form action="" method="POST" name="translate_form" >
    English words: <br />
    <textarea name="translate" style="height 300px; width: 500px; padding: 0px" ><? if (isset($_POST['translate'])) { echo htmlentities(trim($_POST['translate'])); } ?></textarea>
    <br /><input type="submit" />
  </form>
  
  <? if (isset($_POST['translate'])) { 
  
      $translator = new Translator();
      $numbers = $translator->translateNumbers($_POST['translate']);
      
      if ($numbers && count($numbers) > 0) {
  ?>
      <strong>Results:</strong>
      <div style="border:1px solid; padding: 5px; background: #ffccbb; ">
          <?=$translator->arrayToString($numbers); ?>
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

class Translator{

  private $numbers = array (  'zero' => 0,
                              'one' => 1,
                              'two' => 2,
                              'three' => 3,
                              'four' => 4,
                              'five' => 5,
                              'six' => 6,
                              'seven' => 7,
                              'eight' => 8,
                              'nine' => 9,
                              'ten' => 10,
                              'eleven' => 11,
                              'twelve' => 12,
                              'thirteen' => 13,
                              'fourteen' => 14,
                              'fifteen' => 15,
                              'sixteen' => 16,
                              'seventeen' => 17,
                              'eighteen' => 18,
                              'nineteen' => 19,
                              'twenty' => 20,
                              'thirty' => 30,
                              'forty' => 40,
                              'fifty' => 50,
                              'sixty' => 60,
                              'seventy' => 70,
                              'eighty' => 80,
                              'ninety' => 90,
                           );

  private $operators = array (  //'negative' => -1,
                                'hundred' => 100,
                                'thousand' => 1000,
                                'million' => 1000000,
                                'billion' => 1000000000,
                                'trillion' => 1000000000000,
                           );

  public function __construct(){
  }

  public function translateNumbers($words=''){
    $numbers = array(); 
    $words = trim(strip_tags($words));   
    $words_array = explode(',',$words);
  
    foreach ($words_array as $word) {
      $number = $this->translateWord($word);
      //$number = $word;
      if ($number !== false) {
        $numbers[] = $number;
      }
    }
    
    return $numbers;
  }

  public function translateWord($word=''){
    $number = false;    
    $number_array = explode(' ',trim($word));
  
    $operator = null;
    if (count($number_array) > 0) {
      $number_array = array_reverse($number_array);
      
      $number = 0;
      $operator = null;
      $tmp_operator = null;
      $count = 0;
      foreach ($number_array as $num) {
        //check if word is a number or operator
        if (isset($this->operators[$num]) && (!$operator || $this->operators[$num] == 100) ) {
          $count++;
          if (!$operator) $operator = 1;
          $operator *= $this->operators[$num];
          $tmp_operator = $operator;
        } else if (isset($this->numbers[$num])) {
          $count++;
          $tmp_number = $this->numbers[$num];
          // apply operator to number
          if ($operator) {
            $tmp_number *= $operator;   
            
            // check if next word is number greater than 20 to apply operator
            if ( $this->numbers[$num] >= 20 || !isset($this->numbers[$number_array[$count]]) || $this->numbers[$number_array[$count]] < 20 ) {
              // check for 100 operator
              if ( (!isset($this->operators[$number_array[$count]]) || $this->operators[$number_array[$count]] != 100) && (!isset($this->operators[$number_array[$count+1]]) || $this->operators[$number_array[$count+1]] != 100) ) {
                $operator = null;
              }
            }
          }
          
          $number += $tmp_number;
        }
      }
      
      // add negative value
      if ($num == 'negative') {
        $number *= -1;      
      }
    }
    
    
    return $number;
  }
  
  public function arrayToString($number_array=array()){
    if (is_array($number_array))
      return implode(',',$number_array);
    else
      return $number_array;
  }
}

?>