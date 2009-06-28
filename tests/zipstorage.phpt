--TEST--
Test Zip storage functionality
--FILE--
<?php
require_once 'OpenDocument/Storage/Zip.php';
$zip = new OpenDocument_Storage_Zip();
$zip->create('text');
$doms = array(
    'content'  => $zip->getContentDom(),
    'meta'     => $zip->getMetaDom(),
    'settings' => $zip->getSettingsDom(),
    'styles'   => $zip->getStylesDom()
);

foreach ($doms as $type => $dom) {
    $root = $dom->firstChild;
    $root->appendChild(
        $dom->createElement('test', $type . '-unittest')
    );
}
$zip->setContentDom($doms['content']);
$zip->setMetaDom($doms['meta']);
$zip->setSettingsDom($doms['settings']);
$zip->setStylesDom($doms['styles']);

$name = sys_get_temp_dir() . '/opendocumentunittest-zip';
$zip->save($name);
unset($zip, $dom, $root);

var_dump(file_exists($name));

$zip = new OpenDocument_Storage_Zip();
$zip->open($name);
$cont = $zip->getContentDom();
var_dump(
    $cont->firstChild->lastChild->tagName,
    $cont->firstChild->lastChild->nodeValue,
    $zip->getMetaDom()->firstChild->lastChild->nodeValue,
    $zip->getSettingsDom()->firstChild->lastChild->nodeValue,
    $zip->getStylesDom()->firstChild->lastChild->nodeValue
);
?>
--CLEAN--
<?php
unlink(sys_get_temp_dir() . '/opendocumentunittest-zip');
?>
--EXPECT--
bool(true)
string(4) "test"
string(16) "content-unittest"
string(13) "meta-unittest"
string(17) "settings-unittest"
string(15) "styles-unittest"