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

/**
* Base for all document classes.
*
* @category File_Formats
* @package  OpenDocument
* @author   Alexander Pak <irokez@gmail.com>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html Lesser General Public License 2.1
* @link     http://pear.php.net/package/OpenDocument
*/
class OpenDocument_Document
{
    /**
     * DOMNode of content node
     *
     * @var DOMNode
     */
    protected $cursor;
    
    /**
     * DOMNode with style information
     *
     * @var DOMNode
     */
    protected $styles;
    
    /**
     * DOMNode with fonts declarations
     *
     * @var DOMNode
     */
    protected $fonts;
    
    /**
     * DOM document for content
     *
     * @var DOMDocument
     */
    protected $contentDOM;

    /**
     * DOMXPath object for content
     *
     * @var DOMXPath
     */
    protected $contentXPath;

    /**
     * DOMDocument for meta information
     *
     * @var DOMDocument
     */
    protected $metaDOM;

    /**
     * DOMXPath for meta information
     *
     * @var DOMXPath
     */
    protected $metaXPath;

    /**
     * DOMDocument for settings
     *
     * @var DOMDocument
     */
    protected $settingsDOM;

    /**
     * DOMXPath for settings
     *
     * @var DOMXPath
     */
    protected $settingsXPath;

    /**
     * DOMDocument for styles
     *
     * @var DOMDocument
     */
    protected $stylesDOM;

    /**
     * DOMXPath for styles
     *
     * @var DOMXPath
     */
    protected $stylesXPath;

    /**
     * Storage driver object
     *
     * @var OpenDocument_Storage
     */
    protected $storage = null;

    /**
     * Collection of children objects
     *
     * @var ArrayIterator
     */
    protected $children;



    /**
     * Constructor
     *
     * @param string $storage Storage object
     *
     * @throws OpenDocument_Exception
     */
    public function __construct(OpenDocument_Storage $storage)
    {
        $this->open($storage);
    }



    /**
     * Open the given file
     *
     * @param string $storage Storage object
     *
     * @return void
     *
     * @throw OpenDocument_Exception
     */
    public function open(OpenDocument_Storage $storage)
    {
        $this->storage = $storage;

        $this->mimetype = 'application/vnd.oasis.opendocument.text';

        $this->contentDOM   = $storage->getContentDom();
        $this->contentXPath = new DOMXPath($this->contentDOM);

        $this->metaDOM   = $storage->getMetaDom();
        $this->metaXPath = new DOMXPath($this->metaDOM);

        $this->settingsDOM   = $storage->getSettingsDom();
        $this->settingsXPath = new DOMXPath($this->settingsDOM);

        $this->stylesDOM   = $storage->getStylesDom();
        $this->stylesXPath = new DOMXPath($this->stylesDOM);

        //set cursor
        $this->cursor = $this->contentXPath->query(
            '/office:document-content/office:body/office:text'
        )->item(0);
        $this->styles = $this->contentXPath->query(
            '/office:document-content/office:automatic-styles'
        )->item(0);
        $this->fonts  = $this->contentXPath->query(
            '/office:document-content/office:font-face-decls'
        )->item(0);
        $this->contentXPath->registerNamespace('text', OpenDocument::NS_TEXT);
        
        $this->listChildren();
        $this->setMax();
    }



