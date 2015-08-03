<?php

    define('MIME_TYPES_LIST', ROOT . '/environment/classes/mail/mimetypes.txt');
    
// +----------------------------------------------------------------------+
// | MIME Types Class 0.1 - 21-Dec-2002                                   |
// +----------------------------------------------------------------------+
// | Author: Keyvan Minoukadeh - keyvan@k1m.com - http://www.keyvan.net   |
// +----------------------------------------------------------------------+
// | PHP class for retrieving the appropriate MIME type of a              |
// | file/extension                                                       |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License          |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// +----------------------------------------------------------------------+

/**
* MIME Types class
*
* This class allows you to:
*   - Retrieve the appropriate MIME type of a file (based on it's extension, or by utilising
*     the file command to guess it based on the file contents).
*   - Retrieve extension(s) associated with a MIME type.
*   - Load MIME types and extensions from a mime.types file.
*
* Example:
*   $mime =& new Mime_Types('/usr/local/apache/conf/mime.types');
*   echo $mime->get_type('pdf');    // application/pdf
*   echo $mime->get_extension('text/vnd.wap.wmlscript');      // wmls
*
* See test_Mime_Types.php file for more examples.
*
* TODO:
*  - method to write MIME types to file.
*  - get_file_type(): possibly preserving the parameters returned by the file command 
*    (e.g. text/plain; charset=us-ascii)
*
* @author Keyvan Minoukadeh <keyvan@k1m.com>
* @version 0.1
*/
class Mime_Types
{
    /**
    * MIME Types
    * Initially we start with the more popular ones.
    * @var array
    * @access private
    */
    var $mime_types = array(
        'txt'   => 'text/plain',
        'gif'   => 'image/gif',
        'jpg'   => 'image/jpeg',
        'bmp'   => 'image/bmp',
        'html'  => 'text/html',
        'htm'   => 'text/html',
    	'qt'	=> 'video/quicktime',
    	'mov'	=> 'video/quicktime',
    	'mp3'	=> 'audio/mpeg',
    	'mp2'	=> 'audio/mpeg',
    	'mpgq'	=> 'audio/mpeg',
    	'aif'	=> 'audio/x-aiff',
    	'aiff'	=> 'audio/x-aiff',
    	'zip'	=> 'application/zip',
    	'doc'	=> 'application/msword',
    	'ppt'	=> 'application/vnd.ms-powerpoint',
    	'xls'	=> 'application/vnd.ms-excel',
    	'docx'	=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    	'xlsx'	=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    	'pptx'	=> 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    	'pdf'	=> 'application/pdf',
    
    	'' => 'application/octet-stream',
		'323' => 'text/h323',
		'acx' => 'application/internet-property-stream',
		'ai' => 'application/postscript',
		'aif' => 'audio/x-aiff',
		'aifc' => 'audio/x-aiff',
		'aiff' => 'audio/x-aiff',
		'asf' => 'video/x-ms-asf',
		'asr' => 'video/x-ms-asf',
		'asx' => 'video/x-ms-asf',
		'au' => 'audio/basic',
		'avi' => 'video/x-msvideo',
		'axs' => 'application/olescript',
		'bas' => 'text/plain',
		'bcpio' => 'application/x-bcpio',
		'bin' => 'application/octet-stream',
		'bmp' => 'image/bmp',
		'c' => 'text/plain',
		'cat' => 'application/vnd.ms-pkiseccat',
		'cdf' => 'application/x-cdf',
		'cer' => 'application/x-x509-ca-cert',
		'class' => 'application/octet-stream',
		'clp' => 'application/x-msclip',
		'cmx' => 'image/x-cmx',
		'cod' => 'image/cis-cod',
		'cpio' => 'application/x-cpio',
		'crd' => 'application/x-mscardfile',
		'crl' => 'application/pkix-crl',
		'crt' => 'application/x-x509-ca-cert',
		'csh' => 'application/x-csh',
		'css' => 'text/css',
		'dcr' => 'application/x-director',
		'der' => 'application/x-x509-ca-cert',
		'dir' => 'application/x-director',
		'dll' => 'application/x-msdownload',
		'dms' => 'application/octet-stream',
		'doc' => 'application/msword',
		'dot' => 'application/msword',
		'dvi' => 'application/x-dvi',
		'dxr' => 'application/x-director',
		'eps' => 'application/postscript',
		'etx' => 'text/x-setext',
		'evy' => 'application/envoy',
		'exe' => 'application/octet-stream',
		'fif' => 'application/fractals',
		'flr' => 'x-world/x-vrml',
		'gif' => 'image/gif',
		'gtar' => 'application/x-gtar',
		'gz' => 'application/x-gzip',
		'h' => 'text/plain',
		'hdf' => 'application/x-hdf',
		'hlp' => 'application/winhlp',
		'hqx' => 'application/mac-binhex40',
		'hta' => 'application/hta',
		'htc' => 'text/x-component',
		'htm' => 'text/html',
		'html' => 'text/html',
		'htt' => 'text/webviewhtml',
		'ico' => 'image/x-icon',
		'ief' => 'image/ief',
		'iii' => 'application/x-iphone',
		'ins' => 'application/x-internet-signup',
		'isp' => 'application/x-internet-signup',
		'jfif' => 'image/pipeg',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'js' => 'application/x-javascript',
		'latex' => 'application/x-latex',
		'lha' => 'application/octet-stream',
		'lsf' => 'video/x-la-asf',
		'lsx' => 'video/x-la-asf',
		'lzh' => 'application/octet-stream',
		'm13' => 'application/x-msmediaview',
		'm14' => 'application/x-msmediaview',
		'm3u' => 'audio/x-mpegurl',
		'man' => 'application/x-troff-man',
		'mdb' => 'application/x-msaccess',
		'me' => 'application/x-troff-me',
		'mht' => 'message/rfc822',
		'mhtml' => 'message/rfc822',
		'mid' => 'audio/mid',
		'mny' => 'application/x-msmoney',
		'mov' => 'video/quicktime',
		'movie' => 'video/x-sgi-movie',
		'mp2' => 'video/mpeg',
		'mp3' => 'audio/mpeg',
		'mpa' => 'video/mpeg',
		'mpe' => 'video/mpeg',
		'mpeg' => 'video/mpeg',
		'mpg' => 'video/mpeg',
		'mpp' => 'application/vnd.ms-project',
		'mpv2' => 'video/mpeg',
		'ms' => 'application/x-troff-ms',
		'mvb' => 'application/x-msmediaview',
		'nws' => 'message/rfc822',
		'oda' => 'application/oda',
		'p10' => 'application/pkcs10',
		'p12' => 'application/x-pkcs12',
		'p7b' => 'application/x-pkcs7-certificates',
		'p7c' => 'application/x-pkcs7-mime',
		'p7m' => 'application/x-pkcs7-mime',
		'p7r' => 'application/x-pkcs7-certreqresp',
		'p7s' => 'application/x-pkcs7-signature',
		'pbm' => 'image/x-portable-bitmap',
		'pdf' => 'application/pdf',
		'pfx' => 'application/x-pkcs12',
		'pgm' => 'image/x-portable-graymap',
		'pko' => 'application/ynd.ms-pkipko',
		'pma' => 'application/x-perfmon',
		'pmc' => 'application/x-perfmon',
		'pml' => 'application/x-perfmon',
		'pmr' => 'application/x-perfmon',
		'pmw' => 'application/x-perfmon',
		'pnm' => 'image/x-portable-anymap',
		'pot' => 'application/vnd.ms-powerpoint',
		'ppm' => 'image/x-portable-pixmap',
		'pps' => 'application/vnd.ms-powerpoint',
		'ppt' => 'application/vnd.ms-powerpoint',
		'prf' => 'application/pics-rules',
		'ps' => 'application/postscript',
		'pub' => 'application/x-mspublisher',
		'qt' => 'video/quicktime',
		'ra' => 'audio/x-pn-realaudio',
		'ram' => 'audio/x-pn-realaudio',
		'ras' => 'image/x-cmu-raster',
		'rgb' => 'image/x-rgb',
		'rmi' => 'audio/mid',
		'roff' => 'application/x-troff',
		'rtf' => 'application/rtf',
		'rtx' => 'text/richtext',
		'scd' => 'application/x-msschedule',
		'sct' => 'text/scriptlet',
		'setpay' => 'application/set-payment-initiation',
		'setreg' => 'application/set-registration-initiation',
		'sh' => 'application/x-sh',
		'shar' => 'application/x-shar',
		'sit' => 'application/x-stuffit',
		'snd' => 'audio/basic',
		'spc' => 'application/x-pkcs7-certificates',
		'spl' => 'application/futuresplash',
		'src' => 'application/x-wais-source',
		'sst' => 'application/vnd.ms-pkicertstore',
		'stl' => 'application/vnd.ms-pkistl',
		'stm' => 'text/html',
		'svg' => 'image/svg+xml',
		'sv4cpio' => 'application/x-sv4cpio',
		'sv4crc' => 'application/x-sv4crc',
		'swf' => 'application/x-shockwave-flash',
		't' => 'application/x-troff',
		'tar' => 'application/x-tar',
		'tcl' => 'application/x-tcl',
		'tex' => 'application/x-tex',
		'texi' => 'application/x-texinfo',
		'texinfo' => 'application/x-texinfo',
		'tgz' => 'application/x-compressed',
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'tr' => 'application/x-troff',
		'trm' => 'application/x-msterminal',
		'tsv' => 'text/tab-separated-values',
		'txt' => 'text/plain',
		'uls' => 'text/iuls',
		'ustar' => 'application/x-ustar',
		'vcf' => 'text/x-vcard',
		'vrml' => 'x-world/x-vrml',
		'wav' => 'audio/x-wav',
		'wcm' => 'application/vnd.ms-works',
		'wdb' => 'application/vnd.ms-works',
		'wks' => 'application/vnd.ms-works',
		'wmf' => 'application/x-msmetafile',
		'wps' => 'application/vnd.ms-works',
		'wri' => 'application/x-mswrite',
		'wrl' => 'x-world/x-vrml',
		'wrz' => 'x-world/x-vrml',
		'xaf' => 'x-world/x-vrml',
		'xbm' => 'image/x-xbitmap',
		'xla' => 'application/vnd.ms-excel',
		'xlc' => 'application/vnd.ms-excel',
		'xlm' => 'application/vnd.ms-excel',
		'xls' => 'application/vnd.ms-excel',
		'xlt' => 'application/vnd.ms-excel',
		'xlw' => 'application/vnd.ms-excel',
		'xof' => 'x-world/x-vrml',
		'xpm' => 'image/x-xpixmap',
		'xwd' => 'image/x-xwindowdump',
		'z' => 'application/x-compress',
		'zip' => 'application/zip',
    );
    
