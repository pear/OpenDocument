--TEST--
Test creating, adding content, saving and reading.
--FILE--
<?php
require_once 'OpenDocument.php';
$doc = OpenDocument::text();
$doc->createHeading('Headline 1', 1);
$doc->createParagraph('This is a paragraph');

$name = sys_get_temp_dir() . '/opendocumentunittest-create-save-read.odt';
$doc->save($name);
unset($doc);

var_dump(file_exists($name));

$doc      = OpenDocument::open($name);
$children = $doc->getChildren();

$first = reset($children);
var_dump(get_class($first));
$second = next($children);
var_dump(get_class($second));
?>
--CLEAN--
<?php
unlink(sys_get_temp_dir() . '/opendocumentunittest-create-save-read.odt');
?>
--EXPECT--
bool(true)
string(28) "OpenDocument_Element_Heading"
string(30) "OpenDocument_Element_Paragraph"
