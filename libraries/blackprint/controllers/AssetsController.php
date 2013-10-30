<?php
namespace blackprint\controllers;

use blackprint\models\Asset;
use blackprint\extensions\storage\FlashMessage;
use lithium\security\validation\RequestToken;
use \MongoDate;

/**
 * This controller is used for working with all assets in the system.
 * Media files stored in GridFS, etc.
 */
class AssetsController extends \lithium\action\Controller {
	
	/**
	 * Lists all of the assets in the system.
	 * 
	*/
	public function admin_index() {
		$this->_render['layout'] = 'admin';
	
		$conditions = array();
		if((isset($this->request->query['q'])) && (!empty($this->request->query['q']))) {
			$search_schema = Asset::searchSchema();
			$search_conditions = array();
			// For each searchable field, adjust the conditions to include a regex
			foreach($search_schema as $k => $v) {
				$field = (is_string($k)) ? $k:$v;
				$search_regex = new \MongoRegex('/' . $this->request->query['q'] . '/i');
				$conditions['$or'][] = array($field => $search_regex);
			}
		}

		$limit = $this->request->limit ?: 25;
		$page = $this->request->page ?: 1;

		$requestedOrder = $this->request->sort ?: false;
		if($requestedOrder) {
			$requestedOrderPieces = explode(',', $requestedOrder);
			$requestedOrder = array();
			$requestedOrder[$requestedOrderPieces[0]] = isset($requestedOrderPieces[1]) ? $requestedOrderPieces[1]:'desc';
			if(isset($requestedOrderPieces[2])) {
				$requestedOrder[$requestedOrderPieces[2]] = isset($requestedOrderPieces[3]) ? $requestedOrderPieces[3]:'desc';
			}
		}
		$order = $requestedOrder ?: array('created' => 'desc', 'retired' => 'asc');

		$total = Asset::count(compact('conditions'));
		$documents = Asset::all(compact('conditions','order','limit','page'));

		$totalPages = ((int)$limit > 0) ? ceil($total / $limit):0;

		return compact('documents', 'total', 'page', 'limit', 'totalPages');
	}
	
}
?>