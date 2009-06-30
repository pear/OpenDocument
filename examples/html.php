<?php
require_once 'OpenDocument.php'; // open document class

//open test.odt
$odt = OpenDocument::open('test.odt');
//output content as html
echo toHTML($odt) . "\n";

function toHTML($obj)
{
    $html = '';
    foreach ($obj->getChildren() as $child) {
        switch (get_class($child)) {
        case 'OpenDocument_Element_Text':
            $html .= $child->text;
            break;
        case 'OpenDocument_Element_Paragraph':
            $html .= '<p>';
            $html .= toHTML($child);
            $html .= '</p>';
            break;
        case 'OpenDocument_Element_Span':
            $html .= '<span>';
            $html .= toHTML($child);
            $html .= '</span>';
            break;
        case 'OpenDocument_Element_Heading':
            $html .= '<h' . $child->level . '>';
            $html .= toHTML($child);
            $html .= '</h' . $child->level . '>';
            break;
        case 'OpenDocument_Element_Hyperlink':
            $html .= '<a href="' . $child->location . '" target="' . $child->target . '">';
            $html .= toHTML($child);
            $html .= '</a>';
            break;
        default:
            $html .= '<del>unknown element</del>';
        }
    }
    return $html;
}

?>