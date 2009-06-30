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
* @since    File available since Release 0.1.0
*/

/**
* OpenDocument_Style abstract class
* 
* OpenDocument_Style absract class - handles element style
*  all other elements inherit from this one
*
* @category File_Formats
* @package  OpenDocument
* @author   Alexander Pak <irokez@gmail.com>
* @license  http://www.gnu.org/copyleft/lesser.html  Lesser General Public License 2.1
* @link     http://pear.php.net/package/OpenDocument
* @since    File available since Release 0.1.0
*/
abstract class OpenDocument_Style
{
    /**
     * OpenDocument Element
     *
     * @var OpenDocument_StyledElement
     */
    protected $element;
    
    /**
     * Style map of properties
     *
     * @var array
     */
    protected $map;
    
    /**
     * Constructor
     *
     * @param OpenDocument_StyledElement $element Element the style is for
     */
    public function __construct(OpenDocument_StyledElement $element)
    {
        $this->element = $element;
        $this->_loadStyle();
    }
    
    /**
     * Load element style information
     *
     * @return void
     */
    private function _loadStyle()
    {
        $map = array_flip($this->map);
        $style = $this->element->getStyle(array_keys($map));
        foreach ($style as $name => $value) {
            if (isset($map[$name])) {
                $this->$map[$name] = $value;
            }
        }
    }
    
    /**
     * Magic method: Set property value
     * Can be used to set values for properties defined in $map
     *
     * @param string $name  Name of property
     * @param mixed  $value Name of value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        if (isset($this->map[$name])) {
            $this->element->applyStyle($this->map[$name], $value);
            $this->$name = $value;
        }
    }
    
    /**
     * Magic method: Get property value
     *
     * @param string $name Name of property to retrieve value for
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            return null;
        }
    }
    
    /**
     * Export style information
     *
     * @return array Key-value array of style names and values
     */
    public function export()
    {
        $style = array();
        foreach ($this->map as $name => $value) {
            $style[$name] = $this->__get($name);
        }
        return $style;
    }
    
    /**
     * Import style information
     *
     * @param array $style Key-values: style names and values
     *
     * @return void
     */
    public function import($style)
    {
        foreach ($style as $name => $value) {
            if (isset($this->map[$name])) {
                $this->__set($name, $value);
            }
        }
    }
    
    /**
     * Copy style from another element
     *
     * @param OpenDocument_Style $styleObject Style to import
     *
     * @return void
     */
    public function copy(OpenDocument_Style $styleObject)
    {
        $style = $styleObject->export();
        $this->import($style);
    }
}
?>