<?php

declare(strict_types=1);

namespace VeeWee\Xml\Encoding\Internal\Encoder\Builder;

use DOMDocument;
use DOMNode;
use Psl\Exception\InvariantViolationException;
use VeeWee\Xml\Encoding\Exception\EncodingException;
use function Psl\Dict\map_with_key;
use function VeeWee\Xml\Dom\Builder\nodes;

/**
 * @psalm-internal VeeWee\Xml\Encoding
 * @return callable(DOMDocument): list<DOMNode>
 *
 * @throws EncodingException
 * @throws InvariantViolationException
 */
function root(array $data): callable
{
    if (is_node_list($data)) {
        throw EncodingException::invalidRoot('list');
    }

    return nodes(
        ...map_with_key(
            $data,
            static fn (string $key, array|string $value): callable
                => parent_node($key, $value)
        )
    );
}
