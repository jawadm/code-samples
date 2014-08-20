<?

/**
 * Feedback class
 * 
 * Load and save feedback information.  Use to generate reports.
 *  
 * @author Jawad Mohyuddin
 * @package helium
 * @version 1.0 
 */

class Feedback {
  
  public $db;

   // database values
  public $id;

  public $error_message;

  public $feedbacks = array();
  public $feedbacks_by_page = array();

   // constructor
  public function __construct($id=null){
    global $db;
    $this->db = &$db;

    $this->error_message = '';
  }
 

   // set the value for a selected id
  public function setID ($id=null) {
    $this->id = $id;
  }
   
   // load data; returns true if it is found in `feedback` table
  public function load ($id=null) {
    if ( $id != null ) $this->setID($id);
    
    $query = "select * from `feedback` where `feedback_id` = '".$this->clean($this->id)."' limit 1";
    $feedbacks = $this->db->query($query);

    if ( count($feedbacks) == 1 ){
       // load up the values from the database
      $feedback = current($feedbacks);
      $this->setId($feedback['feedback_id']);

      foreach ($feedback as $tag => $value){
      // assign values to object
        $this->$tag = $value;
      }
                   
      return true;           
    } 
    else {
      // data was not found
      $this->clearAll();
      return false;
    }             
  }
  
  // function to insert or update feedback information using an array of data
  public function writeData($data=null, $feedback_id=null, $admin=false){
    $load = true;
    $result = false;
    
    if (!$feedback_id) {
      $feedback_id = $this->id;
      $load = false;
    }
    
    
    // add timestamp for new feedback data
    if ( !$feedback_id || $feedback_id == NEW_PRIMARY_KEY ) {
      if (!isset($data['timestamp']))
        $data['timestamp'] = date('Y-m-d H:i:s');
      if (!isset($data['ip_address']) && isset($_SERVER['REMOTE_ADDR']) )
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'];
    }

    $page_extension = '';
    if ( defined('PAGE_FILE_EXTENSION') && PAGE_FILE_EXTENSION != '' ) {
      $page_extension = '.'.PAGE_FILE_EXTENSION;
    }

    // generate list of query data
    $first = true;
    $data_query = '';
    foreach ($data as $tag => $value){
      if (!$tag) continue;
      
      if ($tag == 'page_url' ) {
        $value = rtrim($value,$page_extension);
      }
      
      // assign values to object
      $this->$tag = $value;
      
      // create set string for sql query
      if (!$first)
         $data_query .= ", ";
      

      $data_query .= "`$tag`='".clean($value)."'";
      $first = false;
    } 
    
    // check for missing fields for new feedback
    if ( !$feedback_id ) {      
      if ( !$this->page_id || (!$this->page_url && $this->page_id != HOME_PAGE_ID) ) {
        $this->addErrorMessage("Page information for feedback is missing.");
        return false;
      }
      else {
        $required_fields = array('information','ease','layout','overall');
        foreach ($required_fields as $required_field) {
          if (!array_key_exists($required_field,$data)) {
            $this->addErrorMessage("Please enter all required fields.");
            return false;
          }
        }
      }
    }
    
    // either insert or update the values in the data query
    if ($data_query) {
      if ($feedback_id && $feedback_id != NEW_PRIMARY_KEY){
        $query = "update `feedback` set $data_query where `feedback_id` = '".$this->clean($feedback_id)."'";
        $this->db->query($query);
        $result = $this->db->mysqli()->affected_rows+1;
      } else {
        $query = "insert into `feedback` set $data_query ";
        $this->db->query($query);
        $result = $this->db->mysqli()->insert_id;
        $feedback_id = $result;
      }
    }

    // set error message
    if ($result) {
        $this->setID($feedback_id);
        $result = $feedback_id;
    }else {
        $this->addErrorMessage("Error occurred while trying to save feedback information.");
    }

    return $result;
  }

   // load data; 
  public function loadFeedback ($page_id=null, $start_date=null, $end_date=null, $order_by='`page_id`,`timestamp` desc') {
    // build query
    $query = "select * from `feedback` where 1 ";
    if ($page_id) $query .= " and `page_id` = '".clean($page_id)."' ";
    if ($start_date) $query .= " and `timestamp` >= '".clean($start_date)."' ";
    if ($end_date) $query .= " and `timestamp` <= '".clean($end_date)."' ";
    if ($order_by) $query .= " order by ".clean($order_by)." ";
    
    $feedbacks = $this->db->query($query,'feedback_id');
    
    if ( !$page_id ) {
      $this->feedbacks = $feedbacks;
  
      if ( count($this->feedbacks) ){
        foreach ( $this->feedbacks as $feedback_id => $feedback ){
          $this->feedbacks_by_page[$feedback['page_id']][] = $feedback_id;
        }
      } 
    }
    
    return $feedbacks;            
  }

  /*-*** OTHER FUNCTIONS *****/

   // insert or update data into table
  public function writeTableData($data, $id=null, $feedback_id=null, $table_name='', $id_name=''){
    $result = false;
    if (!$feedback_id) $feedback_id = $this->id;

    // create string to attach to query with data values and fields
    $setstring = '';
    foreach ($data as $tag => $value){
        $setstring .= "`$tag`='".clean($value)."', ";
    }
    $setstring = substr($setstring,0,-2);
    
    // add or update data to database      
    if ( !$id && isset($data[$id_name]) )
      $id = $data[$id_name];
    
    if ( !$id || $id == NEW_PRIMARY_KEY ){
      $query = "insert into `$table_name` set `feedback_id`='".$this->clean($feedback_id)."', ".$setstring;
      $this->db->query($query);
      $result = $this->db->mysqli()->insert_id;
    }else{
      $query =  "update `$table_name` set ".$setstring." where `$id_name`='".$this->clean($id)."' and `feedback_id`='".$this->clean($feedback_id)."'";
      $this->db->query($query);
      $result = $this->db->mysqli()->affected_rows;
    }
    return $result;
  }

  public function addErrorMessage($message){
    if ($this->error_message != '' && $message != '')
        $this->error_message .= "<br/>\n";
    $this->error_message .= $message;
  }

  // remove tags and escape string before adding to database
  public function clean($text){
    $clean_string = htmlentities(strip_tags(clean($text)), ENT_QUOTES);
  	return $clean_string;
  }
      
  public function toArray() {
  	 $a = array();
	   foreach ($this as $property => $value){
	     if (!is_object($value)) {
	       $a[$property] = $value;
	     }
	   }
	   return $a;
  }
        
  public function fetchDataArray(){
    $data = get_object_vars($this);
    return $data;
  }
  
  public function clearAll(){
  	foreach ($this as $property => $value){  	 
      if ($property != 'db' && $property != 'smarty')  	 
        $this->$property = (is_array($value))?array():null;
    }
  }
   
}

?>