    /**
    * Path to file command - empty string disables the use of the file command
    * @var string
    */
    var $file_cmd = '';
    // var $file_cmd = '/usr/bin/file';

    /**
    * File options, used with the file command
    * Example:
    *   ['i'] => null // this option asks file to produce a MIME type if it can
    *   ['b'] => null // this option tells file to be brief
    * will result in 'file -i -b test_file'
    * @var array
    */
    var $file_options = array('b'=>null, 'i'=>null);

    /**
    * Constructor
    * optional parameter can be either a string containing the path to the
    * mime.types files, or an associative array holding the extension
    * as key and the MIME type as value.
    * Example:
    *   $mime =& new Mime_Types('/usr/local/apache/conf/mime.types');
    *  or
    *   $mime =& new Mime_Types(array(
    *        'application/pdf'        => 'pdf', 
    *        'application/postscript' => array('ai','eps')
    *        ));
    * @param mixed $mime_types
    */
    function Mime_Types($mime_types=null)
    {
        if (is_string($mime_types)) {
            $this->load_file($mime_types);
        } elseif (is_array($mime_types)) {
            $this->set($mime_types);
        }
    }

    /**
    * Scan - goes through all MIME types passing the extension and type to the callback function.
    * The types will be sent in alphabetical order.
    * If a type has multiple extensions, each extension will be passed seperately (not as an array).
    *
    * The callback function can be a method from another object (eg. array(&$my_obj, 'my_method')).
    * The callback function should accept 3 arguments:
    *  1- A reference to the Mime_Types object (&$mime)
    *  2- An array holding extension and type, array keys: 
    *     [0]=>ext, [1]=>type
    *  3- An optional parameter which can be used for whatever your function wants :),
    *     even though you might not have a use for this parameter, you need to define
    *     your function to accept it.  (Note: you can have this parameter be passed by reference)
    * The callback function should return a boolean, a value of 'true' will tell scan() you want
    * it to continue with the rest of the types, 'false' will tell scan() to stop calling
    * your callback function.
    *
    * @param mixed $callback function name, or array holding an object and the method to call.
    * @param mixed $param passed as the 3rd argument to $callback
    */
    function scan($callback, &$param)
    {
        if (is_array($callback)) $method =& $callback[1];
        $mime_types = $this->mime_types;
        asort($mime_types);
        foreach ($mime_types as $ext => $type) {
            $ext_type = array($ext, $type);
            if (isset($method)) {
                $res = $callback[0]->$method($this, $ext_type, $param);
            } else {
                $res = $callback($this, $ext_type, $param);
            }
            if (!$res) return;
        }
    }

