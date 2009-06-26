--TEST--
Test if OpenDocument_Manifest works
--FILE--
<?php
require_once 'OpenDocument/Manifest.php';
$m = new OpenDocument_Manifest();
$m->addFile('content.xml', 'text/xml');
$m->addFile('settings.xml', 'text/xml');
$m->addFile('images/1.png', 'image/png');
echo (string)$m;
?>
--EXPECT--
<?xml version="1.0" encoding="utf-8"?>
<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">
  <manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>
  <manifest:file-entry manifest:full-path="settings.xml" manifest:media-type="text/xml"/>
  <manifest:file-entry manifest:full-path="images/1.png" manifest:media-type="image/png"/>
</manifest:manifest>