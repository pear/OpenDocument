<?php
/**
* PEAR OpenDocument package
* 
* PHP version 5
*
* LICENSE: This library is free software; you can redistribute it and/or
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
* 
* @category File_Formats
* @package  OpenDocument
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html Lesser General Public License 2.1
* @version  CVS: $Id$
* @link     http://pear.php.net/package/OpenDocument
* @since    File available since Release 0.1.0
*/

require_once 'OpenDocument/Storage/Zip.php';
require_once 'OpenDocument/Exception.php';

/**
* Base class containing methods to open and create documents.
* It contains namespace definitions as well.
*
* This class is not to be extendet or derived from. Its only purpose is
* to server as hub to create document objects, and to keep commonly used
* constants.
*
* @category File_Formats
* @package  OpenDocument
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html Lesser General Public License 2.1
* @link     http://pear.php.net/package/OpenDocument
*/
abstract class OpenDocument
{
    /**
     * Manifest namespace
     */
    const NS_MANIFEST = 'urn:oasis:names:tc:opendocument:xmlns:manifest:1.0';

    /**
     * text namespace URL
     */
    const NS_TEXT = 'urn:oasis:names:tc:opendocument:xmlns:text:1.0';
    
    /**
     * style namespace URL
     */
    const NS_STYLE = 'urn:oasis:names:tc:opendocument:xmlns:style:1.0';
    
    /**
     * fo namespace URL
     */
    const NS_FO = 'urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0';
    
    /**
     * office namespace URL
     */
    const NS_OFFICE = 'urn:oasis:names:tc:opendocument:xmlns:office:1.0';
    
    /**
     * svg namespace URL
     */
    const NS_SVG = 'urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0';
    
    /**
     * xlink namespace URL
     */
    const NS_XLINK = 'http://www.w3.org/1999/xlink';

    /**
     * Array of supported document types.
     *
     * @var array
     *
     * @usedby create()
     */
    public static $documenttypes = array(
        'text',
        'spreadsheet',
        'presentation',
        'drawing',
        'image',
        'chart',
    );



    /**
     * This class cannot be instantiated.
     * To create documents, use text(), spreadsheet() or the other
     * static methods.
     *
     */
    private function __construct()
    {
    }



    /**
     * Open the given file
     *
     * @param string $file Name (path) of file to open
     *
     * @return OpenDocument_Document A document object
     *
     * @throw OpenDocument_Exception
     */
    public static function open($file)
    {
        //FIXME: detect correct storage
        $storage = new OpenDocument_Storage_Zip();
        $storage->open($file);

        $mimetype = $storage->getMimeType();

        switch ($mimetype) {
        case 'application/vnd.oasis.opendocument.text':
            $class = 'OpenDocument_Document_Text';
            break;
        default:
            throw new OpenDocument_Exception(
                'Unsupported MIME type ' . $mimetype
            );
            break;
        }

        self::includeClassFile($class);

        return new $class($storage);
    }//public static function open($file)



    /**
     * Creates and returns a new OpenDocument document object.
     *
     * @param string $type    Type of document to create: 'text', 'spreadsheet',
     *                        'drawing', 'chart', 'image', 'presentation'
     * @param string $file    Name of the file to be saved as
     * @param mixed  $storage Storage class or object to use. Object need to
     *                        implement OpenDocument_Storage
     *
     * @return OpenDocument_Document Document object
     *
     * @throws OpenDocument_Exception In case the type is unsupported, or
     *                                the document or storage class cannot
     *                                be loaded
     *
     * @see text()
     * @see spreadsheet()
     * @see presentation()
     * @see drawing()
     * @see chart()
     * @see image()
     *
     * @uses $documenttypes
     * @uses includeClassFile
     */
    public static function create($type, $file = null, $storage = null)
    {
        if (!in_array($type, self::$documenttypes)) {
            throw new OpenDocument_Exception(
                'Unsupported document type ' . $type
            );
        }
        $class = 'OpenDocument_Document_' . ucfirst($type);
        self::includeClassFile($class);

        if ($storage === null) {
            $storage = 'OpenDocument_Storage_Zip';
        }
        if (is_string($storage)) {
            self::includeClassFile($storage);
            $storage = new $storage();
            $storage->create($type, $file);
        } else if (!$storage instanceof OpenDocument_Storage) {
            throw new OpenDocument_Exception(
                'Storage must implement OpenDocument_Storage interface'
            );
        }

        return new $class($storage);
    }//public static function create(..)



    /**
     * Includes the correct class for the given file
     *
     * @param string $class Class name to load file for
     *
     * @return void
     *
     * @throws OpenDocument_Exception When the class cannot be loaded
     */
    protected function includeClassFile($class)
    {
        $file = str_replace('_', '/', $class) . '.php';
        include_once $file;
        if (!class_exists($class)) {
            throw new OpenDocument_Exception(
                'Class could not be loaded: ' . $class
            );
        }
    }//protected function includeClassFile(..)



    /**
     * Creates and returns a new text document.
     *
     * @param string $file    Name of file that will be saved
     * @param mixed  $storage Storage class or object to use
     *
     * @return OpenDocument_Document_Text
     *
     * @see create()
     */
    public static function text($file = null, $storage = null)
    {
        return self::create('text', $file, $storage);
    }



    /**
     * Creates and returns a new spreadsheet document.
     * NOT IMPLEMENTED YET
     *
     * @param string $file    Name of file that will be saved
     * @param mixed  $storage Storage class or object to use
     *
     * @return OpenDocument_Document_Spreadsheet
     *
     * @see create()
     */
    public static function spreadsheet($file = null, $storage = null)
    {
        //FIXME
        throw new OpenDocument_Exception(
            'Spreadsheet functionality not implemented yet'
        );
    }



    /**
     * Creates and returns a new drawing document.
     * NOT IMPLEMENTED YET
     *
     * @param string $file    Name of file that will be saved
     * @param mixed  $storage Storage class or object to use
     *
     * @return OpenDocument_Document_Drawing
     *
     * @see create()
     */
    public static function drawing($file = null, $storage = null)
    {
        //FIXME
        throw new OpenDocument_Exception(
            'Drawing functionality not implemented yet'
        );
    }



    /**
     * Creates and returns a new presentation document.
     * NOT IMPLEMENTED YET
     *
     * @param string $file    Name of file that will be saved
     * @param mixed  $storage Storage class or object to use
     *
     * @return OpenDocument_Document_Presentation
     *
     * @see create()
     */
    public static function presentation($file = null, $storage = null)
    {
        //FIXME
        throw new OpenDocument_Exception(
            'Presentation functionality not implemented yet'
        );
    }



    /**
     * Creates and returns a new chart document.
     * NOT IMPLEMENTED YET
     *
     * @param string $file    Name of file that will be saved
     * @param mixed  $storage Storage class or object to use
     *
     * @return OpenDocument_Document_Chart
     *
     * @see create()
     */
    public static function chart($file = null, $storage = null)
    {
        //FIXME
        throw new OpenDocument_Exception(
            'Chart functionality not implemented yet'
        );
    }



    /**
     * Creates and returns a new image document.
     * NOT IMPLEMENTED YET
     *
     * @param string $file    Name of file that will be saved
     * @param mixed  $storage Storage class or object to use
     *
     * @return OpenDocument_Document_Image
     *
     * @see create()
     */
    public static function image($file = null, $storage = null)
    {
        //FIXME
        throw new OpenDocument_Exception(
            'Image functionality not implemented yet'
        );
    }
}
?>