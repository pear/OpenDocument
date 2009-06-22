<?php
require_once 'OpenDocument/Element.php';

class OpenDocument_Element_Bookmark extends OpenDocument_Element
{
    private $name;
    
    public function __constructor($node, $document, $name)
    {
        parent::__constructor($node, $document);
        $this->name = $name;
    }
}
?>