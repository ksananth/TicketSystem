<?php 
function sendEmail($email,$sub,$mesg,$id){
		
			//Send Email
			$to = $email;
			$subject = $sub;			
			$message = $mesg;

// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: <webmaster@example.com>' . "\r\n";
$headers .= 'Cc: myboss@example.com' . "\r\n";

mail($to,$subject,$message,$headers);
}
function notifyUser($id,$action) {
	require_once('../gcm/loader.php');
	
	$db = new DbHandler();	
	
	if($action=="addTicket"){
		$query="SELECT c.gcm_regid AS gcm_regid, c.email AS email, a.user_email AS user_email, a.user_msg_send AS user_msg_send	FROM site_users a LEFT JOIN gcm_users c ON a.user_id = c.user_id	WHERE a.user_id	IN (SELECT  `user_id` FROM  `site_users` WHERE user_level =0			OR user_level =4)";
		$pushMessage="New Ticket  added - Ticket# $id";	
	}else if($action=="addComment"){		
		$query="SELECT  c.gcm_regid as gcm_regid, c.email as email, a.user_email as user_email, a.user_msg_send as user_msg_send FROM site_users a left JOIN gcm_users c ON a.user_id = c.user_id WHERE a.user_id = (select call_staff from site_calls where call_id=$id) union all SELECT  c.gcm_regid as gcm_regid, c.email as email, a.user_email as user_email, a.user_msg_send as user_msg_send FROM site_users a left JOIN gcm_users c ON a.user_id = c.user_id WHERE a.user_id = (select call_user from site_calls where call_id=$id)";
		$pushMessage="New Comment has been added for Ticket# $id";	
	}else if($action=="assignTicket"){
		$query="SELECT  c.gcm_regid as gcm_regid, c.email as email, a.user_email as user_email, a.user_msg_send as user_msg_send FROM site_users a left JOIN gcm_users c ON a.user_id = c.user_id WHERE a.user_id = (select call_staff from site_calls where call_id=$id)";
		$pushMessage="Ticket# $id is assigned to you.";	
	}else if($action=="fixedTicket"){
		$pushMessage="Ticket# $id has been fixed.";	
		$query="SELECT  c.gcm_regid as gcm_regid, c.email as email, a.user_email as user_email, a.user_msg_send as user_msg_send FROM site_users a left JOIN gcm_users c ON a.user_id = c.user_id WHERE a.user_id = (select call_user from site_calls where call_id=$id)";
	}else if($action=="ticketClosed" || $action=="ticketPending"){
		$query="SELECT  c.gcm_regid as gcm_regid, c.email as email, a.user_email as user_email, a.user_msg_send as user_msg_send FROM site_users a left JOIN gcm_users c ON a.user_id = c.user_id WHERE a.user_id = (select call_staff from site_calls where call_id=$id)";
		$pushMessage="Ticket# $id has been Closed by User.";
	}
	
	
	//print_r($query);
	$gcmId= $db->getAllRecord($query);
	$gcmId=json_encode($gcmId);	
	
	$gcmId = json_decode( $gcmId );	
	//print_r($gcmId);

	foreach($gcmId as $item) { //foreach element in $arr
		$result="";
		$user_msg_send = $item->user_msg_send; 
		$gcm_regid = $item->gcm_regid; 
		$user_email = $item->user_email; 
		//print_r($gcm_regid);
		//print_r($user_email);
		
		$registatoin_ids = array($gcm_regid);
        $message = array("price" => $pushMessage); 
			if($item->user_msg_send==1){
				$subject = "Ticketing System:".$pushMessage;
				$emailMessage = "
												<!DOCTYPE html>
												<html>
												<head>
												<style> 
												#rcorners1 {
													border-radius: 10px;
													background: #FDFDFD;
													padding: 20px; 
													width: 50%;
													height: 250px;    
													margin:auto;
												}
												
												#rcorners2 {
													border-radius: 10px;
													background: #4486F9;
													padding: 20px; 
													width: 150px;
													height: 50px;    
													margin:auto;
													color:#fff;
												}
												
												
												body {
													border-radius: 10px;
													background: #3498DB;
													padding: 20px; 
												   
												}
												
												
												</style>
												</head>
												<body>
												
												
												<div id='rcorners1' style='text-align: center;'>
												
												<h3>Welcome to Ticketing System</h3>
												
												$pushMessage.
												
												<a href='http://mangaisolutions.com/dev/TicketSystem/dashboard.html#/addTicket?ticket=$id'>Ticket ID</a>
									Have a Great day,
									Team Ticket system.
									</div>


									</body>
									</html>
							";
				sendEmail($item->user_email,$subject,$emailMessage,$id);
				if(empty($item->gcm_regid)){
					$result="";
				}else{
					$result=send_push_notification($registatoin_ids, $message);
				}
				
			}
		     
				
}
return  $result;
}

//***************************************************
//     CHECK SESSION
//***************************************************
$app->get('/session', function() {
    $db = new DbHandler();
    $session = $db->getSession();
    $response["uid"] = $session['uid'];
    $response["email"] = $session['email'];
    $response["name"] = $session['name'];
	$response["user_level"] = $session['user_level'];
    echoResponse(200, $session);
});



//***************************************************
//     Generate secret code
//***************************************************
$app->post('/getSecretCode', function() use ($app) {
    $db = new DbHandler();    
	$response = array();
	$r = json_decode($app->request->getBody());
	$login = $r->search->login;
 

		 $query1 = "select randomNumber,user_email from site_users where user_email='$login' or user_login='$login' LIMIT 1";
		 $result = $db->getAllRecord($query1);	
	
	
		 
        if ($result != NULL) {
				$randomNumber=$result[0]['randomNumber'];
				$user_email=$result[0]['user_email'];
		 		$response["message"] = "Secret code sent.Please check registered email!";
				$response["status"] = "success";
				$response["statusCode"] = "200";
				$subject="Ticket System - Secret code";
				$message = "
									<!DOCTYPE html>
									<html>
									<head>
									
									</head>
									<body>
									
									
									<div id='rcorners1' style='text-align: center;'>
									
									<h3>Welcome to Ticketing System</h3>
									
									Please find the Secret code below,
									<h3>$randomNumber</h3>
						Have a Great day,
						Team Ticket system.
						</div>


						</body>
						</html>
						";
				sendEmail($user_email,$subject,$message,'');
	 }
	 else{
	 		$response["status"] = "error";
        	$response["message"] = "Issue in generating Code!";
			$response["statusCode"] = "201";     
	 }
	 
	
    echoResponse($response["statusCode"], $response);
});