    /**
    * Get file type - returns MIME type by trying to guess it using the file command. 
    * Optional second parameter should be a boolean.  If true (default), get_file_type() will
    * try to guess the MIME type based on the file extension if the file command fails to find
    * a match.
    * Example:
    *   echo $mime->get_file_type('/path/to/my_file', false);
    *  or
    *   echo $mime->get_file_type('/path/to/my_file.gif');
    * @param string $file
    * @param bool $use_ext default: true
    * @return string false if unable to find suitable match
    */
    function get_file_type($file, $use_ext=true)
    {
        $file = trim($file);
        if ($file == '') return false;
        $type = false;
        $result = false;
        if ($this->file_cmd && is_readable($file) && is_executable($this->file_cmd)) {
            $cmd = $this->file_cmd;
            foreach ($this->file_options as $option_key => $option_val) {
                $cmd .= ' -'.$option_key;
                if (isset($option_val)) $cmd .= ' '.escapeshellarg($option_val);
            }
            $cmd .= ' '.escapeshellarg($file);
            $result = @exec($cmd);
            if ($result) {
                $result = strtolower($result);
                $pattern = '[a-z0-9.+_-]';
                if (preg_match('!(('.$pattern.'+)/'.$pattern.'+)!', $result, $match)) {
                    if (in_array($match[2], array('application','audio','image','message',
                                                  'multipart','text','video','chemical','model')) ||
                            (substr($match[2], 0, 2) == 'x-')) {
                        $type = $match[1];
                    }
                }
            }
        }
        // try and get type from extension
        if (!$type && $use_ext && strpos($file, '.')) $type = $this->get_type($file);
        // this should be some sort of attempt to match keywords in the file command output
        // to a MIME type, I'm not actually sure if this is a good idea, but for now, it tries
        // to find an 'ascii' string.
        if (!$type && $result && preg_match('/\bascii\b/', $result)) $type = 'text/plain';
        return $type;
    }

