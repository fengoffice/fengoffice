<div style="padding:7px">
<div class="contact">
<?php
	if ($contact->hasPicture()){
		$image = '<div class="cardIcon" style="text-align:center;">';
		
		if ($contact->canEdit(logged_user())) {
			$image .= '<a class="internalLink" href="' . $contact->getUpdatePictureUrl() .'" title="' . lang('edit picture') . '">';
		}
		$image .= '<img src="' . $contact->getPictureUrl() .'" alt="'. clean($contact->getObjectName()) .' picture" />';
		
		if ($contact->canEdit(logged_user())) {
			$image .= '</a>';
		}
		
		$image .= '</div>';
		
		tpl_assign("image",$image);
	} else {
		
		if ($contact->canEdit(logged_user())) {
			$image .= '<a class="internalLink" href="' . $contact->getUpdatePictureUrl() .'" title="' . lang('edit picture') . '"><div id="2_iconDiv" class="coViewIconImage ico-large-contact"></div></a>';
			tpl_assign("image",$image);
		}
	}
	
	$description = "";
	$company = $contact->getCompany();
	if ($company instanceof Contact)
		$description = '<a class="internalLink coViewAction ico-company" style="padding-top:0px;" href="' . $company->getCardUrl() . '">' . clean($company->getObjectName()) . '</a>';
	
	if ($contact->getJobTitle() != ''){
		if($description != '')
			$description .= ' | ';
		$description .= clean($contact->getJobTitle());
	}
	
	if ($contact->getDepartment() != ''){
		if($description != ''){
			if ($contact->getJobTitle() != '')
				$description .= ' | ';
			else
				$description .= ' | ';
		}
		$description .= clean($contact->getDepartment());
	}
    
	tpl_assign("description", $description);
	tpl_assign("content_template", array('card_content', 'contact'));
	tpl_assign("object", $contact);
	tpl_assign("title", clean($contact->getObjectName()));
	tpl_assign('iconclass', $contact->isTrashed()? 'ico-large-contact-trashed' :  ($contact->isArchived() ? 'ico-large-contact-archived' : 'ico-large-contact'));
		
  	$this->includeTemplate(get_template_path('view', 'co'));
  	
  	clear_page_actions();
?>

</div>
</div>