//***************************************************
//     set new password
//***************************************************
$app->post('/sendPassword', function() use ($app) {
    $db = new DbHandler();    
	$response = array();
	$r = json_decode($app->request->getBody());
	$login = $r->search->login;
	$code = $r->search->code;
	$pass = $r->search->pass;
	
 

		 $query1 = "select user_password,randomNumber,user_email from site_users where user_email='$login' or user_login='$login' LIMIT 1";
		 $result = $db->getAllRecord($query1);	
	
	
		 
        if ($result != NULL) {
				$randomNumber=$result[0]['randomNumber'];
				$user_email=$result[0]['user_email'];
				
				if($randomNumber==$code){
					$user_password = passwordHash::hash($pass);
					$user_password_set = "user_password='$user_password'";
					$query = "UPDATE site_users SET $user_password_set where user_login = '$login' or user_email='$login';";
	         		$result = $db->executeQuery($query);	

                    if ($result != NULL || $result==0 ) {	
					    $response["message"] = "password updated successfully!";
						$response["status"] = "success";
						$response["statusCode"] = "200";
					}else{
							$response["status"] = "error";
						$response["message"] = "unable to update!";
						$response["statusCode"] = "201";  
					}
	
					    
						
				}else{
					$response["status"] = "error";
					$response["message"] = "Invalid code!";
					$response["statusCode"] = "201";    
				}
		 		
	 }
	 else{
	 		$response["status"] = "error";
        	$response["message"] = "Issue in generating Code!";
			$response["statusCode"] = "201";     
	 }
	 
	
    echoResponse($response["statusCode"], $response);
});

//***************************************************
//     LOGIN
//***************************************************
$app->post('/login', function() use ($app) {
    require_once 'passwordHash.php';
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('email', 'password'),$r->customer);
    $response = array();
    $db = new DbHandler();
    $password = $r->customer->password;
    $email = $r->customer->email;
    $user = $db->getOneRecord("select user_id,user_level,user_name,user_login,user_password,user_email,user_pending,created from site_users where user_login='$email' or user_email='$email'");
    if ($user != NULL) {
		
		if($user['user_pending']==1){
		   $response['status'] = "error";
            $response['message'] = 'Login failed. your registration is pending!';
		}
        else if(passwordHash::check_password($user['user_password'],$password)){
			$response['status'] = "success";
			$response['message'] = 'Logged in successfully.';
			$response['name'] = $user['user_name'];
			$response['user_login'] = $user['user_login'];
			$response['password'] = $password;
			$response['uid'] = $user['user_id'];
			$response['email'] = $user['user_email'];
			$response['createdAt'] = $user['created'];
			$response['user_level'] = $user['user_level'];
			if (!isset($_SESSION)) {
				session_start();
			}
			$_SESSION['uid'] = $user['user_id'];
			$_SESSION['email'] = $email;
			$_SESSION['name'] = $user['user_name'];
			
			$_SESSION['user_id'] = $user['user_id'];
			$_SESSION['user_name'] = $user['user_name'];
			$_SESSION['user_id'] = $user['user_id'];
			$_SESSION['user_level'] = $user['user_level'];
        } else {
            $response['status'] = "error";
            $response['message'] = 'Login failed. Incorrect credentials';
        }
    }else {
            $response['status'] = "error";
            $response['message'] = 'No such user is registered';
        }
    echoResponse(200, $response);
});


//***************************************************
//     SIGN UP
//***************************************************
$app->post('/signUp', function() use ($app) {
    $response = array();
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('user_email','user_name', 'user_login', 'user_password'),$r->customer);
    require_once 'passwordHash.php';
    $db = new DbHandler();
    $phone = $r->customer->user_phone;
    $fullName = $r->customer->user_name;
	$loginName = $r->customer->user_login;
    $email = $r->customer->user_email;
    
    $password = $r->customer->user_password;
	$category = $r->customer->user_category;
	$registerationId = $r->customer->user_registrationId;
	$department = $r->customer->user_department;
	$course = $r->customer->user_course;
	$user_profile_pic = $r->customer->user_profile_pic;
	//echo "$user_profile_pic-".$user_profile_pic;
	
    $isUserExists = $db->getOneRecord("select 1 from site_users where user_login='$loginName' or user_email='$email'");
    if(!$isUserExists){
		
		if($user_profile_pic == 'profilepic/avatar5.png'){
		 	$r->customer->user_profile_pic = $user_profile_pic;
		}
		else{
			//Upload file to server
			$encodedData=$user_profile_pic;		
			
			$encodedData = str_replace(' ','+',$encodedData);
			$decodedData = base64_decode($encodedData);		
			$data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $encodedData));
			$uniqueName='image_' . date('Y-m-d-H-i-s') . '_' . uniqid().".png";
			$new_image_name = '../../profilepic/' . $uniqueName;
			file_put_contents($new_image_name, $data);
			$r->customer->user_profile_pic = 'profilepic/'.$uniqueName;
		}
		


        $r->customer->user_password = passwordHash::hash($password);
        $tabble_name = "site_users";
        $column_names = array('user_phone', 'user_name','user_login', 'user_email', 'user_password', 'user_category', 'user_registrationId','user_department','user_course','user_profile_pic');
        $result = $db->insertIntoTable($r->customer, $column_names, $tabble_name);
        if ($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "User account created successfully";
            $response["uid"] = $result;
            if (!isset($_SESSION)) {
                session_start();
            }
            $_SESSION['uid'] = $response["uid"];
            $_SESSION['phone'] = $phone;
            $_SESSION['name'] = $loginName;
            $_SESSION['email'] = $email;
			
			//Send Email
			$to = $email;
			$subject = "Welcome to Ticketing System";
			
			$message = "
			<!DOCTYPE html>
			<html>
			<head>
			<style> 
			#rcorners1 {
				border-radius: 10px;
				background: #FDFDFD;
				padding: 20px; 
				width: 50%;
				height: 250px;    
				margin:auto;
			}
			
			#rcorners2 {
				border-radius: 10px;
				background: #4486F9;
				padding: 20px; 
				width: 150px;
				height: 50px;    
				margin:auto;
				color:#fff;
			}
			
			
			body {
				border-radius: 10px;
				background: #3498DB;
				padding: 20px; 
			   
			}
			
			
			</style>
			</head>
			<body>
			
			
			<div id='rcorners1' style='text-align: center;'>
			
			<h3>Welcome to Ticketing System</h3>
			
			Thank you for signing up in Ticket Syatem.
			
			Please help us to make sure that we add you by verifying your email id
			<br><br><br><form action='http://mangaisolutions.com/dev/TicketSystem/api/v1/activate.php'  target='_blank'><input type='hidden' name='id' value='".$response["uid"]."' /><input type='submit' value='Verify and Contine' id='rcorners2'></form>
			<br><br><br>
