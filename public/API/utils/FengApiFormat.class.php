<?php

class FengApiFormat {
	
	static function format_file_list($list, $columns_to_show=null, $obj_template="", $id_prefix="") {
		if (is_null($columns_to_show)) {
			$columns_to_show = array('icon' => '', 'name' => lang('name'), 'size' => lang('size'), 'download_link' => '');
		}
		
		$html = "";
		$html .= "<table class='table_container' id='{$id_prefix}files_container' cellpadding='0' cellspacing='5'>";
		$html .= "<tr class='table_header_container' id='{$id_prefix}table_header_container'>";
		foreach ($columns_to_show as $col => $title) {
			$html .= "<th class='table_header_column' id='{$id_prefix}table_column_$col'>$title</th>";
		}
		$html .= "</tr>";
		
		foreach ($list as $k => $object) {
			$template_url = $obj_template . "?id=" . array_var($object, 'id');
			$html .= "<tr class='table_row' id='{$id_prefix}table_row_$k'>";
			foreach ($columns_to_show as $col => $title) {
				$html .= self::format_files_column_value($col, $object, $template_url);
			}
			$html .= "</tr>";
		}
		$html .= "</table>";

		return $html;
	}
	
	protected function format_files_column_value($col, $object, $template_url) {
		$html = "";
		switch ($col) {
			case 'icon': $html .= "<td class='table_row_value'><img src='".array_var($object, 'icon')."' alt='icon'/></td>"; break;
			case 'icon_l': $html .= "<td class='table_row_value'><img src='".array_var($object, 'icon_l')."' alt='icon'/></td>"; break;
			case 'name': $html .= "<td class='table_row_value'><a href='$template_url'>".array_var($object, 'name')."</a></td>";break;
			case 'size': $html .= "<td class='table_row_value'>".format_filesize(array_var($object, 'size'))."</td>"; break;
			default : $html .= "<td class='table_row_value'>".array_var($object, $col, '')."</td>"; break;
		}
		return $html;
	}
	
	static function format_single_file($object_info, $id_prefix="") {
		$html .= "<table class='file_info_container' id='{$id_prefix}file_info_container' cellpadding='0' cellspacing='5'>";
		
		$html .= "<tr><td><img class='view_file_icon' src='".array_var($object_info, 'icon_l')."' alt='icon' /></td>";
		$html .= "<td><div class='file_name'>".array_var($object_info, 'name')."</div>";
		
		$upd_by = array_var($object_info, 'updated_by') . " - ".array_var($object_info, 'updated_on');
		$html .= "<div class='file_updated'>".lang('modified by').": $upd_by</div></td>";
		$html .= "</tr></table>";
		
		$revisions = array_var($object_info, 'revisions');
		if (is_array($revisions)) {
			$html .= "<div class='revisions_title'>".lang('revisions')."</div>";
			$html .= "<table class='revisions_container' id='{$id_prefix}revisions_container' cellpadding='0' cellspacing='5'>";
			foreach ($revisions as $revision) {
				$html .= "<tr class='revision_info_container'>";
				$html .= "<td class='revision_info_number'>#".array_var($revision, 'number')."</td>";
				$html .= "<td class='revision_info_name'>".array_var($revision, 'created_by')." - ".array_var($revision, 'created_on')."</td>";
				$html .= "<td class='revision_info_download'>".array_var($revision, 'download_link')."</td>";
				if (strlen(array_var($revision, 'comment', '')) > 0)
					$html .= "</tr><tr><td colspan='3' class='revision_info_comment'>".lang('comment').": ".array_var($revision, 'comment')."</td>";
				$html .= "</tr>";
			}
			$html .= "</table>";
		}
		
		return $html;
	}
}
?>