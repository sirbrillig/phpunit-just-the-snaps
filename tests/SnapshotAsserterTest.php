<?php
declare(strict_types=1);

namespace PHPUnitJustSnaps;

use JustSnaps\FileDriver;
use JustSnaps\CreatedSnapshotException;
use JustSnaps\SerializerPrinter;
use JustSnaps\SerializerTester;
use JustSnaps\Serializer;
use PHPUnitJustSnaps\SnapshotAsserter;

// TODO: why is autoload not working?
require('src/SnapshotAsserter.php');

class SnapshotAsserterTest extends \PHPUnit\Framework\TestCase {
	use SnapshotAsserter;

	public function setUp() {
		$this->setSnapshotDirectory('./tests/__snapshots__');
		$this->removeSnapshot();
	}

	public function tearDown() {
		$this->removeSnapshot();
	}

	private function removeSnapshot() {
		$snapFileDriver = FileDriver::buildWithDirectory($this->getSnapshotDirectory());
		$snapFileDriver->removeSnapshotForTest($this->getName());
	}

	private function createSnapshot($actual) {
		try {
			$this->assertMatchesSnapshot($actual);
		} catch (CreatedSnapshotException $err) {
		}
	}

	private function addSecretSerializer() {
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

	public function testSkipsTestIfSnapshotDoesNotExist() {
		$actual = ['foo' => 'bar'];
		$this->expectException(\PHPUnit\Framework\IncompleteTestError::class);
		$this->assertMatchesSnapshot($actual);
	}

	public function testSucceedsIfSnapshotExists() {
		$actual = ['foo' => 'bar'];
		$this->createSnapshot($actual);
		$this->assertMatchesSnapshot($actual);
	}

	public function testFailsIfSnapshotDiffers() {
		$actual = ['foo' => 'bar'];
		$changed = ['foo' => 'baz'];
		$this->createSnapshot($actual);
		$this->expectException(\PHPUnit\Framework\ExpectationFailedException::class);
		$this->assertMatchesSnapshot($changed);
	}

	public function testHasSnapshotDirectory() {
		$this->assertEquals('./tests/__snapshots__', $this->getSnapshotDirectory());
	}

	public function testAllowsChangingSnapshotDirectory() {
		$this->setSnapshotDirectory('./tests/__othersnapshots__');
		$this->assertEquals('./tests/__othersnapshots__', $this->getSnapshotDirectory());
	}

	public function testSnapshotDirectoryIsUsedForSnapshots() {
		$this->setSnapshotDirectory('./tests/__othersnapshots__');
		$actual = ['foo' => 'bar'];
		$this->createSnapshot($actual);
		$snapFileDriver = FileDriver::buildWithDirectory($this->getSnapshotDirectory());
		$this->assertFileExists($snapFileDriver->getSnapshotFileName($this->getName()));
	}

	public function testAllowsAddingASerializer() {
		$actual = [ 'foo' => 'bar', 'secret' => 'thisisasecretpassword' ];
		$this->addSecretSerializer();
		$this->createSnapshot($actual);
		$this->assertMatchesSnapshot($actual);
	}

	public function testSerializersAreApplied() {
		$actual = [ 'foo' => 'bar', 'secret' => 'thisisasecretpassword' ];
		$this->addSecretSerializer();
		$this->createSnapshot($actual);
		$snapFileDriver = FileDriver::buildWithDirectory($this->getSnapshotDirectory());
		$snapshotContents = file_get_contents($snapFileDriver->getSnapshotFileName($this->getName()));
		$this->assertThat($snapshotContents, $this->stringContains('xxx'));
		$this->assertThat($snapshotContents, $this->logicalNot($this->stringContains('thisisasecretpassword')));
	}
}
