<?php

  /**
  * Date format
  *
  * @version 1.0
  * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
  */
  class DateFormatConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $options = array();
      
      $option_attributes = $this->getValue() == 'd/m/Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('dd/mm/yyyy', 'd/m/Y', $option_attributes);

      $option_attributes = $this->getValue() == 'j/n/Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('d/m/yyyy', 'j/n/Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'm/d/Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('mm/dd/yyyy', 'm/d/Y', $option_attributes);

      $option_attributes = $this->getValue() == 'n/j/Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('m/d/yyyy', 'n/j/Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'Y/m/d' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy/mm/dd', 'Y/m/d', $option_attributes);

      $option_attributes = $this->getValue() == 'Y/n/j' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy/m/d', 'Y/n/j', $option_attributes);
      
      $option_attributes = $this->getValue() == 'd-m-Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('dd-mm-yyyy', 'd-m-Y', $option_attributes);

      $option_attributes = $this->getValue() == 'j-n-Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('d-m-yyyy', 'j-n-Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'm-d-Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('mm-dd-yyyy', 'm-d-Y', $option_attributes);

      $option_attributes = $this->getValue() == 'n-j-Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('m-d-yyyy', 'n-j-Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'Y-m-d' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy-mm-dd', 'Y-m-d', $option_attributes);

      $option_attributes = $this->getValue() == 'Y-n-j' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy-m-d', 'Y-n-j', $option_attributes);
      
      $option_attributes = $this->getValue() == 'd.m.Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('dd.mm.yyyy', 'd.m.Y', $option_attributes);

      $option_attributes = $this->getValue() == 'j.n.Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('d.m.yyyy', 'j.n.Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'm.d.Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('mm.dd.yyyy', 'm.d.Y', $option_attributes);

      $option_attributes = $this->getValue() == 'n.j.Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('m.d.yyyy', 'n.j.Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'Y.m.d' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy.mm.dd', 'Y.m.d', $option_attributes);

      $option_attributes = $this->getValue() == 'Y.n.j' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy.m.d', 'Y.n.j', $option_attributes);
      
      return select_box($control_name, $options);
    } // render
    
    /**
     * 
     * @param type $control_name
     * @return type
     */
    public static function renderExtended($control_name,$selected,$attr) {
      $options = array();
      
      $options[] = option_tag(lang('Select Format'), '');
      
      $option_attributes = 
        $selected == 'd/m/Y' ? 
          array('selected' => 'selected', 'title' => lang('day/month/year with leading zeros')) : 
          array('title' => lang('day/month/year with leading zeros'));
      $options[] = option_tag('dd/mm/yyyy', 'd/m/Y', $option_attributes);
      
      $option_attributes = 
        $selected == 'm/d/Y' ? 
          array('selected' => 'selected', 'title' => lang('month/day/year with leading zeros')) : 
          array('title' => lang('month/day/year with leading zeros'));
      $options[] = option_tag('mm/dd/yyyy', 'm/d/Y', $option_attributes);
      
      $option_attributes = 
        $selected == 'Y/m/d' ? 
          array('selected' => 'selected', 'title' => lang('year/month/day with leading zeros')) : 
          array('title' => lang('year/month/day with leading zeros'));
      $options[] = option_tag('yyyy/mm/dd', 'Y/m/d', $option_attributes);

      $option_attributes = 
        $selected == 'j/n/Y' ? 
          array('selected' => 'selected', 'title' => lang('day/month/year with no leading zeros')) : 
          array('title' => lang('day/month/year with no leading zeros'));
      $options[] = option_tag('d/m/yyyy', 'j/n/Y', $option_attributes);
      
      $option_attributes = 
        $selected == 'n/j/Y' ? 
          array('selected' => 'selected', 'title' => lang('month/day/year with no leading zeros')) : 
          array('title' => lang('month/day/year with no leading zeros'));
      $options[] = option_tag('m/d/yyyy', 'n/j/Y', $option_attributes);
      
      $option_attributes = 
        $selected == 'Y/n/j' ? 
          array('selected' => 'selected', 'title' => lang('year/month/day with no leading zeros')) : 
          array('title' => lang('year/month/day with no leading zeros'));
      $options[] = option_tag('yyyy/m/d', 'Y/n/j', $option_attributes);

      $option_attributes = 
        $selected == 'd-m-Y' ? 
          array('selected' => 'selected', 'title' => lang('day-month-year with leading zeros')) : 
          array('title' => lang('day-month-year with leading zeros'));
      $options[] = option_tag('dd-mm-yyyy', 'd-m-Y', $option_attributes);
      
      $option_attributes = 
        $selected == 'm-d-Y' ? 
          array('selected' => 'selected', 'title' => lang('month-day-year with leading zeros')) : 
          array('title' => lang('month-day-year with leading zeros'));
      $options[] = option_tag('mm-dd-yyyy', 'm-d-Y', $option_attributes);
      
      $option_attributes = 
        $selected == 'Y-m-d' ? 
          array('selected' => 'selected', 'title' => lang('year-month-day with leading zeros')) : 
          array('title' => lang('year-month-day with leading zeros'));
      $options[] = option_tag('yyyy-mm-dd', 'Y-m-d', $option_attributes);
      
      $option_attributes = 
        $selected == 'd.m.Y' ? 
          array('selected' => 'selected', 'title' => lang('day.month.year with leading zeros')) : 
          array('title' => lang('day.month.year with leading zeros'));
      $options[] = option_tag('dd.mm.yyyy', 'd.m.Y', $option_attributes);
      
      $option_attributes = 
        $selected == 'm.d.Y' ? 
        array('selected' => 'selected', 'title' => lang('month.day.year with leading zeros')) : 
        array('title' => lang('month.day.year with leading zeros'));
      $options[] = option_tag('mm.dd.yyyy', 'm.d.Y', $option_attributes);
      
      $option_attributes = 
        $selected == 'Y.m.d' ? 
          array('selected' => 'selected', 'title' => lang('year.month.day with leading zeros')) : 
          array('title' => lang('year.month.day with leading zeros'));
      $options[] = option_tag('yyyy.mm.dd', 'Y.m.d', $option_attributes);

      $option_attributes = 
        $selected == 'd F, Y' ? 
        array('selected' => 'selected', 'title' => lang('Day Month name, Year')) : 
        array('title' => lang('Day Month name, Year'));
      $options[] = option_tag('dd MM,yyyy', 'd F, Y', $option_attributes);

      $option_attributes = 
        $selected == 'F d, Y' ? 
          array('selected' => 'selected', 'title' => lang('Month name Day, Year')) : 
          array('title' => lang('Month name Day, Year'));
      $options[] = option_tag('MM dd,yyyy', 'F d, Y', $option_attributes);

      $option_attributes = 
        $selected == 'Y, F d' ? 
          array('selected' => 'selected', 'title' => lang('Year, Month name Day')) : 
          array('title' => lang('Year, Month name Day'));
      $options[] = option_tag('yyyy, MM dd', 'Y, F d', $option_attributes);

      $option_attributes = 
        $selected == 'd' ? 
          array('selected' => 'selected', 'title' => lang('day')) : 
          array('title' => lang('day'));
      $options[] = option_tag('dd', 'd', $option_attributes);
      
      $option_attributes = 
        $selected == 'm' ? 
          array('selected' => 'selected', 'title' => lang('month')) : 
          array('title' => lang('month'));
      $options[] = option_tag('mm', 'm', $option_attributes);
            
      $option_attributes = 
        $selected == 'Y' ? 
          array('selected' => 'selected', 'title' => lang('year')) : 
          array('title' => lang('year'));
      $options[] = option_tag('yyyy', 'Y', $option_attributes);

      $option_attributes = 
        $selected == 'dmY' ? 
          array('selected' => 'selected', 'title' => lang('DayMonthYear with no separators')) : 
          array('title' => lang('DayMonthYear with no separators'));
      $options[] = option_tag('dmY', 'dmY', $option_attributes);

      $option_attributes = 
        $selected == 'mdY' ? 
          array('selected' => 'selected', 'title' => lang('MonthDayYear with no separators')) : 
          array('title' => lang('MonthDayYear with no separators'));
      $options[] = option_tag('mdY', 'mdY', $option_attributes);

      $option_attributes = 
        $selected == 'Ymd' ? 
          array('selected' => 'selected', 'title' => lang('YearMonthDay with no separators')) : 
          array('title' => lang('YearMonthDay with no separators'));
      $options[] = option_tag('Ymd', 'Ymd', $option_attributes);

      $option_attributes = 
        $selected == 'd M Y' ? 
          array('selected' => 'selected', 'title' => lang('Day Month abbreviation Year')) : 
          array('title' => lang('Day Month abbreviation Year'));
      $options[] = option_tag('d M Y', 'd M Y', $option_attributes);

      $option_attributes = 
        $selected == 'M d Y' ? 
          array('selected' => 'selected', 'title' => lang('Month abbreviation Day Year')) : 
          array('title' => lang('Month abbreviation Day Year'));
      $options[] = option_tag('M d Y', 'M d Y', $option_attributes);

      $option_attributes = 
        $selected == 'Y M d' ? 
          array('selected' => 'selected', 'title' => lang('Year Month abbreviation Day')) : 
          array('title' => lang('Year Month abbreviation Day'));
      $options[] = option_tag('Y M d', 'Y M d', $option_attributes);

      $option_attributes = 
        $selected == 'd M, Y' ? 
          array('selected' => 'selected', 'title' => lang('Day Month abbreviation, Year')) : 
          array('title' => lang('Day Month abbreviation, Year'));
      $options[] = option_tag('d M, Y', 'd M, Y', $option_attributes);

      $option_attributes = 
        $selected == 'M d, Y' ? 
          array('selected' => 'selected', 'title' => lang('Month abbreviation Day, Year')) : 
          array('title' => lang('Month abbreviation Day, Year'));
      $options[] = option_tag('M d, Y', 'M d, Y', $option_attributes);

      $option_attributes = 
        $selected == 'Y, M d' ? 
          array('selected' => 'selected', 'title' => lang('Year, Month abbreviation Day')) : 
          array('title' => lang('Year, Month abbreviation Day'));
      $options[] = option_tag('Y, M d', 'Y, M d', $option_attributes);

      return select_box($control_name, $options,$attr);
    } // render
    
  
  } // DateFormatConfigHandler

?>