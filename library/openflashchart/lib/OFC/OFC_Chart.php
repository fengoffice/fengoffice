<?php
/**
 * PHP Integration of Open Flash Chart
 * Copyright (C) 2008 John Glazebrook <open-flash-chart@teethgrinder.co.uk>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

if (! function_exists('json_encode'))
{
    require_once(OFC_LIBRARY_PATH . '/lib/OFC/JSON.php');
}

require_once(OFC_LIBRARY_PATH . '/lib/OFC/JSON_Format.php');

require_once(OFC_LIBRARY_PATH . '/lib/OFC/OFC_Elements.php');

require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/OFC_Charts_Area.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/OFC_Charts_Bar.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/OFC_Charts_Line.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/OFC_Charts_Pie.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/OFC_Charts_Scatter.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/Area/OFC_Charts_Area_Hollow.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/Bar/OFC_Charts_Bar_Filled.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/Bar/OFC_Charts_Bar_3d.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/Bar/OFC_Charts_Bar_Glass.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/Bar/OFC_Charts_Bar_Horizontal.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/Bar/OFC_Charts_Bar_Sketch.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/Bar/OFC_Charts_Bar_Stack.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/Line/OFC_Charts_Line_Dot.php');
require_once(OFC_LIBRARY_PATH . '/lib/OFC/Charts/Line/OFC_Charts_Line_Hollow.php');

class OFC_Chart
{
	function OFC_Chart()
	{
		$this->title = new OFC_Elements_Title( "Many data lines" );
		$this->elements = array();
	}

	function set_title( $t )
	{
		$this->title = $t;
	}

	function set_x_axis( $x )
	{
		$this->x_axis = $x;
	}

	function set_y_axis( $y )
	{
		$this->y_axis = $y;
	}

	function add_y_axis( $y )
	{
		$this->y_axis = $y;
	}

	function set_y_axis_right( $y )
	{
		$this->y_axis_right = $y;
	}

	function add_element( $e )
	{
		$this->elements[] = $e;
	}

	function set_x_legend( $x )
	{
		$this->x_legend = $x;
	}

	function set_y_legend( $y )
	{
		$this->y_legend = $y;
	}

	function set_bg_colour( $colour )
	{
		$this->bg_colour = $colour;
	}

	function toString()
	{
		if (function_exists('json_encode'))
		{
			return json_encode($this);
		}
		else
		{
			$json = new Services_JSON();
			return $json->encode( $this );
		}
	}

	function toPrettyString()
	{
		return json_format( $this->toString() );
	}
}

class shape_point
{
	function shape_point( $x, $y )
	{
		$this->x = $x;
		$this->y = $y;
	}
}

class shape
{
	function shape( $colour )
	{
		$this->type = "shape";
		$this->colour = $colour;
		$this->values = array();
	}
	
	function append_value( $p )
	{
		$this->values[] = $p;	
	}
	
	function set_text($text) {
		$this->text = $text;
	}
	function set_alpha($alpha) {
		$this->alpha = $alpha;
	}
	
	function set_line_color($color) {
		$this->{'line-colour'} = $color;
	}
	function set_line_alpha($alpha) {
		$this->{'line-alpha'} = $alpha;
	}
	function set_line_width($w) {
		$this->width = $w;
	}
}
