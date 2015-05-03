/**
 * Various helper methods for tracking. This requires Google Universal Analytics.
 * 
*/

// In miliseconds, when this script has loaded. Not quite the start of the page, but close enough. Useful in mitigating false engagements, etc.
var blackprintTrackLoadTime = new Date().getTime();

/**
 * Tracks events in Google Analytics
 * 
 * @type {Object}
*/
var blackprintTrack = {
	/**
	 * Returns how long it's been since this script was loaded.
	 * 
	 * @return {number} Time elapsed in miliseconds
	*/
	timeSinceLoad: function() {
		var end = new Date().getTime();
		return (end - blackprintTrackLoadTime);
	},
	/**
	 * Just a wrapper around Google Analytics ga() with some defaults and a check 
	 * to ensure a label has been set (which is typically optional, but in our case required).
	 * This shortens the amount of code written.
	 * 
	 * So for example, to track a click on some welcome video on a page:
	 * blackprintTrack.event({"label": "welcome video"})
	 *
	 * Or if that's to be tracked as a "watch" then:
	 * blackprintTrack.event({"label": "welcome video", "action": "watch"})
	 *
	 * The default category here is "object" which tells the reporter that we're talking about 
	 * some object on the page; an image, video, button, etc.
	 * However, even the category can be changed.
	 * 
	 * @param  {Object} opts
	*/
	event: function(opts) {
		var options = {
			"category": "object",
			"action": "click",
			"label": ""
		}
		if(typeof(opts) === 'object') {
			$.extend(options, opts);
		}
		if(options.label != "") {
			return ga('send', 'event', options.category, options.action, options.label);
		}
		return false;
	},
	/**
	 * Tracks a "read" event when users have spent enough time on a page and scrolled far enough.
	 * 
	 * @param  {Object} opts
	 *         minTime:  The amount of time, in seconds, that must pass before the event is considered valid (estimated time to read the content?).
	 *         selector: The element selector (class, id, etc.) that is measured for scrolling.
	 *         category: The Google Analytics Event category.
	 *         action:   The Google Analytics Event action (likely no reason to change this given what the function is for).
	 *         label:    The Google Analytics Event label (useful for categorizing events).
	 *         debug: 	 Logs information to the console
	*/
	read: function(opts) {
		var options = {
			"minTime": 10,
			"selector": "body",
			"category": "page",
			"action": "read",
			"label": "engagements",
			"debug": false
		}
		if(typeof(opts) === 'object') {
			$.extend(options, opts);
		}
		var start = new Date().getTime();
		var enoughTimeHasPassed = false;
		var sentEvent = false;
		var hasScrolledFarEnough = false;

		// Every 2 seconds, check the conditions and send the event if satisfied.
		setInterval(function(){
			var end = new Date().getTime();
			if((end - start) > (options.minTime*1000)) {
				if(options.debug && !enoughTimeHasPassed) {
					console.log("blackprintTrack.read() " + options.minTime + " seconds have passed");
				}
				enoughTimeHasPassed = true;
			}

			if(hasScrolledFarEnough === true && enoughTimeHasPassed === true) {
				// Send an event to Google Analytics.
				if(sentEvent === false) {
					if(options.debug) {
						console.log("blackprintTrack.read() Logging page read event");
						console.dir(options);
					}
					ga('send', 'event', options.category, options.action, options.label);
				}
				sentEvent = true;
			}
		},(2*1000));

	    $(window).bind('scroll', function() {
	    	if($(window).scrollTop() >= $(options.selector).offset().top + $(options.selector).outerHeight() - window.innerHeight) {
	    		$(window).unbind('scroll');
	    		if(options.debug) {
					console.log("blackprintTrack.read() The user has scrolled far enough down the page");
				}
				hasScrolledFarEnough = true;
	        }
		});
	},
	/**
	 * Tracks a "share" event when users share the page on social media, via e-mail, etc.
	 * 
	 * @param  {Object} opts
	 *         type: 	 How the content was shared, via e-mail, which social network, etc.
	 *         category: The Google Analytics Event category
	 *         action: 	 The Google Analytics Event action
	 *         label: 	 The Google Analytics Event label (optional, this will be the "type" by default)
	 *         debug: 	 Logs information to the console
	*/
	share: function(opts) {
		var options = {
			"type": "",
			"category": "social",
			"action": "share",
			"label": "",
			"debug": false
		}
		if(typeof(opts) === 'object') {
			$.extend(options, opts);
		}

		if(options.type != "") {
			if(options.label == "") {
				options.label = options.type;
			}
			if(options.debug) {
				console.log("blackprintTrack.share() The user has shared content via " + type + " labeled as: " + options.label);
			}			
			ga('send', 'event', options.category, options.action, options.label);
		}
	},
	/**
	 * Tracks a click on a link/button that takes a user away from the page.
	 * This ensures the hit is recorded before directing the user onward.
	 * NOTE: Web crawlers will still rely on the "href" attribute being an actual URL.
	 * If this function is used without that attribute, then web crawlers may not properly index 
	 * those linked pages (which may not matter if they are not on the same domain anyway).
	 * So use the "onClick" attribute to call this function instead or register an event listener.
	 * 
	 * @param  {Object} opts
	 *         url: 			The URL to redirect to once done tracking
	 *         returnUrl: 		Just return the URL and don't actually redirect
	 *         trackDomainOnly: Just send the domain name to Google Analytics as the label instead of the full URL
	 *         category: 		The Google Analytics Event category
	 *         action: 			The Google Analytics Event action
	 *         label: 			The Google Analytics Event label (optional, this will be the URL by default)
	 *         debug: 			Logs information to the console
	 * @return redirect
	 */
	linkOut: function(opts) {
		var options = {
			"url": "",
			"returnUrl": false,
			"onComplete": false,
			"trackDomainOnly": false,
			"category": "outbound",
			"action": "navigate",
			"label": "",
			"debug": false
		}
		if(typeof(opts) === 'object') {
			$.extend(options, opts);
		}

		if(options.url === "") {
			return false;
		}

		// By default the label is going to be the link out.
		var label = options.url;
		if(options.trackDomainOnly === true) {
			var tmp = document.createElement ('a');
			tmp.href = options.url;
			label = tmp.hostname;
		}
		// But that can be overridden by the call by passing a label value.
		if(options.label != "") {
			label = options.label;
		}

		ga('send', {
			'hitType': 'event',
			'eventCategory': options.category,
			'eventAction': options.action,
			'eventLabel': label,
			'hitCallback':function() {
				if(options.debug) {
					console.log("blackprintTrack.linkOut() Recording outbound navigate for " + label);
					// First, use the onComplete callback if defined (which always returns the URL).
					if(typeof(options.onComplete) == 'function') {
						return options.onComplete(options.url);
					}

					// Then either return the URL or redirect based on the options passed.
					if(options.returnUrl === false) {
						console.log("blackprintTrack.linkOut() The user will now be redirected to " + options.url);
						return setTimeout(function(){
							return window.location.href = options.url;
						}, 5000);
					}
					return options.url;
				} else {
					// First, use the onComplete callback if defined (which always returns the URL).
					if(typeof(options.onComplete) == 'function') {
						return options.onComplete(options.url);
					}

					// Then either return the URL or redirect based on the options passed.
					if(options.returnUrl === false) {
						return window.location.href = options.url;
					}
					return options.url;
				}
			}
		});

		// Just in case
		if(options.returnUrl === true) {
			return options.url;
		}
	}
};