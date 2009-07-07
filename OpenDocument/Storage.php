<?php
/**
* PEAR OpenDocument package
* 
* PHP version 5
*
* @category File_Formats
* @package  OpenDocument
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  Lesser General Public License 2.1
* @version  CVS: $Id$
* @link     http://pear.php.net/package/OpenDocument
* @since    File available since Release 0.2.0
*/

/**
 * Generic OpenDocument data and file storage interface.
 * Each storage mechanism needs to implement this interface.
 *
 * The OpenDocument specification defines two document
 * representations:
 * - one single large XML document
 * - a ZIP file containing several subdocuments
 *
 * By providing an implementation agnostic interface, we can
 * easily support both modes.
 *
 * @category File_Formats
 * @package  OpenDocument
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html Lesser General Public License 2.1
 * @link     http://pear.php.net/package/OpenDocument
 */
interface OpenDocument_Storage
{
    /**
     * Creates a new file.
     * The file name may be passed, but can be omitted if the
     * final storage location is not known yet.
     *
     * Storage drivers may choose to create temporary files or
     * directories in case no file name is given here.
     *
     * @param string $type Document type ('text', 'spreadsheet')
     * @param string $file Name of the file to be created
     *
     * @return void
     *
     * @throws OpenDocument_Exception In case creating the given file
     *                                is not possible.
     */
    public function create($type, $file = null);

    /**
     * Opens the given file.
     * An implementation might open the zip file
     * or verify that the file itself exists.
     *
     * @param string $file Path of the file to open.
     *
     * @return void
     *
     * @throws OpenDocument_Exception In case loading the file
     *                                did not work or the file
     *                                does not exist.
     *
     * @see create()
     */
    public function open($file);

    /**
     * Returns the MIME type of the opened file.
     *
     * @return string MIME Type.
     */
    public function getMimeType();

    /**
     * Returns the Dom object containing the content.
     *
     * @return DOMDocument
     */
    public function getContentDom();

    /**
     * Returns the Dom object containing the meta data.
     *
     * @return DOMDocument
     */
    public function getMetaDom();

    /**
     * Returns the Dom object containing the settings.
     *
     * @return DOMDocument
     */
    public function getSettingsDom();

    /**
     * Returns the Dom object containing the styles.
     *
     * @return DOMDocument
     */
    public function getStylesDom();

    /**
     * Saves the file as the given file name.
     *
     * @param string $file Path of the file to open.
     *
     * @return void
     *
     * @throws OpenDocument_Exception In case saving the file
     *                                did not work.
     *
     * @see create()
     * @see open()
     */
    public function save($file = null);

    /**
     * Sets the DOM object containing the content.
     * <office:document-content>
     *
     * @param DOMDocument $content Content object
     *
     * @return void
     */
    public function setContentDom(DOMDocument $content);

    /**
     * Sets the DOM object containing the meta data.
     * <office:document-meta>
     *
     * @param DOMDocument $meta Meta object
     *
     * @return void
     */
    public function setMetaDom(DOMDocument $meta);

    /**
     * Sets the DOM object containing the settings.
     * <office:document-settings>
     *
     * @param DOMDocument $settings Settings object
     *
     * @return void
     */
    public function setSettingsDom(DOMDocument $settings);

    /**
     * Sets the DOM object containing the styles..
     * <office:document-styles>
     *
     * @param DOMDocument $styles Styles object
     *
     * @return void
     */
    public function setStylesDom(DOMDocument $styles);

    /**
     * Adds a file to the document.
     * Returns the file name that has to be used to reference
     * the file in the document content.
     *
     * @param string $path     File path
     * @param string $mimetype MIME type of the file. Leave it null
     *                         for auto detection.
     *
     * @return string Relative filename that has to be used to
     *                reference the file in content.
     *
     * @see removeFile()
     */
    public function addFile($path, $mimetype = null);

    /**
     * Removes an already added file from the document.
     *
     * @param string $relpath Relative path that was returned
     *                        by addFile()
     *
     * @return void
     *
     * @see addFile()
     */
    public function removeFile($relpath);



    /**
     * Imports data from another storage object
     *
     * @param OpenDocument_Storage $storage Storage object
     *
     * @return void
     *
     * @throws OpenDocument_Exception In case something goes wrong
     */
    public function import(OpenDocument_Storage $storage);
}

?>