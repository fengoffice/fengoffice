<?php
/*

ods-php a library to read and write ods files from php.

This library has been forked from eyeOS project and licended under the LGPL3
terms available at: http://www.gnu.org/licenses/lgpl-3.0.txt (relicenced
with permission of the copyright holders)

Copyright: Juan Lao Tebar (juanlao@eyeos.org) and Jose Carlos Norte (jose@eyeos.org) - 2008

https://sourceforge.net/projects/ods-php/

*/

class PHPOds {
	var $fonts;
	var $styles;
	var $sheets;
	var $lastElement;
	var $fods;
	var $currentSheet;
	var $currentRow;
	var $currentCell;
	var $lastRowAtt;
	var $repeat;
	
	var $stylesNew= array();
	var $valuesNew= array();

	function PHPOds() {


		$content = '<?xml version="1.0" encoding="UTF-8"?>
	<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" office:version="1.0"><office:scripts/><office:font-face-decls><style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/><style:font-face style:name="DejaVu Sans" svg:font-family="&apos;DejaVu Sans&apos;" style:font-family-generic="system" style:font-pitch="variable"/></office:font-face-decls><office:automatic-styles><style:style style:name="co1" style:family="table-column"><style:table-column-properties fo:break-before="auto" style:column-width="2.267cm"/></style:style><style:style style:name="ro1" style:family="table-row"><style:table-row-properties style:row-height="0.453cm" fo:break-before="auto" style:use-optimal-row-height="true"/></style:style><style:style style:name="ta1" style:family="table" style:master-page-name="Default"><style:table-properties table:display="true" style:writing-mode="lr-tb"/></style:style></office:automatic-styles><office:body><office:spreadsheet><table:table table:name="Hoja1" table:style-name="ta1" table:print="false"><office:forms form:automatic-focus="false" form:apply-design-mode="false"/><table:table-column table:style-name="co1" table:default-cell-style-name="Default"/><table:table-row table:style-name="ro1"><table:table-cell/></table:table-row></table:table><table:table table:name="Hoja2" table:style-name="ta1" table:print="false"><table:table-column table:style-name="co1" table:default-cell-style-name="Default"/><table:table-row table:style-name="ro1"><table:table-cell/></table:table-row></table:table><table:table table:name="Hoja3" table:style-name="ta1" table:print="false"><table:table-column table:style-name="co1" table:default-cell-style-name="Default"/><table:table-row table:style-name="ro1"><table:table-cell/></table:table-row></table:table></office:spreadsheet></office:body></office:document-content>';

		$this->styles = array();
		$this->fonts = array();
		$this->sheets = array();
		$this->currentRow = 0;
		$this->currentSheet = 0;
		$this->currentCell = 0;
		$this->repeat = 0;
		$this->parse($content);

	}

