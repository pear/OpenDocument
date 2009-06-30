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
* @license  http://www.gnu.org/copyleft/lesser.html  Lesser General Public License 2.1
* @version  CVS: $Id$
* @link     http://pear.php.net/package/OpenDocument
* @since    File available since Release 0.1.0
*/

require_once 'OpenDocument/StyledElement.php';

/**
* Heading element
*
* @category File_Formats
* @package  OpenDocument
* @author   Alexander Pak <irokez@gmail.com>
* @license  http://www.gnu.org/copyleft/lesser.html  Lesser General Public License 2.1
* @link     http://pear.php.net/package/OpenDocument
*/
class OpenDocument_Element_Heading extends OpenDocument_StyledElement
{
    /**
     * Heading level
     *
     * @var integer
     */
    private $level;
    
    /**
     * Node namespace
     */
    const nodeNS = OpenDocument::NS_TEXT;
    
    /**
     * Node namespace
     */
    const nodePrefix = 'text';
    
    /**
     * Node name
     */
    const nodeName = 'h';
    
    /**
     * Element style name prefix
     */
    const styleNamePrefix = 'H';

    /**
     * Style family to use
     *
     * @var string
     */
    const styleFamily = 'paragraph';



    /**
     * Constructor
     *
     * @param DOMNode      $node     Node to add heading to
     * @param OpenDocument $document Document to add heading to
     */
    public function __construct(DOMNode $node, OpenDocument_Document $document)
    {
        parent::__construct($node, $document);
        $this->level = $node->getAttributeNS(OpenDocument::NS_TEXT, 'outline-level');
        
        $this->allowedElements = array(
            'OpenDocument_Span',
            'OpenDocument_Hyperlink',
        );
    }
    
    /**
     * Create a heading element
     *
     * @param mixed   $object  Document or element to append heading to
     * @param mixed   $content Content of heading
     * @param integer $level   Level from 1 to 6 (1 highest)
     *
     * @return OpenDocument_Element_Heading
     */
    public static function instance($object, $content, $level = 1)
    {
        if ($object instanceof OpenDocument_Document) {
            $document = $object;
            $node     = $object->cursor;
        } else if ($object instanceof OpenDocument_Element) {
            $document = $object->getDocument();
            $node     = $object->getNode();
        } else {
            throw new OpenDocument_Exception(
                'OpenDocument_Element or OpenDocument_Document expected',
                OpenDocument_Exception::ELEM_OR_DOC_EXPECTED
            );
        }
        
        $element = new OpenDocument_Element_Heading(
            $node->ownerDocument->createElementNS(
                self::nodeNS, self::nodeName
            ),
            $document
        );
        $node->appendChild($element->node);

        if (is_scalar($content)) {
            $element->createTextElement($content);
        }
        
        $element->__set('level', $level);

        return $element;
    }
    
    /**
     * Set element properties
     *
     * @param string $name  Name of property to set ('level')
     * @param mixed  $value Value of property
     *
     * @return void
     */
    public function __set($name, $value)
    {
        switch ($name) {
        case 'level':
            if (!is_int($value) && !ctype_digit($value)) {
                $value = 1;
            }
            $this->type = $value;
            $this->node->setAttributeNS(
                OpenDocument::NS_TEXT, 'outline-level', $value
            );
            break;
        default:
        }
    }
    
    /**
     * Get element properties
     *
     * @param string $name Name of property to retrieve
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ($value = parent::__get($name)) {
            return $value;
        }
        if (isset($this->$name)) {
            return $this->$name;
        }
    }
    
    /**
     * Generate element new style name
     *
     * @return string
     */
    public function generateStyleName()
    {
        self::$styleNameMaxNumber ++;
        return self::styleNamePrefix . self::$styleNameMaxNumber;
    }

    /************** Elements ***********************/
    
    /**
     * Create text element
     *
     * @param string $text Contents
     *
     * @return OpenDocument_Element_Text
     */
    public function createTextElement($text)
    {
        return OpenDocument_Element_Text::instance($this, $text);
    }

    /**
     * Create a hyperlink
     *
     * @param string $text     Content text for link
     * @param string $location URL
     * @param string $type     'simple'
     * @param string $target   Target frame
     * @param string $name     Name (id) of link
     *
     * @return OpenDocument_Element_Hyperlink
     */
    public function createHyperlink(
        $text, $location, $type = 'simple', $target = '', $name = ''
    ) {
        return OpenDocument_Element_Hyperlink::instance(
            $this, $text, $location, $type, $target, $name
        );
    }
    
    /**
     * Create span element
     *
     * @param string $text Content
     *
     * @return OpenDocument_Span
     */
    public function createSpan($text)
    {
        return OpenDocument_Element_Span::instance($this, $text);
    }
}
?>