<?
require_once(LIB_PATH.'curl_functions.php');

/**
 * AWS class
 * 
 * Get search results from Amazon Cloud Search.  
 * Sends and receives data JSON format.
 *  
 * @author Jawad Mohyuddin
 * @package helium
 * @version 1.0 
 */

class AWS{

  // url variables
  public $url_to_webservice;
  public $search_url;
  
  // search variables
  const SEARCH_SIZE = 10;

  public function __construct(){
    $this->url_to_webservice = AWS_SEARCH_URL;
    $this->search_url = AWS_SEARCH_URL;
  }

  
  public function curlForWebSearch($post_array=array(),$poststring=null,$url=null){
    if ( !$url )
      $url = $this->url_to_webservice;
    $ch = curl_init();
    if ( !$poststring && count($post_array) > 0 ) {
      $poststring = http_build_query($post_array);
    }
    
    $search_url = $url . '?' . $poststring;
    
    curl_setopt($ch, CURLOPT_URL, $search_url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    if ( preg_match("/dev\.bpssolutions\.com/i",$url) )
      curl_setopt($ch, CURLOPT_USERPWD,'bpsuser:silence7');
    $header = "Content-Type: application/x-www-form-urlencoded";
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
   $result = curl_exec($ch);
    //Quick debugging information
    //print_r(curl_getinfo($ch));
    curl_close($ch);
    return $result;
  }
    
   
  public function parseResponseForSearch($json_string){
    $response = array( 'SUCCESSCODE' => '0',
                       'MESSAGE'     => 'Unreachable');
    
    $response_array = json_decode($json_string, true);

    if ( is_array($response_array) ){
      $response = $response_array;
    }
    
    return $response;
  }

  public function submitRequestForSearch($post_array){
    $response_string = $this->curlForWebSearch($post_array);
    return $response_string;
  }

  public function fetchSearchResults($search,$section,$page_number,$title=null){
    $post_array = $this->buildArrayForSearch($search,$section,$page_number,$title);
    $raw_response = $this->submitRequestForSearch($post_array);
    $parsed_response = $this->parseResponseForSearch($raw_response);
    
    /* */
    if ( false ){
      print '<pre style="text-align:left;">';
      print 'Request:'.$this->search_url. '?' . http_build_query($post_array) ."\n" . htmlspecialchars(print_r($post_array,true));
      print 'Response:'."\n". htmlspecialchars(print_r($raw_response,true));
      print "\n".'Parsed:'."\n". htmlspecialchars(print_r($parsed_response,true));
      print '</pre>';
    }/**/
    
    return $parsed_response;
  }
  
  public function buildArrayForSearch($search,$section=null,$page_number=1,$title=null){
    $sections = array('discontinued','recipes','parts','products');
  
    // look for item or part number based on search string
    if ( preg_match("/[a-z0-9]+/i",$search) && strlen($search) > 1 ){ 
      $q_length = strlen($search);

       // lookup products based on item number
      global $db;
      $product_id_rows = $db->query("select `item_number` from `items` where REPLACE(`item_number`, '-', '') like '".clean($search)."%' order by `item_number` asc ");
      if ( count($product_id_rows) ){
        $tmp_string = str_replace('-','',$product_id_rows[0]['item_number'],$hyphen_count);
        if (strtolower($search) == strtolower(substr($tmp_string,0,$q_length)) ) {
          $search = substr($product_id_rows[0]['item_number'],0,$q_length+$hyphen_count);       	
        }
      }
      else {

         // lookup parts based on part number
        $part_id_rows = $db->query("select `part_number` from `parts` where REPLACE(`part_number`, '-', '') like '".clean($search)."%' order by `part_number` asc ");
        if ( count($part_id_rows) ){
          $tmp_string = str_replace('-','',$part_id_rows[0]['part_number'],$hyphen_count);
          if (strtolower($search) == strtolower(substr($tmp_string,0,$q_length)) ) {
            $search = substr($part_id_rows[0]['part_number'],0,$q_length+$hyphen_count);       	
          }
        }
      }
    }

    // build search query 
    $search = str_replace('"', '', $search);
    $search = str_replace("'", '', $search);
    $query = '"'.$search.'"';

    $section_query = "( or boost=20 (term field=section boost=10 'products') (term field=section boost=5 'parts') (term field=section boost=2 'recipes') (term field=section boost=2 'discontinued') (not boost=1 (term field=section 'products') ) (term field=title boost=2 'cuisinart') )";    
    $add_query = ""; //"(term field=title 'Cuisinart Original')"; 

    if ($title) {
      $title = str_replace('"', '', $title);
      $title = str_replace("'", '', $title);
      $add_query = "(term field=title '$title')"; 
    }

    $query = "( and '".$search."' {$add_query} {$section_query} )";    
    
    // build filter
    $filter = null; 
    if ($section) { 
      $filter .= "( and section:'".$section."' )"; 
    }
    
    // create array of search parameters
    $start = self::SEARCH_SIZE*($page_number-1);
    $post_array = array(  'q'         =>  $query,
                          'q.parser'  =>  "structured",  // for compound queries
                          'q.options' =>  "{fields:['section^20','url^10','title^2','html^1','keywords^0.5']}",  // specify fields to search and weight for text relevance score
                          'size'      =>  self::SEARCH_SIZE,
                          'start'     =>  $start,
                          'return'    =>  'url,title,section,_score',
                       );
    if ( $filter ) $post_array['fq'] = $filter;                   
            
    return $post_array;
  }  
}

?>
