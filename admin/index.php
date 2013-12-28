<?php

/**
 * @author over_coder <over.coder@yahoo.it>
 * Twitter: @over_coder
 */

try {
	require_once '../db.php';
	require_once './ss_man.php';
	require_once './config.php';
	require_once '../functions.php';
	require_once './google-api-php-client/src/Google_Client.php';
	require_once './google-api-php-client/src/contrib/Google_YouTubeService.php';
	require_once './google-api-php-client/src/contrib/Google_Oauth2Service.php';
}catch(Exception $e) {
	// Google PHP API require CURL extension enabled!
	echo 'Alcuni file non sono presenti o l\' estensione CURL di PHP non è attiva.';
}

define("UPLOAD_DIR", "../uploads/");

// YouTube API keys
$OAUTH2_CLIENT_ID = 'OAUTH2_CLIENT_ID';
$OAUTH2_CLIENT_SECRET = 'OAUTH2_CLIENT_SECRET';

$htmlBody = '';
$max_storage_days = 3; // -1 to infinite

$db = new Database();
$sman = new SessionManager();

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
	FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);
$youtube = new Google_YoutubeService($client);
$oauth2 = new Google_Oauth2Service($client);

// DB was creaded successuf?

if($db === false) {
	$htmlBody = error_msg('We have problem with the database, please try again later.');
	log_error('Database connection error.');
	exit();
}

if($sman === false) {
	$htmlBody = error_msg('We have problem with the sessions for the admin, please try again later.');
	log_error('Session error.');
	$db->close();
	exit();
}

function old_videos() {
	global $db, $max_storage_days;
	
	if($max_storage_days != -1) {
		$result = $db->query("SELECT videos.* FROM videos WHERE DATEDIFF( CURDATE(), videos.date ) >= " . $max_storage_days, false);
		if($result) {
			$old_videos = $db->result_array($result);
			return $old_videos;
		}
	}
	return false;
}

