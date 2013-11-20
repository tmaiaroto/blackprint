<?php
namespace blackprint\controllers;

use blackprint\models\Post;
use blackprint\models\Label;
use blackprint\extensions\Util;
use blackprint\extensions\storage\FlashMessage;
use lithium\security\validation\RequestToken;
use lithium\util\Inflector;
use \MongoDate;
use \MongoId;

class PostsController extends \lithium\action\Controller {

  public function admin_index() {
	$this->_render['layout'] = 'admin';

	$conditions = array();
	$q = ((isset($this->request->query['q'])) && (!empty($this->request->query['q']))) ? $this->request->query['q']:'';
	if(!empty($q)) {
	  $search_schema = Post::$searchSchema;
	  $search_conditions = array();
	  // For each searchable field, adjust the conditions to include a regex
	  foreach($search_schema as $k => $v) {
		$field = (is_string($k)) ? $k:$v;
		$search_regex = new \MongoRegex('/' . $q . '/i');
		$conditions['$or'][] = array($field => $search_regex);
	  }
	}

	$limit = isset($this->request->query['limit']) ? (int)$this->request->query['limit']:25;
	$page = isset($this->request->query['page']) ? (int)$this->request->query['page']:1;
	// For example: ?order=created,desc
	$order = array('created' => 'desc');
	if(isset($this->request->query['order'])) {
	  $orderArray = explode(',', $this->request->query['order']);
	  if(isset($orderArray[0]) && isset($orderArray[1])) {
		$order = array($orderArray[0] => $orderArray[1]);
	  }
	}

	// Set from router.
	$limit = $this->request->limit ?: $limit;
	$page = $this->request->page ?: $page;

	$total = Post::count(compact('conditions'));
	$documents = Post::all(compact('conditions','order','limit','page'));
	$success = true;
	$totalPages = ((int)$limit > 0) ? ceil($total / $limit):0;

	// Get the 10 most popular labels. For more see popular_labels() method below.
	$popularLabels = Post::popularLabels(10);

	// Set data for the view template
	return compact('documents', 'total', 'page', 'limit', 'totalPages', 'order', 'q', 'success', 'popularLabels');
  }

  public function admin_create() {
	$this->_render['layout'] = 'admin';

	$document = Post::create();
	$rainbowThemes = Post::getRainbowThemes();
	$highlightThemes = Post::getHighlightThemes();

	// If data was passed, set some more data and save
	if ($this->request->data) {
	  // CSRF
	  if(!RequestToken::check($this->request)) {
		RequestToken::get(array('regenerate' => true));
	  } else {
		$now = new MongoDate();
		$this->request->data['created'] = $now;
		$this->request->data['modified'] = $now;

		// If using the li3b_users plugin (or if $this->request->user is set by any user plugin), use that for the author id
		$this->request->data['_authorId'] = (isset($this->request->user['_id'])) ? $this->request->user['_id']:null;
		$this->request->data['_authorId'] = (isset($this->request->user['id'])) ? $this->request->user['id']:$this->request->data['_authorId'];

		// Set the pretty URL that gets used by a lot of front-end actions.
		$this->request->data['url'] = $this->_generateUrl();

		// Save
		if($document->save($this->request->data)) {
		  FlashMessage::write('The post has been created successfully.', 'default');
		  $this->redirect(array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'index', 'admin' => true));
		} else {
		  FlashMessage::write('The post could not be created, please try again.', 'default');
		}
	  }
	}

