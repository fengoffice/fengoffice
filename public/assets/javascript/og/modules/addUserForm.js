App.modules.addUserForm = {
  
  /**
  * Change state on generate random password checkbox
  *
  * @param void
  * @return null
  */
  generateRandomPasswordClick: function() {
    if(Ext.getDom('userFormRandomPassword').checked) {
      Ext.getDom('userFormPasswordInputs').style.display = 'none';
    } else {
      Ext.getDom('userFormPasswordInputs').style.display = 'block';
    } // if
  },
  
  generateSpecifyPasswordClick: function() {
    if(Ext.getDom('userFormSpecifyPassword').checked) {
      Ext.getDom('userFormPasswordInputs').style.display = 'block';
    } else {
      Ext.getDom('userFormPasswordInputs').style.display = 'none';
    } // if
  }
  
}