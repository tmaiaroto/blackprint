<?php
namespace blackprint\models;

use lithium\core\Libraries;
use \MongoId;

class Post extends \blackprint\models\BaseModel {

	protected $_meta = array(
		'locked' => true,
		'source' => 'blackprint.posts'
		);

	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'title' => array('type' => 'string'),
		'subtitle' => array('type' => 'string'),
		'_authorId' => array('type' => 'string'),
		'body' => array('type' => 'string'),
		'url' => array('type' => 'string'),
		'draftHash' => array('type' => 'string'),
		'labels' => array('type' => 'array'),
    'meta' => array('type' => 'object'),
		'options' => array('type' => 'object'),
		'published' => array('type' => 'boolean'),
		'modified' => array('type' => 'date'),
		'created' => array('type' => 'date')
		);

	static $urlField = 'title';

	static $urlSeparator = '-';

	static $searchSchema = array(
		'title' => array(
			'weight' => 1
			),
		'body' => array(
			'weight' => 1
			)
		);

	public $validates = array(
		'title' => array(
			array('notEmpty', 'message' => 'Ttile cannot be empty.')
			),
		'body' => array(
			array('notEmpty', 'message' => 'Body cannot be empty.')
			)
		);

	public static $defaultOptions = array(
		'rainbowTheme' => 'blackboard',
		'codeLineNumberes' => true,
		'highlightTheme' => 'solarized_dark',
		'socialSharing' => array('facebook', 'twitter', 'googleplus', 'linkedin')
		);

  /**
   * Returns all installed themes for Rainbow syntax highlighter.
   * Users can put their own themes in the main app's
   * webroot/css/rainbow-themes directory.
   *
   * @return array The available themes for Rainbow
   */
  public static function getRainbowThemes() {
  	$themes = array();
  	$blackprintConfig = Libraries::get('blackprint');
  	$appConfig = Libraries::get(true);

  	foreach(scandir($blackprintConfig['path'] . '/webroot/css/rainbow-themes') as $theme) {
  		if(substr($theme, -3) == 'css') {
  			$themes['/blackprint/css/rainbow-themes/' . $theme] = substr($theme, 0, -4);
  		}
  	}

  	if(file_exists($appConfig['path'] . '/webroot/css/rainbow-themes')) {
  		foreach(scandir($appConfig['path'] . '/webroot/css/rainbow-themes') as $theme) {
  			if(substr($theme, -3) == 'css') {
  				$themes['/css/rainbow-themes/' . $theme] = substr($theme, 0, -4);
  			}
  		}
  	}

  	return $themes;
  }

  /**
   * Returns all installed themes for Highlight.js syntax highlighter.
   * Users can put their own themes in the main app's
   * webroot/css/highlight-themes directory.
   *
   * @return array The available themes for Highlight.js
   */
  public static function getHighlightThemes() {
  	$themes = array();
  	$blackprintConfig = Libraries::get('blackprint');
  	$appConfig = Libraries::get(true);

  	foreach(scandir($blackprintConfig['path'] . '/webroot/css/highlight-themes') as $theme) {
  		if(substr($theme, -3) == 'css') {
  			$themes['/blackprint/css/highlight-themes/' . $theme] = substr($theme, 0, -4);
  		}
  	}

  	if(file_exists($appConfig['path'] . '/webroot/css/highlight-themes')) {
  		foreach(scandir($appConfig['path'] . '/webroot/css/highlight-themes') as $theme) {
  			if(substr($theme, -3) == 'css') {
  				$themes['/css/highlight-themes/' . $theme] = substr($theme, 0, -4);
  			}
  		}
  	}

  	return $themes;
  }

  public static function searchSchema() {
    return Post::$searchSchema;
  }

  /**
   * Returns a list of popular labels across all blog posts.
   *
   * @param  int $limit The limit for number of labels to return. By default, the 10 most popular.
   * @return array
   */
  public static function popularLabels($limit=10) {
	// This looks like a job for the MongoDB Aggregation Framework.
  	$conditions = array('published' => true);
  	$connection = Post::connection();
  	$meta = Post::meta();
  	$db = $connection->connection;
  	$labels = $db->command(array(
  		'aggregate' => $meta['source'],
  		'pipeline' => array(
  			array(
  				'$match' => $conditions
  				),
  			array(
  				'$unwind' => '$labels'
  				),
  			array(
  				'$group' => array(
  					'_id' => '$labels',
  					'count' => array('$sum' => 1)
  					)
  				),
  			array(
  				'$sort' => array('count' => -1),
  				),
  			array(
  				'$limit' => $limit
  				)
  			)
  		));

  	if(isset($labels['result'])) {
  		$labels = $labels['result'];
  	} else {
  		return array();
  	}

	// See all this? Since there's no JOIN in MongoDB...
  	$labelIds = array();
  	foreach($labels as $label) {
  		$labelIds[] = new MongoId($label['_id']);
  	}
  	if(!empty($labelIds)) {
  		$labelDocs = Label::find('all', array('conditions' => array('_id' => array('$in' => $labelIds))));
  	}

  	foreach($labels as $k => $v) {
  		foreach($labelDocs as $doc) {
  			if((string)$doc->_id == $v['_id']) {
  				$labels[$k]['name'] = $doc->name;
  				$labels[$k]['color'] = $doc->color;
  				$labels[$k]['bgColor'] = $doc->bgColor;
  			}
  		}
  	}

	// Sort again now that the ordering got messed up.
  	usort($labels, (function($a, $b) {
  		if($a['count'] == $b['count']) {
  			return 0;
  		}
  		return ($a['count'] < $b['count']) ? 1 : -1;
  	}));

  	return $labels;
  }

}

/**
 * FILTERS
 * One of which will associate labels to the blog post(s).
 */
Post::applyFilter('find', function($self, $params, $chain) {
	if(isset($params['options']['skip_filter']) && $params['options']['skip_filter'] === true) {
		return $chain->next($self, $params, $chain);
	}

	$result = $chain->next($self, $params, $chain);

  // for single results
	if($result instanceOf \lithium\data\entity\Document) {
		if($result->labels) {
			$result->_labels = Label::find('all', array('conditions' => array('_id' => $result->labels->data())));
		}
		if($result->_authorId) {
			$result->_author = User::find('first', array('conditions' => array('_id' => $result->_authorId)));
		}
	}

  // for multiple results
	if($result instanceOf \lithium\data\collection\DocumentSet) {
		foreach($result as $k => $v) {
			if($result->offsetGet($k)->labels) {
				$result->offsetGet($k)->_labels = Label::find('all', array('conditions' => array('_id' => $result->offsetGet($k)->labels)));
			}
			if($result->offsetGet($k)->_authorId) {
				$result->offsetGet($k)->_author = User::find('first', array('conditions' => array('_id' => $result->offsetGet($k)->_authorId)));
			}
		}

	}

	return $result;
});
?>