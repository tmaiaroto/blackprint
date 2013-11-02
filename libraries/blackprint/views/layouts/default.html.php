<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://ogp.me/ns/fb#">
<head>
	<?php echo $this->html->charset();?>
	<?php $title = $this->title() ? $this->title():''; ?>
	<title><?=$title ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Blackprint">
	<meta name="author" content="Shift8Creative">
	<?php
		$this->blackprintAsset->style(array(
			'/bower_components/bootstrap/dist/css/bootstrap.min.css',
			'/bower_components/font-awesome/css/font-awesome.min.css',
			'//ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css',
			'/blackprint/css/jquery/tipsy.css',
			'/blackprint/css/site'
		), array('inline' => false));
	?>
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<link href="/bower_components/font-awesome/css/font-awesome-ie7.css" type="text/css">
	<![endif]-->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script>!window.jQuery && document.write('<script src="/js/jquery-1.10.2.min.js"><\/script>')</script>
	<?php
		$this->blackprintAsset->script(array(
			'//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js',
			'/bower_components/bootstrap/dist/js/bootstrap.min.js',
			'/bower_components/holderjs/holder.js',
			'/blackprint/js/jquery/jquery.tipsy.js'
		), array('inline' => false));
	?>
	<?php
		echo $this->blackprintAsset->scripts(array('optimize' => true));
		echo $this->blackprintAsset->styles(array('optimize' => true));
	?>
	<link href='http://fonts.googleapis.com/css?family=Libre+Baskerville:400,700,400italic' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Raleway:400,700,100' rel='stylesheet' type='text/css'>
	<?php echo $this->html->link('Icon', '/img/blackprint-favicon.png', array('type' => 'icon')); ?>
</head>
<body>
	<?=$this->_render('element', 'navbar', array('user' => $this->request()->user), array('library' => 'blackprint')); ?>
	<div class="container content-container">
		<?php echo $this->content(); ?>
	</div><!--/.container-->
	<?=$this->_render('element', 'footer', array(), array('library' => 'blackprint')); ?>
	<script type="text/javascript">
		$(function() {
			// Tooltips
			$('.tip').tooltip({html: true});
			$('.tip-small').tooltip({html: true});
		});
	</script>
	<?php
	/**
	 * Handle some social media JS SDKs, such as Facebook's, if the application has configured them.
	 */
	if(isset($this->request()->social)) {
	?>
		<?php if(isset($this->request()->social['facebook']) && isset($this->request()->social['facebook']['appId'])) { ?>
			<?php
			// Get all the options and set some defaults.
			$fbOptions = $this->request()->social['facebook'] += array(
				//'channelUrl' => false,
				'status' => true, // check login status
				'cookie' => true, // enable cookies to allow the server to access the session
				'xfbml' => true // parse XFBML
			);
			?>
			<div id="fb-root"></div>
			<script type="text/javascript">
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
			
	<?php
	/**
	 * Handle Google Analytics if configured.
	 */
	if(isset($this->request()->googleAnalytics) && isset($this->request()->googleAnalytics['code']) && isset($this->request()->googleAnalytics['domain'])) {
	?>
	<script type="text/javascript">
		// GA
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', '<?=$this->request()->googleAnalytics['code']; ?>', '<?=$this->request()->googleAnalytics['domain']; ?>');
		ga('send', 'pageview');
	</script>
	<?php } // end Google Analytics ?>
	<?=$this->blackprint->flash(); ?>
</body>
</html>