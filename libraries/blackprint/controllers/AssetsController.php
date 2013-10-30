<?php
namespace blackprint\controllers;

use blackprint\models\Asset;
use blackprint\extensions\storage\FlashMessage;
use lithium\security\validation\RequestToken;
use \MongoDate;

/**
 * This controller is used for working with all assets in the system.
 * Media files stored in GridFS, etc.
 *
 * It would be nice to just store everything in GridFS and then send
 * assets off to S3 or some other CDN...While that would be very convenient
 * for backups, migrations, etc. Larger assets such as HD videos could
 * quickly take up a lot of space in the database requiring one to shard
 * before they might want/need to.
 *
 * So assets will have a "service" field that tells us where they are stored.
 * By default this will be "mongo" - meaning the file data is stored in GridFS.
 * However, in the future there will be other options such as "S3" and even "disk"
 * and the "source" field then would be different depending on the service.
 *
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
	
	/**
	 * Allows admins to create/upload new assets.
	 *
	 * @return
	 */
	public function admin_create() {
		$this->_render['layout'] = 'admin';

		if($this->request->data) {
			$now = new MongoDate();
			$data = array();

			// If there was only one file uploaded, stick it into a multi-dimensional array.
			// It's just easier to always run the foreach() and code the processing stuff once and here.
			// For now...while we're saving to disk.
			if(!isset($this->request->data['Filedata'][0]['error'])) {
				$this->request->data['Filedata'] = array($this->request->data['Filedata']);
			}

			foreach($this->request->data['Filedata'] as $file) {
				// Save file to gridFS
				if ($file['error'] == UPLOAD_ERR_OK) {
					$ext = substr(strrchr($file['name'], '.'), 1);
					switch(strtolower($ext)) {
						case 'jpg':
						case 'jpeg':
						case 'png':
						case 'gif':
						case 'png':
							// Asset::store() works much like the mongo PHP driver. Filename first, then metadata.
							// We are creating a unique file name as well, otherwise we'd overwrite stuff or have failed saves.
							$gridFileId = Asset::store(
								$file['tmp_name'],
								array(
									'filename' => (string)uniqid(php_uname('n') . '.') . '.'.$ext,
									'fileExt' => $ext,
									'exif' => exif_read_data($file['tmp_name']),
									'originalFilename' => $file['name']
									)
								);
							break;
						default:
							//exit();
							break;
					}
				}
			}
			return;
		}
		FlashMessage::write('Sorry, there was seemingly nothing to upload, please add some files and try again.');
	}

	/**
	 * Allows admins to view the details of an asset.
	 *
	 * @param string $id The asset id
	*/
	public function admin_read($id=null) {
		$this->_render['layout'] = 'admin';

		// Get the document from the db to edit
		$conditions = array('_id' => $id);
		$document = Asset::find('first', array('conditions' => $conditions));

		return compact('document');
	}

	/**
	 * Allows admins to delete assets.
	 *
	 * @param string $id The asset id
	*/
	public function admin_delete($id=null) {
		$this->_render['layout'] = 'admin';

		// Get the document from the db to edit
		$conditions = array('_id' => $id);
		$document = Asset::find('first', array('conditions' => $conditions));

		// Redirect if not found
		if(empty($document)) {
			FlashMessage::write('That asset was not found.');
			return $this->redirect(array('library' => 'blackprint', 'controller' => 'assets', 'action' => 'index', 'admin' => true));
		}

		if($document->delete()) {
			FlashMessage::write('The asset has been deleted.');
		} else {
			FlashMessage::write('The asset could not be deleted, please try again.');
		}

		return $this->redirect(array('library' => 'blackprint', 'controller' => 'assets', 'action' => 'index', 'admin' => true));
	}

}
?>