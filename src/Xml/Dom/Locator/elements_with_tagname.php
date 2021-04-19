<?php

declare(strict_types=1);

namespace VeeWee\Xml\Dom\Locator;

use DOMDocument;
use DOMElement;
use VeeWee\Xml\Dom\Collection\NodeList;
use function VeeWee\Xml\Dom\Locator\Element\locate_by_tag_name;

/**
 * @return callable(DOMDocument): NodeList<DOMElement>
 */
function elements_with_tagname(string $tagName): callable
{
    return
        /**
         * @return NodeList<DOMElement>
         */
        static fn (DOMDocument $document): NodeList
            => locate_by_tag_name($document->documentElement, $tagName);
}
