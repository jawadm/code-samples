<?

$log_string = 'AppFeedback:';

require_once(dirname(__FILE__).'/../../lib/bps.php');
require_once(LIB_PATH.'App.php');
require_once(LIB_PATH.'Feedback.php');

$status = false;
$error = null;

$action = (isset($post['action'])) ? $post['action'] : null;

if ( $action == 'save' && isset($post['pageid']) && isset($post['pageurl']) ){

  // format feedback data for saving
  $post['page_url'] = $post['pageurl'];  
  $post['page_id'] = $post['pageid'];
  $post['user_id'] = (isset($post['userid']))?$post['userid']:null;
  
  $data = array();
  $save_fields = array('user_id','page_id','page_url','information','ease','layout','overall','recommendation','topic','comments');
  foreach ($save_fields as $save_field) {
    if (isset($post[$save_field])) {
      $data[$save_field] = $post[$save_field]; 
    }
  }
  
  // save feedback data
  $feedback = new Feedback();      
  $result = $feedback->writeData($data);
  
  if ($result) {
    $status = true;      
  } else {
    $error = $feedback->error_message;
  }
  
}  


$status = ( $status ) ? 'ok' : 'bad';
$response = array( 'response' => array( 'status' => $status ) );
                 
if ($error) {
  $response['error'] = $error;
}
  

print json_encode($response);

?>