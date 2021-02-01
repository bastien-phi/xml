# Reader Component

The Reader component can be used to detect patterns in a memory-safe way inside a big XML document.
It uses matchers to determine if you care about specific elements or not.
As a result, the reader provides a generator of XML strings that match your matchers!

## Example

```php
use VeeWee\Xml\Dom\Document;
use VeeWee\Xml\Reader\Reader;
use VeeWee\Xml\Reader\Matcher;

$reader   = Reader::fromXmlFile('large-data.xml');
$provider = $reader->provide(
    Matcher\all(
        Matcher\node_name('item'),
        Matcher\node_attribute('locale', 'nl-BE')
    )
);

foreach ($provider as $nlItem) {
    $dom = Document::fromXmlString($nlItem);
    // Do something with it
}
```

## Reader

The Reader consists out of following composable blocks:

- [Configurators](#configurators): Configure how the Reader behaves.
- [Loaders](#loaders): Specify how the Reader loads the XML document.
- [Matchers](#matchers) Determine which XML elements you are interested in.

### Configurators

You can configure how a reader behaves based on configurators.
This package provides following configurators:

#### parser_options

You can specify one of [PHP's reader parser options](https://www.php.net/manual/en/class.xmlreader.php#xmlreader.constants.options):

```php
use VeeWee\Xml\Reader\Reader;
use XMLReader;
use function VeeWee\Xml\Reader\Configurator\parser_options;

$reader = Reader::fromXmlFile('some-file.xml', parser_options([
    XMLReader::LOADDTD => true,
    XMLReader::VALIDATE => true,
    XMLReader::DEFAULTATTRS => true,
    XMLReader::SUBST_ENTITIES => true,
]));
```

#### xsd_schema

You can specify a XSD Schema that will be validated whilst reading:

```php
use VeeWee\Xml\Reader\Reader;
use function VeeWee\Xml\Reader\Configurator\xsd_schema;

$reader = Reader::fromXmlFile('some-file.xml', xsd_schema('schema.xsd'));
```

#### Writing your own configurator

A configurator can be any `callable` that is able to configure an `XMLReader`:

```php
namespace VeeWee\Xml\Reader\Configurator;

use XMLReader;

interface Configurator
{
    public function __invoke(XMLReader $reader): XMLReader;
}

```

You can use the new configurator instance as followed:

```php
use VeeWee\Xml\Reader\Reader;

$reader = Reader::configure($yourLoader, ...$configurators);
```

### Loaders

#### xml_file_loader

```php
use VeeWee\Xml\Reader\Reader;

$reader = Reader::fromXmlFile('some-file.xml', ...$configurators);
```

#### xml_string_loader

```php
use VeeWee\Xml\Reader\Reader;

$reader = Reader::fromXmlString('<xml />', ...$configurators);
```

#### Writing your own loader

A loader can be any `callable` that is able to create an `XMLReader`:

```php
namespace VeeWee\Xml\Reader\Loader;

use XMLReader;

interface Loader
{
    public function __invoke(): XMLReader;
}
```

You can create a new reader instance like this:

```php
use VeeWee\Xml\Reader\Reader;

$reader = Reader::configure($yourLoader, ...$configurators);
```

### Matchers

#### all

All provided matchers need to match in order for this matcher to succceed:

```php
Matcher\all(
    Matcher\node_name('item'),
    Matcher\node_attribute('locale', 'nl-BE')
);
```

#### node_attribute

Matches current element on attribute `locale="nl-BE"`.

```php
Matcher\node_attribute('locale', 'nl-BE');
```

#### node_name

Matches current element on node name `<item />`.

```php
Matcher\node_name('item');
```

#### Writing your own matcher

A matcher can be any `callable` that takes a `NodeSequence` as input and returns a `bool` that specifies if it matches or not:

```php
namespace VeeWee\Xml\Reader\Matcher;

use VeeWee\Xml\Reader\Node\NodeSequence;

interface Matcher
{
    publict function __invoke(NodeSequence $sequence): bool;
}
```

The `NodeSequence` class can be seen as the breadcrumbs of the current XML.
It points to the current element and all of its attributes.
You can select the current element, its parent or a complete sequence of elements until the current one in order matches.
