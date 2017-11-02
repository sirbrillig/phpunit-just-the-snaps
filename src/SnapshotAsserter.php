<?php
declare(strict_types=1);

namespace PHPUnitJustSnaps;

trait SnapshotAsserter {
	private $justSnapsDirectory = './tests/__snapshots__';
	private $justSnapsSerializers = [];

	public function setSnapshotDirectory($dirName) {
		$this->justSnapsDirectory = $dirName;
	}

	public function getSnapshotDirectory() {
		return $this->justSnapsDirectory;
	}

	public function assertMatchesSnapshot($actual) {
		$asserter = \JustSnaps\buildSnapshotAsserter($this->justSnapsDirectory);
		foreach ($this->justSnapsSerializers as $serializer) {
			$asserter->addSerializer($serializer);
		}
		$this->assertTrue($asserter->forTest($this->getName())->assertMatchesSnapshot($actual));
	}

	public function addSnapshotSerializer(\JustSnaps\Serializer $serializer) {
		$this->justSnapsSerializers[] = $serializer;
	}
}
