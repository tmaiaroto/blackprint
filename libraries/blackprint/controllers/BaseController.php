<?php
/**
 * Blackprint's base controller provides some methods which all controllers,
 * choosing to extend it, can use. This means less code repetition.
 * It also means that 3rd party add-ons can extend this controller to follow
 * conventions and use some of the same methods without needing to re-write them.
 *
*/
namespace blackprint\controllers;

use blackprint\models\Asset;
use blackprint\extensions\Util;
use lithium\util\Inflector;
use lithium\core\Libraries;
use lithium\net\http\Router;
use \MongoDate;
use \MongoId;

class BaseController extends \lithium\action\Controller {

	/**
	 * Generates a pretty URL for the content document.
	 *
	 * @return string
	 */
	public function _generateUrl($options=array()) {
		$defaults = array(
			'url' => false,
			'model' => 'blackprint\models\Content',
			'separator' => '-'
		);
		$options += $defaults;

		if(class_exists($options['model'])) {
			$urlField = $options['model']::urlField();
			$urlSeparator = $options['model']::urlSeparator();
			if($urlField != '_id' && !empty($urlField)) {
				if(is_array($urlField)) {
					foreach($urlField as $field) {
						if(isset($this->request->data[$field]) && $field != '_id') {
							$options['url'] .= $this->request->data[$field] . ' ';
						}
					}
					$options['url'] = Inflector::slug(trim($options['url']), $urlSeparator);
				} else {
					$options['url'] = Inflector::slug($this->request->data[$urlField], $urlSeparator);
				}
			}
		} else {
			// If not using a model, then just make up a slug...But note that we can't guarantee it won't be a duplicate.
			// If we continued along and used Util::uniqueUrl(), false would be returned because we don't have a model.
			// So just return here with a slug.
			return Inflector::slug(trim($options['url']), $options['separator']);
		}
		
		// Note: if an id was passed in the $options, the uniqueUrl() method will ensure a document can use
		// its own pretty URL on update instead of getting a new one.
		return Util::uniqueUrl($options);
	}

}
?>