<?php
namespace blackprint\extensions\helper;

use blackprint\models\Config;
use \lithium\core\Libraries;
use \lithium\net\http\Media;

class BlackprintAsset extends \lithium\template\helper\Html {

	/**
	 * A drop in replacement for $this->html->sript()
	 * This method will also take into consideration versioned scripts
	 * that may exist in some `dist` directory from a package pulled
	 * in from Bower (which is fairly common).
	 * 
	 * Otherwise, there would be a huge maintenance nightmare
	 * (without using something like Require.js) because the layout
	 * templates would specify a certain file name that might not exist.
	 * Why developers decide to put version numbers in their file names
	 * in the `dist` directory I'll never know...It should just be
	 * a versionless file name (the current version) in there.
	 * Or even additional files with version numbers in the file name
	 * if need be for people who wanted to use a specific version...
	 * But by constantly changing the version number, these libraries
	 * introduce a maintenance problem.
	 *
	 * This method will resolve that problem.
	 * What it will do is allow wildcard names to be passed.
	 * So, for example:
	 * 
	 * $this->blackprintAsset->script(array(
	 * 		'/bower_components/bootstrap-wysihtml5/dist/bootstrap-wysihtml5-*.min.js'
	 * ));
	 *
	 * That would load the latest version (even if there were multiple versions)
	 * of the JavaScript in that directory, matching that base file name.
	 * Currently, at the time of this comment, the version is 0.0.2 but that
	 * of course will change in the future.
	 *
	 * PHP's glob() function is used for the matching. It loops the matching results,
	 * and since that should be alphabetically, it should use the latest version.
	 *
	 * Naturally, to do this, the extra code makes things a little slower.
	 * However, when used in combination with the `scripts()` method here,
	 * the overall load time will be faster due to consolidation and minification.
	*/
	public function script($path, array $options = array()) {
		$defaults = array('inline' => true);
		list($scope, $options) = $this->_options($defaults, $options);
		$webroot = Media::webroot(true);

		if (is_array($path)) {
			foreach ($path as $i => $item) {
				if(strstr($item, '*')) {
					foreach (glob($webroot . $item) as $filename) {
						$item = substr($filename, (strlen($webroot)));
					}
				}
				$path[$i] = $this->script($item, $scope);
			}
			return ($scope['inline']) ? join("\n\t", $path) . "\n" : null;
		} else {
			if(strstr($path, '*')) {
				foreach (glob($webroot . $path) as $filename) {
					$path = substr($filename, (strlen($webroot)));
				}
			}
		}
		$m = __METHOD__;
		$params = compact('path', 'options');

		$script = $this->_filter(__METHOD__, $params, function($self, $params, $chain) use ($m) {
			return $self->invokeMethod('_render', array($m, 'script', $params));
		});
		if ($scope['inline']) {
			return $script;
		}
		if ($this->_context) {
			$this->_context->scripts($script);
		}
	}

	/**
	 * A drop in replacement for $this->html->scripts()
	 * This method will output the scripts on the page in a combined and minified version.
	 *
	*/
	public function scripts($config=array()) {
		$config += array(
			'optimize' => true,
			'compression' => 'jsmin',
			'outputDirectory' => 'optimized',
			'packerEncoding' => 'Normal',
			'packerFastDecode' => true,
			'packerSpecialChars' => false
		);
		
		// Allow this method to work exactly how Lithium does by default and offer no consolidation or compression for JavaScript assets.
		if(!$config['optimize']) {
			return $this->_context->scripts();
		}

		// Ensure output directory is formatted properly, first remove any beginning slashes
		if($config['outputDirectory'][0] == DIRECTORY_SEPARATOR) {
			$config['outputDirectory'] = substr($config['outputDirectory'], 1);
		}
		// ...then any trailing slashes
		if(substr($config['outputDirectory'], -1, 1) == DIRECTORY_SEPARATOR) {
			$config['outputDirectory'] = substr($config['outputDirectory'], 0, -1);
		}
		
		// Set the output path
		$webroot = Media::webroot(true);
		$outputHash = md5(serialize($this->_context->scripts));
		$outputFile = Media::asset($config['outputDirectory'] . DIRECTORY_SEPARATOR . $outputHash, 'js');
		$outputFolder = $webroot . strstr($outputFile, $outputHash, true);
		
		// If the output directory doesn't exist, return the scripts like normal... TODO: also ensure permissions to write here?
		if(!file_exists($outputFolder)) {
			// If it doesn't exist, try to create it
			if (!mkdir($outputFolder, 0777, true)) {
				die('Failed to create folders...');
			}
			// If it still doesn't exist, return the scripts
			if(!file_exists($outputFolder)) {
				return $this->_context->scripts();
			}
		}

		if(!empty($config['compression'])) {
			if(!file_exists($webroot . $outputFile)) {
				$js = '';
				// JSMin
				if(($config['compression'] === true) || ($config['compression'] == 'jsmin')) {
					foreach($this->_context->scripts as $file) {
						if(preg_match('/src=\"(.*)\"/i', $file, $matches)) {
							$script = (isset($matches[1])) ? $webroot . $matches[1]:false;
							//$script = $webroot . Media::asset($matches[1], 'js');
							// It is possible that a reference to a file that does not exist was passed
							if(file_exists($script)) {
								$js .= \blackprint\vendor\jsminphp\JSMin::minify(file_get_contents($script));
							}
						}
					}
				// Dean Edwards Packer
				} elseif($config['compression'] == 'packer') {
					foreach($this->_context->scripts as $file) {
						if(preg_match('/src=\"(.*)\"/', $file, $matches)) {
							$script = (isset($matches[1])) ? $webroot . $matches[1]:false;
							// $script = $webroot . Media::asset($matches[1], 'js');
							// It is possible that a reference to a file that does not exist was passed                            
							if(file_exists($script)) {
								$scriptContents = file_get_contents($script);
								$packer = new \blackprint\vendor\packer\JavaScriptPacker($scriptContents, $config['packerEncoding'], $script, $config['packerFastDecode'], $script, $config['packerSpecialChars']);
								$js .= $packer->pack();
							}
						}
					}
				}
				file_put_contents($webroot . $outputFile, $js);
			}			

			// One last safety check to ensure the file is there (reasons why it may not be: primarily, write permissions)
			if(file_exists($webroot . $outputFile)) {
				return '<script type="text/javascript" src="' . Media::asset($outputFile, 'js') . '"></script>';
			} else {
				return $this->_context_scripts();
			}
		} else {
			return $this->_context->scripts();
		}
		
	}

