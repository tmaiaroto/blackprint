<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://ogp.me/ns/fb#">
<head>
	<?php
	// Page title
	$defaultTitle = '';
	if($this->request() && isset($this->request()->blackprintConfig['siteName'])) {
		$defaultTitle = $this->request()->blackprintConfig['siteName'];
	}
	$title = $this->title() ? $this->title():$defaultTitle;
	echo '<title>' . $title . '</title>' . "\n\t";
	echo $this->html->charset() . "\n\t";

	// Meta tags, OG, etc.
	echo $this->blackprint->metaTags();
	echo $this->blackprint->ogTags();
	?>
	<?php
		$this->blackprintAsset->style(array(
			//'/blackprint/css/datepicker.css',
			'/bower_components/bootstrap/dist/css/bootstrap.min.css',
			'/blackprint/css/sidebar.css',
			'/bower_components/font-awesome/css/font-awesome.min.css',
			//'//ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css',
			//'/blackprint/css/jquery/tipsy.css',
			
			'/bower_components/medium-editor-insert-plugin/dist/css/medium-editor-insert-plugin.min.css',

			'/bower_components/RRSSB/css/rrssb.css',
			//
			// 
			//'/blackprint/css/medium-editor-insert-plugin.css',
			'/blackprint/css/site',
			'/blackprint/css/content'
		), array('inline' => false));
	?>
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<link href="/bower_components/font-awesome/css/font-awesome-ie7.css" type="text/css">
	<![endif]-->
	<?php
		$this->blackprintAsset->script(array(
			//'//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js',
			'/bower_components/bootstrap/dist/js/bootstrap.min.js',
			'/blackprint/js/sidebar.js',
		//	'/blackprint/js/blackprintTracking.js',
			//'/bower_components/holderjs/holder.js',
			'/bower_components/RRSSB/js/rrssb.min.js'
			//'/blackprint/js/jquery/jquery.tipsy.js'
		), array('inline' => false));
		// NOTE: jQuery must be loaded first.
	?>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script>!window.jQuery && document.write('<script src="/bower_components/jquery/dist/jquery.min.js"><\/script>')</script>
	<?php
		echo $this->blackprintAsset->scripts(array('optimize' => false));
		echo $this->blackprintAsset->styles(array('optimize' => false));
	?>
	<link href='http://fonts.googleapis.com/css?family=Libre+Baskerville:400,700,400italic' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Raleway:300,400,700,100' rel='stylesheet' type='text/css'>
	<?php echo $this->html->link('Icon', '/img/blackprint-favicon.png', array('type' => 'icon')); ?>
</head>
<body id="home">
	<?=$this->_render('element', 'navbar', array('user' => $this->request()->user, 'document' => isset($document) ? $document:null), array('library' => 'blackprint')); ?>
	<div class="container page-container">
		<?php echo $this->content(); ?>
	
		<script type="text/javascript">
			$(function() {
				
				// Tooltips
				// $('.tip').tooltip({html: true});
				// $('.tip-small').tooltip({html: true});
				// Datepicker
				//$('.datepicker').datepicker();
			});
		</script>
		<?php
		/**
		 * Handle some social media JS SDKs, such as Facebook's, if the application has configured them.
		 */
		if(isset($this->request()->blackprintConfig['socialApps'])) {
		?>
			<?php if(isset($this->request()->blackprintConfig['socialApps']['facebook']) && isset($this->request()->blackprintConfig['socialApps']['facebook']['appId'])) { ?>
				<?php
				// Set some options so that they don't render as "1" ... ensure they are true or false, no quotes. TODO: Find better way to do this...Or just look to see if Facebook will accept "1" as true and "0" as false.
				$fbStatus = isset($this->request()->blackprintConfig['socialApps']['facebook']['status']) && !empty($this->request()->blackprintConfig['socialApps']['facebook']['status']) ? true:false;
				unset($this->request()->blackprintConfig['socialApps']['facebook']['status']);
				$fbCookie = isset($this->request()->blackprintConfig['socialApps']['facebook']['cookie']) && !empty($this->request()->blackprintConfig['socialApps']['facebook']['cookie']) ? true:false;
				unset($this->request()->blackprintConfig['socialApps']['facebook']['cookie']);
				$fbXfbml = isset($this->request()->blackprintConfig['socialApps']['facebook']['xfbml']) && !empty($this->request()->blackprintConfig['socialApps']['facebook']['xfbml']) ? true:false;
				unset($this->request()->blackprintConfig['socialApps']['facebook']['xfbml']);
				// Get all the options and set some defaults.
				$fbOptions = $this->request()->blackprintConfig['socialApps']['facebook'] += array(
					//'channelUrl' => false,
					'status' => $fbStatus, // check login status
					'cookie' => $fbCookie, // enable cookies to allow the server to access the session
					'xfbml' => $fbXfbml // parse XFBML
				);
				?>
				<div id="fb-root"></div>
				<script type="text/javascript">
					//$(funcion(){$('figure').baseline(22);});
					window.fbAsyncInit = function() {
						FB.init({<?php
							$i = 1;
							$totalFbOptions = count($fbOptions);
							foreach($fbOptions as $key => $value) {
								$lineEnd = ($i < $totalFbOptions) ? ', ':'';
								if(is_bool($value)) {
									$value = ($value === true) ? 'true':'false';
									echo $key . ': ' . $value . $lineEnd;
								} else {
									echo $key . ': "' . $value . '"' . $lineEnd;
								}
								$i++;
							}
							?>});
						if(typeof(fbReady) == 'function') { fbReady(); }
					};

					// Load the SDK Asynchronously
					(function(d){
						var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
						if (d.getElementById(id)) {return;}
						js = d.createElement('script'); js.id = id; js.async = true;
						js.src = "//connect.facebook.net/en_US/all.js";
						ref.parentNode.insertBefore(js, ref);
					}(document));
				</script>
			<?php } // end Facebook include ?>
		<?php } // end social SDKs ?>
	</div>
	<?php // $this->_render('element', 'footer', array(), array('library' => 'blackprint')); ?>	
	<?php
	/**
	 * Handle Google Analytics if configured.
	 */
	if(isset($this->request()->blackprintConfig['analytics']) && isset($this->request()->blackprintConfig['analytics']['googleAnalytics']) && isset($this->request()->blackprintConfig['analytics']['googleAnalytics']['code']) && isset($this->request()->blackprintConfig['analytics']['googleAnalytics']['domain']) && !empty($this->request()->blackprintConfig['analytics']['googleAnalytics']['code'])) {
	?>
	<script type="text/javascript">
		// GA
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		<?php
		// A string value of "none" for the domain in the configuration will allow local testing.
		$gaOpts = array(
			'cookieDomain' => $this->request()->blackprintConfig['analytics']['googleAnalytics']['domain']
		);
		// User tracking (if a user is signed in).
		if($user = $this->request()->user) {
			$gaOpts['userId'] = $user['_id'];
		}
		?>
		ga('create', '<?=$this->request()->blackprintConfig['analytics']['googleAnalytics']['code']; ?>', <?php echo json_encode($gaOpts); ?>);
		ga('send', 'pageview');
	</script>
	<?php } // end Google Analytics ?>
	<?=$this->blackprint->flash(); ?>
</body>
</html>