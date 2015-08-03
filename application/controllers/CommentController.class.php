<?php

/**
 * Handle all comment related requests
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class CommentController extends ApplicationController {

	/**
	 * Construct the CommentController
	 *
	 * @access public
	 * @param void
	 * @return FilesController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	/**
	 * Add comment
	 *
	 * Through this controller only logged users can post (no anonymous comments here)
	 *
	 * @param void
	 * @return null
	 */
	function add() {
		$this->setTemplate('add_comment');

		$object_id = get_id('object_id');
		
		$object = Objects::findObject($object_id);
		if(!($object instanceof ContentDataObject)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$comment = new Comment();
		$comment_data = array_var($_POST, 'comment');

		tpl_assign('comment_form_object', $object);
		tpl_assign('comment', $comment);
		tpl_assign('comment_data', $comment_data);

		if(is_array($comment_data)) {
			try {
				try {
					$attached_files = ProjectFiles::handleHelperUploads(active_context());
				} catch(Exception $e) {
					$attached_files = null;
				} // try

				$comment->setFromAttributes($comment_data);
				$comment->setRelObjectId($object_id);
				$comment->setObjectName(substr_utf($comment->getText(), 0, 250));

				DB::beginWork();
				$comment->save();
				
				$comment->addToMembers($object->getMembers());
				$comment->addToSharingTable();
				
				if(is_array($attached_files)) {
					foreach($attached_files as $attached_file) {
						$comment->attachFile($attached_file);
					} // foreach
				} // if

				// Subscribe user to object
				if(!$object->isSubscriber(logged_user())) {
					$object->subscribeUser(logged_user());
				} // if
				if (strlen($comment->getText()) < 100) {
					$comment_head = $comment->getText();
				} else {
					$lastpos = strpos($comment->getText(), " ", 100);
					if ($lastpos === false) $comment_head = $comment->getText();
					else $comment_head = substr($comment->getText(), 0, $lastpos) . "...";
				}
				$comment_head = html_to_text($comment_head);
				
				DB::commit();
				
				ApplicationLogs::createLog($comment, ApplicationLogs::ACTION_COMMENT, false, false, true, $comment_head);
				flash_success(lang('success add comment'));

				evt_add("scroll to comment", array('comment_id' => $comment->getId()));
				
				ajx_current("reload");
			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // add

	/**
	 * Edit comment
	 *
	 * @param void
	 * @return null
	 */
	function edit() {
		$this->setTemplate('add_comment');

		$comment = Comments::findById(get_id());
		if(!($comment instanceof Comment)) {
			flash_error(lang('comment dnx'));
			ajx_current("empty");
			return;
		}

		$object = $comment->getRelObject();
		if(!($object instanceof ContentDataObject)) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		}
		
		if(trim($comment->getViewUrl())) {
			$redirect_to = $comment->getViewUrl();
		} elseif(trim($object->getObjectUrl())) {
			$redirect_to = $object->getObjectUrl();
		}

		if(!$comment->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		$comment_data = array_var($_POST, 'comment');
		if(!is_array($comment_data)) {
			$comment_data = array(
	          'text' => $comment->getText(),
			);
		}

		tpl_assign('comment_form_object', $object);
		tpl_assign('comment', $comment);
		tpl_assign('comment_data', $comment_data);

		if(is_array(array_var($_POST, 'comment'))) {
			try {
				
				$comment->setFromAttributes($comment_data);
				$comment->setRelObjectId($object->getId());
				
				$comment->setObjectName(substr_utf($comment->getText(), 0, 250));
				
				DB::beginWork();
				$comment->save();
				$object->onEditComment($comment);
				DB::commit();
				ApplicationLogs::createLog($comment, ApplicationLogs::ACTION_EDIT);
				
				flash_success(lang('success edit comment'));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // edit

	/**
	 * Delete specific comment
	 *
	 * @param void
	 * @return null
	 */
	function delete() {
		$comment = Comments::findById(get_id());
		if(!($comment instanceof Comment)) {
			flash_error(lang('comment dnx'));
			ajx_current("empty");
			return;
		} // if

		$object = $comment->getRelObject();
		if(!($object instanceof ContentDataObject)) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		} // if

		if(trim($object->getObjectUrl())) $redirect_to = $object->getObjectUrl();

		if(!$comment->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {
			DB::beginWork();
			$comment->trash();
			DB::commit();
			ApplicationLogs::createLog($comment, ApplicationLogs::ACTION_TRASH);
			
			flash_success(lang('success delete comment'));
			ajx_current("reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete comment'));
			ajx_current("empty");
		} // try

	} // delete
	
	
	function view() {
		$comment = Comments::findById(get_id());
		if(!($comment instanceof Comment)) {
			flash_error(lang('comment dnx'));
			ajx_current("empty");
			return;
		}

		if(!$comment->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$object = $comment->getRelObject();
		if (!$object instanceof ContentDataObject) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		}
		
		redirect_to($object->getViewUrl());		
	}

} // CommentController

?>