Have a Great day,
Team Ticket system.
</div>


</body>
</html>
";

// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: <webmaster@example.com>' . "\r\n";
$headers .= 'Cc: myboss@example.com' . "\r\n";

mail($to,$subject,$message,$headers);

            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to create customer. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        $response["message"] = "An user with the provided phone or email or username exists!";
        echoResponse(201, $response);
    }
});


//***************************************************
//     LOGOUT
//***************************************************
$app->get('/logout', function() {
    $db = new DbHandler();
    $session = $db->destroySession();
    $response["status"] = "info";
    $response["message"] = "Logged out successfully";
    echoResponse(200, $response);
});


//***************************************************
//     ADD TICKET
//***************************************************
$app->post('/addTicket', function() use ($app) {
    $response = array();
	
	
	if (!isset($_SESSION)) {
                session_start();
     }
	 if(isset($_SESSION['uid'])){
	 		 $r = json_decode($app->request->getBody());
	//print_r($r);
    
    $db = new DbHandler();
    $call_id = $r->customer->call_id;
	$call_department = $r->customer->call_department;
	$call_device = $r->customer->call_device;
	$call_details = $r->customer->call_details;
	$call_email = $r->customer->call_email;
	$call_first_name = $r->customer->call_first_name;
	$call_phone = $r->customer->call_phone;
	$call_request = $r->customer->call_request;
	$call_solution = $r->customer->call_solution;
	$call_location = $r->customer->call_location;
	$call_staff = $r->customer->call_staff;
	$call_status = $r->customer->call_status;
	$call_user = $r->customer->call_user;
	$call_date = $r->customer->call_date;
$call_request =3;
	$type="";
	if($call_id!=''){
	  $type="update";
	  $user_level=$_SESSION['user_level'];
	  if($user_level==1){
		$updateQuery="UPDATE  `site_calls` SET  `call_status` =  '3' WHERE  `site_calls`.`call_id` =$call_id";
		$result = $db->executeQuery($updateQuery);
	  }

	  $query = "UPDATE `site_calls` SET `call_first_name` = '$call_first_name',`call_department` = '$call_department',`call_device` = '$call_device',`call_details` = '$call_details',`call_email` = '$call_email',`call_date`=UNIX_TIMESTAMP(), `call_phone` = '$call_phone', `call_request` = '$call_request', `call_location` = '$call_location',`call_solution` = '$call_solution', `call_staff` = '$call_staff', `call_user` = '$call_user', `call_status` = '$call_status' WHERE `site_calls`.`call_id` = $call_id;";
	  
	}
	else{
	 $type="insert";
	 $call_user=$_SESSION['uid'];
	 $query = "INSERT INTO `site_calls` (`call_department`, `call_device`, `call_details`, `call_email`, `call_first_name`, `call_phone`, `call_request`, `call_solution`,call_location, `call_staff`, `call_date`, `call_status`,  `call_user`) VALUES  ('$call_department', '$call_device', '$call_details', '$call_email', '$call_first_name', '$call_phone', '$call_request', '$call_solution','$call_location', '$call_staff', UNIX_TIMESTAMP(), '$call_status',  '$call_user');";
	}
	$result = $db->executeQuery($query);
	//Seting ticket id
	if($type=="insert"){
		$ticketId=$result;
	}else{
		$ticketId=$call_id;
	}
			
			
		//print_r($result);
        if ($result != NULL || $result==0 ) {
		 		$response["message"] = "Ticket Added successfully!";
				$response["status"] = "success";
				$response["statusCode"] = "200";
				if($type=="update" && $call_status==4){
					$response["gsmStatus"] = notifyUser($ticketId,"fixedTicket");
				}else if($type=="update" && $call_status!=4){
					$response["gsmStatus"] = notifyUser($ticketId,"assignTicket");
				}else if($type=="insert"){
					$response["gsmStatus"] = notifyUser($ticketId,"addTicket");
				}
				
								
				
		}else{
				$response["message"] = "Failed to add Ticket!";
				$response["status"] = "failed";
				$response["statusCode"] = "201";   
		}
		
	 }
	 else{
				$response["message"] = "please login!";
				$response["status"] = "error";
				$response["statusCode"] = "201";   
	 }
	echoResponse($response["statusCode"], $response);
});



