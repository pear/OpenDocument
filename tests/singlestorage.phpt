--TEST--
Test Single XML storage functionality
--FILE--
<?php
require_once 'OpenDocument/Storage/Single.php';
$single = new OpenDocument_Storage_Single();
$single->create('text');
$doms = array(
    'content'  => $single->getContentDom(),
    'meta'     => $single->getMetaDom(),
    'settings' => $single->getSettingsDom(),
    'styles'   => $single->getStylesDom()
);

foreach ($doms as $type => $dom) {
    $root = $dom->firstChild;
    $root->appendChild(
        $dom->createElement('test', $type . '-unittest')
    );
}
$single->setContentDom($doms['content']);
$single->setMetaDom($doms['meta']);
$single->setSettingsDom($doms['settings']);
$single->setStylesDom($doms['styles']);

$name = sys_get_temp_dir() . '/opendocumentunittest-single';
$single->save($name);
unset($single, $dom, $root);

var_dump(file_exists($name));

$single = new OpenDocument_Storage_Single();
$single->open($name);
$cont = $single->getContentDom();
var_dump(
$cont,$single,
    $cont->firstChild->lastChild->tagName,
    $cont->firstChild->lastChild->nodeValue,
    $single->getMetaDom()->firstChild->lastChild->nodeValue,
    $single->getSettingsDom()->firstChild->lastChild->nodeValue,
    $single->getStylesDom()->firstChild->lastChild->nodeValue
);
?>
--CLEAN--
<?php
#unlink(sys_get_temp_dir() . '/opendocumentunittest-single');
?>
--EXPECT--
bool(true)
string(4) "test"
string(16) "content-unittest"
string(13) "meta-unittest"
string(17) "settings-unittest"
string(15) "styles-unittest"