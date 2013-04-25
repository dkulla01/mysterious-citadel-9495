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
if(filesize($file) > 0) {
	$rankedFriends = unserialize(file_get_contents($file));
} else {
	$rankedFriends = $rank->getFriends();
	file_put_contents($file,serialize($rankedFriends));
}
//$ranked_data = serialize($rankedFriends);
$close = $rank->getCloseFriends($rankedFriends);

foreach ($rankedFriends as $trick) {
 
 	$name = $trick['name'];
 	$score = $trick['score'];
 	$inbox_score = $trick['weight']['inbox_chat'];
 	//echo($name . '(' . $score . ') / ');
  /*
  $id = idx($friend, 'id');	
  echo($id . ' ');
  print_r($friend['links']['data'][0]['name']);
  echo('------------');*/
  //$query = '/' . $id . 'links?limit=1';
  //$link = idx($facebook->api($query), 'data', array());
  
  //$friend['shlink'] = $link;
}


$agg = new LinkAggregator($facebook);

//print_r($close);

//$agg->getLinks($close);

//print_r($holyshit);



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

    <header class="clearfix">
      <?php if (isset($basic)) { ?>
      <div>
        <h1>Hello, <strong><?php echo he(idx($basic, 'name')); ?>!</strong>.</h1>
      </div>
      <?php } else { ?>
      <div>
        <h1>Welcome! Log In to start. I promise that Facebook doesn't allow me to see any of your data.</h1>
        <div class="fb-login-button" data-scope="read_mailbox,read_stream,user_photos,friends_photos"></div>
      </div>
      <?php } ?>
    </header>
    
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
			<h2 id='home'>Home Friends</h2>
			<h2 id='school'>School Friends</h2>
			<h2 id='top'>Top Stories</h2>
			<h2 id='video'>Videos</h2>
			<h2 id='image'>Images</h2>
			<h2 id='article'>Articles</h2>		
		</aside>
		<section>
			<h1 class='title'>All Links</h1>
			  <article class="col1 med school article">
			    <blockquote>
			      <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
			    </blockquote>
			    <p>&mdash; Marcus Aurelius</p>
			    <em>shared by: jay peterson</em>			    	
			  </article>		
		
			<article class="col2 big home top video">
			    <h2>Last Day Dream</h2>
			    <p>
			      <object width="460" height="265"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=4155700&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=ff4000&amp;fullscreen=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=4155700&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=ff4000&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="460" height="265"></embed></object>
			    </p>
			
			    <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
			    <em>shared by: pete rose</em>	
			  </article>
			
			  <article class="col1 small home article">
			    <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
			    <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p> 
			    <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
			    <p>Lorem ipsum dolor sit amet.</p>
			    <em>shared by: peter jason</em>				
			  </article>
			
			  <article class="col2 small school image top" style='height:400px'>
			    <p>
			      <a href="http://www.flickr.com/photos/george_eastman_house/3123693806/in/set-72157611386593623"><img src="http://farm4.static.flickr.com/3121/3123693806_4cb1ca16d9.jpg" alt="" /></a>
			    </p>
			    <em>shared by: david ross</em>		
			  </article>
			
			  <article class="col1 big home image top">
			    <p>
			      <a href="http://www.flickr.com/photos/george_eastman_house/3123692308/in/set-72157611386593623">
			        <img src="http://farm4.static.flickr.com/3282/3123692308_9e81bc4d14.jpg" alt="" />
			      </a>
			    </p>
			    <p>[PARENTS MAGAZINE, GIRL WITH CAT]</p>
			     <em>shared by: jay peterson</em>	
			  </article>
			
			  <article class="col1 small school article">
			    <blockquote>
			      <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur</p>
			    </blockquote>
			
			    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
			  			     <em>shared by: marcia wallace</em>	
			  </article>
			
			  <article class="col2 big home article top">
			    <h2>Ut enim ad minim veniam</h2>
			    <ul>
			       <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
			       <li>Aliquam tincidunt mauris eu risus.</li>
			       <li>Vestibulum auctor dapibus neque.</li>
			    </ul>
			    <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. <a href="#">Mauris placerat eleifend leo.</a> Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p>
			  			     <em>shared by: jay peterson</em>	
			  </article>
			
			  <article class="col3 med school article">
			    <h3>Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis.</h3>      
			
			
			    <h3>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. <a href="#">Mauris placerat eleifend leo.</a></h3>
			  			     <em>shared by: david dodd</em>	
			  </article>
			
			  <article class="col1 big home image top">
			    <h3>feugiat vitae, ultricies eget</h3>
			    <a href="http://www.flickr.com/photos/library_of_congress/2179137415/"><img src="http://farm3.static.flickr.com/2109/2179137415_0e63ebb36e_m.jpg" alt="" /></a>
			  			     <em>shared by: rick rowe</em>	
			  </article>
			
			  <article class="col2 small school article">
			    <h2>A Tremendous Celebration</h2>
			    <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p>
			    <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p>
			    <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p>
			    <ol>
			       <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
			       <li>Aliquam tincidunt mauris eu risus.</li>
			       <li>Vestibulum auctor dapibus neque.</li>
			    </ol>
			
			  			     <em>shared by: pete rose</em>	
			  </article>
			
			
			  <article class="col2 big home video">
			    <object width="460" height="265"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=6185327&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=1&amp;color=dd4499&amp;fullscreen=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=6185327&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=1&amp;color=dd4499&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="460" height="265"></embed></object>
			  			     <em>shared by: lucy rose</em>	
			  </article>
			
			  <article class="col2 med school article top">
			    <h2>And of Deliberate Consequences</h2>
			
			    <ol>
			       <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
			       <li>Aliquam tincidunt mauris eu risus.</li>
			       <li>Vestibulum auctor dapibus neque.</li>
			    </ol>
			
			    <h3>Aenean ultricies mi</h3>
			
			    <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p>
			    <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p>
			
			    <h3>Vestibulum tortor quam</h3>
			
			    <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p>
			
			  			     <em>shared by: james watson</em>	
			  </article>
			
			  <article class="col1 small school article">
			    <blockquote>
			      <p>Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. 
			
			      </p>
			    </blockquote>
			  			     <em>shared by: blake roberts</em>	
			  </article>
			
			  <article class="col1 med home image top">
			    <p>
			      <a href="http://www.flickr.com/photos/library_of_congress/2179136893/" title="Women workers install fixtures and assemblies to a tail fuselage section of a B-17F bomber at the Douglas Aircraft Company, Long Beach, Calif. Better known as the &quot;Flying Fortress,&quot; the B-17F is a later model of the B-17 which distinguished itself in acti by The Library of Congress, on Flickr"><img src="http://farm3.static.flickr.com/2186/2179136893_a12b3ace56_m.jpg" alt="Women workers install fixtures and assemblies to a tail fuselage section of a B-17F bomber at the Douglas Aircraft Company, Long Beach, Calif. Better known as the &quot;Flying Fortress,&quot; the B-17F is a later model of the B-17 which distinguished itself in acti"></a>
			    </p>
			    <p>
			
			      Women workers install fixtures and assemblies to a tail fuselage section of a B-17F bomber at the Douglas Aircraft Company, Long Beach, Calif. Better known as the "Flying Fortress," the B-17F is a later model of the B-17 which distinguished itself in action in the South Pacific, over Germany and elsewhere. It is a long range, high altitude heavy bomber, with a crew of seven to nine men, and with armament sufficient to defend itself on daylight missions
			    </p>
			  			     <em>shared by: liz jameson</em>	
			  </article>
			
			  <article class="col1 med school article top">
			    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p>
			    <p><em>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo</em> consequat. <strong>Duis aute irure dolor in reprehenderit</strong> in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
			    </p>
			  			     <em>shared by: danny roberts</em>	
			  </article>
			
			  <article class="col1 small home image">
			    <p>
			      <a href="http://www.flickr.com/photos/library_of_congress/2178407555/"><img src="http://farm3.static.flickr.com/2008/2178407555_9a9bcfe31f_m.jpg" height="240" alt="" /></a>
			    </p>
			  			     <em>shared by: jay peterson</em>	
			  </article>
			
			
			  <article class="col3 big school video">
			     <object width="700" height="394"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=7174318&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=1&amp;color=dd4499&amp;fullscreen=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=7174318&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=1&amp;color=dd4499&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="700" height="394"></embed></object>
			  			     <em>shared by: rick catz</em>	
			  </article>
			
			  <article class="col1 med home article top">
			    <blockquote>
			      <p>Sing, O goddess, the anger of Achilles son of Peleus, that brought countless ills upon the Achaeans. Many a brave soul did it send hurrying down to Hades, and many a hero did it yield a prey to dogs and vultures, for so were the counsels of Jove fulfilled from the day on which the son of Atreus, king of men, and great Achilles, first fell out with one another.</p>
			    </blockquote>
			
			    <cite><a href="http://classics.mit.edu/Homer/iliad.1.i.html">Homer &mdash; The Iliad</a></cite>
			  			     <em>shared by: homer simpson</em>	
			  </article>
			
			  <article class="col1 small school article">
			    <h2>Aliens attack South Dakota</h2>
			      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p>
			    <blockquote><p>Yes, it did happen.</p></blockquote>
			
			    <p><em>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo</em> consequat. <strong>Duis aute irure dolor in reprehenderit</strong> in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
			    </p>
			  			     <em>shared by: marcia wallace</em>	
			  </article>
			
			  <article class="col3 big home image article top">
			     <p style="float: right; margin-left: 20px"><a href="http://www.flickr.com/photos/george_eastman_house/3122875223/in/set-72157611386593623/"><img src="http://farm4.static.flickr.com/3197/3122875223_917b1ccafc.jpg" alt="McCall Cover, Joan Caulfield" /></a></p>
			    <h2>And then, there is this.</h2>
			     <p class="caption"><a href="http://www.flickr.com/photos/george_eastman_house/3122875223/in/set-72157611386593623/"><em>McCall Cover, Joan Caulfield</em> by Nickolas Muray</a></p>
			     <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
			     <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
			          <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
			  			     <em>shared by: betsy phish</em>	
			  </article>										
		</section>
	</div>
    
  </body>
</html>