    /**
     * Provide read only access to cursor private variable
     *
     * @param string $name Variable to read
     *
     * @return mixed Variable contents
     */
    public function __get($name)
    {
        switch ($name) {
        case 'cursor':
            return $this->cursor;
        default:
        }
    }


    
    /**
     * Get children list
     *
     * @return ArrayIterator
     */
    public function getChildren()
    {
        return $this->children->getIterator();
    }


    
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
    }
    

    
    /**
     * Delete document child element
     *
     * @param OpenDocument_Element $element Element to remove
     *
     * @return void
     */
    public function deleteElement(OpenDocument_Element $element)
    {
        $this->cursor->removeChild($element->getNode());
        unset($element);
    }


    
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
    }


    
    /********************* Styles ****************************/   
    
    /**
     * Apply style information to object.
     *
     * If object has no style information yet, then create new
     * style node. If object style information is similar to other
     * object's style info, then apply the same style name.
     * And if object old style information was not shared with other
     * objects then delete old style info.
     * Otherwise leave old style info or just add new style description
     *
     * @param string                     $style_name Name of style to apply
     * @param string                     $name       Name of property to set
     *                                               (e.g. 'fo:font-weight')
     * @param mixed                      $value      Value of property
     * @param OpenDocument_StyledElement $object     Object to apply style to
     *
     * @return string Name of style that has been applied
     */
    public function applyStyle(
        $style_name, $name, $value, OpenDocument_StyledElement $object
    ) {
        //check if other nodes have the same style name
        $nodes = $this->cursor->getElementsByTagName('*');
        $count = 0;
        foreach ($nodes as $node) {
            if ($node->hasAttributeNS(OpenDocument::NS_TEXT, 'style-name')
                && $node->getAttributeNS(OpenDocument::NS_TEXT, 'style-name') == $style_name
            ) {
                $count ++;
                if ($count > 1) {
                    break;
                }
            }
        }

        $generate = false;

        //get style node
        if ($count > 1) {
            $style = $this->getStyleNode($style_name)->cloneNode(true);
            $this->styles->appendChild($style);
            $generate = true;
            $style_name = uniqid('tmp');//$object->generateStyleName();
            $style->setAttributeNS(OpenDocument::NS_STYLE, 'name', $style_name);
            $style->setAttributeNS(
                OpenDocument::NS_STYLE, 'family',
                constant(get_class($object) . '::styleFamily')
            );
        } else {
            $style = $this->getStyleNode($style_name);
        }

        if (empty($style)) {
            if (empty($style_name)) {
                $generate   = true;
                $style_name = uniqid('tmp');
            }
            $style = $this->contentDOM->createElementNS(OpenDocument::NS_STYLE, 'style');
            $style->setAttributeNS(OpenDocument::NS_STYLE, 'name', $style_name);
            //workaround for php5_2
            $style->setAttributeNS(
                OpenDocument::NS_STYLE, 'family',
                constant(get_class($object) . '::styleFamily')
            );
            $style->setAttributeNS(OpenDocument::NS_STYLE, 'parent-style-name', 'Standard');
            $this->styles->appendChild($style);
        }

        $nodes = $style->getElementsByTagNameNS(OpenDocument::NS_STYLE, 'text-properties');
        if ($nodes->length) {
            $text_properties = $nodes->item(0);
        } else {
            $text_properties = $this->contentDOM->createElementNS(
                OpenDocument::NS_STYLE, 'text-properties'
            );
            $style->appendChild($text_properties);
        }
        $text_properties->setAttribute($name, $value);

        //find alike style
        $nodes = $this->styles->getElementsByTagNameNS(
            OpenDocument::NS_STYLE, 'style'
        );
        foreach ($nodes as $node) {
            if (!$style->isSameNode($node)
                && $this->compareChildNodes($style, $node)
            ) {
                $style->parentNode->removeChild($style);
                return $node->getAttributeNS(OpenDocument::NS_STYLE, 'name');
            }
        }
        
        if ($generate) {
            $style_name = $object->generateStyleName();
            $style->setAttributeNS(OpenDocument::NS_STYLE, 'name', $style_name);
        }
        return $style->getAttributeNS(OpenDocument::NS_STYLE, 'name');
    }



    /**
     * Get array of style values
     *
     * @param string $style_name Name of style to retrieve properties from
     * @param array  $properties Array of namespace-prefixed properties to
     *                           retrieve
     *
     * @return array Key-value array of properties and their values
     */
    public function getStyle($style_name, array $properties)
    {
        $style = array();
        if ($node = $this->getStyleNode($style_name)) {
            $nodes = $node->getElementsByTagNameNS(
                OpenDocument::NS_STYLE, 'text-properties'
            );
            if ($nodes->length) {
                $text_properties = $nodes->item(0);
                foreach ($properties as $property) {
                    list($prefix, $name) = explode(':', $property);
                    $ns = $text_properties->lookupNamespaceURI($prefix);
                    $style[$property] = $text_properties->getAttributeNS($ns, $name);
                }
            }
        }
        return $style;
    }


    
    /**
     * Get style node
     *
     * @param string $style_name Name of style
     *
     * @return DOMNode Style node
     */
    protected function getStyleNode($style_name)
    {
        $nodes = $this->styles->getElementsByTagNameNS(OpenDocument::NS_STYLE, 'style');
        foreach ($nodes as $node) {
            $node->getAttributeNS(OpenDocument::NS_STYLE, 'name');
            if ($node->getAttributeNS(OpenDocument::NS_STYLE, 'name') == $style_name) {
                return $node;
            }
        }
        return false;
    }


    
    /**
     * Check if two style info are similar
     *
     * @param string $style_name1 Name of first style
     * @param string $style_name2 Name of second style
     *
     * @return bool True if both styles equal each other
     */
    protected function compareStyles($style_name1, $style_name2)
    {
        $style_node1 = $this->getStyleNode($style_name1);
        $style_node2 = $this->getStyleNode($style_name2);
        return $this->compareNodes($style_node1, $style_node2);
    }


    
    /********************* Fonts ****************************/
    
    /**
     * Get array of declared font names
     *
     * @return array Array of font nodes
     */
    protected function getFonts()
    {
        $nodes = $this->fonts->getElementsByTagNameNS(OpenDocument::NS_STYLE, 'font-face');
        $fonts = array();
        foreach ($nodes as $node) {
            $fonts[] = $node->getAttributeNS(OpenDocument::NS_STYLE, 'name');
        }
        return $fonts;
    }


    
    /**
     * Add new font declaration
     *
     * @param string $font_name   Name of font
     * @param string $font_family Name of font family
     *
     * @return void
     */
    public function addFont($font_name, $font_family = '')
    {
        if (!in_array($font_name, $this->getFonts())) {
            $node = $this->contentDOM->createElementNS(OpenDocument::NS_STYLE, 'font-face');
            $this->fonts->appendChild($node);
            $node->setAttributeNS(OpenDocument::NS_STYLE, 'name', $font_name);
            if (!strlen($font_family)) {
                $font_family = $font_name;
            }
            $node->setAttributeNS(OpenDocument::NS_SVG, 'font-family', $font_family);
        }
    }


    
    /**
     * Compare two DOMNode nodes and check if they are equal
     *
     * @param mixed $node1 First DOM node
     * @param mixed $node2 Second DOM node
     *
     * @return bool True if both are equal
     */
    protected function compareNodes($node1, $node2)
    {
        if (!($node1 instanceof DOMNode) || !($node2 instanceof DOMNode)) {
            return false;
        }
        $attributes = $node1->attributes;
        if ($attributes->length == $node2->attributes->length) {
            for ($i = 0; $i < $attributes->length; $i ++) {
                $name  = $attributes->item($i)->name;
                $value = $attributes->item($i)->value;
                if (!$node2->hasAttribute($name)
                    || $node2->getAttribute($name) != $value
                ) {
                    return false;
                }
            }
        } else {
            return false;
        }
        
        $children = $node1->childNodes;
        if ($children->length == $node2->childNodes->length) {
            for ($i = 0; $i < $children->length; $i ++) {
                $node    = $children->item($i);
                $matches = $this->getChildrenByName($node2, $node->nodeName);
                $test    = false;
                foreach ($matches as $match) {
                    if ($this->compareNodes($node, $match)) {
                        $test = true;
                        break;
                    }
                }
                if (!$test) {
                    return false;
                }
            }
        } else {
            return false;
        }
        
        return true;
    }


    
    /**
     * Compare DOMNode children
     *
     * @param DOMNode $node1 First DOM node
     * @param DOMNode $node2 Second DOM node
     *
     * @return bool True if they are equal
     */
    protected function compareChildNodes(DOMNode $node1, DOMNode $node2)
    {
        $children = $node1->childNodes;
        if ($children->length == $node2->childNodes->length) {
            for ($i = 0; $i < $children->length; $i ++) {
                $node    = $children->item($i);
                $matches = $this->getChildrenByName($node2, $node->nodeName);
                $test    = false;
                foreach ($matches as $match) {
                    if ($this->compareNodes($node, $match)) {
                        $test = true;
                        break;
                    }
                }
                if (!$test) {
                    return false;
                }
            }
        } else {
            return false;
        }
        
        return true;
    }


    
    /**
     * Get DOMNode children by name
     *
     * @param DOMNode $node Parent node
     * @param string  $name Name of children tags
     *
     * @return array
     */
    protected function getChildrenByName(DOMNode $node, $name)
    {
        $nodes = array();
        foreach ($node->childNodes as $node) {
            if ($node->nodeName == $name) {
                array_push($nodes, $node);
            }
        }
        return $nodes;
    }


    
    /**
     * Save changes in document or save as a new document
     * or under another name.
     *
     * @param string $filename Name to save document as. If no name
     *                         given, the name that was used to open
     *                         the file is used.
     *
     * @return void
     *
     * @throws OpenDocument_Exception
     */
    public function save($filename = null)
    {
        $storage = $this->storage;
        $storage->setContentDom($this->contentDOM);
        $storage->setMetaDom($this->metaDOM);
        $storage->setSettingsDom($this->settingsDOM);
        $storage->setStylesDom($this->stylesDOM);
        $storage->save($filename);
    }



    /**
     * Returns the internal DOM document of the given type.
     * Should be used for debugging and internal development purposes
     * only - e.g. unit testing.
     *
     * @param string $type DOM to fetch: styles, manifest, settings,
     *                                   content, meta
     *
     * @return DOMDocument Desired DOM document
     *
     * @throws OpenDocument_Exception If the type is unknown.
     */
    public function getDOM($type)
    {
        $variable = $type . 'DOM';
        if (isset($this->$variable)) {
            return $this->$variable;
        }
        throw new OpenDocument_Exception('No DOM for ' . $type);
    }



    /**
     * Returns the internal XPath object of the given type.
     * Should be used for debugging and internal development purposes
     * only - e.g. unit testing.
     *
     * @param string $type XPath to fetch: styles, manifest, settings,
     *                     content, meta
     *
     * @return DOMXPath Desired xpath object
     *
     * @throws OpenDocument_Exception If the type is unknown.
     */
    public function getXPath($type)
    {
        $variable = $type . 'XPath';
        if (isset($this->$variable)) {
            return $this->$variable;
        }
        throw new OpenDocument_Exception('No XPath for ' . $type);
    }
}
?>