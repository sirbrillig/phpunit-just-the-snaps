# PHPUnitJustSnaps

A snapshot testing library for PHPUnit

Uses the generic snapshot library [just-the-snaps](https://github.com/sirbrillig/just-the-snaps).

**Still under development! API may change!**

## Installation

```
composer require --dev sirbrillig/phpunit-just-the-snaps
```

## Usage

Include the trait `SnapshotAsserter` in your test class to get access to the methods of this package.

```php
use PHPUnitJustSnaps\SnapshotAsserter;

class MyTest extends \PHPUnit\Framework\TestCase {
	use SnapshotAsserter;
}
```

For this example, assume we have a function called `getData()` which returns an array.

```php
public function testDataHasNotChanged() {
	$actual = getData();
	$this->assertMatchesSnapshot($actual);
}
```

The first time this code is run, the test will be marked as "skipped" and the data being tested (`$actual`) will be serialized to the file `tests/__snapshots__/testThatFooIsBar.snap`.

The second time (and all subsequent times) this code is run, it will compare the data being tested with the contents of the snapshot file and return true or false depending on if it is the same.

This will protect against any regressions in `getData()`.

If the results of `getData()` change _intentionally_, then the test can be "reset" by simply deleting the snapshot file. The next time the test is run, it will re-create it as above.

## Serializers

JustSnaps will run `json_encode()` on any data before writing it to the snapshot file. If your data needs some manipulation before being written, you can create custom serializers.

Each serializer consists of two objects:

1. A class implementing `SerializerTester` which has one function: `shouldSerialize(mixed $data): bool`. This function must return true if the serializer should modify the data.
2. A class implementing `SerializerPrinter` which has one function: `serializeData(mixed $data): mixed`. This function can manipulate the data and then must return the new data which will be written to the snapshot file. Note that the data returned from `serializeData()` will still be passed through `json_encode()` prior to writing.

Here's an example of using a custom serializer to hide sensitive information.

```php
use JustSnaps\SerializerPrinter;
use JustSnaps\SerializerTester;
use JustSnaps\Serializer;

public function addSecretSerializer() {
	$printer = new class implements SerializerPrinter {
		public function serializeData($outputData) {
			$outputData['secret'] = 'xxx';
			return $outputData;
		}
	};
	$tester = new class implements SerializerTester {
		public function shouldSerialize($outputData): bool {
			return is_array($outputData) && isset($outputData['secret']);
		}
	};
	$this->addSnapshotSerializer(new Serializer($tester, $printer));
}

public function testDataHasNotChanged() {
	$actual = [ 'foo' => 'bar', 'secret' => 'thisisasecretpassword' ];
	$this->addSecretSerializer();
	$this->assertMatchesSnapshot($actual);
}
```

## Similar projects

- [phpunit-snapshot-assertions](https://github.com/spatie/phpunit-snapshot-assertions)