//***************************************************
//     SEARCH TICKET
//***************************************************
$app->post('/searchTicket', function() use ($app) {
    $db = new DbHandler();
	$searchquery = "";
	$r = json_decode($app->request->getBody());
	
	//print_r($r->search->status);
	if(isset($r->search->status) && $r->search->status!=""){
		  $status = $r->search->status;	 
	}
	else{
		 $status ="-1";
	}
	//if( $r->search->status=="0"  ) {$status = $r->search->status; } else {  $status ="-1"; }
	if(isset($r->search->call_date1)) { $call_date1 = $r->search->call_date1; } else { $call_date1 =""; }
	if(isset($r->search->call_date2)) { $call_date2 = $r->search->call_date2; } else { $call_date2 =""; }
	
	if ($call_date2 == "") {
		$call_date2 = $call_date1;
	}
	
	if(isset($r->search->call_first_name)) { $call_first_name = $r->search->call_first_name; } else { $call_first_name =""; }
	if(isset($r->search->call_email)) { $call_email = $r->search->call_email; } else { $call_email =""; }
	if(isset($r->search->call_phone)) { $call_phone = $r->search->call_phone; } else { $call_phone =""; }
	if(isset($r->search->call_department)) { $call_department = $r->search->call_department; } else { $call_department =""; }
	if(isset($r->search->call_request)) { $call_request = $r->search->call_request; } else { $call_request =""; }
	if(isset($r->search->call_device)) { $call_device = $r->search->call_device; } else { $call_device =""; }
	if(isset($r->search->call_staff)) { $call_staff = $r->search->call_staff; } else { $call_staff =""; }
	
	
	//print_r("++".$status."ff");
	if ($status==-1) {$searchquery .= " AND (P.call_status = 0 OR P.call_status = 1 OR P.call_status = 2 OR P.call_status = 3 OR P.call_status = 4)"; } else{ $searchquery .= " AND (P.call_status = $status)"; };
	//if ($status==0) {$searchquery .= " AND (P.isopened = 1)";  } else if($status==3){ $searchquery .= " AND (P.isopened = 0)"; };
	if (!empty($call_date1)) {$searchquery .= " AND (P.call_date BETWEEN $call_date1 AND $call_date2)";};
	if (!empty($call_first_name)) {$searchquery .= " AND (P.call_first_name LIKE '%$call_first_name%')";};
	if (!empty($call_email)) {$searchquery .= " AND (P.call_email LIKE '%$call_email%')";};
	if (!empty($call_phone)) {$searchquery .= " AND (P.call_phone LIKE '%$call_phone%')";};
	if (!empty($call_department)) {$searchquery .= " AND (P.call_department = $call_department)";};
	if (!empty($call_request)) {$searchquery .= " AND (P.call_request = $call_request)";};
	if (!empty($call_device)) {$searchquery .= " AND (P.call_device = $call_device)";};
	if (!empty($call_staff)) {$searchquery .= " AND (P.call_staff = $call_staff)";};
	if (!empty($call_details)) {$searchquery .= " AND (P.call_details LIKE '%$call_details%')";};
	if (!empty($call_solution)) {$searchquery .= " AND (P.call_solution LIKE '%$call_solution%')";};
	 if (!isset($_SESSION)) {
                session_start();
     }
	$user_id=$_SESSION['uid'];
	if( $_SESSION['user_level']==1){
	  $searchquery .= (" and (P.call_user=$user_id or P.call_staff=$user_id)");
	}else if($_SESSION['user_level']==2){
	  $searchquery .= (" and (P.call_user=$user_id or P.call_staff=$user_id)");
	}
	$searchquery .= (" order by call_date desc;");
	
	//print_r($searchquery);
	//$showTicket = $db->getAllRecord("Select * from site_calls WHERE 1=1 $searchquery");
	
    $showTicket = $db->getAllRecord("SELECT P.call_status, P.call_id,P.isopened, P.`call_department` , P.`call_first_name` , C.type_name AS dept, D.type_name AS device, G.type_name AS 
type , F.user_login,F.user_level, DATE_FORMAT( FROM_UNIXTIME( P.`call_date` ) ,  '%b %d, %Y' ) AS call_date
FROM site_calls P
INNER JOIN site_types C ON P.`call_department` = C.type_id
INNER JOIN site_types D ON ( P.`call_device` = D.type_id ) 
INNER JOIN site_types G ON ( P.`call_request` = G.type_id ) 
LEFT OUTER JOIN site_users F ON ( P.`call_staff` = F.user_id )  WHERE 1=1 $searchquery");
//print_r($showTicket);
$userLevel=$_SESSION['user_level'];
	$showTicket=json_encode($showTicket);
	$response["showTicket"] = json_decode($showTicket);
	$response["userLevel"]=$userLevel;
    $response["status"] = "Success";
    $response["message"] = "List of Tickets";
	
    echoResponse(200, $response);
});




//***************************************************
//     UPDATE Pending status depends on user feedback of TICKET
//***************************************************
$app->post('/updateFixedTicket', function() use ($app) {
    $db = new DbHandler();
	$searchquery = "";
	$r = json_decode($app->request->getBody());
	
	//print_r($r->search->status);
	//print_r($r->search->call_id);
	
	if(isset($r->search->call_id)) { $call_id = $r->search->call_id; } else { $call_id =""; }
	if(isset($r->search->status)) { $status = $r->search->status; } else { $status =""; }
	
	
	 if (!isset($_SESSION)) {
                session_start();
     }
	$user_id=$_SESSION['uid'];
	$action="";
	if(isset($user_id)){
		if($status=="OK"){
			$query = "UPDATE  site_calls SET  call_status =  '1' WHERE  call_id =$call_id;";
			$action="ticketClosed";
		}else if($status=="NOK"){
			$query = "UPDATE  site_calls SET  call_status =  '3' WHERE  call_id =$call_id;";
			$action="ticketPending";			
		}
   
	
	
	
		//	print_r($query);
		$result = $db->executeQuery($query);	
        if ($result != NULL || $result==0 ) {
		 		$response["message"] = "Ticket Updated successfully!";
				$response["status"] = "success";
				$response["statusCode"] = "200";
				$response["gsmStatus"] = notifyUser($call_id,$action);
		}else{
				$response["message"] = "Failed to update Ticket status!";
				$response["status"] = "failed";
				$response["statusCode"] = "201";   
		}
	}
     else{
				$response["message"] = "please login!";
				$response["status"] = "error";
				$response["statusCode"] = "201";   
	 }
	echoResponse($response["statusCode"], $response);
});




//***************************************************
//     DASHBOARD
//***************************************************
$app->post('/dashboard', function() {
    $db = new DbHandler();
    //$session = $db->destroySession();
	$response = array();
   
	  
	 if (!isset($_SESSION)) {
                session_start();
     }
	 if(isset($_SESSION['uid'])){
	 
	 if($_SESSION['user_level']==2){
	  $userId=$_SESSION['uid'];
	  $applyCondition=" and call_user=$userId";
	 }else if($_SESSION['user_level']==1){
	  $userId=$_SESSION['uid'];
	  $applyCondition=" and call_staff=$userId";
	 }else{
	  $applyCondition=" and 1=1";
	 }
	 $category= $db->getAllRecord("SELECT b.type_category,b.type_id,b.type_name,b.defaultType FROM site_types b where b.type=1");
	  $openTicket = $db->getAllRecord("SELECT count(*) as count, a.call_department,b.type_name FROM site_calls a, site_types b where a.call_department=b.type_id and a.`call_status`=0 group by a.call_department");
	  $openTicketbyStaff=$db->getAllRecord("SELECT COUNT( * ) AS count, a.call_staff, b.user_name FROM site_calls a, site_users b WHERE a.call_staff = b.user_id AND a.`call_status` =0 GROUP BY b.user_name");
	  $deptTicket = $db->getAllRecord("SELECT count(*) as count, a.call_status,b.type_name FROM site_calls a ,site_types b where b.type_id=a.call_department and b.type=1 and b.defaultType=1 group by a.call_status");
	  $allDeptStatus=$db->getAllRecord("SELECT DISTINCT (a.call_department ), b.type_name, 
										(SELECT COUNT( * ) FROM site_calls WHERE call_status =1 AND call_department = b.type_id ) AS closed,
										(SELECT COUNT( * ) FROM site_calls WHERE call_status =0 AND call_department = b.type_id ) AS open,
										(SELECT COUNT( * ) FROM site_calls WHERE call_status =3 AND call_department = b.type_id ) AS pending,
										(SELECT COUNT( * ) FROM site_calls WHERE call_status =4 AND call_department = b.type_id ) AS fixed
										FROM  `site_calls` a
										JOIN site_types b ON a.call_department = b.type_id
										WHERE b.type =1");
	  $dashboard = $db->getOneRecord("SELECT `user_name`,`user_profile_pic`,`user_category`,`user_level`,
									(SELECT count(user_name) from site_users) as total_user,
									(select count(call_id) from site_calls where call_status = 0 $applyCondition)  as TotalOpen ,
									(select count(call_id) from site_calls where call_status = 3 $applyCondition)  as Pending ,
									(select count(call_id) from site_calls where call_status < 5 $applyCondition) as TotalTicket,
									(SELECT count(user_name) from site_users WHERE user_pending=1 ) as pending_user 
									FROM `site_users` WHERE `user_id`=".$_SESSION['uid']);
								
			$dashboard['user_profile_pic']=BASEURL.$dashboard['user_profile_pic'];					
			$category=json_encode($category);
			$dashboard=json_encode($dashboard);
			$openTicket= json_encode($openTicket);
			$openTicketbyStaff= json_encode($openTicketbyStaff);
			$deptTicket= json_encode($deptTicket);
			$allDeptStatus= json_encode($allDeptStatus);
			
			$response["dashboard"] = json_decode($dashboard);
			$response["openTicketbyStaff"] = json_decode($openTicketbyStaff);
			$response["openTicket"] = json_decode($openTicket);
			$response["deptTicket"] = json_decode($deptTicket);
			$response["category"] = json_decode($category);	
			$response["allDeptStatus"] = json_decode($allDeptStatus);				
		 	$response["status"] = "Success";
        	$response["message"] = "User  logged in!";
			$status=200;			
	 }else{
	 		$response["status"] = "error";
        	$response["message"] = "User not logged in!";
			$status=201;
       
	 }
	//echo $_SESSION['uid'];

    echoResponse($status, $response);
});



//***************************************************
//     CHANGE DEPT - BAR chart
//***************************************************
$app->post('/changeDept', function() use ($app) {
    $db = new DbHandler();
    //$session = $db->destroySession();
	$response = array();
   
	  
	 if (!isset($_SESSION)) {
                session_start();
     }
	 if(isset($_SESSION['uid'])){
	 $r = json_decode($app->request->getBody());
	
	$selectedDept = $r->search->selectedDept;
	$deptTicket = $db->getAllRecord("SELECT count(*) as count, a.call_status,b.type_name FROM site_calls a ,site_types b where b.type_id=$selectedDept and b.type=1 and a.call_department=$selectedDept group by a.call_status");
				
			$deptTicket= json_encode($deptTicket);

			$response["deptTicket"] = json_decode($deptTicket);						
		 	$response["status"] = "Success";
        	$response["message"] = "User  logged in!";
			$status=200;			
	 }else{
	 		$response["status"] = "error";
        	$response["message"] = "User not logged in!";
			$status=201;
       
	 }
	//echo $_SESSION['uid'];

    echoResponse($status, $response);
});

//***************************************************
//     Delete TICKETS
//***************************************************
$app->post('/deleteTicket', function() use ($app) {
    $db = new DbHandler();    
	$response = array();
	$r = json_decode($app->request->getBody());
    $ticket = $r->search->ticket;
	if (!isset($_SESSION)) {
                session_start();
     }
	 if(isset($_SESSION['uid'])){
		 $query="delete from site_calls where call_id=$ticket";
		 $result = $db->executeQuery($query);	
		//print_r($result);
        if ($result != NULL || $result==0 ) {
		 		$response["message"] = "Ticket deleted successfully!";
				$response["status"] = "success";
				$response["statusCode"] = "200";
				//f$response["gsmStatus"] = notifyUser($result);
	 }
	 else{
	 		$response["status"] = "error";
        	$response["message"] = "Issue in deleting Ticket!";
			$response["statusCode"] = "200";     
	 }
	 }
	
    echoResponse($response["statusCode"], $response);
});


//***************************************************
//     Delete Comment
//***************************************************
$app->post('/deleteComment', function() use ($app) {
    $db = new DbHandler();    
	$response = array();
	$r = json_decode($app->request->getBody());
    $ticket = $r->search->ticket;
	if (!isset($_SESSION)) {
                session_start();
     }
	 if(isset($_SESSION['uid'])){
		 $query="delete from site_notes where note_relation=$ticket";
		 $result = $db->executeQuery($query);	
		//print_r($result);
        if ($result != NULL || $result==0 ) {
		 		$response["message"] = "Comment successfully deleted!";
				$response["status"] = "success";
				$response["statusCode"] = "200";
				//f$response["gsmStatus"] = notifyUser($result);
	 }
	 else{
	 		$response["status"] = "error";
        	$response["message"] = "Issue in deleting Comment!";
			$response["statusCode"] = "200";     
	 }
	 }
	
    echoResponse($response["statusCode"], $response);
});



//***************************************************
//     Add comments
//***************************************************
$app->post('/addComment', function() use ($app) {
    $db = new DbHandler();    
	$response = array();
	$r = json_decode($app->request->getBody());
    $ticket = $r->search->ticket;
	$comment = $r->search->comment;
	$name = $r->search->name;
	
	if (!isset($_SESSION)) {
                session_start();
     }
	 if(isset($_SESSION['uid'])){
		 $userId=$_SESSION['uid'];
		 $query="INSERT INTO `site_notes` ( `note_title`, `note_body`, `note_relation`,`note_relation_name`, `note_type`, `note_post_date`, `note_post_user`) VALUES ( '', '$comment', $ticket,'$name', '1', UNIX_TIMESTAMP(), $userId);";
		 
		 $result = $db->executeQuery($query);	
		//print_r($result);
        if ($result != NULL || $result==0 ) {
		 		$response["message"] = "Comment added successfully!";
				$response["status"] = "success";
				$response["statusCode"] = "200";
				$response["commentId"] = $result;
				$response["gsmStatus"] = notifyUser($ticket,"addComment");
	 }
	 else{
	 		$response["status"] = "error";
        	$response["message"] = "Issue in deleting Ticket!";
			$response["statusCode"] = "200";     
	     }
	 }
	
    echoResponse($response["statusCode"], $response);
});

//***************************************************
//     EDIT TICKETS
//***************************************************
$app->post('/editTicket', function() use ($app) {
    $db = new DbHandler();
    //$session = $db->destroySession();
	$response = array();
	$r = json_decode($app->request->getBody());
    $ticket = $r->search->ticket;
	if($ticket==null || $ticket ==''){
	  $ticket=0;
	}
	  
	 if (!isset($_SESSION)) {
                session_start();
     }
	 if(isset($_SESSION['uid'])){
	  $userId=$_SESSION['uid'];
	  
	  if($ticket>0){
		  $updateQuery="UPDATE  `site_calls` SET  `isopened` =  '0' WHERE  `site_calls`.`call_id` =$ticket";
		  $result = $db->executeQuery($updateQuery);
	  }
	  $department = $db->getAllRecord("SELECT `type_name` as department,type_id FROM `site_types` WHERE `type`=1");
	  $request = $db->getAllRecord("SELECT type_category,`type_name` as request,type_id FROM `site_types` WHERE `type`=2");
	  $device = $db->getAllRecord("SELECT type_category,`type_name` as device,type_id FROM `site_types` WHERE `type`=3");
	  $userPersonalDetail = $db->getOneRecord("SELECT user_level,user_login,user_name,user_email,user_phone FROM site_users WHERE user_id=$userId");
	  $users = $db->getAllRecord("SELECT `user_name` as name,user_id  FROM `site_users` WHERE `user_level`=1 or `user_level`=0 or `user_level`=4");
	  $notes = $db->getAllRecord("SELECT `note_id`,`note_post_user`,note_relation_name,`note_post_date`,`note_body`  FROM `site_notes` WHERE `note_relation`=$ticket");
	  
	  $userDetails = $db->getOneRecord("SELECT A.call_id,A.call_first_name,A.call_phone,A.call_email,A.call_department,A.call_request,A.call_device,A.call_details,A.call_date,A.call_status,A.call_solution,A.call_location,A.call_user,A.call_staff,C.type_name as department,D.type_name as device,E.type_name as request FROM `site_calls` A INNER JOIN site_types C ON A.`call_department` = C.type_id INNER JOIN site_types D ON A.`call_device` = D.type_id INNER JOIN site_types E ON A.`call_request` = E.type_id WHERE A.`call_id`=".$ticket);
		
		 if(!$userDetails){
				 $userDetails='{
							"call_id": "",
							"call_first_name": "",
							"call_phone": "",
							"call_email": "",
							"call_department": "",
							"call_request": "",
							"call_device": "",
							"call_details": "",
							"call_date": "",
							"call_status": "",
							"call_solution": "",
							"call_location": "",
							"call_user": "",
							"call_staff": "",
							"department": "",
							"device": "",
							"request": ""
						}';
						$userDetails=json_decode($userDetails);
		 }
		 							
			$userDetails=json_encode($userDetails);
			$department= json_encode($department);
			$request= json_encode($request);
			$device= json_encode($device);
			$users= json_encode($users);
			$notes= json_encode($notes);
			$userPersonalDetail= json_encode($userPersonalDetail);
				
			$response["userDetails"] = json_decode($userDetails);
			$response["department"] = json_decode($department);
			$response["request"] = json_decode($request);
			$response["device"] = json_decode($device);
			$response["users"] = json_decode($users);
			$response["notes"] = json_decode($notes);
			$response["userPersonalDetail"] = json_decode($userPersonalDetail);			
		 	$response["status"] = "Success";
        	$response["message"] = "View/Add Ticket";
			$status=200;			
	 }else{
	 		$response["status"] = "error";
        	$response["message"] = "Issue in fetching detail!";
			$status=201;
       
	 }
	//echo $_SESSION['uid'];

    echoResponse($status, $response);
});



//***************************************************
//     GET Department & Course
//***************************************************
$app->post('/getDepartment', function() use ($app) {
    $db = new DbHandler();
    //$session = $db->destroySession();
	$response = array();
	
	  
	 if (!isset($_SESSION)) {
                session_start();
     }
	 
	  //$userId=$_SESSION['uid'];
	  $department = $db->getAllRecord("SELECT `type_name` as department,type_id FROM `site_types` WHERE `type`=4");
	  $course = $db->getAllRecord("SELECT `type_name` as request,type_id FROM `site_types` WHERE `type`=5");  
		
			
			$department= json_encode($department);
			$course= json_encode($course);				
			
			$response["department"] = json_decode($department);
			$response["course"] = json_decode($course);
			
					
		 	$response["status"] = "success";
        	$response["message"] = "Data fetched successfully";
			$status=200;			
	 
	

    echoResponse($status, $response);
});





//***************************************************
//     Edit Account Detail
//***************************************************
$app->post('/editAccount', function() use ($app) {
    $response = array();
	
	
	if (!isset($_SESSION)) {
                session_start();
     }
	 if(isset($_SESSION['uid'])){
	 		 $r = json_decode($app->request->getBody());
	
   
   $db = new DbHandler();
    $user_registrationId = $r->customer->user_registrationId;
	$user_department = $r->customer->user_department;
	$user_course = $r->customer->user_course;
	$password = $r->customer->password;
	$user_password = $r->customer->user_password;
	$user_address = $r->customer->user_address;
	$user_name = $r->customer->user_name;
	$user_city = $r->customer->user_city;
	$user_state = $r->customer->user_state;
	$user_zip = $r->customer->user_zip;
	$user_country = $r->customer->user_country;
	$user_phone = $r->customer->user_phone;
	$user_email = $r->customer->user_email;
	$user_msg_send = $r->customer->user_msg_send;
	$user_alert = $r->customer->user_alert;
	$user_profile_pic = $r->customer->user_profile_pic;

	if($password==''){
		$password = $user_password;
	}else{
		$password = passwordHash::hash($password);
	}
	
	if($user_alert==true){
		$user_alert=0;
	}else{
		$user_alert=1;
	}
	$user_id=$_SESSION['uid'];
	  $query = "UPDATE site_users SET  user_profile_pic='$user_profile_pic',user_course='$user_course',user_password='$password',user_department='$user_department',user_registrationId='$user_registrationId',user_email='$user_email',user_name='$user_name',user_phone='$user_phone',user_address='$user_address',user_city='$user_city',user_state='$user_state',user_zip='$user_zip',user_country='$user_country',user_msg_send=$user_alert where user_id = $user_id;";
	
	
	
			
		$result = $db->executeQuery($query);	
        if ($result != NULL || $result==0 ) {
		 		$response["message"] = "Account Updated successfully!";
				$response["status"] = "success";
				$response["statusCode"] = "200";
		}else{
				$response["message"] = "Failed to update Account!";
				$response["status"] = "failed";
				$response["statusCode"] = "201";   
		}
		
	 }
	 else{
				$response["message"] = "please login!";
				$response["status"] = "error";
				$response["statusCode"] = "201";   
	 }
	echoResponse($response["statusCode"], $response);
});




//***************************************************
//     GET ACCOUNT DETAILS
//***************************************************
$app->post('/getAccountDetail', function() {
    $db = new DbHandler();
    //$session = $db->destroySession();
	$response = array();
   
	  
	 if (!isset($_SESSION)) {
                session_start();
     }
	 if(isset($_SESSION['uid'])){
      $user_id=$_SESSION['uid'];
	  $accountDetail = $db->getAllRecord("SELECT user_login,user_category,user_profile_pic,user_registrationId,user_department,user_course,user_password,user_name,user_address,user_city,user_state,user_zip,user_country,user_phone,user_email,user_msg_send,user_level FROM site_users WHERE (user_id = $user_id) limit 1;");
	  							
			$accountDetail=json_encode($accountDetail);		
				
			$response["accountDetail"] = json_decode($accountDetail);				
		 	$response["status"] = "Success";
        	$response["message"] = "User  logged in!";
			$status=200;			
	 }else{
	 		$response["status"] = "error";
        	$response["message"] = "User not logged in!";
			$status=201;
       
	 }
    echoResponse($status, $response);
});

//***************************************************
//     VIEW USERS
//***************************************************
$app->post('/getUsers', function() {
    $db = new DbHandler();
    //$session = $db->destroySession();
	$response = array();
   
	  
	 if (!isset($_SESSION)) {
                session_start();
     }
	 if(isset($_SESSION['uid'])){
      $user_id=$_SESSION['uid'];
	  $userList = $db->getAllRecord("SELECT user_id,user_name,user_email,user_pending,user_level,user_protect_edit,user_msg_send ,(SELECT count(call_id) from site_calls WHERE (call_user = user_id) AND (call_status = 0) ) as openCont from site_users where 1  order by user_level,user_id desc;");
	  							
			$userList=json_encode($userList);		
				
			$response["userList"] = json_decode($userList);				
		 	$response["status"] = "Success";
        	$response["message"] = "User  logged in!";
			$status=200;			
	 }else{
	 		$response["status"] = "error";
        	$response["message"] = "User not logged in!";
			$status=201;
       
	 }
    echoResponse($status, $response);
});



//***************************************************
//     Edit USERS
//***************************************************
$app->post('/editUsers', function() use ($app) {
    $db = new DbHandler();
    //$session = $db->destroySession();
	$response = array();
   
	$r = json_decode($app->request->getBody());
    $userId = $r->search->userId;
	if($userId==null || $userId ==''){
	  $userId=0;
	}
	
	 if (!isset($_SESSION)) {
                session_start();
     }
	 if(isset($_SESSION['uid'])){
      $user_id=$_SESSION['uid'];
	  $user_level=$_SESSION['user_level'];
	  $query="SELECT user_login,user_password,user_name,user_address,user_category,user_registrationId,user_department,user_course,user_city,user_state,user_zip,user_country,user_phone,user_email,user_msg_send,user_protect_edit,user_pending,user_level FROM site_users WHERE (user_id = $userId) limit 1;";

	  $userDetail = $db->getAllRecord($query);
	  							
			$userDetail=json_encode($userDetail);		
				
			$response["userDetail"] = json_decode($userDetail);				
		 	$response["status"] = "success";
			$response["user_level"]=$user_level;
        	$response["message"] = "User  logged in!";
			$status=200;			
	 }else{
	 		$response["status"] = "error";
        	$response["message"] = "User not logged in!";
			$status=201;
       
	 }
    echoResponse($status, $response);
});


//***************************************************
//     SAVE USERS
//***************************************************
$app->post('/saveUsers', function() use ($app) {
    $db = new DbHandler();
	require_once 'passwordHash.php';
    //$session = $db->destroySession();
	$response = array();
   
	   $r = json_decode($app->request->getBody());
	
    
    $db = new DbHandler();
	$userid = $r->customer->userId;
    $user_address = $r->customer->user_address;
	$user_category = $r->customer->user_category;
	$user_city = $r->customer->user_city;
	$user_country = $r->customer->user_country;
	$user_course = $r->customer->user_course;
	$user_department = $r->customer->user_department;
	$user_email = $r->customer->user_email;
	$user_level = $r->customer->user_level;
	$user_msg_send = $r->customer->user_msg_send;
	$user_name = $r->customer->user_name;
	$user_login = $r->customer->user_login;
	$user_password = $r->customer->user_password;
	$user_pending = $r->customer->user_pending;
	$user_phone = $r->customer->user_phone;
	$user_protect_edit = $r->customer->user_protect_edit;
	$user_registrationId = $r->customer->user_registrationId;
	$user_state = $r->customer->user_state;
	$user_zip = $r->customer->user_zip;
	
	if($user_pending!='' && ($user_pending==true || $user_pending=="1")){
		$user_pending=1;
	}else{
		$user_pending=0;
	}
	if($user_msg_send!='' && ($user_msg_send==true || $user_msg_send=="1")){
		$user_msg_send=1;
	}else{
		$user_msg_send=0;
	}
	if($user_protect_edit!='' && ($user_protect_edit==true || $user_protect_edit=="1")){
		$user_protect_edit=1;
	}else{
		$user_protect_edit=0;
	}
	
	 if (!isset($_SESSION)) {
                session_start();
     }
	 
	 $user_password_set = "";
	 if(!empty($user_password)){
		 $user_password = passwordHash::hash($user_password);
		 $user_password_set = "user_password='$user_password',";
	 }else{
	 	$user_password ="";
	 }
	 
	 
	 if(isset($_SESSION['uid'])){
      $user_id=$_SESSION['uid'];
	  
	  if($userid==''){
		 $query = "INSERT INTO site_users(user_login,user_email,user_password,user_name,user_phone,user_address,user_city,user_state,user_zip,user_country,user_level,user_status,user_course,user_department,user_registrationId,user_protect_edit,user_pending,user_msg_send)VALUES
		 ('$user_login','$user_email','$user_password','$user_name','$user_phone','$user_address','$user_city','$user_state','$user_zip','$user_country','$user_level',1,'$user_course','$user_department','$user_registrationId','$user_protect_edit','$user_pending','$user_msg_send');"; 
	  }else{
		  $query = "UPDATE site_users SET $user_password_set user_email='$user_email',user_course='$user_course',user_department='$user_department',user_registrationId='$user_registrationId',user_name='$user_name',user_phone='$user_phone',user_address='$user_address',user_city='$user_city',user_state='$user_state',user_zip='$user_zip',user_country='$user_country',user_level=$user_level,user_msg_send=$user_msg_send,user_protect_edit=$user_protect_edit,user_pending=$user_pending where user_id = $userid;";
	  }
	  
	//print_r($query);
		$result = $db->executeQuery($query);	
		//print_r($result);
        if ($result != NULL || $result==0 ) {
		 		$response["message"] = "Account Updated successfully!";
				$response["status"] = "success";
				$status=200;
		}else{
				$response["message"] = "Failed to update Account!";
				$response["status"] = "failed";				
				$status=201;				
		}			
	 }else{
	 		$response["status"] = "error";
        	$response["message"] = "User not logged in!";
			$status=201;
       
	 }
    echoResponse($status, $response);
});






//***************************************************
//     UPLOAD
//***************************************************
$app->post('/upload', function() {
    if(isset($_FILES['file'])){    
    $errors= array();        
    $file_name = $_FILES['file']['name'];
    $file_size =$_FILES['file']['size'];
    $file_tmp =$_FILES['file']['tmp_name'];
    $file_type=$_FILES['file']['type'];   
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $extensions = array("jpeg","jpg","png");        
    if(in_array($file_ext,$extensions )=== false){
         $errors[]="image extension not allowed, please choose a JPEG or PNG file.";
		 $response["status"] = "error";
        $response["message"] = "image extension not allowed, please choose a JPEG or PNG file.";
		$status=201;
    }
    if($file_size > 2097152){
        $errors[]='File size cannot exceed 2 MB';
		$response["status"] = "error";
        $response["message"] = "File size cannot exceed 2 MB";
		$status=201;
    }               
    if(empty($errors)==true){
		$new_image_name = 'image_' . date('Y-m-d-H-i-s') . '_' . uniqid().".".$file_ext;
        move_uploaded_file($file_tmp,"upload/".$new_image_name);
       // echo " uploaded file: " . "images/" . $new_image_name;
		 $response["status"] = "Success";
        $response["message"] = "images/" . $new_image_name;
		$status=200;
    }else{
        //print_r($errors);
		$response["status"] = "error";
        $response["message"] = "error";
		$status=201;
    }
}
else{
    $errors= array();
    $errors[]="No image found";
    //print_r($errors);
	$response["status"] = "error";
    $response["message"] = "No image found";
	$status=201;
}
  
    echoResponse($status, $response);
});
?>