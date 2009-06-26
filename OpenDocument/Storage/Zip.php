<?php
/**
* PEAR OpenDocument package
* 
* PHP version 5
*
* @category File Formats
* @package  OpenDocument
* @author   Christian Weiske <cweiske@php.et>
* @license  http://www.gnu.org/copyleft/lesser.html  Lesser General Public License 2.1
* @version  @package_version@
* @link     http://pear.php.net/package/OpenDocument
* @since    File available since Release 0.2.0
*/

/**
 * Zip storage - the default OpenDocument storage.
 * Creates one zip file containing several XML files.
 *
 * @author Christian Weiske <cweiske@php.net>
 */
class OpenDocument_Storage_Zip implements OpenDocument_Storage
{
    /**
     * File name to store file as
     *
     * @var string
     */
    protected $file = null;

    /**
     * Zip document
     *
     * @var Zip_Archive
     */
    protected $zip = null;

    /**
     * DOM document containing the content
     *
     * @var DOMDocument
     */
    protected $contentDom = null;

    /**
     * DOM document containing the meta data
     *
     * @var DOMDocument
     */
    protected $metaDom = null;

    /**
     * DOM document containing the settings
     *
     * @var DOMDocument
     */
    protected $settingsDom = null;

    /**
     * DOM document containing the styles
     *
     * @var DOMDocument
     */
    protected $stylesDom = null;



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
    public function create($type, $file = null)
    {
        if ($file !== null) {
            $this->checkWritability($file);
        }

        //load file content
        $this->loadFile(self::getTemlateFile($type));

        //reset file name to our new file to prevent overwriting the template
        $this->file = $file;
    }



    /**
     * Opens the given file, loading the XML into memory
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
    public function open($file)
    {
        $this->checkReadability();
        $this->checkWritability();

        $this->loadFile($file);
    }



    /**
     * Checks if the given file is writable
     *
     * @param string $file Path of file
     *
     * @return void
     *
     * @throws OpenDocument_Exception In case the file is not writable
     */
    public function checkWritability($file)
    {
        if (is_writable($file)) {
            return;
        }
        throw new OpenDocument_Exception('File is not writable: ' . $file);
    }



    /**
     * Checks if the given file is readable
     *
     * @param string $file Path of file
     *
     * @return void
     *
     * @throws OpenDocument_Exception In case the file is not readable
     */
    public function checkReadability($file)
    {
        if (is_readable($file)) {
            return;
        }
        throw new OpenDocument_Exception('File is not readable: ' . $file);
    }



    /**
     * Loads content of the given file.
     *
     * Sets $this->file to $file.
     * One needs to make sure the file is readable before calling
     * this method.
     *
     * @param string $file Filename
     *
     * @return void
     *
     * @throws Exception When the file is corrupt or does not exist.
     */
    protected function loadFile($file)
    {
        $zip = new Zip_Archive();
        if (!$zip->open($file)) {
            throw new OpenDocument_Exception('Cannot open ZIP file: ' . $file);
        }
        $this->contentDom  = $this->loadDomFromZip($zip, 'content.xml');
        $this->metaDom     = $this->loadDomFromZip($zip, 'meta.xml');
        $this->settingsDom = $this->loadDomFromZip($zip, 'settings.xml');
        $this->stylesDom   = $this->loadDomFromZip($zip, 'styles.xml');
        //FIXME: what to do with embedded files (e.g. images)?
    }



    /**
     * Loads the DOM document of the given file name from the zip archive
     *
     * @param Zip_Archive $zip  Opened ZIP file object
     * @param string      $file Relative path of file to load from zip
     *
     * @return DOMDocument Document of XML file
     *
     * @throws OpenDocument_Exception In case the file does not exist in 
     *                                the zip.
     */
    protected function loadDomFromZip(Zip_Archive $zip, $file)
    {
        $index = $zip->locate($file);
        if ($index === false) {
            throw new OpenDocument_Exception('File not found in zip: ' . $file);
        }

        $dom = new DOMDocument();
        $dom->loadXML($zip->getFromIndex($index));

        return $dom;
    }



    /**
     * Returns the DOM object containing the content.
     *
     * @return DOMDocument
     */
    public function getContentDom()
    {
    }

    /**
     * Returns the DOM object containing the meta data.
     *
     * @return DOMDocument
     */
    public function getMetaDom()
    {
    }

    /**
     * Returns the DOM object containing the settings.
     *
     * @return DOMDocument
     */
    public function getSettingsDom()
    {
    }

    /**
     * Returns the DOM object containing the styles.
     *
     * @return DOMDocument
     */
    public function getStylesDom()
    {
    }

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
    public function save($file = null)
    {
    }

    /**
     * Sets the DOM object containing the content.
     * <office:document-content>
     *
     * @param DOMDocument $content Content object
     *
     * @return void
     */
    public function setContentDom(DOMDocument $content)
    {
    }

    /**
     * Sets the DOM object containing the meta data.
     * <office:document-meta>
     *
     * @param DOMDocument $content Meta object
     *
     * @return void
     */
    public function setMetaDom(DOMDocument $meta)
    {
    }

    /**
     * Sets the DOM object containing the settings.
     * <office:document-settings>
     *
     * @param DOMDocument $content Settings object
     *
     * @return void
     */
    public function setSettingsDom(DOMDocument $settings)
    {
    }

    /**
     * Sets the DOM object containing the styles..
     * <office:document-styles>
     *
     * @param DOMDocument $content Styles object
     *
     * @return void
     */
    public function setStylesDom(DOMDocument $styles)
    {
    }

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
    public function addFile($path, $mimetype = null)
    {
        throw new OpenDocument_Exception('Adding files not supported yet');
    }

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
    public function removeFile($relpath)
    {
        throw new OpenDocument_Exception('Removing files not supported yet');
    }
}

?>