	/**
	 * Drop in replacement for Html::style()
	 * This one also allows wildcard asset names.
	*/
	public function style($path, array $options = array()) {
		$defaults = array('type' => 'stylesheet', 'inline' => true);
		list($scope, $options) = $this->_options($defaults, $options);
		$webroot = Media::webroot(true);

		if (is_array($path)) {
			foreach ($path as $i => $item) {
				if(strstr($item, '*')) {
					foreach (glob($webroot . $item) as $filename) {
						$item = substr($filename, (strlen($webroot)));
					}
				}
				$path[$i] = $this->style($item, $scope);
			}
			return ($scope['inline']) ? join("\n\t", $path) . "\n" : null;
		} else {
			if(strstr($path, '*')) {
				foreach (glob($webroot . $path) as $filename) {
					$path = substr($filename, (strlen($webroot)));
				}
			}
		}
		$method = __METHOD__;
		$type = $scope['type'];
		$params = compact('type', 'path', 'options');
		$filter = function($self, $params, $chain) use ($defaults, $method) {
			$template = ($params['type'] === 'import') ? 'style-import' : 'style-link';
			return $self->invokeMethod('_render', array($method, $template, $params));
		};
		$style = $this->_filter($method, $params, $filter);

		if ($scope['inline']) {
			return $style;
		}
		if ($this->_context) {
			$this->_context->styles($style);
		}
	}
	
