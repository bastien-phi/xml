<?php

declare(strict_types=1);

namespace VeeWee\Xml\Xmlns;

/**
 * @psalm-immutable
 */
final class Xmlns
{
    private string $xmlns;

    private function __construct(string $xmlns)
    {
        $this->xmlns = $xmlns;
    }

    /**
     * @psalm-pure
     */
    public static function xml(): self
    {
        return new self('http://www.w3.org/XML/1998/namespace');
    }

    /**
     * @psalm-pure
     */
    public static function xsd(): self
    {
        return new self('http://www.w3.org/2001/XMLSchema-instance');
    }

    public static function load(string $namespace)
    {
        return new self($namespace);
    }

    public function value(): string
    {
        return $this->xmlns;
    }

    public function matches(Xmlns $other): bool
    {
        return $this->value() === $other->value();
    }
}
