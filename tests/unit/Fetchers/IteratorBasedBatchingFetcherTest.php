<?php

namespace Tests\BatchingIterator;

use BatchingIterator\Fetchers\IteratorBasedBatchingFetcher;

/**
 * @covers BatchingIterator\Fetchers\IteratorBasedBatchingFetcher
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class IteratorBasedBatchingFetcherTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider fetchCountProvider
	 */
	public function testGivenNoValues_fetchNextReturnEmptyArray( $fetchCount ) {
		$fetcher = new IteratorBasedBatchingFetcher( new \ArrayIterator() );

		$this->assertSame(
			array(),
			$fetcher->fetchNext( $fetchCount )
		);
	}

	public function fetchCountProvider() {
		return array(
			array( 1 ),
			array( 2 ),
			array( 3 ),
			array( 10 ),
			array( 9001 ),
		);
	}

	public function testGivenOneValue_onlyFirstFetchReturnsIt() {
		$fetcher = new IteratorBasedBatchingFetcher( new \ArrayIterator( array( 'foo' ) ) );

		$this->assertSame( array( 'foo' ), $fetcher->fetchNext( 1 ) );
		$this->assertSame( array(), $fetcher->fetchNext( 1 ) );
	}

	public function testGivenOneValue_fetchingTwoReturnsOnlyOne() {
		$fetcher = new IteratorBasedBatchingFetcher( new \ArrayIterator( array( 'foo' ) ) );

		$this->assertSame( array( 'foo' ), $fetcher->fetchNext( 2 ) );
	}

	public function testSuccessiveFetchesGivenMultipleValues() {
		$fetcher = new IteratorBasedBatchingFetcher( new \ArrayIterator( array( 'foo', 'bar', 'baz', 'bah' ) ) );

		$this->assertSame( array( 'foo' ), $fetcher->fetchNext( 1 ) );
		$this->assertSame( array( 'bar', 'baz' ), $fetcher->fetchNext( 2 ) );
		$this->assertSame( array( 'bah' ), $fetcher->fetchNext( 3 ) );
		$this->assertSame( array(), $fetcher->fetchNext( 2 ) );
	}

	public function testWhenResultsHaveRunOut_rewindGoesToFirstElement() {
		$fetcher = new IteratorBasedBatchingFetcher( new \ArrayIterator( array( 'foo', 'bar', 'baz', 'bah' ) ) );

		$fetcher->fetchNext( 10 );
		$fetcher->rewind();

		$this->assertSame( array( 'foo' ), $fetcher->fetchNext( 1 ) );
	}

	public function testWhenSomeFetchingHasHappened_rewindGoesToFirstElement() {
		$fetcher = new IteratorBasedBatchingFetcher( new \ArrayIterator( array( 'foo', 'bar', 'baz', 'bah' ) ) );

		$fetcher->fetchNext( 2 );
		$fetcher->rewind();

		$this->assertSame( array( 'foo' ), $fetcher->fetchNext( 1 ) );
	}

	public function testWhenNoFetchingHasHappened_rewindStaysAtTheFirstElement() {
		$fetcher = new IteratorBasedBatchingFetcher( new \ArrayIterator( array( 'foo', 'bar', 'baz', 'bah' ) ) );

		$fetcher->rewind();

		$this->assertSame( array( 'foo' ), $fetcher->fetchNext( 1 ) );
	}

}