	public function styles($config=array()) {
		$config += array(
			'optimize' => false,
			'compression' => true, // possible values: "tidy", true, false
			'tidyTemplate' => 'highest_compression', // possible values: "high_compression", "highest_compression", "low_compression", or "default"
			'lessDebug' => false, // sends lessphp error message to a log file, possible values: true, false
			'outputDirectory' => 'optimized' // directory is from webroot/css if full path is not defined
		);

		// Allow this method to work exactly how Lithium does by default and offer no consolidation or compression for CSS assets.
		if(!$config['optimize']) {
			return $this->_context->styles();
		}

		// Ensure output directory is formatted properly, first remove any beginning slashes
		if($config['outputDirectory'][0] == DIRECTORY_SEPARATOR) {
			$config['outputDirectory'] = substr($config['outputDirectory'], 1);
		}
		// ...then any trailing slashes
		if(substr($config['outputDirectory'], -1, 1) == DIRECTORY_SEPARATOR) {
			$config['outputDirectory'] = substr($config['outputDirectory'], 0, -1);
		}
		
		// Set the output path
		$webroot = Media::webroot(true);
		$outputHash = md5(serialize($this->_context->styles));
		$outputFile = Media::asset($config['outputDirectory'] . DIRECTORY_SEPARATOR . $outputHash, 'css');
		$outputFolder = $webroot . strstr($outputFile, $outputHash, true);
			
		// If the output directory doesn't exist, return the scripts like normal...
		if(!file_exists($outputFolder)) {
			// If it doesn't exist, try to create it
			if (!mkdir($outputFolder, 0777, true)) {
				die('Failed to create folders...');
			}
			// If it still doesn't exist, return the scripts
			if(!file_exists($outputFolder)) {
				return $this->_context->styles();
			}
		}
		
		// Run any referenced .less files through lessphp first
		foreach($this->_context->styles as $file) {
			// preg_match('/\/css\/(.*.less).css"/', $file, $matches)
			if(preg_match('/href=\"(.*\.less\.css)\"/', $file, $matches)) {
				//$sheet = $webroot . substr(Media::asset($matches[1], 'css'), 0, -4);
				$sheet = (isset($matches[1])) ? $webroot . $matches[1]:false;
				try {
					$less = new \blackprint\vendor\lessphp\lessc();
					// fortunately, the Html::script() helper will automatically append .css, so the output file can just have .css appended too and match.                    
					$less::ccompile($sheet, $sheet . '.css');
				} catch (\exception $ex) {
					if($config['lessDebug'] === true) {
						$fp = fopen($outputFolder . DIRECTORY_SEPARATOR .'less_errors', 'a');
						fwrite($fp, '[' . date("D M j G:i:s Y") . '] [file ' . $file . '] ' . $ex->getMessage() . "\n");
						fclose($fp);
					}
				}
			}
		}
		
		// Check compression type and compress/combine
		if(!empty($config['compression'])) {
			$css = '';
			if(!file_exists($webroot . $outputFile)) {
				// true is just basic compression and combination. Basically remove white spaces and line breaks where possible.
				if($config['compression'] === true) {
					foreach($this->_context->styles as $file) {
						if(preg_match('/href=\"(.*)"/', $file, $matches)) {
							// $sheet = $webroot . Media::asset($matches[1], 'css');
							$sheet = (isset($matches[1])) ? $webroot . $matches[1]:false;
							// It is possible that a reference to a file that does not exist was passed
							if(file_exists($sheet)) {
								$contents = file_get_contents($sheet);
							} else {
								$contents = '';
							}
							// remove comments
							$contents = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $contents);
							// remove tabs, spaces, newlines, etc.
							$contents = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $contents);
							// remove single spaces next to braces (can't remove single spaces everywhere, but we can in a few places)
							$contents = str_replace(array('{ ', ' {', '; }'), array('{', '{', ';}'), $contents);
							$css .= $contents;
						}
					}
				// 'tidy' setting will run the css files through csstidy which not only removes white spaces and line breaks, but also shortens things like #000000 to #000, etc. where possible.
				} elseif($config['compression'] == 'tidy') {
					$tidy = new \blackprint\vendor\csstidy\CssTidy();
					$tidy->set_cfg('remove_last_;',TRUE);
					$tidy->load_template($config['tidyTemplate']);
					// Loop through all the css files, run them through tidy, and combine into one css file
					foreach($this->_context->styles as $file) {
						if(preg_match('/\/css\/(.*)"/', $file, $matches)) {
						   $sheet = $webroot . Media::asset($matches[1], 'css');
							// It is possible that a reference to a file that does not exist was passed
							if(file_exists($sheet)) {
								$tidy->parse(file_get_contents($sheet));
								$css .= $tidy->print->plain();
							}
						}
					}
					
				}
				file_put_contents($webroot . $outputFile, $css);
			}
			// One last safety check to ensure the file is there (reasons why it may not be: primarily, write permissions)
			if(file_exists($webroot . $outputFile)) {
				return '<link rel="stylesheet" type="text/css" href="' . Media::asset($outputFile, 'css') . '" />';
			} else {
				return $this->_context->styles();
			}
		} else {
			// If compression wasn't set, just return the style sheets like normal
			return $this->_context->styles();
		}
		
	}
	
	/*
	 * Call this method at the top of a view template to apply a filter to return all images called with the Html::image() helper
	 * in that template as base64 data URIs. Note: IE6 & 7 do not support data URIs. 
	 *
	*/ 
	public function images($config=array()) {
		$this->_context->Html->applyFilter('image', function($self, $params, $chain) {
			$config += array(
				'compression' => true,
				'allowedFormats' => array('jpeg', 'jpg', 'jpe', 'png', 'gif')
			);
			
			// If the image is not in the list of allowed formats or compression is false, don't encode it, just display it as normal
			$format = substr($params['path'], strrpos($params['path'], '.') + 1);
			if ((!in_array($format, $config['allowedFormats'])) || ($config['compression'] !== true)) {
				return $chain->next($self, $params, $chain);
			}
			
			// Encode the image data
			if(substr($params['path'], 0, 4) == 'http') {
				$file = $params['path'];
			} else {
				$file = Media::webroot(true) . Media::asset($params['path'], 'image');
			}
			$data = base64_encode(file_get_contents($file));
			
			// Set the html options that go within the img tag
			$htmlOptions = '';
			foreach($params['options'] as $k => $v) {
				$htmlOptions .= $k . '="' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '" ';
			}
			
			// Return the image URI
			return '<img src="data:image/'.$format.';base64,'.$data.'" ' . $htmlOptions . '/>';            
		});
	}
	
	// TODO: make $this->optimize->script(...) so that scripts can be called inline and minified...
	
}
?>