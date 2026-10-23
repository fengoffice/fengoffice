<?php
function recalculate_members_custom_display_names($ot_ids) {
    foreach ($ot_ids as $ot_key) {
        $object_subtype_id = 0;
        $object_type_id = $ot_key;

        if (str_starts_with($ot_key, 'ostId_')) {
            if (!Plugins::instance()->isActivePlugin('object_subtypes')) {
                continue;
            }
            $object_subtype_id = intval(substr($ot_key, strlen('ostId_')));
            $subtype = ObjectSubTypes::instance()->findOne([
                'conditions' => ['id=?', $object_subtype_id]
            ]);
            if (!$subtype instanceof ObjectSubtype) {
                continue;
            }
            $object_type_id = $subtype->getObjectTypeId();
        }

        $members = Members::instance()->findAll([
            "conditions" => 'object_type_id=' . $object_type_id
        ]);

        foreach ($members as $m) {
            $display_name = build_member_display_name($m);
            $m->setColumnValue('display_name', $display_name);
            $m->save();
        }
    }
}
