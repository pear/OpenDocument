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
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  Lesser General Public License 2.1
* @version  CVS: $Id$
* @link     http://pear.php.net/package/OpenDocument
* @since    File available since Release 0.1.0
*/

require_once 'OpenDocument/Document.php';
require_once 'OpenDocument/Element/Text.php';
require_once 'OpenDocument/Element/Span.php';
require_once 'OpenDocument/Element/Paragraph.php';
require_once 'OpenDocument/Element/Heading.php';
require_once 'OpenDocument/Element/Bookmark.php';
require_once 'OpenDocument/Element/Hyperlink.php';

/**
* Text document
*
* @category File_Formats
* @package  OpenDocument
* @author   Alexander Pak <irokez@gmail.com>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html Lesser General Public License 2.1
* @link     http://pear.php.net/package/OpenDocument
*/
class OpenDocument_Document_Text extends OpenDocument_Document
{
    /**
     * Fills $this->children with all DOMNodes
     *
     * @return void
     */
    protected function listChildren()
    {
        $this->children = new ArrayObject();
        if ($this->cursor instanceof DOMNode) {
            $childrenNodes = $this->cursor->childNodes;
            foreach ($childrenNodes as $child) {
                switch ($child->nodeName) {
                case 'text:p':
                    $element = new OpenDocument_Element_Paragraph($child, $this);
                    break;
                case 'text:h':
                    $element = new OpenDocument_Element_Heading($child, $this);
                    break;
                default:
                    $element = false;
                }
                if ($element) {
                    $this->children->append($element);
                }
            }
        }
    }//protected function listChildren()
    
    
    
    /**
     * Set maximum values of style name suffixes
     *
     * @return void
     */
    protected function setMax()
    {
        $classes = array(
            'OpenDocument_Element_Paragraph',
            'OpenDocument_Element_Heading',
            'OpenDocument_Element_Hyperlink'
        );
        $max = array();
        if ($this->cursor instanceof DOMNode) {
            $nodes = $this->cursor->getElementsByTagName('*');
            foreach ($nodes as $node) {
                if ($node->hasAttributeNS(OpenDocument::NS_TEXT, 'style-name')) {
                    $style_name = $node->getAttributeNS(OpenDocument::NS_TEXT, 'style-name');
                    foreach ($classes as $class) {
                        $reflection = new ReflectionClass($class);
                        $prefix = $reflection->getConstant('styleNamePrefix');
                        if (preg_match("/^$prefix(\d)+$/", $style_name, $m)) {
                            $max[$class] = isset($max[$class])
                                ? ($max[$class] < $m[1] ? $m[1]
                                : $max[$class]) : $m[1];
                        }
                    }
                }
            }
        }
        foreach ($classes as $class) {
            $method = new ReflectionMethod($class, 'setStyleNameMaxNumber');
            if (!isset($max[$class])) {
                $max[$class] = 0;
            }
            $method->invoke(null, $max[$class]);
        }
    }//protected function setMax()


    
    /**
     * Create paragraph
     *
     * @param string $text Content of paragraph
     *
     * @return OpenDocument_Element_Paragraph
     */
    public function createParagraph($text = '')
    {
        return OpenDocument_Element_Paragraph::instance($this, $text);
    }


    
    /**
     * Create heading
     *
     * @param string  $text  Contents of heading
     * @param integer $level Level 1-6 (1 highest)
     *
     * @return OpenDocument_Heading
     */
    public function createHeading($text = '', $level = 1)
    {
        return OpenDocument_Element_Heading::instance($this, $text, $level);
    }



    /**
     * Create a bookmark
     *
     * @param string $name Readable name of the bookmark
     * @param string $type 'start' or 'end'
     *
     * @return OpenDocument_Element_Bookmark
     *
     * @todo finish method
     */
    public function createBookmark($name, $type = 'start')
    {
        if (!in_array($type, array('start', 'end'))) {
            $type = 'start';
        }
        $bookmark = new OpenDocument_Element_Bookmark(
            $this->contentDOM->createElementNS(OpenDocument::NS_TEXT, 'bookmark-' . $type),
            $this, $name, $type
        );
        $this->cursor->appendChild($bookmark->getNode());
        $bookmark->getNode()->setAttributeNS(OpenDocument::NS_TEXT, 'name', $name);
        return $bookmark;
    }

}
?>