    /**
    * Get type - returns MIME type based on the file extension.
    * Example:
    *   echo $mime->get_type('txt');
    *  or
    *   echo $mime->get_type('test_file.txt');
    * both examples above will return the same result.
    * @param string $ext
    * @return string false if extension not found
    */
    function get_type($ext)
    {
        $ext = strtolower($ext);
        // get position of last dot
        $dot_pos = strrpos($ext, '.');
        if ($dot_pos !== false) $ext = substr($ext, $dot_pos+1);
        if (($ext != '') && isset($this->mime_types[$ext])) return $this->mime_types[$ext];
        return false;
    }

    /**
    * Set - set extension and MIME type
    * Example:
    *   $mime->set('text/plain', 'txt');
    *  or
    *   $mime->set('text/html', array('html','htm'));
    *  or
    *   $mime->set('text/html', 'html htm');
    *  or
    *   $mime->set(array(
    *        'application/pdf'        => 'oda', 
    *        'application/postscript' => array('ai','eps')
    *        ));
    * @param mixed $type either array containing type and extensions, or the type as string
    * @param mixed $exts either array holding extensions, or string holding extensions
    * seperated by space.
    * @return void
    */
    function set($type, $exts=null)
    {
        if (!isset($exts)) {
            if (is_array($type)) {
                foreach ($type as $mime_type => $exts) {
                    $this->set($mime_type, $exts);
                }
            }
            return;
        }
        if (!is_string($type)) return;
        // get rid of any parameters which might be included with the MIME type
        // e.g. text/plain; charset=iso-8859-1
        $type = strtr(strtolower(trim($type)), ",;\t\r\n", '     ');
        if ($sp_pos = strpos($type, ' ')) $type = substr($type, 0, $sp_pos);
        // not bothering with an extensive check of the MIME type, just checking for slash
        if (!strpos($type, '/')) return;
        // loop through extensions
        if (!is_array($exts)) $exts = explode(' ', $exts);
        foreach ($exts as $ext) {
            $ext = trim(str_replace('.', '', $ext));
            if ($ext == '') continue;
            $this->mime_types[strtolower($ext)] = $type;
        }
    }

    /**
    * Has extension - returns true if extension $ext exists, false otherwise
    * Example:
    *   if ($mime->has_extension('pdf')) echo 'Got it!';
    * @param string $ext
    * @return bool
    */
    function has_extension($ext)
    {
        return (isset($this->mime_types[strtolower($ext)]));
    }

