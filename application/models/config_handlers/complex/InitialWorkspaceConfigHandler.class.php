<?php

  /**
  * Class that handles integer config values
  *
  * @version 1.0
  * @author Carlos Palma <chonwil@gmail.com>
  */
  class InitialWorkspaceConfigHandler extends ConfigHandler {
    
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      return render_initial_workspace_chooser($control_name, $this->getValue());
    } // render
    
  } // InitialWorkspaceConfigHandler

?>