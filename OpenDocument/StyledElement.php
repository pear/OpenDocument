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
* @author   Alexander Pak <irokez@gmail.com>
* @license  http://www.gnu.org/copyleft/lesser.html Lesser General Public License 2.1
* @version  CVS: $Id$
* @link     http://pear.php.net/package/OpenDocument
*/

require_once 'OpenDocument/Element.php';
require_once 'OpenDocument/ElementStyle.php';

/**
* Base class for elements with styles
* 
* @category File_Formats
* @package  OpenDocument
* @author   Alexander Pak <irokez@gmail.com>
* @license  http://www.gnu.org/copyleft/lesser.html Lesser General Public License 2.1
* @link     http://pear.php.net/package/OpenDocument
* @since    File available since Release 0.1.0
*/
abstract class OpenDocument_StyledElement extends OpenDocument_Element
{
    /**
     * Style information
     *
     * @var OpenDocument_ElementStyle
     */
    protected $style;

    /**
     * Style family used,
     * e.g. "text" for spans or "paragraph" for paras.
     *
     * @var string
     */
    const styleFamily = 'paragraph';

    /**
     * Style name suffix max value
     *
     * @var integer
     */
    protected static $styleNameMaxNumber = 0;
    
    /**
     * Style name prefix
     *
     */
    const styleNamePrefix = 'E';
    
    /**
     * Generate new style name
     *
     * @return string New style name
     */
    abstract public function generateStyleName();
    
    /**
     * Constructor
     *
     * @param DOMNode               $node     DOM node to create element for
     * @param OpenDocument_Document $document Document object the element is
     *                                        being created for
     */
    public function __construct(DOMNode $node, OpenDocument_Document $document)
    {
        parent::__construct($node, $document);
        $this->style = new OpenDocument_ElementStyle($this);
    }
    
    /**
     * Magic method: Get a property value.
     *
     * @param string $name Name of property to retrieve ('style')
     *
     * @return mixed Value of property.
     */
    public function __get($name)
    {
        if ($name == 'style') {
            return $this->style;
        }
    }
    
    /**
     * Get style information
     *
     * @param array $properties Array of namespace-prefixed properties to
     *                          retrieve.
     *
     * @return array Key-value array of properties and their values
     *
     * @see OpenDocument::getStyle()
     */
    public function getStyle($properties)
    {
        return $this->document->getStyle($this->getStyleName(), $properties);
    }
    
    /**
     * Get style name
     *
     * @return string Get name of style
     */
    public function getStyleName()
    {
        return $this->node->getAttributeNS(OpenDocument::NS_TEXT, 'style-name');
    }
    
    /**
     * Get style name prefix
     *
     * @return string Prefix for style name
     */
    public function getStyleNamePrefix()
    {
        return $this->styleNamePrefix;
    }
    
    /**
     * Get style name suffix max value
     *
     * @return integer
     */
    public static function getStyleNameMaxNumber()
    {
        return self::$styleNameMaxNumber;
    }
    
    /**
     * Set style name suxxif max value
     *
     * @param integer $number Maxium style number
     *
     * @return void
     */
    public static function setStyleNameMaxNumber($number)
    {
        self::$styleNameMaxNumber = $number;
    }

    /**
     * Apply style information
     *
     * @param string $name  Name of style property ('fo:font-weight')
     * @param mixed  $value Value for property
     *
     * @return void
     */
    public function applyStyle($name, $value)
    {
        $style_name = $this->node->getAttributeNS(
            OpenDocument::NS_TEXT, 'style-name'
        );
        $style_name = $this->document->applyStyle(
            $style_name, $name, $value, $this
        );
        $this->node->setAttributeNS(
            OpenDocument::NS_TEXT, 'style-name', $style_name
        );
    }
}
?>