<?php
/*  Gelsheet Project, version 0.0.1 (Pre-alpha)
 *  Copyright (c) 2008 - Ignacio Vazquez, Fernando Rodriguez, Juan Pedro del Campo
 *
 *  Ignacio "Pepe" Vazquez <elpepe22@users.sourceforge.net>
 *  Fernando "Palillo" Rodriguez <fernandor@users.sourceforge.net>
 *  Juan Pedro "Perico" del Campo <pericodc@users.sourceforge.net>
 *
 *  Gelsheet is free distributable under the terms of an GPL license.
 *  For details see: http://www.gnu.org/copyleft/gpl.html
 *
 */

	$cnf['path']['Sheet'] 		= "model/Sheet.class.php";
	$cnf['path']['Book'] 		= "model/Book.class.php";
	$cnf['path']['Row']			= "model/Row.class.php";
	$cnf['path']['Column']		= "model/Column.class.php";
	$cnf['path']['Cell']		= "model/Cell.class.php";
	$cnf['path']['Connection'] 	= "Connection.php";
	$cnf['path']['FontStyle'] 	= "model/FontStyle.class.php";
	$cnf['path']['LayoutStyle'] = "model/LayoutStyle.class.php";
	$cnf['path']['BorderStyle'] = "model/BorderStyle.class.php";
	$cnf['path']['Model'] 		= "model/Model.class.php";

	/*************** CONTROLLER PATHS *********************/
	$cnf['path']['FrontController']			= "controller/FrontController.class.php";
	$cnf['path']['UserController']			= "controller/UserController.class.php";
	$cnf['path']['SpreadsheetController']	= "controller/SpreadsheetController.class.php";
	$cnf['path']['BookController']			= "controller/BookController.class.php";
	$cnf['path']['ExportController']		= "controller/ExportController.class.php";
	$cnf['path']['LanguageController']		= "controller/LanguageController.class.php";
	$cnf['path']['Controller']				= "controller/Controller.class.php";
	$cnf['path']['SecurityController']		= "controller/SecurityController.class.php" ;
	$cnf['path']['OgSecurityController']	= "controller/OgSecurityController.class.php" ;
	

	/*************** COMUNICATION PATHS *****************/
	$cnf['path']['MessageHandler']	= "comm/MessageHandler.php";
	$cnf['path']['Message']	= "comm/Message.php";
	$cnf['path']['GsError']	= "comm/Message.php";
	$cnf['path']['Warning']	= "comm/Message.php";
	$cnf['path']['Success']	= "comm/Message.php";	
	$cnf['path']['Notice']	= "comm/Message.php";	
	$cnf['path']['ContentList']	= "comm/Message.php";

	/*************** HELPER PATHS *********************/
	$cnf['path']['OgHelper']	= 	"util/helpers/OgHelper.class.php";
	
	
	
	/*************** EXPORT LIBRARY PATHS *****************/
	$cnf['path']['PHPOds']	= "export/PHPOds/PHPOds.php";
	$cnf['path']['PHPExcel']= "export/PHPExcel/Classes/PHPExcel.php";
	$cnf['path']['Temp'] 		= '../../../../../tmp/';  
	$cnf['path']['PHPExcel_Worksheet_Drawing_Shadow']		= "export/PHPExcel/Classes/PHPExcel/Worksheet/Drawing/Shadow.php";
	$cnf['path']['PHPExcel_Worksheet_Drawing']				= "export/PHPExcel/Classes/PHPExcel/Worksheet/Drawing.php";
	$cnf['path']['PHPExcel_Worksheet_BaseDrawing']			= "export/PHPExcel/Classes/PHPExcel/Worksheet/BaseDrawing.php";
	$cnf['path']['PHPExcel_Worksheet_HeaderFooter']			= "export/PHPExcel/Classes/PHPExcel/Worksheet/HeaderFooter.php";
	$cnf['path']['PHPExcel_Worksheet_RowDimension']			= "export/PHPExcel/Classes/PHPExcel/Worksheet/RowDimension.php";
	$cnf['path']['PHPExcel_Worksheet_ColumnDimension']		= "export/PHPExcel/Classes/PHPExcel/Worksheet/ColumnDimension.php";
	$cnf['path']['PHPExcel_Worksheet_PageMargins']			= "export/PHPExcel/Classes/PHPExcel/Worksheet/PageMargins.php";
	$cnf['path']['PHPExcel_Worksheet_PageSetup']			= "export/PHPExcel/Classes/PHPExcel/Worksheet/PageSetup.php";
	$cnf['path']['PHPExcel_Worksheet_Protection']			= "export/PHPExcel/Classes/PHPExcel/Worksheet/Protection.php";
	$cnf['path']['PHPExcel_Worksheet_HeaderFooterDrawing']	= "export/PHPExcel/Classes/PHPExcel/Worksheet/HeaderFooterDrawing.php";
	$cnf['path']['PHPExcel_RichText_ITextElement']			= "export/PHPExcel/Classes/PHPExcel/RichText/ITextElement.php";
	$cnf['path']['PHPExcel_RichText_Run']					= "export/PHPExcel/Classes/PHPExcel/RichText/Run.php";
	$cnf['path']['PHPExcel_RichText_TextElement']			= "export/PHPExcel/Classes/PHPExcel/RichText/TextElement.php";

	$cnf['path']['PHPExcel_Cell_Hyperlink']					= "export/PHPExcel/Classes/PHPExcel/Cell/Hyperlink.php";
	$cnf['path']['PHPExcel_Cell_DataValidation']			= "export/PHPExcel/Classes/PHPExcel/Cell/DataValidation.php";
	$cnf['path']['PHPExcel_Cell_DataType']					= "export/PHPExcel/Classes/PHPExcel/Cell/DataType.php";

	$cnf['path']['PHPExcel_Calculation_FormulaParser']		= "export/PHPExcel/Classes/PHPExcel/Calculation/FormulaParser.php";
	$cnf['path']['PHPExcel_Calculation_FormulaToken']		= "export/PHPExcel/Classes/PHPExcel/Calculation/FormulaToken.php";
	$cnf['path']['PHPExcel_Calculation_Function']			= "export/PHPExcel/Classes/PHPExcel/Calculation/Function.php";
	$cnf['path']['PHPExcel_Calculation_Functions']			= "export/PHPExcel/Classes/PHPExcel/Calculation/Functions.php";


	$cnf['path']['PHPExcel_Reader_CSV']						= "export/PHPExcel/Classes/PHPExcel/Reader/CSV.php";
	$cnf['path']['PHPExcel_Reader_Excel2007']				= "export/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php";
	$cnf['path']['PHPExcel_Reader_Excel5']					= "export/PHPExcel/Classes/PHPExcel/Reader/Excel5.php";
	$cnf['path']['PHPExcel_Reader_IReader']					= "export/PHPExcel/Classes/PHPExcel/Reader/IReader.php";
	$cnf['path']['PHPExcel_Reader_Serialized']				= "export/PHPExcel/Classes/PHPExcel/Reader/Serialized.php";

	$cnf['path']['PHPExcel_Style_Alignment']				= "export/PHPExcel/Classes/PHPExcel/Style/Alignment.php";
	$cnf['path']['PHPExcel_Style_NumberFormat']				= "export/PHPExcel/Classes/PHPExcel/Style/NumberFormat.php";
	$cnf['path']['PHPExcel_Style_Fill']						= "export/PHPExcel/Classes/PHPExcel/Style/Fill.php";
	$cnf['path']['PHPExcel_Style_Border']					= "export/PHPExcel/Classes/PHPExcel/Style/Border.php";
	$cnf['path']['PHPExcel_Style_Borders']					= "export/PHPExcel/Classes/PHPExcel/Style/Borders.php";
	$cnf['path']['PHPExcel_Style_Color']					= "export/PHPExcel/Classes/PHPExcel/Style/Color.php";
	$cnf['path']['PHPExcel_Style_Conditional']				= "export/PHPExcel/Classes/PHPExcel/Style/Conditional.php";
	$cnf['path']['PHPExcel_Style_Font']						= "export/PHPExcel/Classes/PHPExcel/Style/Font.php";
	$cnf['path']['PHPExcel_Style_Protection']				= "export/PHPExcel/Classes/PHPExcel/Style/Protection.php";


	$cnf['path']['PHPExcel_Shared_ZipStreamWrapper']		= "export/PHPExcel/Classes/PHPExcel/Shared/ZipStreamWrapper.php";
	$cnf['path']['PHPExcel_Shared_XMLWriter']= "export/PHPExcel/Classes/PHPExcel/Shared/XMLWriter.php";
	$cnf['path']['PHPExcel_Shared_String']= "export/PHPExcel/Classes/PHPExcel/Shared/String.php";
	$cnf['path']['PHPExcel_Shared_PDF']= "export/PHPExcel/Classes/PHPExcel/Shared/PDF.php";
	$cnf['path']['PHPExcel_Shared_PasswordHasher']= "export/PHPExcel/Classes/PHPExcel/Shared/PasswordHasher.php";
	$cnf['path']['PHPExcel_Shared_OLERead']= "export/PHPExcel/Classes/PHPExcel/Shared/OLERead.php";
	$cnf['path']['PHPExcel_Shared_OLE']= "export/PHPExcel/Classes/PHPExcel/Shared/OLE.php";
	$cnf['path']['PHPExcel_Shared_Font']= "export/PHPExcel/Classes/PHPExcel/Shared/Font.php";
	$cnf['path']['PHPExcel_Shared_File']= "export/PHPExcel/Classes/PHPExcel/Shared/File.php";
	$cnf['path']['PHPExcel_Shared_Drawing']= "export/PHPExcel/Classes/PHPExcel/Shared/Drawing.php";
	$cnf['path']['PHPExcel_Shared_Date']= "export/PHPExcel/Classes/PHPExcel/Shared/Date.php";

	$cnf['path']['PHPExcel_Shared_OLE_ChainedBlockStream']= "export/PHPExcel/Classes/PHPExcel/Shared/OLE/ChainedBlockStream.php";
	$cnf['path']['PHPExcel_Shared_OLE_PPS_File']= "export/PHPExcel/Classes/PHPExcel/Shared/OLE/OLE_File.php";
	$cnf['path']['PHPExcel_Shared_OLE_PPS']= "export/PHPExcel/Classes/PHPExcel/Shared/OLE/OLE_PPS.php";
	$cnf['path']['PHPExcel_Shared_OLE_PPS_Root']= "export/PHPExcel/Classes/PHPExcel/Shared/OLE/OLE_Root.php";

	$cnf['path']['FPDF']= "export/PHPExcel/Classes/PHPExcel/Shared/PDF/fpdf.php";

	$cnf['path']['PHPExcel_Cell_DataType']= "export/PHPExcel/Classes/PHPExcel/Cell/DataType.php";
	$cnf['path']['PHPExcel_Cell_DataValidation']= "export/PHPExcel/Classes/PHPExcel/Cell/DataValidation.php";
	$cnf['path']['PHPExcel_Cell_HyperLink']= "export/PHPExcel/Classes/PHPExcel/Cell/HyperLink.php";

	$cnf['path']['PHPExcel_ReferenceHelper']= "export/PHPExcel/Classes/PHPExcel/ReferenceHelper.php";
	$cnf['path']['PHPExcel_PasswordHasher']= "export/PHPExcel/Classes/PHPExcel/PasswordHasher.php";
	$cnf['path']['PHPExcel_Font']= "export/PHPExcel/Classes/PHPExcel/Font.php";
	$cnf['path']['PHPExcel_IComparable']= "export/PHPExcel/Classes/PHPExcel/IComparable.php";
	$cnf['path']['PHPExcel_Calculation']= "export/PHPExcel/Classes/PHPExcel/Calculation.php";
	$cnf['path']['PHPExcel_Worksheet']= "export/PHPExcel/Classes/PHPExcel/Worksheet.php";
	$cnf['path']['PHPExcel_DocumentProperties']= "export/PHPExcel/Classes/PHPExcel/DocumentProperties.php";
	$cnf['path']['PHPExcel_DocumentSecurity']= "export/PHPExcel/Classes/PHPExcel/DocumentSecurity.php";
	$cnf['path']['PHPExcel_NamedRange']= "export/PHPExcel/Classes/PHPExcel/NamedRange.php";
	$cnf['path']['PHPExcel_Cell']= "export/PHPExcel/Classes/PHPExcel/Cell.php";
	$cnf['path']['PHPExcel_Style']= "export/PHPExcel/Classes/PHPExcel/Style.php";
	$cnf['path']['PHPExcel_Comment']= "export/PHPExcel/Classes/PHPExcel/Comment.php";
	$cnf['path']['PHPExcel_IOFactory']= "export/PHPExcel/Classes/PHPExcel/IOFactory.php";
	$cnf['path']['PHPExcel_HashTable']= "export/PHPExcel/Classes/PHPExcel/HashTable.php";

	$cnf['path']['PHPExcel_Writer_Excel2007_Comments']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007/Comments.php";
	$cnf['path']['PHPExcel_Writer_Excel2007_ContentTypes']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007/ContentTypes.php";
	$cnf['path']['PHPExcel_Writer_Excel2007_DocProps']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007/DocProps.php";
	$cnf['path']['PHPExcel_Writer_Excel2007_Drawing']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007/Drawing.php";
	$cnf['path']['PHPExcel_Writer_Excel2007_Rels']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007/Rels.php";
	$cnf['path']['PHPExcel_Writer_Excel2007_StringTable']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007/StringTable.php";
	$cnf['path']['PHPExcel_Writer_Excel2007_Style']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007/Style.php";
	$cnf['path']['PHPExcel_Writer_Excel2007_Theme']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007/Theme.php";
	$cnf['path']['PHPExcel_Writer_Excel2007_Workbook']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007/Workbook.php";
	$cnf['path']['PHPExcel_Writer_Excel2007_Worksheet']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007/Worksheet.php";
	$cnf['path']['PHPExcel_Writer_Excel2007_WriterPart']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007/WriterPart.php";

	$cnf['path']['PHPExcel_Writer_Excel5_BIFFwriter']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel5/BIFFwriter.php";
	$cnf['path']['PHPExcel_Writer_Excel5_Format']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel5/Format.php";
	$cnf['path']['PHPExcel_Writer_Excel5_Parser']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel5/Parser.php";
	$cnf['path']['PHPExcel_Writer_Excel5_Workbook']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel5/Workbook.php";
	$cnf['path']['PHPExcel_Writer_Excel5_Worksheet']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel5/Worksheet.php";
	$cnf['path']['PHPExcel_Writer_Excel5_Writer']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel5/Writer.php";

	$cnf['path']['PHPExcel_Writer_CSV']= "export/PHPExcel/Classes/PHPExcel/Writer/CSV.php";
	$cnf['path']['PHPExcel_Writer_Excel2007']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel2007.php";
	$cnf['path']['PHPExcel_Writer_Excel5']= "export/PHPExcel/Classes/PHPExcel/Writer/Excel5.php";
	$cnf['path']['PHPExcel_Writer_HTML']= "export/PHPExcel/Classes/PHPExcel/Writer/HTML.php";
	$cnf['path']['PHPExcel_Writer_IWriter']= "export/PHPExcel/Classes/PHPExcel/Writer/IWriter.php";
	$cnf['path']['PHPExcel_Writer_PDF']= "export/PHPExcel/Classes/PHPExcel/Writer/PDF.php";
	$cnf['path']['PHPExcel_Writer_Serialized']= "export/PHPExcel/Classes/PHPExcel/Writer/Serialized.php";

?>