	function parse($data) {
		$xml_parser = xml_parser_create();
		xml_set_object ( $xml_parser, $this );
		xml_set_element_handler($xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($xml_parser, "characterData");

		xml_parse($xml_parser, $data, strlen($data));

		xml_parser_free($xml_parser);
	}

	function array2ods() {
		$fontArray = $this->fonts;
		$styleArray = $this->stylesNew;
		$sheetArray = $this->sheets;
		// Header
		$string = '<?xml version="1.0" encoding="UTF-8"?><office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" office:version="1.0">';

		// ToDo: scripts
		$string .= '<office:scripts/>';

		// Fonts
		$string .= '<office:font-face-decls>';
//		foreach ($fontArray as $fontName => $fontAttribs) {
//			$string .= '<style:font-face ';
//			foreach ($fontAttribs as $attrName => $attrValue) {
//				$string .= strtolower($attrName) . '="' . $attrValue . '" ';
//			}
//			$string .= '/>';
//		}

$string .= '<style:font-face style:name="Lucida Sans Console" svg:font-family="Lucida Sans Console" style:font-family-generic="swiss"/>';
$string .= '<style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="swiss"/>';
$string .= '<style:font-face style:name="Courier" svg:font-family="Courier" style:font-family-generic="modern" style:font-pitch="fixed"/>';
$string .= '<style:font-face style:name="Times New Roman" svg:font-family="Times New Roman" style:font-family-generic="roman" style:font-pitch="variable"/>';
$string .= '<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>';
$string .= '<style:font-face style:name="Verdana" svg:font-family="Verdana" style:font-family-generic="swiss" style:font-pitch="variable"/>';
$string .= '<style:font-face style:name="DejaVu Sans" svg:font-family="DejaVu Sans" style:font-family-generic="system" style:font-pitch="variable"/>';

		
		
		$string .= '</office:font-face-decls>';

		// Styles
		$string .= '<office:automatic-styles>';
		
		$string .= "<style:style style:name='co1' style:family='table-column'>
		<style:table-column-properties fo:break-before='auto' style:column-width='0.8925in'/>
		</style:style>";

		$string .= "<style:style style:name='ro1' style:family='table-row'>
		<style:table-row-properties style:row-height='0.178in' fo:break-before='auto' style:use-optimal-row-height='true'/>
		</style:style>";		
		
		$string .= "<style:style style:name='ta1' style:family='table' style:master-page-name='Default'>
		<style:table-properties table:display='true' style:writing-mode='lr-tb'/>
		</style:style>";

		foreach($styleArray as $styleName => $styleAttribs){
			
			
			
				$string .= "<style:style style:name='$styleName' style:family='table-cell' style:parent-style-name='Default'>";
				
				$string .= $styleAttribs;
						
				$string .= '</style:style>';
					
			
		} 
		
		
		
//		foreach ($styleArray as $styleName => $styleAttribs) {
//			$string .= '<style:style ';
//			foreach ($styleAttribs['attrs'] as $attrName => $attrValue) {
//				$string .= strtolower($attrName) . '="' . $attrValue . '" ';
//			}
//			$string .= '>';
//
//			// Subnodes
//			foreach ($styleAttribs['styles'] as $nodeName => $nodeTree) {
//				$string .= '<' . $nodeName . ' ';
//				foreach ($nodeTree as $attrName => $attrValue) {
//					$string .= strtolower($attrName) . '="' . $attrValue . '" ';
//				}
//				$string .= '/>';
//			}
//
//			$string .= '</style:style>';
//		}
		$string .= '</office:automatic-styles>';

		// Body
		$string .= '<office:body>';
		$string .= '<office:spreadsheet>';
		foreach ($sheetArray as $tableIndex => $tableContent) {
			
			$sheetNumber= $tableIndex +1;
			$string .= '<table:table table:name= "Sheet '. $sheetNumber .'"  table:style-name="ta1" table:print="false">';
			//$string .= '<office:forms form:automatic-focus="false" form:apply-design-mode="false"/>';
						
			$currentRow= 1;
			foreach ($tableContent['rows'] as $rowIndex => $rowContent) {
				
				$string.= armarEmptyRow($currentRow, $rowIndex);
				$currentRow= $rowIndex;
				
				
				$string .= '<table:table-row table:style-name="ro1">';				
				
								$currentCell=-1;
								
								foreach($rowContent as $cellIndex => $cellContent) {
									
									$string.= armarEmptyCells($currentCell, $cellIndex);
									$currentCell= $cellIndex; 
									
									$style_name= "ce".$rowIndex.$cellIndex;
									$string .= '<table:table-cell table:style-name="'.$style_name.'" ';				
									
									foreach ($cellContent['attrs'] as $attrName => $attrValue) {
										$string .= strtolower($attrName) . '="' . $attrValue . '" ';
									}
									$string .= '>';
				
									if (isset($cellContent['value'])) {
										$string .= '<text:p>' . $cellContent['value'] . '</text:p>';
									}
				
									$string .= '</table:table-cell>';
								}

				$string .= '</table:table-row>';
			}

			$string .= '</table:table>';
		}

		$string .= '</office:spreadsheet>';
		$string .= '</office:body>';

		// Footer
		$string .= '</office:document-content>';

		return $string;
	}

	function startElement($parser, $tagName, $attrs) {
		$cTagName = strtolower($tagName);
		if($cTagName == 'style:font-face') {
			$this->fonts[$attrs['STYLE:NAME']] = $attrs;
		} elseif($cTagName == 'style:style') {
			$this->lastElement = $attrs['STYLE:NAME'];
			$this->styles[$this->lastElement]['attrs'] = $attrs;
		} elseif($cTagName == 'style:table-column-properties' || $cTagName == 'style:table-row-properties'
			|| $cTagName == 'style:table-properties' || $cTagName == 'style:text-properties') {
			$this->styles[$this->lastElement]['styles'][$cTagName] = $attrs;
		} elseif($cTagName == 'table:table-cell') {
			$this->lastElement = $cTagName;
			$this->sheets[$this->currentSheet]['rows'][$this->currentRow][$this->currentCell]['attrs'] = $attrs;
			if(isset($attrs['TABLE:NUMBER-COLUMNS-REPEATED'])) {
				$times = intval($attrs['TABLE:NUMBER-COLUMNS-REPEATED']);
				$times--;
				for($i=1;$i<=$times;$i++) {
					$cnum = $this->currentCell+$i;
					$this->sheets[$this->currentSheet]['rows'][$this->currentRow][$cnum]['attrs'] = $attrs;
				}
				$this->currentCell += $times;
				$this->repeat = $times;
			}
			if(isset($this->lastRowAtt['TABLE:NUMBER-ROWS-REPEATED'])) {
				$times = intval($this->lastRowAtt['TABLE:NUMBER-ROWS-REPEATED']);
				$times--;
				for($i=1;$i<=$times;$i++) {
					$cnum = $this->currentRow+$i;
					$this->sheets[$this->currentSheet]['rows'][$cnum][$i-1]['attrs'] = $attrs;
				}
				$this->currentRow += $times;
			}
		} elseif($cTagName == 'table:table-row') {
			$this->lastRowAtt = $attrs;
		}
	}

	function endElement($parser, $tagName) {
		$cTagName = strtolower($tagName);
		if($cTagName == 'table:table') {
			$this->currentSheet++;
			$this->currentRow = 0;
		} elseif($cTagName == 'table:table-row') {
			$this->currentRow++;
			$this->currentCell = 0;
		} elseif($cTagName == 'table:table-cell') {
			$this->currentCell++;
			$this->repeat = 0;
		}
	}

	function characterData($parser, $data) {
		if($this->lastElement == 'table:table-cell') {
			$this->sheets[$this->currentSheet]['rows'][$this->currentRow][$this->currentCell]['value'] = $data;
			if($this->repeat > 0) {
				for($i=0;$i<$this->repeat;$i++) {
					$cnum = $this->currentCell - ($i+1);
					$this->sheets[$this->currentSheet]['rows'][$this->currentRow][$cnum]['value'] = $data;
				}
			}
		}
	}

	function getMeta($lang) {
		$myDate = date('Y-m-j\TH:i:s');
		$meta = '<?xml version="1.0" encoding="UTF-8"?>
		<office:document-meta xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:ooo="http://openoffice.org/2004/office" office:version="1.0">
			<office:meta>
				<meta:generator>ods-php</meta:generator>
				<meta:creation-date>'.$myDate.'</meta:creation-date>
				<dc:date>'.$myDate.'</dc:date>
				<dc:language>'.$lang.'</dc:language>
				<meta:editing-cycles>2</meta:editing-cycles>
				<meta:editing-duration>PT15S</meta:editing-duration>
				<meta:user-defined meta:name="Info 1"/>
				<meta:user-defined meta:name="Info 2"/>
				<meta:user-defined meta:name="Info 3"/>
				<meta:user-defined meta:name="Info 4"/>
			</office:meta>
		</office:document-meta>';
		return $meta;
	}

	function getStyle() {
		return '<?xml version="1.0" encoding="UTF-8"?>
				<office:document-styles xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:rdfa="http://docs.oasis-open.org/opendocument/meta/rdfa#" office:version="1.2"><office:font-face-decls><style:font-face style:name="Lucida Sans Console" svg:font-family="&apos;Lucida Sans Console&apos;" style:font-family-generic="swiss"/><style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="swiss"/><style:font-face style:name="Courier" svg:font-family="Courier" style:font-family-generic="modern" style:font-pitch="fixed"/><style:font-face style:name="Times New Roman" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="roman" style:font-pitch="variable"/><style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/><style:font-face style:name="Verdana" svg:font-family="Verdana" style:font-family-generic="swiss" style:font-pitch="variable"/><style:font-face style:name="DejaVu Sans" svg:font-family="&apos;DejaVu Sans&apos;" style:font-family-generic="system" style:font-pitch="variable"/></office:font-face-decls><office:styles><style:default-style style:family="table-cell"><style:table-cell-properties style:decimal-places="2"/><style:paragraph-properties style:tab-stop-distance="0.5in"/><style:text-properties style:font-name="Arial" fo:language="en" fo:country="US" style:font-name-asian="DejaVu Sans" style:language-asian="zxx" style:country-asian="none" style:font-name-complex="DejaVu Sans" style:language-complex="zxx" style:country-complex="none"/></style:default-style><number:number-style style:name="N0"><number:number number:min-integer-digits="1"/></number:number-style><number:currency-style style:name="N104P0" style:volatile="true"><number:currency-symbol number:language="en" number:country="US">$</number:currency-symbol><number:number number:decimal-places="2" number:min-integer-digits="1" number:grouping="true"/></number:currency-style><number:currency-style style:name="N104"><style:text-properties fo:color="#ff0000"/><number:text>-</number:text><number:currency-symbol number:language="en" number:country="US">$</number:currency-symbol><number:number number:decimal-places="2" number:min-integer-digits="1" number:grouping="true"/><style:map style:condition="value()&gt;=0" style:apply-style-name="N104P0"/></number:currency-style><style:style style:name="Default" style:family="table-cell"/><style:style style:name="Result" style:family="table-cell" style:parent-style-name="Default"><style:text-properties fo:font-style="italic" style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" fo:font-weight="bold"/></style:style><style:style style:name="Result2" style:family="table-cell" style:parent-style-name="Result" style:data-style-name="N104"/><style:style style:name="Heading" style:family="table-cell" style:parent-style-name="Default"><style:table-cell-properties style:text-align-source="fix" style:repeat-content="false"/><style:paragraph-properties fo:text-align="center"/><style:text-properties fo:font-size="16pt" fo:font-style="italic" fo:font-weight="bold"/></style:style><style:style style:name="Heading1" style:family="table-cell" style:parent-style-name="Heading"><style:table-cell-properties style:rotation-angle="90"/></style:style></office:styles><office:automatic-styles><style:page-layout style:name="Mpm1"><style:page-layout-properties style:writing-mode="lr-tb"/><style:header-style><style:header-footer-properties fo:min-height="0.2957in" fo:margin-left="0in" fo:margin-right="0in" fo:margin-bottom="0.0984in"/></style:header-style><style:footer-style><style:header-footer-properties fo:min-height="0.2957in" fo:margin-left="0in" fo:margin-right="0in" fo:margin-top="0.0984in"/></style:footer-style></style:page-layout><style:page-layout style:name="Mpm2"><style:page-layout-properties style:writing-mode="lr-tb"/><style:header-style><style:header-footer-properties fo:min-height="0.2957in" fo:margin-left="0in" fo:margin-right="0in" fo:margin-bottom="0.0984in" fo:border="0.0346in solid #000000" fo:padding="0.0071in" fo:background-color="#c0c0c0"><style:background-image/></style:header-footer-properties></style:header-style><style:footer-style><style:header-footer-properties fo:min-height="0.2957in" fo:margin-left="0in" fo:margin-right="0in" fo:margin-top="0.0984in" fo:border="0.0346in solid #000000" fo:padding="0.0071in" fo:background-color="#c0c0c0"><style:background-image/></style:header-footer-properties></style:footer-style></style:page-layout></office:automatic-styles><office:master-styles><style:master-page style:name="Default" style:page-layout-name="Mpm1"><style:header><text:p><text:sheet-name>???</text:sheet-name></text:p></style:header><style:header-left style:display="false"/><style:footer><text:p>Page <text:page-number>1</text:page-number></text:p></style:footer><style:footer-left style:display="false"/></style:master-page><style:master-page style:name="Report" style:page-layout-name="Mpm2"><style:header><style:region-left><text:p><text:sheet-name>???</text:sheet-name> (<text:title>???</text:title>)</text:p></style:region-left><style:region-right><text:p><text:date style:data-style-name="N2" text:date-value="2009-06-09">06/09/2009</text:date>, <text:time>04:23:25</text:time></text:p></style:region-right></style:header><style:header-left style:display="false"/><style:footer><text:p>Page <text:page-number>1</text:page-number> / <text:page-count>99</text:page-count></text:p></style:footer><style:footer-left style:display="false"/></style:master-page></office:master-styles></office:document-styles>';
	}

	function getSettings() {
		return '<?xml version="1.0" encoding="UTF-8"?>
				<office:document-settings xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:config="urn:oasis:names:tc:opendocument:xmlns:config:1.0" xmlns:ooo="http://openoffice.org/2004/office" office:version="1.2"><office:settings><config:config-item-set config:name="ooo:view-settings"><config:config-item config:name="VisibleAreaTop" config:type="int">449</config:config-item><config:config-item config:name="VisibleAreaLeft" config:type="int">2258</config:config-item><config:config-item config:name="VisibleAreaWidth" config:type="int">11290</config:config-item><config:config-item config:name="VisibleAreaHeight" config:type="int">8351</config:config-item><config:config-item-map-indexed config:name="Views"><config:config-item-map-entry><config:config-item config:name="ViewId" config:type="string">View1</config:config-item><config:config-item-map-named config:name="Tables"><config:config-item-map-entry config:name="Sheet1"><config:config-item config:name="CursorPositionX" config:type="int">8</config:config-item><config:config-item config:name="CursorPositionY" config:type="int">10</config:config-item><config:config-item config:name="HorizontalSplitMode" config:type="short">0</config:config-item><config:config-item config:name="VerticalSplitMode" config:type="short">0</config:config-item><config:config-item config:name="HorizontalSplitPosition" config:type="int">0</config:config-item><config:config-item config:name="VerticalSplitPosition" config:type="int">0</config:config-item><config:config-item config:name="ActiveSplitRange" config:type="short">2</config:config-item><config:config-item config:name="PositionLeft" config:type="int">0</config:config-item><config:config-item config:name="PositionRight" config:type="int">0</config:config-item><config:config-item config:name="PositionTop" config:type="int">0</config:config-item><config:config-item config:name="PositionBottom" config:type="int">0</config:config-item><config:config-item config:name="ZoomType" config:type="short">0</config:config-item><config:config-item config:name="ZoomValue" config:type="int">100</config:config-item><config:config-item config:name="PageViewZoomValue" config:type="int">60</config:config-item><config:config-item config:name="ShowGrid" config:type="boolean">true</config:config-item></config:config-item-map-entry></config:config-item-map-named><config:config-item config:name="ActiveTable" config:type="string">Sheet1</config:config-item><config:config-item config:name="HorizontalScrollbarWidth" config:type="int">270</config:config-item><config:config-item config:name="ZoomType" config:type="short">0</config:config-item><config:config-item config:name="ZoomValue" config:type="int">100</config:config-item><config:config-item config:name="PageViewZoomValue" config:type="int">60</config:config-item><config:config-item config:name="ShowPageBreakPreview" config:type="boolean">false</config:config-item><config:config-item config:name="ShowZeroValues" config:type="boolean">true</config:config-item><config:config-item config:name="ShowNotes" config:type="boolean">true</config:config-item><config:config-item config:name="ShowGrid" config:type="boolean">true</config:config-item><config:config-item config:name="GridColor" config:type="long">12632256</config:config-item><config:config-item config:name="ShowPageBreaks" config:type="boolean">true</config:config-item><config:config-item config:name="HasColumnRowHeaders" config:type="boolean">true</config:config-item><config:config-item config:name="HasSheetTabs" config:type="boolean">true</config:config-item><config:config-item config:name="IsOutlineSymbolsSet" config:type="boolean">true</config:config-item><config:config-item config:name="IsSnapToRaster" config:type="boolean">false</config:config-item><config:config-item config:name="RasterIsVisible" config:type="boolean">false</config:config-item><config:config-item config:name="RasterResolutionX" config:type="int">1270</config:config-item><config:config-item config:name="RasterResolutionY" config:type="int">1270</config:config-item><config:config-item config:name="RasterSubdivisionX" config:type="int">1</config:config-item><config:config-item config:name="RasterSubdivisionY" config:type="int">1</config:config-item><config:config-item config:name="IsRasterAxisSynchronized" config:type="boolean">true</config:config-item></config:config-item-map-entry></config:config-item-map-indexed></config:config-item-set><config:config-item-set config:name="ooo:configuration-settings"><config:config-item config:name="ShowZeroValues" config:type="boolean">true</config:config-item><config:config-item config:name="ShowNotes" config:type="boolean">true</config:config-item><config:config-item config:name="ShowGrid" config:type="boolean">true</config:config-item><config:config-item config:name="GridColor" config:type="long">12632256</config:config-item><config:config-item config:name="ShowPageBreaks" config:type="boolean">true</config:config-item><config:config-item config:name="LinkUpdateMode" config:type="short">3</config:config-item><config:config-item config:name="HasColumnRowHeaders" config:type="boolean">true</config:config-item><config:config-item config:name="HasSheetTabs" config:type="boolean">true</config:config-item><config:config-item config:name="IsOutlineSymbolsSet" config:type="boolean">true</config:config-item><config:config-item config:name="IsSnapToRaster" config:type="boolean">false</config:config-item><config:config-item config:name="RasterIsVisible" config:type="boolean">false</config:config-item><config:config-item config:name="RasterResolutionX" config:type="int">1270</config:config-item><config:config-item config:name="RasterResolutionY" config:type="int">1270</config:config-item><config:config-item config:name="RasterSubdivisionX" config:type="int">1</config:config-item><config:config-item config:name="RasterSubdivisionY" config:type="int">1</config:config-item><config:config-item config:name="IsRasterAxisSynchronized" config:type="boolean">true</config:config-item><config:config-item config:name="AutoCalculate" config:type="boolean">true</config:config-item><config:config-item config:name="PrinterName" config:type="string"/><config:config-item config:name="PrinterSetup" config:type="base64Binary"/><config:config-item config:name="ApplyUserData" config:type="boolean">true</config:config-item><config:config-item config:name="CharacterCompressionType" config:type="short">0</config:config-item><config:config-item config:name="IsKernAsianPunctuation" config:type="boolean">false</config:config-item><config:config-item config:name="SaveVersionOnClose" config:type="boolean">false</config:config-item><config:config-item config:name="UpdateFromTemplate" config:type="boolean">true</config:config-item><config:config-item config:name="AllowPrintJobCancel" config:type="boolean">true</config:config-item><config:config-item config:name="LoadReadonly" config:type="boolean">false</config:config-item><config:config-item config:name="IsDocumentShared" config:type="boolean">false</config:config-item></config:config-item-set></office:settings></office:document-settings>';
	}

	function getManifest() {
		return '<?xml version="1.0" encoding="UTF-8"?>
<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">
 <manifest:file-entry manifest:media-type="application/vnd.oasis.opendocument.spreadsheet" manifest:full-path="/"/>
 <manifest:file-entry manifest:media-type="" manifest:full-path="Configurations2/statusbar/"/>
 <manifest:file-entry manifest:media-type="" manifest:full-path="Configurations2/accelerator/"/>
 <manifest:file-entry manifest:media-type="" manifest:full-path="Configurations2/floater/"/>
 <manifest:file-entry manifest:media-type="" manifest:full-path="Configurations2/popupmenu/"/>
 <manifest:file-entry manifest:media-type="" manifest:full-path="Configurations2/progressbar/"/>
 <manifest:file-entry manifest:media-type="" manifest:full-path="Configurations2/menubar/"/>
 <manifest:file-entry manifest:media-type="" manifest:full-path="Configurations2/toolbar/"/>
 <manifest:file-entry manifest:media-type="" manifest:full-path="Configurations2/images/Bitmaps/"/>
 <manifest:file-entry manifest:media-type="" manifest:full-path="Configurations2/images/"/>
 <manifest:file-entry manifest:media-type="application/vnd.sun.xml.ui.configuration" manifest:full-path="Configurations2/"/>
 <manifest:file-entry manifest:media-type="text/xml" manifest:full-path="content.xml"/>
 <manifest:file-entry manifest:media-type="text/xml" manifest:full-path="styles.xml"/>
 <manifest:file-entry manifest:media-type="text/xml" manifest:full-path="meta.xml"/>
 <manifest:file-entry manifest:media-type="" manifest:full-path="Thumbnails/"/>
 <manifest:file-entry manifest:media-type="text/xml" manifest:full-path="settings.xml"/>
</manifest:manifest>';
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $sheet
	 * @param unknown_type $row
	 * @param unknown_type $cell
	 * @param unknown_type $value
	 * @param unknown_type $type (ACCEPTED VALUES: string, date, boolean, float, time)
	 */
	function addCell($sheet,$row,$cell,$value,$type) {
		$this->sheets[$sheet]['rows'][$row][$cell]['attrs'] = array('OFFICE:VALUE-TYPE'=>$type,'OFFICE:VALUE'=>$value);
		$this->sheets[$sheet]['rows'][$row][$cell]['value'] = $value;
	}

	function editCell($sheet,$row,$cell,$value) {
		$this->sheets[$sheet]['rows'][$row][$cell]['attrs']['OFFICE:VALUE'] = $value;
		$this->sheets[$sheet]['rows'][$row][$cell]['value'] = $value;
	}
	
	function addStyle($fontStyle, $cell){
		
		$celda= new Cell();
		$celda= $cell;
		
		$styleName= "ce".$celda->getDataRow().$celda->getDataColumn();
		
		$this->stylesNew[$styleName]= armarStyleODF($fontStyle);
				
	}
	
}



function armarStyleODF($fontStyle){
	
//	$celda= new Cell();
//	$celda= $cell;
//	
//	$fontStyle= new FontStyle();
//	$fontId= $celda->getFontStyleId();
//	$fontStyle= $book->getFontStyle($fontId);
	$fontName= $fontStyle->getFontName();
	

	$str= '';
	
	switch ($fontStyle->fontHAlign){
		
		case 0:

			switch ($fontStyle->fontVAlign){
				
				case 0:
					$str= '<style:table-cell-properties style:text-align-source="fix" style:repeat-content="false" />';
					$str.= '<style:paragraph-properties fo:text-align="justify" fo:margin-left="0in"/>';					
					break;
				case 1:
					$str= '<style:table-cell-properties style:text-align-source="fix" style:repeat-content="false" style:vertical-align="middle"/>';
					$str.= '<style:paragraph-properties fo:text-align="justify" fo:margin-left="0in"/>';					
					break;
				case 2:
					$str= '<style:table-cell-properties style:text-align-source="fix" style:repeat-content="false" style:vertical-align="top"/>';
					$str.= '<style:paragraph-properties fo:text-align="justify" fo:margin-left="0in"/>';					
					break;				
			}			
			break;
		case 1:
			switch ($fontStyle->fontVAlign){
				
				case 1:
					$str= '<style:table-cell-properties style:vertical-align="middle"/>';
					break;
				case 2:
					$str= '<style:table-cell-properties style:vertical-align="top"/>';
					break;
			}			
			
			break;
		case 2:
			switch ($fontStyle->fontVAlign){
				
				case 0:
					$str= '<style:table-cell-properties style:text-align-source="fix" style:repeat-content="false"/>';
					$str.= '<style:paragraph-properties fo:text-align="center" fo:margin-left="0in"/>';
					break;
				case 1:
					$str= '<style:table-cell-properties style:text-align-source="fix" style:repeat-content="false" style:vertical-align="middle"/>';
					$str.= '<style:paragraph-properties fo:text-align="center" fo:margin-left="0in"/>';
					break;
				case 2:
					$str= '<style:table-cell-properties style:text-align-source="fix" style:repeat-content="false" style:vertical-align="top"/>';
					$str.= '<style:paragraph-properties fo:text-align="center" fo:margin-left="0in"/>';
					break;				
			}			
			
			break;
		case 3:

			switch ($fontStyle->fontVAlign){
				
				case 0:
					$str= '<style:table-cell-properties style:text-align-source="fix" style:repeat-content="false"/>';
					$str.= '<style:paragraph-properties fo:text-align="end" fo:margin-left="0in"/>';
					break;
				case 1:
					$str= '<style:table-cell-properties style:text-align-source="fix" style:repeat-content="false" style:vertical-align="middle"/>';
					$str.= '<style:paragraph-properties fo:text-align="end" fo:margin-left="0in"/>';
					break;
				case 2:
					$str= '<style:table-cell-properties style:text-align-source="fix" style:repeat-content="false" style:vertical-align="top"/>';
					$str.= '<style:paragraph-properties fo:text-align="end" fo:margin-left="0in"/>';
					break;
			}			
			
			break;								
	}

		
	$str.= "<style:text-properties ";
	
	$str.= "fo:color= '".$fontStyle->getFontColor()."' ";
	
	$str.= "style:font-name='$fontName' ";
	
	$str.= "fo:font-size= '" .$fontStyle->getFontSize()."pt' ";
	
	if ($fontStyle->getFontItalic() == 1)
		$str.= "fo:font-style= 'italic' ";
			
	if ($fontStyle->getFontBold() == 1)
		$str.= "fo:font-weight= 'bold' ";
		
	$str.= "style:font-size-asian= '".$fontStyle->getFontSize()."pt' ";
	
	if ($fontStyle->getFontItalic() == 1)
		$str.= "style:font-style-asian= 'italic' ";
			
	if ($fontStyle->getFontBold() == 1)
		$str.= "style:font-weight-asian= 'bold' ";
				
	$str.= "style:font-size-complex= '".$fontStyle->getFontSize()."pt' ";
	
	if ($fontStyle->getFontItalic() == 1)
		$str.= "style:font-style-complex= 'italic' ";
			
	if ($fontStyle->getFontBold() == 1)
		$str.= "style:font-weight-complex= 'bold' ";
		
	$str.="/>";
		
	
	
	return $str;
	/*

							switch ($fontStyle->fontHAlign){
								
								case 0:									
									$HzAlign= PHPExcel_Style_Alignment::HORIZONTAL_GENERAL;
									break;
								case 1:									
									$HzAlign= PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
									break;
								case 2:									
									$HzAlign= PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
									break;
								case 3:									
									$HzAlign= PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
									break;								
							}






<style:text-properties fo:color="#ff3333" 
fo:font-size="16pt" 
fo:font-style="italic" 
fo:font-weight="bold" 
style:font-size-asian="16pt" 
style:font-style-asian="italic" 
style:font-weight-asian="bold" 
style:font-size-complex="16pt" 
style:font-style-complex="italic" 
style:font-weight-complex="bold"/>



 */
	
	
	
}


function armarEmptyCells($currentCell, $cellIndex){
	
	$str_aux= null;
	
	//cho "$currentCell - $cellIndex\n";
	//if ($currentCell == 0) $currentCell--;

	for ($i= $currentCell; $i <$cellIndex -1; $i++)
		$str_aux.= "<table:table-cell/>";
	
		return $str_aux;
}

function  armarEmptyRow($currentRow, $rowIndex){
	
	$str_aux= null;
	
	//cho "$currentCell - $cellIndex\n";

	for ($i= $currentRow; $i <$rowIndex -1 ; $i++)
		$str_aux.= "<table:table-row table:style-name=\"ro1\"/>";
	
		return $str_aux;
	
}


//END OF CLASS

function parseOds($file) {
	$tmp = get_tmp_dir();
	copy($file,$tmp.'/'.basename($file));
	$path = $tmp.'/'.basename($file);
	$uid = uniqid();
	mkdir($tmp.'/'.$uid);
	shell_exec('unzip '.escapeshellarg($path).' -d '.escapeshellarg($tmp.'/'.$uid));
	$obj = new PHPOds();
	$obj->parse(file_get_contents($tmp.'/'.$uid.'/content.xml'));
	return $obj;
}

function saveOds($obj,$file) {


	$charset = ini_get('default_charset');
	//ini_set('default_charset', 'UTF-8');
	global $cnf;
	$current= $cnf['path']['Temp'];
	$tmp= "";

	file_put_contents($current.$tmp.'/content.xml',$obj->array2ods());
	file_put_contents($current.$tmp.'/mimetype','application/vnd.oasis.opendocument.spreadsheet');
	file_put_contents($current.$tmp.'/meta.xml',$obj->getMeta('es-ES'));
	file_put_contents($current.$tmp.'/styles.xml',$obj->getStyle());
	file_put_contents($current.$tmp.'/settings.xml',$obj->getSettings());
	mkdir($current.$tmp.'/META-INF/');
	mkdir($current.$tmp.'/Configurations2/');
	mkdir($current.$tmp.'/Configurations2/accelerator/');
	mkdir($current.$tmp.'/Configurations2/images/');
	mkdir($current.$tmp.'/Configurations2/popupmenu/');
	mkdir($current.$tmp.'/Configurations2/statusbar/');
	mkdir($current.$tmp.'/Configurations2/floater/');
	mkdir($current.$tmp.'/Configurations2/menubar/');
	mkdir($current.$tmp.'/Configurations2/progressbar/');
	mkdir($current.$tmp.'/Configurations2/toolbar/');
	file_put_contents($current.$tmp.'/META-INF/manifest.xml',$obj->getManifest());
	file_put_contents($current.$tmp.'/Configurations2/accelerator/current.xml', "");
	//shell_exec('cd '.$tmp.'/'.$uid.';zip -r '.escapeshellarg($file).' ./');

/*
	if (stripos($_SERVER['HTTP_USER_AGENT'], 'win') !== FALSE) {
	// client is using a windows browser
	
		
	} else {
	// he is using Un*x based system
	
		shell_exec('cd '.$current.$tmp.';zip -r '.escapeshellarg($file).' ./');			
		
	}	
	
		*/
	$zip = new ZipArchive();
	
	$name= tempnam($current.$tmp, "default");
	
	$res= $zip->open($name.".zip", ZIPARCHIVE::CREATE); 
	
	if ($res === TRUE) {		
		
		try{
		
		$zip->addFile(dirname($name).'/Configurations2/accelerator/current.xml', 'Configurations2/accelerator/current.xml');
		$zip->addFile(dirname($name).'/META-INF/manifest.xml', 'META-INF/manifest.xml');

		$zip->addFile(dirname($name).'/content.xml', 'content.xml');
		$zip->addFile(dirname($name).'/styles.xml', 'styles.xml');
		$zip->addFile(dirname($name).'/settings.xml', 'settings.xml');
		$zip->addFile(dirname($name).'/meta.xml', 'meta.xml');
		$zip->addFile(dirname($name).'/mimetype', 'mimetype');
		}
		catch (Exception $e){
			
			echo $e->getTrace();
			
		}
		
		$zip->close();
		
	}

	unlink_dir($current.$tmp.'/Configurations2/');
	unlink_dir($current.$tmp.'/META-INF/');
	unlink($current.$tmp.'/content.xml');
	unlink($current.$tmp.'/styles.xml');
	unlink($current.$tmp.'/settings.xml');
	unlink($current.$tmp.'/meta.xml');
	unlink($current.$tmp.'/mimetype');		
	
	
	//echo 'cp '.$current.'/'.$tmp.'/'.$file.' ' .$current.'/'.$file;
	//shell_exec('cp -f'.$current.'/'.$tmp.'/'.$file.' ' .$current.'/'.$file);

	//copy($current.$file, $current.$file);
	//shell_exec('cd '.$current.'/'.$tmp.';rm -rf *');
	//shell_exec('cd '.$current.'/'.$tmp.';rm -rf *');

	//ini_set('default_charset',$charset);
	
	$filename= basename($name);
	
//	header("Pragma: public");
//	header("Expires: 0");
//	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//	header("Content-Type: application/force-download");
//	header("Content-Type: application/octet-stream");
//	header("Content-Type: application/download");
//	header("Content-Disposition: attachment;filename= $filename.ods");
//	header("Content-Transfer-Encoding: binary ");

	
	    //Begin writing headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
   
    //Use the switch-generated Content-Type
    header("Content-Type: application/vnd.oasis.opendocument.spreadsheet");

    //Force the download
    $header="Content-Disposition: attachment; filename=spreadsheet.ods";
    header($header);
    header("Content-Transfer-Encoding: binary");

	readfile($cnf['path']['Temp'].$filename.".zip");
	unlink($cnf['path']['Temp'].$filename) ;
	unlink($cnf['path']['Temp'].$filename.".zip") ;	
	
	
}

function newOds() {
	/*
	$content = '<?xml version="1.0" encoding="UTF-8"?>
	<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" office:version="1.0"><office:scripts/><office:font-face-decls><style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/><style:font-face style:name="DejaVu Sans" svg:font-family="&apos;DejaVu Sans&apos;" style:font-family-generic="system" style:font-pitch="variable"/></office:font-face-decls><office:automatic-styles><style:style style:name="co1" style:family="table-column"><style:table-column-properties fo:break-before="auto" style:column-width="2.267cm"/></style:style><style:style style:name="ro1" style:family="table-row"><style:table-row-properties style:row-height="0.453cm" fo:break-before="auto" style:use-optimal-row-height="true"/></style:style><style:style style:name="ta1" style:family="table" style:master-page-name="Default"><style:table-properties table:display="true" style:writing-mode="lr-tb"/></style:style></office:automatic-styles><office:body><office:spreadsheet><table:table table:name="Hoja1" table:style-name="ta1" table:print="false"><office:forms form:automatic-focus="false" form:apply-design-mode="false"/><table:table-column table:style-name="co1" table:default-cell-style-name="Default"/><table:table-row table:style-name="ro1"><table:table-cell/></table:table-row></table:table><table:table table:name="Hoja2" table:style-name="ta1" table:print="false"><table:table-column table:style-name="co1" table:default-cell-style-name="Default"/><table:table-row table:style-name="ro1"><table:table-cell/></table:table-row></table:table><table:table table:name="Hoja3" table:style-name="ta1" table:print="false"><table:table-column table:style-name="co1" table:default-cell-style-name="Default"/><table:table-row table:style-name="ro1"><table:table-cell/></table:table-row></table:table></office:spreadsheet></office:body></office:document-content>';
	$obj = new PHPOds();
	$obj->parse($content);
	*/
	return $this;
}

function get_tmp_dir() {
	
	/*
	$path = '';
	if(!function_exists('sys_get_temp_dir')){
		$path = try_get_temp_dir();
	}else{
		$path = sys_get_temp_dir();
		if(is_dir($path)){
			return $path;
		}else{
			$path = try_get_temp_dir();
		}
	}
	return $path;
*/
		global $cnf;
	
		return $cnf['path']['Temp'];
}

function try_get_temp_dir() {
	
	global $cnf;
	
	return $cnf['path']['Temp'];
	
	/*
    // Try to get from environment variable
	if(!empty($_ENV['TMP'])){
		$path = realpath($_ENV['TMP']);
	}else if(!empty($_ENV['TMPDIR'])){
		$path = realpath( $_ENV['TMPDIR'] );
	}else if(!empty($_ENV['TEMP'])){
		$path = realpath($_ENV['TEMP']);
	}
	// Detect by creating a temporary file
	else{
		// Try to use system's temporary directory
		// as random name shouldn't exist
		$temp_file = tempnam(md5(uniqid(rand(),TRUE)),'');
		if ($temp_file){
			$temp_dir = realpath(dirname($temp_file));
			unlink($temp_file);
			$path = $temp_dir;
		}else{
			//return "/tmp";
			echo "no file avaliable";
		}
	}
	return $path;
*/
}


// Function to recursively add a directory,
// sub-directories and files to a zip archive
function addFolderToZip($dir, $zipArchive){
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {

            //Add the directory
            $zipArchive->addEmptyDir($dir);

            // Loop through all the files
            while (($file = readdir($dh)) !== false) {

                //If it's a folder, run the function again!
                if(!is_file($dir . $file)){
                    // Skip parent and root directories
                    if( ($file !== ".") && ($file !== "..")){
                        addFolderToZip($dir . $file . "/", $zipArchive);
                    }

                }else{
                    // Add the files
                    $zipArchive->addFile($dir . $file);

                }
            }
        }
    }
    
    
    
}

/**
 * *
 * deletes a directory 
 * @param string $dir
 */
function unlink_dir($dir)
{
	$dh = @opendir($dir);
	if (!is_resource($dh)) return;
    while (false !== ($obj = readdir($dh))) {
		if($obj == '.' || $obj == '..') continue;
		$path = "$dir/$obj";
		if (is_dir($path)) {
			unlink_dir($path);
		} else {
			@unlink($path);
		}
	}
	@closedir($dh);
	@rmdir($dir);
	
} 

?>