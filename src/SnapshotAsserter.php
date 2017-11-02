<?php
declare(strict_types=1);

namespace PHPUnitJustSnaps;

trait SnapshotAsserter {
	private $justSnapsDirectory = './tests/__snapshots__';

	public function setSnapshotDirectory($dirName) {
		$this->justSnapsDirectory = $dirName;
	}

	public function assertMatchesSnapshot($actual) {
		$asserter = \JustSnaps\buildSnapshotAsserter($this->justSnapsDirectory);
		$this->assertTrue($asserter->forTest($this->getName())->assertMatchesSnapshot($actual));
	}
}
