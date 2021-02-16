<?php

declare(strict_types=1);

namespace VeeWee\Tests\Stress\Memory;

use Generator;
use PHPUnit\Framework\TestCase;
use VeeWee\Xml\Reader\Reader;
use VeeWee\Xml\Writer\Writer;
use function Safe\tempnam;
use function VeeWee\Xml\Reader\Matcher\node_name;
use function VeeWee\Xml\Writer\Builder\children;
use function VeeWee\Xml\Writer\Builder\document;
use function VeeWee\Xml\Writer\Builder\element;
use function VeeWee\Xml\Writer\Builder\namespace_attribute;
use function VeeWee\Xml\Writer\Builder\prefixed_attribute;
use function VeeWee\Xml\Writer\Builder\prefixed_element;
use function VeeWee\Xml\Writer\Configurator\indentation;

final class ReadWriteTest extends TestCase
{
    private string $file = '';
    private string $previousLimit = '';

    protected function setUp(): void
    {
        $this->file = tempnam(sys_get_temp_dir(), 'xmlwriter');
        $this->previousLimit = ini_get('memory_limit');
        ini_set('memory_limit', $_ENV['STRESS_MAX_MB'].'MB');

        fwrite(STDOUT, 'Writing to file: '.$this->file.PHP_EOL);
    }

    protected function tearDown(): void
    {
        ini_set('memory_limit', $this->previousLimit);
        @unlink($this->file);
    }

    public function test_it_can_handle_a_shitload_of_xml(): void
    {
        $maxMemoryMb = (int) $_ENV['STRESS_MAX_MB'];

        fwrite(STDOUT, 'Running Read/Write stress test...'.PHP_EOL);
        fwrite(STDOUT, 'Number of tags: '.$_ENV['STRESS_TAGS_M'].'M'.PHP_EOL);
        fwrite(STDOUT, 'Max memory: '.$_ENV['STRESS_MAX_MB'].'MB'.PHP_EOL.PHP_EOL);

        $size = $this->writeALot();
        fwrite(STDOUT, 'Written: '.$size.'MB'.PHP_EOL);
        static::assertGreaterThan($maxMemoryMb, $size);
        static::assertLessThan($maxMemoryMb, memory_get_peak_usage(true) / (1024**2));

        $numberOfFizzBuzzTags = $this->readALot();
        self::assertGreaterThan(50000, $numberOfFizzBuzzTags);
        static::assertLessThan($maxMemoryMb, memory_get_peak_usage(true) / (1024**2));
    }

    private function writeALot(): float
    {
        $this->time(function(): void {
            $writer = Writer::forFile($this->file, indentation('  '));
            $writer->write(
                document('1.0', 'UTF-8', children([
                    element('root', namespace_attribute('http://fizzbuzz.com', 'fizzbuzz'), children(
                        $this->provideFizzBuzzTags()
                    ))
                ]))
            );
        });

        return filesize($this->file) / (1024**2);
    }

    private function readALot(): int
    {
        fwrite(STDOUT, 'Reading...'.PHP_EOL);

        return $this->time(
            function () {
                $reader = Reader::fromXmlFile($this->file);
                $cursor = $reader->provide(node_name('FizzBuzz'));
                $counter = 0;
                foreach ($cursor as $item) {
                    $counter++;
                }

                return $counter;
            }
        );
    }

    private function time(callable $run)
    {
        $start = hrtime(true);
        $result = $run();
        $stop = hrtime(true);

        fwrite(STDOUT, 'Action took: '.(($stop-$start)/1e+6).'ms'.PHP_EOL);

        return $result;
    }

    private function provideFizzBuzzTags(): Generator
    {
        $amount = ((int) $_ENV['STRESS_TAGS_M']) * (1024 ** 2);

        for ($i=1; $i<$amount; $i++) {
            yield match (true) {
                $i%3 === 0 && $i%5 === 0 => element('FizzBuzz'),
                $i%5 === 0 => element('Buzz'),
                $i%3 === 0 => element('Fizz'),
                default => prefixed_element('fizzbuzz', 'num', prefixed_attribute('fizzbuzz', 'value', (string) $i))
            };
        }
    }
}