// Admin is logged?
if($sman->isLogged()) {
	// Admin is logged
	
	// Want to logout?
	if(isset($_GET['logout'])) {
		$sman->close();
		header('Location: index.php');
	}
	
	// YouTube login
	if (isset($_GET['code'])) {
		if (strval($_SESSION['state']) !== strval($_GET['state'])) {
			die('The session state did not match.');
		}
	
		$client->authenticate();
		$_SESSION['token'] = $client->getAccessToken();
		header('Location: ' . $redirect);
	}
	if (isset($_SESSION['token'])) {
		$client->setAccessToken($_SESSION['token']);
	}
	else {
		$state = mt_rand();
  		$client->setState($state);
  		$_SESSION['state'] = $state;
  		$authUrl = $client->createAuthUrl();
  		$htmlBody = '<div id="container">
			<a class="nulled" href="index.php"><h1>NoDZF <span class="tube_back">Tube</span></h1></a>
  			<h4 class="center">Authorization Required</h4>
  			<p>You need to <a href="' .$authUrl. '">authorize access to YouTube</a> before proceeding.<p>
  			</div>';
		$req_auth = true;
	}
	
	// Admin want to perform some actions?
	if(isset($_GET['action']) && isset($_GET['id']) && $client->getAccessToken()) {
		$action = $db->real_escape_string($_GET['action']);
		$id = $db->real_escape_string($_GET['id']);
		$user = $oauth2->userinfo->get();
		$user['email'] = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
		$user['picture'] = filter_var($user['picture'], FILTER_VALIDATE_URL);
		
		// Getting video informations
		$query = "SELECT * FROM videos WHERE id = ".$id;
		$result = $db->query($query);
		$video = mysqli_fetch_array($result);
		
		// Send video to YouTube
		if($action == 'send') {
			$req_auth = true;
			// Confirmed
			if(isset($_POST['status']) && isset($_POST['tags'])) {
				$vstatus = $db->real_escape_string($_POST['status']);
				$tags = $db->real_escape_string($_POST['tags']);
				$category = $db->real_escape_string($_POST['category']);
				$v_tags = explode(',', $tags);
				//$ext = getExtensions($video['name']);
				$ext = substr($video['name'], strpos($video['name'],'.'), strlen($video['name'])-1);
				
				// First update DB
				$query = "UPDATE videos SET videos.tag='" . $tags . "', videos.status = '" . $vstatus . "' WHERE videos.id = ".$id;
				$result = $db->query($query);
				
				// Complete information and send
				if($result) {
					$snippet = new Google_VideoSnippet($client);
					$snippet->setTitle($video['title']);
					$snippet->setDescription($video['description']);
					$snippet->setTags($v_tags);
					$snippet->setCategoryId((int)$category);
					
					$status = new Google_VideoStatus();
    				$status->privacyStatus = $vstatus;
					
					$gvideo = new Google_Video();
    				$gvideo->setSnippet($snippet);
    				$gvideo->setStatus($status);
					
					if(file_exists(UPLOAD_DIR . $video['prefix'] . $video['name']))
						$data = file_get_contents(UPLOAD_DIR . $video['prefix'] . $video['name']);
					else {
						$htmlBody = error_msg('Video not found on server');
						$db->close();
						exit();
					}
					
					$mimeType = 'video/mp4';
					
					switch ($ext) {
						case '.mp4':
							$mimeType = 'video/mp4';
							break;
						
						case '.avi':
							$mimeType = 'video/avi';
							break;
							
						case '.mov':
							$mimeType = 'video/quicktime';
							break;
						
						case '.mpeg':
							$mimeType = 'video/mpeg';
							break;
						
						case '.mkv':
							$mimeType = 'video-x-matroska';
							break;
						
						case '.wmv':
							$mimeType = 'video/x-ms-wmv';
							break;
						
						default:
							// Woh, possible cracking attack here! Via file.sh or file.php for example
							break;
					}
					
					$mimeType = 'video/mp4';
					
    				$mediaUpload = new Google_MediaFileUpload($mimeType,$data);
    				$error = true;
    				$i = 0;
					
    				$retryErrorCodes = array(500, 502, 503, 504);
    				while($i < 10 && $error) {
        				try{
            				$ret = $youtube->videos->insert("status,snippet", 
                                                 		$gvideo, 
                                                   		array("data" => $data,
															"mimeType" => $mimeType
												   		));
            				$error = false;
        				} catch(Google_ServiceException $e) {
        					$reason = $e->getErrors();
							$create_channel = '';
							if($reason = 'youtubeSignupRequired'){
             					//https://developers.google.com/youtube/v3/docs/errors#youtube.api.RequestContextError-unauthorized-youtubeSignupRequired
             					$create_channel = '<br /><br />Occorre la registrazione di un <a href="https://www.youtube.com/create_channel">canale su youtube</a> per il tuo account';
 	         				}
            				$htmlBody = error_msg("Caught Google service Exception ".$e->getCode()
                  				. " message is ".$e->getMessage()) . $create_channel;
            				if(!in_array($e->getCode(), $retryErrorCodes)){
                				break;
            				}
            				$i++;
        				}
   					}
					if(!$error) {
						$htmlBody = success_msg('Video sended to YouTube!');
					}
   					
				}
				
			}
			else {
				
				$ext = substr($video['name'], strpos($video['name'],'.'), strlen($video['name'])-1);
				
				$mimeType = 'video/mp4';
					
					switch ($ext) {
						case '.mp4':
							$mimeType = 'video/mp4';
							break;
						
						case '.avi':
							$mimeType = 'video/avi';
							break;
							
						case '.mov':
							$mimeType = 'video/quicktime';
							break;
						
						case '.mpeg':
							$mimeType = 'video/mpeg';
							break;
						
						case '.mkv':
							$mimeType = 'video-x-matroska';
							break;
						
						case '.wmv':
							$mimeType = 'video/x-ms-wmv';
							break;
						
						default:
							// Woh, possible cracking attack here! Via file.sh or file.php for example
							break;
					}
				
				$htmlBody = '
					<div id="container">
					<a class="nulled" href="index.php"><h1>NoDZF <span class="tube_back">Tube</span></h1></a>
					<h1 style="float:right"> <a href="'.$user['link'].'"><img src="'. $user['picture'] .'?sz=30">'. $user['given_name'] .'</a> - '. $user['locale'] .'</h1>
					<div id="up_form">
					<form name="send_form" action="' . $_SERVER['PHP_SELF'] . '?action=send&id=' . $id . '" method="post">
					 <h4>Title</h4><p>'. $video['title'] .'</p>
					 <h4>Description:</h4><p>'. $video['description'] .'</p>
					 <h4>Status: </label>
					 <select name="status">
					  <option value="public">Public</option>
					  <option value="private">Private</option>
					  <option value="unlisted" selected="true">Unlisted</option>
					 </select>
					 <br />
					 <h4>Tags (separated by comma): </label>
					 <input type="text" name="tags" value="'. $video['tag'] .'" required />
					 <h4>Category: </label>
					 <input type="text" name="category" value="1" required />
					 <h4>Upload date: '. $video['date'] .'</h4>
					 <h4>Upload time: '. $video['time'] .'</h4>
					 <h4>Size: '. $video['size'] .' MB</h4>
					 <input type="submit" value="Send video!" class="input button" />
					</form>
					</div>
					</div>
					
					<br /><br />
					
					<video controls>
					 <source src="'. UPLOAD_DIR . $video['prefix'] . $video['name'] .'" type="'. $mimeType .'" />
					</video>
					
					
				';
			}
		}
		// Edit video
		else if($action == 'edit') {
			$req_auth = true;
			// Confirmed
			if(isset($_POST['title']) && isset($_POST['description']) && isset($_POST['name']) && isset($_POST['prefix'])) {
				$title = $db->real_escape_string($_POST['title']);
				$description = $db->real_escape_string($_POST['description']);
				$name = $db->real_escape_string($_POST['name']);
				$prefix = $db->real_escape_string($_POST['prefix']);
				$rename_result = false;
				
				if(strlen($prefix) == 5) {
					try {
						$rename_result = rename(UPLOAD_DIR . $video['prefix'] . $video['name'], UPLOAD_DIR . $prefix . $name);
					}
					catch(Exception $e) {
						log_error('Error renaming a video: ' . $e);
					}
				}
				
				if(isset($_POST['tags'])) {
					$tags = $db->real_escape_string($_POST['tags']);
					$query = "UPDATE videos SET videos.title = '".$title."', videos.description = '".$description."', videos.name = '".$name."', videos.prefix = '".$prefix."', videos.tag = '".$tags."'  WHERE videos.id = ".$id;
				}
				else {
					$query = "UPDATE videos SET videos.title = '".$title."', videos.description = '".$description."', videos.name = '".$name."', videos.prefix = '".$prefix."' WHERE videos.id = ".$id;
				}
				
				$result = $db->query($query);
				
				if($result && $rename_result) {
					$htmlBody = success_msg('Video edited successfully');
				}
				else {
					$htmlBody = error_msg('Problems editing the video');
				}
			}
			else {
				$htmlBody = '<div id="container">
					<a class="nulled" href="index.php"><h1>NoDZF <span class="tube_back">Tube</span></h1></a>
					<h1 style="float:right"> <a href="'.$user['link'].'"><img src="'. $user['picture'] .'?sz=30">'. $user['given_name'] .'</a> - '. $user['locale'] .'</h1>
					<div id="up_form">
					<form name="edit_form" action="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '" method="post">
					 <h4>Titolo</h4>
					 <input type="text" name="title" value="'. $video['title'] .'" /><br />
					 <h4>Descrizione</h4>
					 <textarea name="description" cols="50" rows="10">'. $video['description'] .'</textarea>
					 <br />
					 <h4>Tags (separated by comma)</h4>
					 <input type="text" name="tags" value="'. $video['tag'] .'" /><br />
					 <h4>RAW Name:</h4>
					 <input type="text" name="name" value="'. $video['name'] .'" /><br />
					 <h4>Prefix:</h4>
					 <input type="text" name="prefix" value="'. $video['prefix'] .'" /><br />
					 <h4>Upload date: '. $video['date'] .'</h4>
					 <h4>Upload time: '. $video['time'] .'</h4>
					 <h4>Size: '. $video['size'] .' MB</h4>
					 <input type="submit" value="Edit video" class="input button" />
					</form>
					</div>
					</div>
				';
			}
		}
		// Delete video
		else if($action == 'delete') {
			$req_auth = true;
			// Confirmed
			if(isset($_POST['submit'])) {
				if(file_exists(UPLOAD_DIR . $video['prefix'] . $video['name'])) {
						$deleted = unlink(UPLOAD_DIR . $video['prefix'] . $video['name']);
						
						$query = "DELETE FROM videos WHERE videos.id = ".$id;
						$result = $db->query($query);
						
						if($result && $deleted) {
							$htmlBody = success_msg('Video <b>'. $video['prefix'] . $video['name'] .'</b> deleted');
						}
						else {
							$htmlBody = error_msg('Error deleting a video');
						}
				}
				else {
					$htmlBody = error_msg('Video not found on server');
					$db->close();
					exit();
				}
			}
			else {
				$htmlBody = '
					<div id="container">
					<a class="nulled" href="index.php"><h1>NoDZF <span class="tube_back">Tube</span></h1></a>
					<h1 style="float:right"> <a href="'.$user['link'].'"><img src="'. $user['picture'] .'?sz=30">'. $user['given_name'] .'</a> - '. $user['locale'] .'</h1>
					<div id="up_form">
					<form name="delete_form" action="' . $_SERVER['PHP_SELF'] . '?action=delete&id=' . $id . '" method="post">
					 <input type="hidden" name="submit" value="submit" />
					 <h4>Title: '. $video['title'] .'</h4>
					 <h4>Name: '. $video['name'] .'</h4>
					 <h4>Prefix: '. $video['prefix'] .'</h4>
					 <h4>Upload date: '. $video['date'] .'</h4>
					 <h4>Upload time: '. $video['time'] .'</h4>
					 <h4>Size: '. $video['size'] .' MB</h4>
					 <input type="submit" value="Delete video" class="input button" />
					</form>
					</div>
					</div>
				';
			}
		}
		// Error
		else {
			$htmlBody = error_msg('Error selecting an action');
		}
		
	}
	
	$query = "SELECT videos.* FROM videos";
	$video = $db->query($query);
	
	// We are logged in YouTube API too?
	if(!isset($req_auth)) {
		$user = $oauth2->userinfo->get();
		$user['email'] = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
		$user['picture'] = filter_var($user['picture'], FILTER_VALIDATE_URL);
		
		$htmlBody = '<div id="container">
			<a class="nulled" href="index.php"><h1>NoDZF <span class="tube_back">Tube</span></h1></a>
			<h1 style="float:right"> <a href="'.$user['link'].'"><img src="'. $user['picture'] .'?sz=30">'. $user['given_name'] .'</a> - '. $user['locale'] .'</h1>
			<div class="cnt_cnt">';
		
		while($row = mysqli_fetch_array($video)) {
			$htmlBody .= '<div class="cnt"><h4 class="center">' . $row['title'] . '</h4>
				<h1 style="float:right"> <a href="'.$user['link'].'"><img src="'. $user['picture'] .'?sz=30">'. $user['given_name'] .'</a> - '. $user['locale'] .'</h1>
				<img src="../images/yt_icon.gif" />
				<br />
				<a href="' . $_SERVER['PHP_SELF'] . '?action=send&id=' . $row['id'] . '">Send</a> | 
				<a href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $row['id'] . '">Edit</a> | 
				<a href="' . $_SERVER['PHP_SELF'] . '?action=delete&id=' . $row['id'] . '">Delete</a></div>';
		}
				
		// Warning old videos stored on server and never uploaded
		$old_videos = old_videos();
		
		if($old_videos) {
			$datenow = new DateTime("now");
			$htmlBody .= '<div id="old_videos"><h4 class="center">There are some old videos!</h4>';
			for($i = 0; $i != count($old_videos); $i++) {
				$old_date = new DateTime($old_videos[$i]['date']);
				$htmlBody .= '
					<br /><a href="' . $_SERVER['PHP_SELF'] . '?action=send&id=' . $old_videos[$i]['id'] . '">ID: #'
					.$old_videos[$i]['id']
					.' - Title: '.
					$old_videos[$i]['title']
					.'</a>
				';
				$interval = date_diff($datenow, $old_date);
				if($interval->format('%d days') > $max_storage_days && $interval->format('%d days') <= $max_storage_days * 2) {
					$htmlBody .= '<span class="old_warn">Warning!</span> ' . ($max_storage_days * 2 - $interval->format('%d days') . ' days remaining');
				}
				else if($interval->format('%d days') > $max_storage_days * 2) {
					if(file_exists(UPLOAD_DIR . $old_videos[$i]['prefix'] . $old_videos[$i]['name'])) {
						$deleted = unlink(UPLOAD_DIR . $old_videos[$i]['prefix'] . $old_videos[$i]['name']);
						
						$query = "DELETE FROM videos WHERE videos.id = ".$old_videos[$i]['id'];
						$result = $db->query($query);
						
						if($result && $deleted) {
							$htmlBody .= '<span class="old_warn">Deleted!</span>';
						}
						else {
							$htmlBody .= 'Error deleting a video';
						}
				}
			}
		}
		$htmlBody .= '</div>';
	}
	
		$htmlBody .= '</div><div id="credits" style="margin-top: 20px;">
					    <a href="'. $_SERVER['PHP_SELF'] .'?logout">Logout</a>
					   </div>
					 </div>';
 }
}
// Attempt for login?
else if(isset($_POST['username']) && isset($_POST['password'])) {
	$username = $db->real_escape_string($_POST['username']);
	$password = $db->real_escape_string($_POST['password']);
	
	$config = new Config;
	
	if($username == $config->getUsername() && $password == $config->getPassword()) {
		$sman->setLogged(true);
		header('Location: index.php');
	}
	else {
		$sman->setLogged(false);
		header('Location: index.php?loginerror');
	}
	
}
// The user are not logged, send login form
else {
	$htmlBody = '
		<div id="container">
		<a class="nulled" href="index.php"><h1>NoDZF <span class="tube_back">Tube</span></h1></a>
		<div id="up_form">
		';
	if(isset($_GET['loginerror']))
		$htmlBody .= '<br><br><h3>Wrong combination of username/password<h3>';
	$htmlBody .= '
		<form name="log_form" action="' . $_SERVER['PHP_SELF'] . '" method="post">
		 <h4>Username:</h4>
		 <input type="text" name="username" value="" required />
		 <br><br>
		 <h4>Password:</h4>
		 <input type="password" name="password" value="" required />
		 <br><br>
		 <input type="submit" value="Login" class="input button" />
		</form>
		</div>
		</div>
	';
}

// Close DB connection

if($db !== false) {
	$db->close();
}

?>

<!doctype html>
<html>
	<head>
		<title>Video Upload Page</title>
		<link href="../css/style.css" rel="stylesheet" type="text/css">
		<link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'>
	</head>
	
	<body>
		<?=$htmlBody?>
	</body>
</html>
