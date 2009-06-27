--TEST--
Paragraphs and span style families.
--DESCRIPTION--
Check if paragraphs and span elements get different style families.
- bug #13002: styles for span elements are rendered with incorrect style:family
--FILE--
<?php
require_once 'OpenDocument/Debug/Text.php';
$doc = new OpenDocument_Debug_Text();
$xp = $doc->getXPath('styles');

$p = $doc->createParagraph('red paragraph');
$p->style->color = '#FF0000';
$sn = $doc->getStyleNode($p->getStyleName());
var_dump($sn->getAttributeNS(OpenDocument::NS_STYLE, 'family'));

$s = $p->createSpan(' with a green span');
$s->style->color = '#00FF00';
$sn = $doc->getStyleNode($s->getStyleName());
var_dump($sn->getAttributeNS(OpenDocument::NS_STYLE, 'family'));
?>
--EXPECT--
string(9) "paragraph"
string(4) "text"
