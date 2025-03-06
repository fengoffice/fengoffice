<?php
function recalculate_members_custom_display_names($ot_ids) {
		foreach ($ot_ids as $ot_id) {
			$members = Members::instance()->findAll(array("conditions" => 'object_type_id='.$ot_id));
			foreach ($members as $m) {
				$display_name = build_member_display_name($m);
				$m->setColumnValue('display_name', $display_name);
				$m->save();
			}
		}
	}