    /**
    * Has type - returns true if type $type exists, false otherwise
    * Example:
    *   if ($mime->has_type('image/gif')) echo 'Got it!';
    * @param string $type
    * @return bool
    */
    function has_type($type)
    {
        return (in_array(strtolower($type), $this->mime_types));
    }

    /**
    * Get extension - returns string containing a extension associated with $type
    * Example:
    *   $ext = $mime->get_extension('application/postscript');
    *   if ($ext) echo $ext;
    * @param string $type
    * @return string false if $type not found
    */
    function get_extension($type)
    {
        $type = strtolower($type);
        foreach ($this->mime_types as $ext => $m_type) {
            if ($m_type == $type) return $ext;
        }
        return false;
    }

    /**
    * Get extensions - returns array containing extension(s)
    * Example:
    *   $exts = $mime->get_extensions('application/postscript');
    *   echo implode(', ', $exts);
    * @param string $type
    * @return array
    */
    function get_extensions($type)
    {
        $type = strtolower($type);
        return (array_keys($this->mime_types, $type));
    }

    /**
    * Remove extension
    * Example:
    *   $mime->remove_extension('txt');
    *  or
    *   $mime->remove_extension('txt exe html');
    *  or
    *   $mime->remove_extension(array('txt', 'exe', 'html'));
    * @param mixed $exts string holding extension(s) seperated by space, or array
    * @return void
    */
    function remove_extension($exts)
    {
        if (!is_array($exts)) $exts = explode(' ', $exts);
        foreach ($exts as $ext) {
            $ext = strtolower(trim($ext));
            if (isset($this->mime_types[$ext])) unset($this->mime_types[$ext]);
        }
    }

    /**
    * Remove type
    * Example:
    *   $mime->remove_type('text/plain');
    *  or
    *   $mime->remove_type('image/*');
    *   // removes all image types
    *  or
    *   $mime->remove_type();
    *   // clears all types
    * @param string $type if omitted, all types will be removed
    * @return void
    */
    function remove_type($type=null)
    {
        if (!isset($type)) {
            $this->mime_types = array();
            return;
        }
        $slash_pos = strpos($type, '/');
        if (!$slash_pos) return;

        $type_info = array('last_match'=>false, 'wildcard'=>false, 'type'=>$type);
        if (substr($type, $slash_pos) == '/*') {
            $type_info['wildcard'] = true;
            $type_info['type'] = substr($type, 0, $slash_pos);
        }
        $this->scan(array(&$this, '_remove_type_callback'), $type_info);
    }

    /**
    * Load file - load file containing mime types.
    * Example:
    *   $result = $mime->load_file('/usr/local/apache/conf/mime.types');
    *   echo (($result) ? 'Success!' : 'Failed');
    * @param string $file
    * @return bool
    */
    function load_file($file)
    {
        if (!file_exists($file) || !is_readable($file)) return false;
        $data = file($file);
        foreach ($data as $line) {
            $line = trim($line);
            if (($line == '') || ($line == '#')) continue;
            $line = preg_split('/\s+/', $line, 2);
            if (count($line) < 2) continue;
            $exts = $line[1];
            // if there's a comment on this line, remove it
            $hash_pos = strpos($exts, '#');
            if ($hash_pos !== false) $exts = substr($exts, 0, $hash_pos);
            $this->set($line[0], $exts);
        }
        return true;
    }

    //
    // private methods
    //

    /**
    * Remove type callback
    * @param object $mime
    * @param array $ext_type
    * @param array $type_info
    * @return bool
    * @access private
    */
    function _remove_type_callback(&$mime, $ext_type, $type_info)
    {
        // temporarily we'll put match to false
        $matched = false;
        list($ext, $type) = $ext_type;
        if ($type_info['wildcard']) {
            if (substr($type, 0, strpos($type, '/')) == $type_info['type']) {
                $matched = true;
            }
        } elseif ($type == $type_info['type']) {
            $matched = true;
        }
        if ($matched) {
            $this->remove_extension($ext);
            $type_info['last_match'] = true;
        } elseif ($type_info['last_match']) {
            // we do not need to continue if the previous type matched, but this type didn't.
            // because all types are sorted in alphabetical order, we can be sure there will be
            // no further successful matches.
            return false;
        }
        return true;
    }        
    
    /**
    * Return manager instance
    *
    * @return Mime_Types 
    */
    function instance() {
      static $instance;
      if(!instance_of($instance, 'Mime_Types')) {
        $instance = new Mime_Types(MIME_TYPES_LIST);
      } // if
      return $instance;
    } // instance
}
?>