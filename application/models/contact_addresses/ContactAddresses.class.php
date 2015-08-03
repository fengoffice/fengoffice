<?php

  /**
  * ContactAddresses
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ContactAddresses extends BaseContactAddresses {
  
    /**
    * Return default contact Address type
    *
    * @access public
    * @param Contact $contact
    * @return AddressType
    */
    function getContactMainAddressType(Contact $contact) {
      
      $contact_address_values_table = ContactAddresss::instance()->getTableName(true);
      $address_types_table = AddressTypes::instance()->getTableName(true);
      
      $sql = "SELECT $address_types_table.* FROM $address_types_table, $contact_address_values_table WHERE $address_types_table.`id` = $contact_address_values_table.`address_type_id` AND $contact_address_values_table.`is_main` = '1' AND $contact_address_values_table.`contact_id` = ?";
      $row = DB::executeOne($sql, $contact->getId());
      if(is_array($row)) {
        return ImTypes::instance()->loadFromRow($row);
      } // if
      
      return null;
      
    } // getContactMainAddressType
    
    /**
    * Return all values by contact
    *
    * @access public
    * @param Contact $contact
    * @return array
    */
    function getByContact(Contact $contact) {
      return self::findAll(array(
        'conditions' => '`contact_id` = ' . DB::escape($contact->getId())
      )); // findAll
    } // getByContact
    
    /**
    * Clear Address values by contact
    *
    * @access public
    * @param Contact $contact
    * @return boolean
    */
    function clearByContact(Contact $contact) {
      return DB::execute('DELETE FROM ' . self::instance()->getTableName(true) . ' WHERE `contact_id` = ?', $contact->getId());
    } // clearByContact
    
    
    /**
    * Return first values by contact
    *
    * @access public
    * @param Contact $contact
    * @param Contact $typeId
    * @param Contact $main
    * @return ContactAddress
    */
    function getAddressByTypeId(Contact $contact,$typeId ,$main = 1 ) {
      return self::findOne(array(
        'conditions' => '`contact_id` = ' . DB::escape($contact->getId()) . 'AND is_main = '. $main. ' AND address_type_id = '.$typeId
      )); // findOne
    } // getAddressByTypeId 
    
  } // getAddressByTypeId 
  


?>