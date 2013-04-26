<?php

/**
 * newsbook v0.5
 * Dan Kass, April 2013
 */

// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');

/* require_once('FriendRank.php'); */
require_once('friendrank.php');
require_once('Aggregator.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');


/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

require_once('sdk/src/facebook.php');

$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));

$rank = new AyFbFriendRank($facebook);

$file = 'ranked.txt';
$rankedFriends;

//if(filesize($file) > 0) {
	//echo('cached');
//	$rankedFriends = unserialize(file_get_contents($file));
//} else {
	//echo('not cached');
$rankedFriends = $rank->getFriends();
//	file_put_contents($file,serialize($rankedFriends));
///}
//print_r($rankedFriends);

//$ranked_data = serialize($rankedFriends);
$close = $rank->getCloseFriends($rankedFriends);
$med = $rank->getMedFriends($rankedFriends);
$far = $rank->getFarFriends($rankedFriends);

/*foreach ($far as $trick) {
 
 	$name = $trick['name'];
 	$score = $trick['score'];
 	$inbox_score = $trick['weight']['inbox_chat'];
 	echo($name . '(' . $score . ') / ');
}*/


$agg = new LinkAggregator($facebook);

//print_r($close);

$agg->getLinks($close, 'small');
$agg->getLinks($med, 'med');
$agg->getLinks($far, 'big');

//$test = $agg->tester();
//print_r($test);

$sortedLinks = $agg->getSortedLinks();
$myLinks = $agg->getMyLinks();


$user_id = $facebook->getUser();
if ($user_id) {
  try {
    // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    if (!$facebook->getUser()) {
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }
}

?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

    <title><?php echo he($app_name); ?></title>
    <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
    <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" />
	<link href="stylesheets/reset.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>
	<link href="stylesheets/style.css" rel="stylesheet" type="text/css" />    

    <!--[if IEMobile]>
    <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"  />
    <![endif]-->

    <script type="text/javascript" src="/javascript/jquery-1.7.1.min.js"></script>
    <script src="/javascript/jquery.isotope.min.js"></script>
    <script src="/javascript/site.js"></script>
    <!--[if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->
  </head>
  <body>
    <div id="fb-root"></div>
    <script type="text/javascript">
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
        });

        // Listen to the auth.login which will be called when the user logs in
        // using the Login button
        FB.Event.subscribe('auth.login', function(response) {
          // We want to reload the page now so PHP can read the cookie that the
          // Javascript SDK sat. But we don't want to use
          // window.location.reload() because if this is in a canvas there was a
          // post made to this page and a reload will trigger a message to the
          // user asking if they want to send data again.
          window.location = window.location;
        });

        FB.Canvas.setAutoGrow();
      };

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>

    <!--<header class="clearfix">
      <?php if (isset($basic)) { ?>
      <div>
        <h1>Hello, <strong><?php //echo he(idx($basic, 'name')); ?>!</strong>.</h1>
      </div>
      <?php } else { ?>
      <div>
        <h1>Welcome! Log In to start. I promise that Facebook doesn't allow me to see any of your data.</h1>
        <div class="fb-login-button" data-scope="read_mailbox,read_stream,user_photos,friends_photos"></div>
      </div>
      <?php } ?>
    </header>-->
    
	<div class='content'>
		<aside>
			<h1 class='error'>No articles to display.</h1>			
			<div class='radar'>
				<div class='big circle' id='big'></div>  
				<div class='med circle' id='med'></div> 
				<div class='small circle' id='small'></div> 
				<div class='dot'>YOU</div>
				<div class='tooltip'>Hello</div>
			</div>	
			
			<h2 class='select' id='all'>Show All</h2>
			<!--<h2 id='home'>Home Friends</h2>
			<h2 id='school'>School Friends</h2>
			<h2 id='top'>Top Stories</h2>
			<h2 id='video'>Videos</h2>
			<h2 id='image'>Images</h2>
			<h2 id='article'>Articles</h2>-->		
		</aside>
		<h1 class='title'>All Links</h1>
		<section>
			
			<?php
			
				foreach($sortedLinks as $link) {
				
					echo '<article data-url="' . $link['url'] . '" class="col1 article ' . $link['class'] . '">'
						. '<h4>' . $link['name'] . '</h4>'
						. '<h5>' . date("F j, Y, g:i a",$link['created_time']) . '<h5>'
						. '<h2>' . $link['title'] . '</h2>';
					if(!empty($link['picture'])) echo '<img src="' . $link['picture'] . '" alt="Image" />';
					if(!empty($link['owner_comment'])) echo '<p class="comment">"' . $link['owner_comment'] . '"</p>';
					else echo '<p>' . $link['summary'] . '</p>';	
					echo '</article>';															
				}

				foreach($myLinks as $link) {
				
					echo '<article data-url="' . $link['url'] . '" class="col1 you article" style="display:none">'
						. '<h4>' . he(idx($basic, 'name')) . '</h4>'
						. '<h5>' . date("F j, Y, g:i a",$link['created_time']) . '<h5>'
						. '<h2>' . $link['title'] . '</h2>';
					if(!empty($link['picture'])) echo '<img src="' . $link['picture'] . '" alt="Image" />';					
					echo '<p>' . $link['summary'] . '<p></article>';		
				}
			
			?>
			 <!-- <article class="col1 med school article">
			  	<h4>Jay Peterson</h4>
			  	<h5>4/24/2013 4:59PM</h5>
			  	<h2>This is the title of the shared article that etc Something</h2>
			  	<img src="http://farm4.static.flickr.com/3197/3122875223_917b1ccafc.jpg" alt="McCall Cover, Joan Caulfield" />
			  	<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>			    	
			  </article>	 -->							
		</section> 
	</div>
    
  </body>
</html>
