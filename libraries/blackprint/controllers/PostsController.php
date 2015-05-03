<?php
namespace blackprint\controllers;

use blackprint\models\Post;
use blackprint\models\Label;
use blackprint\extensions\Util;
use blackprint\extensions\storage\FlashMessage;
use lithium\security\validation\RequestToken;
use lithium\util\Inflector;
use lithium\uil\Hash;
use lithium\util\String;
use \MongoDate;
use \MongoId;
use lithium\net\http\Router;

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

  /**
   * Creates a blank post intended for immediate in place editing through the front-end of the site.
   * TODO: Make admin_create() do the same thing with an argument and remove this.
   * 
   * @return
   */
  public function admin_create_blank() {
  	$this->_render['template'] = false;
  	$this->_render['layout'] = false;

  	$document = Post::create();

		// A default title and body (to be changed by the user)
  	$this->request->data['title'] = 'Untitled Post';
  	$this->request->data['body'] = 'Start here...';

  	$now = new MongoDate();
  	$this->request->data['created'] = $now;
  	$this->request->data['modified'] = $now;
  	$this->request->data['published'] = false;

		// If using the li3b_users plugin (or if $this->request->user is set by any user plugin), use that for the author id
  	$this->request->data['_authorId'] = (isset($this->request->user['_id'])) ? $this->request->user['_id']:null;
  	$this->request->data['_authorId'] = (isset($this->request->user['id'])) ? $this->request->user['id']:$this->request->data['_authorId'];

	// Set the pretty URL that gets used by a lot of front-end actions.
  	$this->request->data['url'] = $this->_generateUrl();
	// Of course we could just use the MongoId since that's pretty obfuscated, but in case we ever switch databases...It's best to have a dedicated field here.
	// And all draftHash values start with an underscore as well to help further distinguish (for routing, etc.).
  	$this->request->data['draftHash'] = '_' . String::hash(String::uuid(), array('type' => 'md5'));

  	if($document->save($this->request->data)) {
  		FlashMessage::write('The post has been created successfully.', 'default');
  		$this->redirect(array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'draft', 'args' => array($this->request->data['draftHash'])));
  	} else {
  		FlashMessage::write('The post could not be created, please try again.', 'default');
  	}

  }

	/**
	* A back-end admin create for new posts.
	* Note: Styles may not reflect the front-end of the web site in this case.
	* 
	* @return
	*/
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
				$this->request->data['draftHash'] = '_' . String::hash(String::uuid(), array('type' => 'md5'));

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
   * Allows admins to update blog posts via admin dashboard update action or JSON request.
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

  	// Allow any of these values for the id.
  	if($id[0] == '_') {
  		$field = 'draftHash';
  	}
  	elseif(preg_match('/[0-9a-f]{24}/', $id)) {
  		$field = '_id';
  	} else {
  		$field = 'url';
  	}

  	$conditions = array($field => $id);
  	$document = Post::find('first', array('conditions' => $conditions));
  	if($document && !empty($this->request->data)) {
		// IMPORTANT: Use MongoDate() when inside an array/object because $_schema isn't deep
  		$now = new MongoDate();

  		$this->request->data['modified'] = $now;

		// Set the pretty URL that gets used by a lot of front-end actions.
		// Pass the document _id so that it doesn't change the pretty URL on an update.
  		$this->request->data['url'] = $this->_generateUrl($document->_id);

  		if($document->save($this->request->data)) {
  			FlashMessage::write('The blog post has been successfully updated.', 'default');
			// Redirect if this is not a JSON request.
  			if($this->request->type != 'json') {
  				return $this->redirect(array('admin' => true, 'library' => 'blackprint', 'controller' => 'posts', 'action' => 'index'));
  			}
  		} else {
  			FlashMessage::write('There was a problem updating the blog post, please try again.', 'default');
  		}
  	}

  	$this->set(compact('document', 'rainbowThemes', 'highlightThemes'));
  }

  /**
   * Applies a label to a blog post.
   * 
   * @param string $id 		The post id
   * @param string $labelId The label id
   * @param int    $remove  If not 0, then the label will be removed instead of being appplied
   */
  public function admin_apply_label($id=null, $labelId=null, $remove=0) {
  	$this->_render['layout'] = false;
  	$this->_render['template'] = false;

  	$response = array('success' => false, 'action' => 'add', 'label' => false);
	if(!$this->request->is('json')) {
		return $response;
	}
  	if(empty($id) || empty($labelId)) {
  		return $response;
  	}

  	// Allow any of these values for the id.
  	if($id[0] == '_') {
  		$field = 'draftHash';
  	}
  	elseif(preg_match('/[0-9a-f]{24}/', $id)) {
  		$field = '_id';
  	} else {
  		$field = 'url';
  	}

  	$conditions = array($field => $id);
  	$document = Post::find('first', array('conditions' => $conditions));
  	if(!$document) {
  		$response['error'] = 'Document not found.';
  		return $response;
  	}

  	$labelDoc = Label::find('first', array('conditions' => array('_id' => $labelId)));
  	if(!$labelDoc) {
  		$response['error'] = 'Label not found.';
  		return $response;
  	}

  	$currentLabels = ($document->labels !== null) ? $document->labels->data():array();
  	if($remove !== 0) {
  		$response['action'] = 'remove';
  		if(in_array($labelId, $currentLabels)) {
  			if(($key = array_search($labelId, $currentLabels)) !== false) {
				unset($currentLabels[$key]);
			}
			if(!is_array($currentLabels)) {
				$currentLabels = array();
			}
			$document->labels = array_values($currentLabels); // use array_values() because for some reason this can become a keyed array and i just haven't figured out why yet and i'm being lazy
  		}
  	} else {
  		if(!in_array($labelId, $currentLabels)) {
  			array_push($currentLabels, $labelId);
  		}
  		$document->labels = array_values($currentLabels);
  	}

  	if($document->save()) {
  		$response['success'] = true;
  	}
 	$response['document'] = $document;
 	$response['label'] = $labelDoc;
  	return $response;
  }

  	/**
	 * Allows admins to publish/unpublish posts.
	 *
	 * @param string $id 	  The post id
	 * @param bool   $publish A value of true will set the post to be published and false will unpublish
	*/
 	public function admin_publish($id=null, $publish=true) {
 		$publish = ($publish === 1 || $publish === "1" || $publish === "true") ? true:false;
		$this->_render['layout'] = false;
		$this->_render['template'] = false;

		$response = array('success' => false);
		if(!$this->request->is('json')) {
			return $response;
		}
		if(empty($id)) {
			return $response;
		}

		// Allow any of these values for the id.
		if($id[0] == '_') {
			$field = 'draftHash';
		}
		elseif(preg_match('/[0-9a-f]{24}/', $id)) {
			$field = '_id';
		} else {
			$field = 'url';
		}

		$conditions = array($field => $id);
		$document = Post::find('first', array('conditions' => $conditions));
		if(!$document) {
			$response['error'] = 'Document not found.';
			return $response;
		}

		$document->published = $publish;
		if($document->save()) {
			$response['success'] = true;
		}

		return $response;
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
   * Draft method.
   *
   * The id can be either a pretty URL or a MongoId or a draft _hash (which is the only thing that starts 
   * with an underscore since pretty URLs use dashes and there's no underscore in a MongoId).
   * 
   * @param string $id
   */
  public function draft($id=null) {
  	if(empty($id)) {
  		return $this->redirect('/');
  	}

	// Only draftHash values start with underscores. Pretty URLs use dashes and MongoIds (and any database id really) shouldn't start with an underscore.
  	if($id[0] == '_') {
  		$field = 'draftHash';
  	}
  	elseif(preg_match('/[0-9a-f]{24}/', $id)) {
  		$field = '_id';
  	} else {
  		$field = 'url';
  	}

  	$isAdmin = (isset($this->request->user['role']) && $this->request->user['role'] == 'administrator') ? true:false;
  	$conditions = $isAdmin ? array($field => $id):array($field => $id, 'published' => true);
	// But remove the published condition if accessing by draft hash. It was shared with someone. They may not be able to edit of course, but they can view.
  	if($field == 'draftHash') {
  		unset($conditions['published']);
  	}
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

	// Nothing special about the draft. It uses the read template. In fact, it's better to use the read template to ensure visual accuracy for a user.
  	$this->_render['template'] = 'read';

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
  	$url_field = Post::$urlField;
  	$url_separator = Post::$urlSeparator;
  	if($url_field != '_id' && !empty($url_field)) {
  		if(is_array($url_field)) {
  			foreach($url_field as $field) {
  				if(isset($this->request->data[$field]) && $field != '_id') {
  					$url .= strip_tags($this->request->data[$field]) . ' ';
  				}
  			}
  			$url = Inflector::slug(trim($url), $url_separator);
  		} else {
  			$url = Inflector::slug(strip_tags($this->request->data[$url_field]), $url_separator);
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