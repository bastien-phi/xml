<?php

declare(strict_types=1);

namespace VeeWee\Xml\Tests\ErrorHandling;

use Generator;
use PHPUnit\Framework\TestCase;
use VeeWee\Xml\Exception\RuntimeException;
use XMLReader;
use function VeeWee\Xml\ErrorHandling\stop_on_first_issue;

final class StopOnFirstIssueTest extends TestCase
{
    public function test_it_can_successfully_iterate_over_valid_xml(): void
    {
        $iterator = $this->createIteratorForXml(
            <<<'EOXML'
<root>
    <user>Jos</user>
    <user>Bos</user>
    <user>Mos</user>
    <user>Los</user>
</root>
EOXML
        );

        static::assertSame(
            ['<user>Jos</user>', '<user>Bos</user>', '<user>Mos</user>', '<user>Los</user>'],
            [...$iterator]
        );
    }

    public function test_it_stops_iterating_on_first_read_chunk(): void
    {
        $iterator = $this->createIteratorForXml(
            <<<'EOXML'
<root>
    <user>Bos</user>
    <user>Mos</user>    
    <invalid
    <user>Los</user>
</root>
EOXML
        );

        [$exception, $found] = $this->runExpectingAnXmlIssue($iterator);

        static::assertCount(0, $found);

        $this->expectExceptionObject($exception);
        throw $exception;
    }

    public function test_it_stops_iterating_on_the_first_error(): void
    {
        $iterator = $this->createIteratorForXml(
            <<<'EOXML'
<root>
    <note>We need lots of valid users first - since it reads with a buffer ...</note>
    <user>Jos</user>
    <user>Bos</user>
    <user>Mos</user>
    <user>Jos</user>
    <user>Bos</user>
    <user>Mos</user>
    <user>Jos</user>
    <user>Bos</user>
    <user>Mos</user>
    <user>Jos</user>
    <user>Bos</user>
    <user>Mos</user>
    <user>Jos</user>
    <user>Bos</user>
    <user>Jos</user>
    <user>Bos</user>
    <user>Mos</user>
    <user>Mos</user>
    <user>Jos</user>
    <user>Bos</user>
    <user>Mos</user>
    <user>Jos</user>
    <user>Bos</user>
    <user>Mos</user>
    <user>Mos</user>
    <user>Jos</user>
    <user>Bos</user>
    <user>Mos</user>
    <user>Mos</user>
    <user>Jos</user>
    <user>Bos</user>
    <user>Mos</user>    
    <invalid
    <user>Los</user>
</root>
EOXML
        );

        [$exception, $found] = $this->runExpectingAnXmlIssue($iterator);

        static::assertGreaterThan(0, count($found));
        foreach ($found as $item) {
            static::assertMatchesRegularExpression('{<user>[A-Z]os</user>}', $item);
        }

        $this->expectExceptionObject($exception);
        throw $exception;
    }

    private function createIteratorForXml(string $xml): Generator
    {
        $reader = new XMLReader();
        $reader->xml($xml);

        return stop_on_first_issue(
            static fn (): bool => $reader->read(),
            static fn (): ?string =>
            $reader->nodeType === XMLReader::ELEMENT && $reader->name === 'user' && $reader
                ? $reader->readOuterXml() ?: null
                : null,
        );
    }

    private function runExpectingAnXmlIssue(Generator $iterator): array
    {
        try {
            $exception = null;
            $found = [];
            foreach ($iterator as $item) {
                $found[] = $item;
            }
        } catch (RuntimeException $exception) {
        }

        static::assertInstanceOf(RuntimeException::class, $exception);

        return [$exception, $found];
    }
}
