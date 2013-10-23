<?php
namespace blackprint\controllers;

use blackprint\models\Content;
use blackprint\models\Asset;
use blackprint\util\Util;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\security\validation\RequestToken;
use lithium\util\Inflector;
use lithium\core\Libraries;
use lithium\net\http\Router;
use \MongoDate;
use \MongoId;

class ContentController extends \blackprint\controllers\BaseController {

	public function admin_index() {
		$this->_render['layout'] = 'admin';

		$conditions = array();
		if((isset($this->request->query['q'])) && (!empty($this->request->query['q']))) {
			$search_schema = Content::searchSchema();
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

		$total = Content::count(compact('conditions'));
		$documents = Content::all(compact('conditions','order','limit','page'));

		$page_number = (int)$page;
		$totalPages = ((int)$limit > 0) ? ceil($total / $limit):0;
		
		$contentTypes = Content::contentTypes();

		// Set data for the view template
		return compact('documents', 'total', 'page', 'limit', 'totalPages', 'contentTypes');
	}

	/**
	 * Allows admins to create new dynamic content pages.
	 * 
	 * @param string $type The type of content, defaults to "page"
	 * @param string $viewContext
	 * @return
	 */
	public function admin_create($type='page', $viewContext=null) {
		$this->_render['layout'] = 'admin';
		
		if(empty($type)) {
			$type = 'page';
		}
		$this->_render['template'] = 'admin_create_' . $type;
		
		// For front-end editing via modal with iframe...or any other special purpose that requires separate templates.
		if(!empty($viewContext) && is_string($viewContext)) {
			$cfg = Libraries::get();
			// layout
			array_unshift($this->_render['paths']['layout'], $cfg['app']['path'] . '/views/_libraries/blackprint/layouts/' . $viewContext . '/{:layout}.{:type}.php', '{:library}/views/layouts/' . $viewContext . '/{:layout}.{:type}.php');
			
			// template
			array_unshift($this->_render['paths']['template'], $cfg['app']['path'] . '/views/_libraries/blackprint/{:controller}/' . $viewContext . '/{:template}.{:type}.php', '{:library}/views/{:controller}/' . $viewContext . '/{:template}.{:type}.php');
		}

		$contentTypes = Content::contentTypes();
		$contentConfig = Content::contentConfig($type);
		$document = Content::create();

		// If data was passed, set some more data and save
		if ($this->request->data) {
			// CSRF
			if(!RequestToken::check($this->request)) {
				RequestToken::get(array('regenerate' => true));
			} else {
				$now = new MongoDate();
				$this->request->data['created'] = $now;
				$this->request->data['modified'] = $now;

				$this->request->data['_ownerId'] = (isset($this->request->user['_id'])) ? $this->request->user['_id']:null;
				$this->request->data['_ownerId'] = (isset($this->request->user['id'])) ? $this->request->user['id']:$this->request->data['_ownerId'];

				// Set the pretty URL that gets used by a lot of front-end actions.
				$this->request->data['url'] = $this->_generateUrl(array('model' => 'blackprint\models\Content'));

				// Set the content type ("page" by default)
				$this->request->data['_type'] = isset($type) ? $type:'page';
				
				// If there were file(s) passed.
				if(isset($this->request->data['files'])) {
					$failedFiles = array();
					$storedFiles = array();

					if(!$contentConfig['files']['allow']) {
						unset($this->request->data['files']);
					} else {
						$allowedExtensions = explode(',', $contentConfig['files']['extensions']);

						// Ensure it's in a multi-dimensional array so we can loop and not need to do if/else.
						// Ideally, the template would use the form helper and specify 'files[]' as the field name,
						// but in case that didn't happen, ensure the loop will work for what was passed.
						if(isset($this->request->data['files']['error'])) {
							$this->request->data['files'] = array($this->request->data['files']);
							// ...Or use 'Filedata' in place of 'files' if set (Agile Uploader's default - this is just convenience).
						} elseif(isset($this->request->data['Filedata'])) {
							if(isset($this->request->data['Filedata']['error'])) {
								$this->request->data['files'] = array($this->request->data['Filedata']);
							} else {
								$this->request->data['files'] = $this->request->data['Filedata'];
							}
						}

						// The 'files' field is not saved on the content document. Instead, a '_files' field is used.
						// This avoids inadvertent conflicts and other issues. This '_files' field is also formatted in a different way.
						// It holds a reference to the file in GridFS along with the original file name, upload date, etc.
						$this->request->data['_files'] = !empty($document->_files) ? $document->_files->data():array();

						$i = 0;
						foreach($this->request->data['files'] as $k => $v) {
							// Only handle as many as set by 'limit' in the config (default is one - zero is unlimited).
							if($i < $contentConfig['files']['limit'] || $contentConfig['files']['limit'] == 0) {
								// Again, ensure what was passed will work...
								if(isset($v['error'])) {
									if($v['error'] != UPLOAD_ERR_OK) {
										$v['errorMessage'] = 'Failed to upload.';
										$failedFiles[] = array($k => $v);
										unset($this->request->data['files'][$k]);
									} else {
										// If it came in ok...
										$ext = substr(strrchr($v['name'],'.'),1);
										$originalFilename = $v['name'];

										// Check if it's an allowed type (of if "*" was passed in the allowed extensions which would allow everything).
										if(!in_array($ext, $allowedExtensions) || in_array('*', $allowedExtensions)) {
											$v['errorMessage'] = 'File type not allowed.';
											$failedFiles[] = array($k => $v);
											unset($this->request->data['files'][$k]);
										} else {
											// Check if it's within the max file size allowed.
											if($v['size'] > $contentConfig['files']['maxSize']) {
												$v['errorMessage'] = 'File size too large.';
												$failedFiles[] = array($k => $v);
												unset($this->request->data['files'][$k]);
											} else {
												// Passed all checks? Store it.
												$gridFileId = Asset::store(
													$v['tmp_name'],
													array(
														'filename' => (string)uniqid(php_uname('n') . '.') . '.'.$ext,
														'originalFilename' => $originalFilename,
														'fileExt' => $ext,
														'created' => $now
													)
												);
												// If saved...
												if ($gridFileId) {
													// Ensure the GridFS file document association is only saved once to the content document.
													// ...And on each association, add some data that may be useful before/without doing a second query to the GridFS collection.
													$this->request->data['_files'][(string)$gridFileId] = array(
														// This is the 'filename' seen publicly...There are routes that use the MongoId and extension to load the file.
														'name' => (string)$gridFileId . '.' . $ext,
														'originalFilename' => $originalFilename,
														'fileExt' => $ext,
														'created' => $now
													);
													$storedFiles[] = array($k => $v);
												} else {
													// Failed to store to GridFS
													$v['errorMessage'] = 'File failed to save, please try again.';
													$failedFiles[] = array($k => $v);
													unset($this->request->data['files'][$k]);
												}
											}
										}
									}
								}
							}
							$i++;
						}
					}
					// Don't save data to this field...Especially not binary file data.
					unset($this->request->data['files']);
				}
				
				$this->request->data['_ownerId'] = (isset($this->request->user['_id'])) ? $this->request->user['_id']:null;
				$this->request->data['_ownerId'] = (isset($this->request->user['id'])) ? $this->request->user['id']:$this->request->data['_ownerId'];

				$publicIdConflict = false;
				$requestedPublicId = '';
				if(isset($this->request->data['_publicId']) && !empty($this->request->data['_publicId'])) {
					$requestedPublicId = $this->request->data['_publicId'];
					$publicIdConflict = Content::find('count', array('fields' => array('_publicId'), 'conditions' => array('_id' => array('$ne' => $document->_id), '_publicId' => $this->request->data['_publicId'])));
					if($publicIdConflict) {
						$this->request->data['_publicId'] = '!___conflict___!';
					}
				}

				$redirect = false;
				if(isset($this->request->data['__redirect'])) {
					$redirect = $this->request->data['__redirect'];
					unset($this->request->data['__redirect']);
				}
				
				// Save
				if($document->save($this->request->data)) {
					FlashMessage::write('The content has been created successfully.', 'blackprint');
					
					// Special redirect if the form has passed one, for example...edit and return to the form.
					if($redirect) {
						// If we want to jump specifically to the update action.
						if(substr($redirect, 0, 6) == 'update') {
							$redirectQueryParams = explode('?', $redirect);
							$redirectArgs = explode('/', $redirectQueryParams[0]);
							$redirectArgs[0] = $document->_id; // overwrite the 'update' it isn't a necessary argument
							$redirect = Router::match(array('admin' => true, 'library' => 'blackprint', 'controller' => 'content', 'action' => 'update', 'args' => $redirectArgs));
							if(isset($redirectQueryParams[1])) {
								$redirect .= '?' . $redirectQueryParams[1];
							}
						}
						return $this->redirect($redirect);
					}
					$this->redirect(array('library' => 'blackprint', 'controller' => 'content', 'action' => 'index', 'admin' => true));
				} else {
					FlashMessage::write('The content could not be created, please try again.', 'blackprint');
					// change back the requseted _publicId if there was a conflict...this is a little bit hacky for validation
					if(isset($this->request->data['_publicId']) && $this->request->data['_publicId'] == '!___conflict___!') {
						$document->_publicId = $requestedPublicId;
					}
				}
			}
		}
		
		$this->set(compact('document', 'contentTypes', 'contentConfig'));
	}

	/**
	 * Allows admins to update dynamic content pages.
	 *
	 * @param string $id The content id
	 */
	public function admin_update($id=null, $viewContext=null) {
		if(empty($id)) {
			FlashMessage::write('You must provide a content id to update.', 'blackprint');
			return $this->redirect(array('admin' => true, 'library' => 'blackprint', 'controller' => 'content', 'action' => 'index'));
		}
		$this->_render['layout'] = 'admin';
		
		$document = Content::find('first', array('conditions' => array('_id' => $id)));
		$contentConfig = Content::contentConfig($document->_type);

		if(!empty($document->_type)) {
			$this->_render['template'] = 'admin_update_' . $document->_type;
		}
		
		// For front-end editing via modal with iframe...or any other special purpose that requires separate templates.
		if(!empty($viewContext) && is_string($viewContext)) {
			$cfg = Libraries::get();
			// layout
			array_unshift($this->_render['paths']['layout'], $cfg['app']['path'] . '/views/_libraries/blackprint/layouts/' . $viewContext . '/{:layout}.{:type}.php', '{:library}/views/layouts/' . $viewContext . '/{:layout}.{:type}.php');
			
			// template
			array_unshift($this->_render['paths']['template'], $cfg['app']['path'] . '/views/_libraries/blackprint/{:controller}/' . $viewContext . '/{:template}.{:type}.php', '{:library}/views/{:controller}/' . $viewContext . '/{:template}.{:type}.php');
		}
		
		if(!empty($this->request->data)) {
			// IMPORTANT: Use MongoDate() when inside an array/object because $_schema isn't deep
			$now = new MongoDate();

			$this->request->data['modified'] = $now;

			// Set the pretty URL that gets used by a lot of front-end actions.
			// Pass the document _id so that it doesn't change the pretty URL on an update.
			$this->request->data['url'] = $this->_generateUrl($document->_id);
			
			// If there were file(s) passed.
			if(isset($this->request->data['files'])) {
				$failedFiles = array();
				$storedFiles = array();

				if(!$contentConfig['files']['allow']) {
					unset($this->request->data['files']);
				} else {
					$allowedExtensions = explode(',', $contentConfig['files']['extensions']);

					// Ensure it's in a multi-dimensional array so we can loop and not need to do if/else.
					// Ideally, the template would use the form helper and specify 'files[]' as the field name,
					// but in case that didn't happen, ensure the loop will work for what was passed.
					if(isset($this->request->data['files']['error'])) {
						$this->request->data['files'] = array($this->request->data['files']);
						// ...Or use 'Filedata' in place of 'files' if set (Agile Uploader's default - this is just convenience).
					} elseif(isset($this->request->data['Filedata'])) {
						if(isset($this->request->data['Filedata']['error'])) {
							$this->request->data['files'] = array($this->request->data['Filedata']);
						} else {
							$this->request->data['files'] = $this->request->data['Filedata'];
						}
					}

					// The 'files' field is not saved on the content document. Instead, a '_files' field is used.
					// This avoids inadvertent conflicts and other issues. This '_files' field is also formatted in a different way.
					// It holds a reference to the file in GridFS along with the original file name, upload date, etc.
					$this->request->data['_files'] = !empty($document->_files) ? $document->_files->data():array();

					$i = 0;
					foreach($this->request->data['files'] as $k => $v) {
						// Only handle as many as set by 'limit' in the config (default is one - zero is unlimited).
						if($i < $contentConfig['files']['limit'] || $contentConfig['files']['limit'] == 0) {
							// Again, ensure what was passed will work...
							if(isset($v['error'])) {
								if($v['error'] != UPLOAD_ERR_OK) {
									$v['errorMessage'] = 'Failed to upload.';
									$failedFiles[] = array($k => $v);
									unset($this->request->data['files'][$k]);
								} else {
									// If it came in ok...
									$ext = substr(strrchr($v['name'],'.'),1);
									$originalFilename = $v['name'];

									// Check if it's an allowed type (of if "*" was passed in the allowed extensions which would allow everything).
									if(!in_array($ext, $allowedExtensions) || in_array('*', $allowedExtensions)) {
										$v['errorMessage'] = 'File type not allowed.';
										$failedFiles[] = array($k => $v);
										unset($this->request->data['files'][$k]);
									} else {
										// Check if it's within the max file size allowed.
										if($v['size'] > $contentConfig['files']['maxSize']) {
											$v['errorMessage'] = 'File size too large.';
											$failedFiles[] = array($k => $v);
											unset($this->request->data['files'][$k]);
										} else {
											// Passed all checks? Store it.
											$gridFileId = Asset::store(
												$v['tmp_name'],
												array(
													'filename' => (string)uniqid(php_uname('n') . '.') . '.'.$ext,
													'originalFilename' => $originalFilename,
													'fileExt' => $ext,
													'created' => $now
												)
											);
											// If saved...
											if ($gridFileId) {
												// Ensure the GridFS file document association is only saved once to the content document.
												// ...And on each association, add some data that may be useful before/without doing a second query to the GridFS collection.
												$this->request->data['_files'][(string)$gridFileId] = array(
													// This is the 'filename' seen publicly...There are routes that use the MongoId and extension to load the file.
													'name' => (string)$gridFileId . '.' . $ext,
													'originalFilename' => $originalFilename,
													'fileExt' => $ext,
													'created' => $now
												);
												$storedFiles[] = array($k => $v);
											} else {
												// Failed to store to GridFS
												$v['errorMessage'] = 'File failed to save, please try again.';
												$failedFiles[] = array($k => $v);
												unset($this->request->data['files'][$k]);
											}
										}
									}
								}
							}
						}
						$i++;
					}
				}
				
				// When updating...We want to check to see what to do with any existing files...By default we just append new files from this update.
				// Which means we would be done right now (aside from saving the content document). However, we could be replacing...
				if(isset($contentConfig['files']['replaceOnUpdate']) && $contentConfig['files']['replaceOnUpdate'] == true) {
					if(!empty($document->_files)) {
						$oldFiles = $document->_files->data();
						foreach($oldFiles as $k => $v) {
							$fileId = new MongoId($k);
							$asset = Asset::find('count', array('conditions' => array('_id' => $fileId)));
							if($asset > 0) {
								if(Asset::remove(array('_id' => $fileId))) {
									// Remove any thumbnails if this was an image asset (or other types of children files)
									Asset::remove(array('_parent' => $fileId));
								}
							}
							
							// We could just unset the data after removing the item...But by making another count() again, we ensure that it was
							//actually removed before removing the association. This is a safer bet even though it involves a few more queries.
							$removedAsset = Asset::find('count', array('conditions' => array('_id' => $fileId)));
							if($removedAsset == 0) {
								// Remove the association from $this->request->data['_files']
								if(isset($this->request->data['_files'][(string)$fileId])) {
									unset($this->request->data['_files'][(string)$fileId]);
								}
							}
						}
					}
				}
				
				// Don't save data to this field...Especially not binary file data.
				unset($this->request->data['files']);
			}
			
			$publicIdConflict = false;
			$requestedPublicId = '';
			if(isset($this->request->data['_publicId']) && !empty($this->request->data['_publicId'])) {
				$requestedPublicId = $this->request->data['_publicId'];
				$publicIdConflict = Content::find('count', array('fields' => array('_publicId'), 'conditions' => array('_id' => array('$ne' => $document->_id), '_publicId' => $this->request->data['_publicId'])));
				if($publicIdConflict) {
					$this->request->data['_publicId'] = '!___conflict___!';
				}
			}
			
			$redirect = false;
			if(isset($this->request->data['__redirect'])) {
				$redirect = $this->request->data['__redirect'];
				unset($this->request->data['__redirect']);
			}

			// No need to save this of course.
			unset($this->request->data['security']);
			
			// if($document->save($this->request->data)) { // <--- this creates problems with FAQ content and likely any other field that has an array/object. It appends (even duplicates) instead of updates.
			if(Content::update($this->request->data, array('_id' => $id))) {
				FlashMessage::write('The content has been successfully updated.', 'blackprint');
				
				// Special redirect if the form has passed one, for example...edit and return to the form.
				if($redirect) {
					// If we want to jump specifically to the update action.
					if(substr($redirect, 0, 6) == 'update') {
						$redirectQueryParams = explode('?', $redirect);
						$redirectArgs = explode('/', $redirectQueryParams[0]);
						$redirectArgs[0] = $document->_id; // overwrite the 'update' it isn't a necessary argument
						$redirect = Router::match(array('admin' => true, 'library' => 'blackprint', 'controller' => 'content', 'action' => 'update', 'args' => $redirectArgs));
						if(isset($redirectQueryParams[1])) {
							$redirect .= '?' . $redirectQueryParams[1];
						}
					}
					return $this->redirect($redirect);
				}
				return $this->redirect(array('admin' => true, 'library' => 'blackprint', 'controller' => 'content', 'action' => 'index'));
			} else {
				FlashMessage::write('There was a problem updating the content, please try again.', 'blackprint');
				// change back the requseted _publicId if there was a conflict...this is a little bit hacky for validation
				if(isset($this->request->data['_publicId']) && $this->request->data['_publicId'] == '!___conflict___!') {
					$document->_publicId = $requestedPublicId;
				}
			}
		}
		
		$this->set(compact('document', 'contentConfig'));
	}
	
	/**
	 * Allows admins to remove files from GridFS (Asset model) and removes the
	 * association on the content document.
	 * 
	 * NOTE: This should be the only way assets associated to content documents
	 * should be removed. Assets should also only be associated with one content
	 * document. Otherwise, there could be orphaned associations.
	 * 
	 * @param string $id The content MongoId
	 * @param string $fileId The file asset MongoId (found on the content document)
	 */
	public function admin_remove_file($id=null, $fileId=null) {
		$response = array('success' => false);
		if(empty($id) || empty($fileId)) {
			return $this->render(array('json' => $response));
		}
		
		// A good check to make...Ensure the content document actually exists.
		$document = Content::find('first', array('fields' => array('_id', '_files'), 'conditions' => array('_id' => $id)));
		if(empty($document)) {
			return $this->render(array('json' => $response));
		}
		
		if(substr($fileId, -5) == '.json') {
			$fileId = substr($fileId, 0, -5);
		}
		$fileId = new MongoId($fileId);
		
		// Also ensure the asset actually exists.
		$asset = Asset::find('first', array('conditions' => array('_id' => $fileId)));
		if(!empty($asset)) {
			if(Asset::remove(array('_id' => $asset->_id))) {
				// Remove any thumbnails if this was an image asset (or other types of children files).
				Asset::remove(array('_parent' => $asset->_id));

				// Update the document to remove associations.
				Content::update(
					array('$unset' => array('_files.' . (string)$asset->_id => true)),
					array('_id' => $document->_id)
				);
				$response['success'] = true;
			}
		}
		
		$this->render(array('json' => $response));
	}

	/**
	 * Allows admins to delete content.
	 *
	 * @param string $id The content id
	*/
	public function admin_delete($id=null) {
		$this->_render['layout'] = 'admin';

		// Get the document from the db to edit
		$conditions = array('_id' => $id);
		$document = Content::find('first', array('conditions' => $conditions));

		// Redirect if invalid content
		if(empty($document)) {
			FlashMessage::write('That content was not found.', 'blackprint');
			return $this->redirect(array('library' => 'blackprint', 'controller' => 'content', 'action' => 'index', 'admin' => true));
		}

		$files = array();
		if(!empty($document->_files)) {
			$files = $document->_files->data();
		}
		if($document->delete()) {
			foreach($files as $k => $v) {
				Asset::remove(array('_id' => $k));
				// Remove any thumbnails if this was an image asset (or other types of children files)
				Asset::remove(array('_parent' => new MongoId($k)));
			}
			FlashMessage::write('The content has been deleted.', 'blackprint');
		}

		return $this->redirect(array('library' => 'blackprint', 'controller' => 'content', 'action' => 'index', 'admin' => true));
	}

	/**
	 * Public index listing method.
	 *
	 */
	public function index() {

		$conditions = array();
		if((isset($this->request->query['q'])) && (!empty($this->request->query['q']))) {
			$search_schema = Content::searchSchema();
			$search_conditions = array();
			// For each searchable field, adjust the conditions to include a regex
			foreach($search_schema as $k => $v) {
				$field = (is_string($k)) ? $k:$v;
				$search_regex = new \MongoRegex('/' . $this->request->query['q'] . '/i');
				$conditions['$or'][] = array($field => $search_regex);
			}
		}

		$conditions += array('published' => true);

		$limit = $this->request->limit ?: 5;
		$page = $this->request->page ?: 1;
		// Allow querystring params for these as well.
		$page = (isset($this->request->query['page'])) ? $this->request->query['page']:$page;
		$limit = (isset($this->request->query['limit'])) ? $this->request->query['limit']:$limit;
		$order = array('created' => 'desc');
		$total = Content::count(compact('conditions'));
		$documents = Content::all(compact('conditions','order','limit','page'));

		$page_number = (int)$page;
		$totalPages = ((int)$limit > 0) ? ceil($total / $limit):0;

		// Set data for the view template
		return compact('documents', 'total', 'page', 'limit', 'totalPages');
	}

	/**
	 * Public view method.
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
		$document = Content::find('first', array('conditions' => $conditions));

		if(empty($document) || !$document->published) {
			FlashMessage::write('Sorry, that content does not exist or is not published.', 'blackprint');
			return $this->redirect('/');
		}

		$defaultAppConfig = Libraries::get(true);
		$appPath = $defaultAppConfig['path'];
		if(!empty($document->_type)) {
			if($document->_type == 'text') {
				// don't let pieces of text be viewed at pages of their own
				return $this->redirect('/');
			}
			$this->_render['template'] = 'read_' . $document->_type;
		}
		$this->_render['paths']['template'] = array(
			$appPath . '/views/_libraries/blackprint/{:controller}/{:template}.{:type}.php',
			'{:library}/views/{:controller}/{:template}.{:type}.php',
			$appPath . '/views/_libraries/blackprint/{:controller}/read.{:type}.php',
			'{:library}/views/{:controller}/read.{:type}.php',
		);
		
		$options = $document->options ? $document->options->data():Content::$defaultOptions;
		
		$this->set(compact('document', 'options'));
	}

}
?>