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

require_once 'OpenDocument/Manifest.php';
require_once 'OpenDocument/Storage.php';

/**
 * Single XML file storage driver.
 * Saves all information in one big XML file.
 * May not contain any images or other files.
 *
 * @category File_Formats
 * @package  OpenDocument
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html Lesser General Public License 2.1
 * @link     http://pear.php.net/package/OpenDocument
 */
class OpenDocument_Storage_Single implements OpenDocument_Storage
{
    /**
     * File name to store file as
     *
     * @var string
     */
    protected $file = null;

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

        include_once 'OpenDocument/Storage/Zip.php';
        $zipstore = new OpenDocument_Storage_Zip();
        $zipstore->create($type);
        $this->import($zipstore);

        //reset file name to our new file to prevent overwriting the template
        $this->file = $file;
    }//public function create(..)



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
        $this->checkReadability($file);
        $this->checkWritability($file);

        $this->loadFile($file);
    }//public function open(..)



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
     * @throws OpenDocument_Exception When the file is corrupt
     *                                or does not exist.
     */
    protected function loadFile($file)
    {
        $dom = DOMDocument::load($file);
        if ($dom->documentElement->nodeName != 'office:document') {
            throw new OpenDocument_Exception(
                'No OpenDocument file: First XML tag is '
                . $dom->documentElement->nodeName
            );
        }

        $domtags = array(
            'contentDom'  => 'office:document-content',
            'metaDom'     => 'office:document-meta',
            'settingsDom' => 'office:document-settings',
            'stylesDom'   => 'office:document-styles',
        );

        foreach ($domtags as $var => $roottagname) {
            $this->$var = new DOMDocument('1.0', 'utf-8');
            $root       = $this->$var->createElementNS(
                OpenDocument::NS_OFFICE,
                $roottagname
            );
            $this->$var->appendChild($root);
        }

        $data = array(
            'content'  => array(),
            'meta'     => array(),
            'settings' => array(),
            'styles'   => array(),
        );

        $root = $dom->documentElement;
        for ($n = 0; $n < $root->childNodes->length; ++$n) {
            $node = $root->childNodes->item($n);
            switch ($node->nodeName) {
            case 'office:meta':
                $data['meta'][] = $node;
                break;
            case 'office:settings':
                $data['settings'][] = $node;
                break;
            case 'office:font-face-decls':
            case 'office:automatic-styles':
                $data['content'][] = $node;
                break;
            case 'office:styles':
            case 'office:master-styles':
                $data['styles'][] = $node;
                break;
            case 'office:scripts':
            case 'office:body':
                $data['content'][] = $node;
                break;
            }
        }

        foreach ($data as $type => $nodes) {
            $sdom = $this->{$type . 'Dom'};
            foreach ($nodes as $node) {
                $newnode = $sdom->importNode($node, true);
                $sdom->documentElement->appendChild($newnode);
            }
        }
    }//protected function loadFile(..)



    /**
     * Returns the MIME type of the opened file.
     *
     * @return string MIME Type.
     */
    public function getMimeType()
    {
        //FIXME: implement functionality
        //load from manifest first
        //if null, load from content
        return 'application/vnd.oasis.opendocument.text';
    }



    /**
     * Returns the DOM object containing the content.
     *
     * @return DOMDocument
     */
    public function getContentDom()
    {
        return $this->contentDom;
    }



    /**
     * Returns the DOM object containing the meta data.
     *
     * @return DOMDocument
     */
    public function getMetaDom()
    {
        return $this->metaDom;
    }



    /**
     * Returns the DOM object containing the settings.
     *
     * @return DOMDocument
     */
    public function getSettingsDom()
    {
        return $this->settingsDom;
    }



    /**
     * Returns the DOM object containing the styles.
     *
     * @return DOMDocument
     */
    public function getStylesDom()
    {
        return $this->stylesDom;
    }



    /**
     * Saves the file as the given file name.
     *
     * @param string $file Path of the file to save.
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
        if ($file === null) {
            $file = $this->file;
        }
        if ($file === null) {
            throw new OpenDocument_Exception(
                'No file name given for saving'
            );
        }

        $dom = new DOMDocument('1.0', 'utf-8');
        $root = $dom->createElementNS(
            OpenDocument::NS_OFFICE,
            'office:document'
        );
        $dom->appendChild($root);

        $doms = array(
            $this->metaDom,
            $this->settingsDom,
            $this->stylesDom,
            $this->contentDom
        );
        foreach ($doms as $part) {
            $kids = $part->documentElement->childNodes;
            for ($n = 0; $n < $kids->length; ++$n) {
                $partnode = $kids->item($n);
                $newnode = $dom->importNode($partnode, true);
                $root->appendChild($newnode);
            }
        }

        //set mime type
        //FIXME: set mime type
        $bytes = $dom->save($file);
        if ($bytes === false) {
            throw new OpenDocument_Exception('Saving failed');
        }
    }//public function save(..)



    /**
     * Extracts the textual MIME type from the content DOM object
     *
     * @param DOMDocument $content DOM object of content
     *
     * @return string MIME type
     */
    protected function getMimeTypeFromContent(DOMDocument $content)
    {
        //FIXME: read root mime type attribute from dom
        return 'application/vnd.oasis.opendocument.text';
    }//protected function getMimeTypeFromContent(..)



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
        $this->contentDom = $content;
    }



    /**
     * Sets the DOM object containing the meta data.
     * <office:document-meta>
     *
     * @param DOMDocument $meta Meta object
     *
     * @return void
     */
    public function setMetaDom(DOMDocument $meta)
    {
        $this->metaDom = $meta;
    }



    /**
     * Sets the DOM object containing the settings.
     * <office:document-settings>
     *
     * @param DOMDocument $settings Settings object
     *
     * @return void
     */
    public function setSettingsDom(DOMDocument $settings)
    {
        $this->settingsDom = $settings;
    }



    /**
     * Sets the DOM object containing the styles.
     * <office:document-styles>
     *
     * @param DOMDocument $styles Styles object
     *
     * @return void
     */
    public function setStylesDom(DOMDocument $styles)
    {
        $this->stylesDom = $styles;
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
        throw new OpenDocument_Exception('Adding files not supported');
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
        throw new OpenDocument_Exception('Removing files not supported');
    }



    /**
     * Imports data from another storage object
     *
     * @param OpenDocument_Storage $storage Storage object
     *
     * @return void
     *
     * @throws OpenDocument_Exception In case something goes wrong
     */
    public function import(OpenDocument_Storage $storage)
    {
        $this->setContentDom($storage->getContentDom());
        $this->setMetaDom($storage->getMetaDom());
        $this->setSettingsDom($storage->getSettingsDom());
        $this->setStylesDom($storage->getStylesDom());
        //FIXME: check for files and throw exception if there are some
        // since we don't suppot them anyway - or ignore them silently
        //FIXME: mime type
    }//public function import(..)

}

?>