	$this->set(compact('document', 'rainbowThemes', 'highlightThemes'));
  }

  /**
   * Allows admins to update blog posts.
   *
   * @param string $id The post id
   */
  public function admin_update($id=null) {
	if(empty($id)) {
	  FlashMessage::write('You must provide a blog post id to update.', 'default');
	  return $this->redirect(array('admin' => true, 'library' => 'blackprint', 'controller' => 'posts', 'action' => 'index'));
	}
	$this->_render['layout'] = 'admin';

	$rainbowThemes = Post::getRainbowThemes();
	$highlightThemes = Post::getHighlightThemes();

	$document = Post::find('first', array('conditions' => array('_id' => $id)));
	if(!empty($this->request->data)) {
	  // IMPORTANT: Use MongoDate() when inside an array/object because $_schema isn't deep
	  $now = new MongoDate();

	  $this->request->data['modified'] = $now;

	  // Set the pretty URL that gets used by a lot of front-end actions.
	  // Pass the document _id so that it doesn't change the pretty URL on an update.
	  $this->request->data['url'] = $this->_generateUrl($document->_id);

	  if($document->save($this->request->data)) {
		FlashMessage::write('The blog post has been successfully updated.', 'default');
		return $this->redirect(array('admin' => true, 'library' => 'blackprint', 'controller' => 'posts', 'action' => 'index'));
	  } else {
		FlashMessage::write('There was a problem updating the blog post, please try again.', 'default');
	  }

	}

	$this->set(compact('document', 'rainbowThemes', 'highlightThemes'));
  }

  /**
   * Allows admins to delete blog posts.
   *
   * @param string $id The post id
  */
  public function admin_delete($id=null) {
	$this->_render['layout'] = 'admin';

	// Get the document from the db to edit
	$conditions = array('_id' => $id);
	$document = Post::find('first', array('conditions' => $conditions));

	// Redirect if invalid post
	if(empty($document)) {
	  FlashMessage::write('That blog post was not found.', 'default');
	  return $this->redirect(array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'index', 'admin' => true));
	}

	if($document->delete()) {
	  FlashMessage::write('The post has been deleted.', 'default');
	}

	return $this->redirect(array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'index', 'admin' => true));
  }

  /**
   * Public index listing method.
   *
   * @param string $labels An optional comma separated list of labels to filter by
   */
  public function index($labels=null) {

	$conditions = array();
	if((isset($this->request->query['q'])) && (!empty($this->request->query['q']))) {
	  $search_schema = Post::searchSchema();
	  $search_conditions = array();
	  // For each searchable field, adjust the conditions to include a regex
	  foreach($search_schema as $k => $v) {
		$field = (is_string($k)) ? $k:$v;
		$search_regex = new \MongoRegex('/' . $this->request->query['q'] . '/i');
		$conditions['$or'][] = array($field => $search_regex);
	  }
	}

	$labels = isset($this->request->query['labels']) ? $this->request->query['labels']:$labels;
	$labelIds = false;
	if($labels) {
	  $labelsArray = explode(',', $labels);
	  $labels = array();
	  foreach($labelsArray as $label) {
		$labels[] = urldecode(trim(strtolower($label)));
	  }
	  $labels = array_filter($labels);
	  $labelDocs = Label::find('all', array('conditions' => array('name' => $labels)));
	  if($labelDocs) {
		$labelIds = array();
		foreach ($labelDocs as $doc) {
		  $labelIds[] = (string)$doc->_id;
		}
	  }
	}

	$conditions += !empty($labels) ? array('published' => true, 'labels' => array('$in' => $labelIds)):array('published' => true);

	$limit = $this->request->limit ?: 5;
	$page = $this->request->page ?: 1;
	// Allow querystring params for these as well.
	$page = (isset($this->request->query['page'])) ? $this->request->query['page']:$page;
	$limit = (isset($this->request->query['limit'])) ? $this->request->query['limit']:$limit;
	$order = array('created' => 'desc');
	$total = Post::count(compact('conditions'));
	$documents = Post::all(compact('conditions','order','limit','page'));

	$page_number = (int)$page;
	$totalPages = ((int)$limit > 0) ? ceil($total / $limit):0;

	// Get the 10 most popular labels. For more see popular_labels() method below.
	$popularLabels = Post::popularLabels(10);

	// Set data for the view template
	return compact('documents', 'total', 'page', 'limit', 'totalPages', 'popularLabels');
  }

  /**
   * Public read method.
   *
   * The id can be either a pretty URL or a MongoId.
   *
   * @param string $id
   */
  public function read($id=null) {
	if(empty($id)) {
	  return $this->redirect('/');
	}

	if(preg_match('/[0-9a-f]{24}/', $id)) {
	  $field = '_id';
	} else {
	  $field = 'url';
	}

	$isAdmin = (isset($this->request->user['role']) && $this->request->user['role'] == 'administrator') ? true:false;
	$conditions = $isAdmin ? array($field => $id):array($field => $id, 'published' => true);
	$document = Post::find('first', array('conditions' => $conditions));

	if(empty($document)) {
	  FlashMessage::write('Sorry, that blog post does not exist or is not published.', 'default');
	  return $this->redirect('/');
	}

	$options = $document->options ? $document->options->data():Post::$defaultOptions;
	$labels = false;
	if($document->labels) {
	  $labels = Label::find('all', array('conditions' => array('_id' => $document->labels->data())));
	}

	// Get the 10 most popular labels. For more see popular_labels() method below.
	$popularLabels = Post::popularLabels(10);

	$this->set(compact('document', 'options', 'labels', 'popularLabels'));
  }

  /**
   * Lists the most popular labels used by blog posts.
   *
   * @return JSON
   */
  public function popular_labels($limit=10) {
	if(!$this->request->is('json') || !is_numeric($limit)) {
	  return $this->redirect('/');
	}

	$this->set(Post::popularLabels((int)$limit));
  }

  /**
   * Generates a pretty URL for the blog post document.
   *
   * @return string
   */
  private function _generateUrl($id=null) {
	$url = '';
	$url_field = Post::urlField();
	$url_separator = Post::urlSeparator();
	if($url_field != '_id' && !empty($url_field)) {
	  if(is_array($url_field)) {
		foreach($url_field as $field) {
		  if(isset($this->request->data[$field]) && $field != '_id') {
			$url .= $this->request->data[$field] . ' ';
		  }
		}
		$url = Inflector::slug(trim($url), $url_separator);
	  } else {
		$url = Inflector::slug($this->request->data[$url_field], $url_separator);
	  }
	}

	// Last check for the URL...if it's empty for some reason set it to "user"
	if(empty($url)) {
	  $url = 'post';
	}

	// Then get a unique URL from the desired URL (numbers will be appended if URL is duplicate) this also ensures the URLs are lowercase
	$options = array(
	  'url' => $url,
	  'model' => 'blackprint\models\Post'
	);
	// If an id was passed, this will ensure a document can use its own pretty URL on update instead of getting a new one.
	if(!empty($id)) {
	  $options['id'] = $id;
	}
	return Util::uniqueUrl($options);
  }

}
?>