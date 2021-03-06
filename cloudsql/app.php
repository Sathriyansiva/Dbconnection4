<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
$app = new Application();

$app['pdo'] = function ($app) {
    $pdo = new PDO(
        $app['mysql.dsn'],
        $app['mysql.user'],
        $app['mysql.password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    return $pdo;
};

$app->get('/', function (Application $app, Request $request) {
    $ip = $request->GetClientIp();
    $octets = explode($separator = ':', $ip);
    if (count($octets) < 2) {  // Must be ip4 address
        $octets = explode($separator = '.', $ip);
    }
    if (count($octets) < 2) {
        $octets = ['bad', 'ip'];  // IP address will be recorded as bad.ip.
    }
    // Replace empty chunks with zeros.
    $octets = array_map(function ($x) {
        return $x == '' ? '0' : $x;
    }, $octets);
    $user_ip = $octets[0] . $separator . $octets[1];

    // Insert a visit into the database.
    /** @var PDO $pdo */
    $pdo = $app['pdo'];
   if(isset($_GET['ibo']))
{
$ibo=$_GET['ibo'];
}


if(isset($_GET['receiveribo']))
{
$receiveribo=$_GET['receiveribo'];
}
$format = strtolower($_GET['format']) == 'json'; //xml is the default
    // Look up the last 10 visits
	
	
    $select = $pdo->prepare(
        'SELECT * FROM chat WHERE (sender_ibo =:ibo1 and receiver_ibo =:receiveribo1) or  
	(sender_ibo=:receiveribo1 and receiver_ibo=:ibo1)');
    $select->execute(array(':ibo1'=>$ibo,':receiveribo1'=>$receiveribo));
    $visits = [""];
    while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
     $image= $row['image'];
		$senderibo =$row['sender_ibo'];
	        $receiveribo=$row['receiver_ibo'];
		$message= $row['message'];
		
		
	$posts[] = array('image'=>$image,'message' => $message,'senderibo'=>$senderibo,'receiveribo'=>$receiveribo);
		
    }
     
	
	if($format == 'json') {
    header('Content-type: application/json');
    echo json_encode(array('posts'=>$posts));
  }
  else {
    header('Content-type: text/xml');
    echo '';
    foreach($posts as $index => $post) {
      if(is_array($post)) {
        foreach($post as $key => $value) {
          echo '<',$key,'>';
          if(is_array($value)) {
            foreach($value as $tag => $val) {
              echo '<',$tag,'>',htmlentities($val),'</',$tag,'>';
            }
          }
          echo '</',$key,'>';
        }
      }
    }
    echo '';
  }
	return new Response(implode("\n", $visits), 200,
        ['Content-Type' => 'text/plain']);
});
# [END example]

return $app;
