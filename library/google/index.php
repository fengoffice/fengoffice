<?php    
require_once 'autoload.php';
require_once '/Client.php';
require_once '/Service/Calendar.php';  
//require_once 'CalendarHelper.php';  
session_start(); 
$client = new Google_Client();
$client->setApplicationName("Client_Library_Examples");
//$client->setDeveloperKey("AIzaSyBBH88dIQPjcl5nIG-n1mmuQ12J7HThDBE");  
$client->setClientId('882360446644-9ui7gju7k6cmj4ti6ilgp054h5glvfp9.apps.googleusercontent.com');
$client->setClientSecret('vVImW-aIa5avv2zh-EUK5zCq');
$client->setRedirectUri('http://localhost:4567/oauth2callback');
$client->setAccessType('offline');   // Gets us our refreshtoken
$client->setScopes(array('https://www.googleapis.com/auth/calendar'));

$client->setState("https://u5.fengoffice.com/_feng");

//For loging out.
if (isset($_GET['logout'])) {
    unset($_SESSION['token']);
}


// Step 2: The user accepted your access now you need to exchange it.
if (isset($_GET['code'])) {
    print_r($_SESSION);
    print_r($_GET);
    $client->authenticate($_GET['code']);  
    $_SESSION['token'] = $client->getAccessToken();
    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    
    $redirect = $_GET['state'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

// Step 1:  The user has not authenticated we give them a link to login    
if (!isset($_SESSION['token'])) {
 
    $authUrl = $client->createAuthUrl();

    print "<a class='login' href='$authUrl'>Connect Me!</a>";
}    
// Step 3: We have access we can now create our service
if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
    print "<a class='logout' href='".$_SERVER['PHP_SELF']."?logout=1'>LogOut</a><br>";  
    
    $service = new Google_Service_Calendar($client);  

    $calendar = $service->calendars->get('primary');

    echo $calendar->getSummary();  
    
    $calendarList  = $service->calendarList->listCalendarList();
    print_r($calendarList);
    print_r($_SESSION);
    while(true) {
        foreach ($calendarList->getItems() as $calendarListEntry) {
            echo $calendarListEntry->getSummary()."<br>\n";
        }
        $pageToken = $calendarList->getNextPageToken();
        if ($pageToken) {
            $optParams = array('pageToken' => $pageToken);
            $calendarList = $service->calendarList->listCalendarList($optParams);
        } else {
            break;
        }
    }
}
?>