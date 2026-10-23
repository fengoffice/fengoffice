<?php

  /**
  * Renders one dropdown per manageable dimension to choose what to do with the subtasks of a
  * task when its classification in that dimension changes.
  *
  * The value is stored as a JSON object keyed by dimension id, so dimensions added later fall
  * back to the default mode instead of silently taking the one of another dimension.
  *
  * @version 1.0
  */
  class SubtaskClassificationConfigHandler extends ConfigHandler {

    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $genid = gen_id();
      $modes = $this->getValue();
      if (!is_array($modes)) $modes = array();

      $dimensions = Dimensions::instance()->findAll(array('conditions' => '`is_manageable` = 1'));
      $enabled_dimension_ids = config_option('enabled_dimensions');

      // dimensions this config option must not offer, taken from its own options column
      $skipped_dimension_codes = array();
      $options_json = $this->getConfigOption()->getOptions();
      if ($options_json != "") {
        $options = json_decode($options_json);
        if (isset($options->skipped_dimension_codes) && is_array($options->skipped_dimension_codes)) {
          $skipped_dimension_codes = $options->skipped_dimension_codes;
        }
      }

      $mode_labels = array(
        SUBTASK_CLASSIFICATION_NEVER => lang('subtask classification never'),
        SUBTASK_CLASSIFICATION_IF_EMPTY => lang('subtask classification if empty'),
        SUBTASK_CLASSIFICATION_ALWAYS => lang('subtask classification always'),
      );

      $permission_group_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(), false);

      $out = '<div class="select-config-options">';
      foreach ($dimensions as $dim) { /* @var $dim Dimension */
        if (!in_array($dim->getId(), $enabled_dimension_ids)) continue;
        if (in_array($dim->getCode(), $skipped_dimension_codes)) continue;
        if ($dim->getDefinesPermissions() && $dim->deniesAllForContact($permission_group_ids)) continue;

        $selected_mode = array_var($modes, $dim->getId(), SUBTASK_CLASSIFICATION_DEFAULT);
        $option_id = $genid . '_' . $control_name . '_' . $dim->getId();

        $mode_options = array();
        foreach ($mode_labels as $mode => $label) {
          $attributes = $selected_mode == $mode ? array('selected' => 'selected') : null;
          $mode_options[] = option_tag($label, $mode, $attributes);
        }

        $out .= '<div class="select-config-option" style="margin-bottom:4px;">';
        $label_attributes = array('style' => 'display:inline-block;min-width:220px;');
        $out .= label_tag($dim->getName(), $option_id, false, $label_attributes, '');
        $out .= select_box($control_name . '[' . $dim->getId() . ']', $mode_options, array('id' => $option_id));
        $out .= '</div>';
      }
      $out .= '</div>';

      return $out;
    } // render


    /**
    * Convert raw value to php
    *
    * @param string $value
    * @return array Mode by dimension id
    */
    function rawToPhp($value) {
      $modes = is_string($value) && trim($value) != '' ? json_decode($value, true) : array();
      return is_array($modes) ? $modes : array();
    } // rawToPhp


    /**
    * Convert the posted value to the raw value to store
    *
    * The posted modes are merged over the stored ones instead of replacing them: the form only
    * renders the dimensions enabled for the installation, so a dimension that is not rendered
    * keeps the mode it has instead of silently falling back to the default.
    *
    * @param array $value Mode by dimension id, as posted by the form
    * @return string
    */
    function phpToRaw($value) {
      if (!is_array($value)) return $value;

      $valid_modes = array(
        SUBTASK_CLASSIFICATION_NEVER,
        SUBTASK_CLASSIFICATION_IF_EMPTY,
        SUBTASK_CLASSIFICATION_ALWAYS,
      );

      // getRawValue() still holds the stored value at this point: setRawValue() runs with what
      // this method returns
      $modes = $this->getValue();
      if (!is_array($modes)) $modes = array();

      foreach ($value as $dimension_id => $mode) {
        if ((int) $dimension_id > 0 && in_array($mode, $valid_modes)) {
          $modes[(int) $dimension_id] = $mode;
        }
      }

      return json_encode($modes);
    } // phpToRaw

  } // SubtaskClassificationConfigHandler
