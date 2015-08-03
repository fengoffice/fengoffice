App.modules.massmailerForm = {
  
  /**
   * Array of available controls indexed by company
   */
  controls : {},
  
  /**
   * Handle company checkbox click
   *
   * @param integer company_id
   */
  companyCheckboxClick : function(company_id) {
    var user_ids = App.modules.massmailerForm.controls['company_' + company_id];
    for(var i = 0; i < user_ids.length; i++) {
      Ext.getDom('massmailerFormCompanyUser' + user_ids[i]).checked = Ext.getDom('massmailerFormCompany' + company_id).checked;
    } // for
  },
  
  /**
   * Handle user checkbox click
   *
   * @param integer company_id
   * @param integer user_id
   */
  userCheckboxClick : function(company_id, user_id) {
    var user_ids = App.modules.massmailerForm.controls['company_' + company_id];
    var all_checked = true;
    for(var i = 0; i < user_ids.length; i++) {
      if(!Ext.getDom('massmailerFormCompanyUser' + user_ids[i]).checked) {
        all_checked = false;
      } // if
    } // for
    Ext.getDom('massmailerFormCompany' + company_id).checked = all_checked;
  }
  
};