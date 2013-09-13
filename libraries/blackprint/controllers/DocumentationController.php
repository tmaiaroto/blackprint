<?php
namespace blackprint\controllers;

/**
 * This controller is used for serving static pages by name, which are located in the
 * `/views/documentation` folder.
 *
 * This controller works basically in the same way the static PagesController works.
 * It's simply separate to keep documentation organized.
 */
class DocumentationController extends \lithium\action\Controller {
	
	/**
	 * Basic static pages.
	 * Publicly visible.
	 * 
	 * @return
	*/
	public function view() {
		$path = func_get_args() ?: array('home');
		return $this->render(array('template' => join('/', $path)));
	}
	
	/**
	 * Admin static pages. Protected - only administrator users can see these.
	 * 
	*/
	public function admin_view() {
		$this->_render['layout'] = 'admin';
		
		$path = func_get_args() ?: array('home');
		// Always prefix the last item in the path with admin_
		$last_piece = end($path);
		$last_piece = (substr($last_piece, 0, 6) != 'admin_') ? 'admin_' . $last_piece:$last_piece;
		$last_key = (count($path) - 1);
		$path[$last_key] = $last_piece;
		return $this->render(array('template' => join('/', $path)));
	}
	
}
?>