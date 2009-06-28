<?php
/**
* PEAR OpenDocument package
* 
* PHP version 5
*
* @category File_Formats
* @package  OpenDocument
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html Lesser General Public License 2.1
* @version  CVS: $Id$
* @link     http://pear.php.net/package/OpenDocument
* @since    File available since Release 0.2.0
*/

require_once 'OpenDocument.php';

/**
 * ZIP Manifest file
 *
 * @category File_Formats
 * @package  OpenDocument
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html Lesser General Public License 2.1
 * @link     http://pear.php.net/package/OpenDocument
 */
class OpenDocument_Manifest
{
    /**
     * DOM document containing the manifest
     *
     * @var DOMDocument
     */
    protected $dom = null;

    /**
     * Root node to add file entries to
     *
     * @var DOMElement
     */
    protected $fileroot = null;



    /**
     * Create a new instance
     */
    public function __construct()
    {
        $this->prepareDom();
    }



    /**
     * Prepares the DOM document used internally
     *
     * @return void
     */
    protected function prepareDom()
    {
        $this->dom = new DOMDocument('1.0', 'utf-8');
        $this->dom->formatOutput = true;

        $this->fileroot = $this->dom->createElementNS(
            OpenDocument::NS_MANIFEST,
            'manifest:manifest'
        );
        $this->dom->appendChild($this->fileroot);
    }



    /**
     * Add a file to the manifest
     *
     * @param string $file     Relative file path
     * @param string $mimetype MIME type of the file
     *
     * @return void
     */
    public function addFile($file, $mimetype)
    {
        $entry = $this->dom->createElementNS(
            OpenDocument::NS_MANIFEST,
            'manifest:file-entry'
        );
        $entry->setAttributeNS(
            OpenDocument::NS_MANIFEST,
            'manifest:full-path',
            $file
        );
        $entry->setAttributeNS(
            OpenDocument::NS_MANIFEST,
            'manifest:media-type',
            $mimetype
        );
        $this->fileroot->appendChild($entry);
    }



    /**
     * Returns the full XML representation of the manifest
     *
     * @return string
     */
    public function __toString()
    {
        return $this->dom->saveXML();
    }

}

?>
