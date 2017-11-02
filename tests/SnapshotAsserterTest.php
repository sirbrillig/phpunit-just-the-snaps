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
		$this->snapshotDirectory = $this->getSnapshotDirectory();
		$this->snapFileDriver = FileDriver::buildWithDirectory($this->snapshotDirectory);
		$this->snapFileDriver->removeSnapshotForTest($this->getName());
	}

	public function tearDown() {
		$this->snapFileDriver->removeSnapshotForTest($this->getName());
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
}
