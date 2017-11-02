<?php
declare(strict_types=1);

namespace PHPUnitJustSnaps;

use JustSnaps\FileDriver;
use JustSnaps\CreatedSnapshotException;
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

	public function removeSnapshot() {
		$snapFileDriver = FileDriver::buildWithDirectory($this->getSnapshotDirectory());
		$snapFileDriver->removeSnapshotForTest($this->getName());
	}

	public function createSnapshot($actual) {
		try {
			$this->assertMatchesSnapshot($actual);
		} catch (CreatedSnapshotException $err) {
		}
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
}
