<?php

use RavenTools\GridManager\Worker;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-05-27 at 14:51:31.
 */
class WorkerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Input
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Worker(array(
			'dequeue_callback' => function() {
				return array(
						array("one","two","three"),
						array("four","five","six")
						);
			},
			'work_item_callback' => function($work_item) {
				return $work_item;
			},
			'queue_callback' => function($work_item) {
				return true;
			},
			'process_exit_callback' => function() { },
			'shutdown_callback' => function() { },
			'shutdown_timeout' => '60 seconds'
		));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

	public function testInitWorkItemCallbackArray() {

		$callbacks = [
			function() {},
			function() {}
		];

		// test constructing w/ an array
		$w = new Worker([
			"work_item_callback" => $callbacks
		]);

		$this->assertSame($callbacks,$w->getWorkItemCallbacks());

		// test adding subsequent callback
		$cb = function() {};
		$w->addWorkItemCallback($cb);
		$callbacks[] = $cb;

		$this->assertSame($callbacks,$w->getWorkItemCallbacks());
	}

	public function testRun() 
	{
		$response = $this->object->run();

		$this->assertArrayHasKey("success",$response);
		$this->assertEquals(1,$response['success']);
		$this->assertArrayHasKey("failure",$response);
		$this->assertEquals(0,$response['failure']);
		$this->assertArrayHasKey("items",$response);
		$this->assertEquals(2,$response['items']);
	}

	public function testSetDequeueCallback() {

		$this->object->setDequeueCallback(function() {
			return array(
				array("desk","chair"),
				array("toaster","microwave"),
				array("couch","television")
			);
		});

		$this->object->setQueueCallback(function($items) {
			for($i=0;$i<count($items);$i++) {
				switch($i) {
					case 0: 
						if($items[0][0] != "desk") {
							return false;
						}
						break;
					case 1: 
						if($items[1][0] != "toaster") {
							return false;
						}
						break;
					case 2: 
						if($items[2][0] != "couch") {
							return false;
						}
						break;
				}
			}

			return true;
		});

		$response = $this->object->run();

        $this->assertArrayHasKey("success",$response);
        $this->assertEquals(1,$response['success']);
        $this->assertArrayHasKey("failure",$response);
        $this->assertEquals(0,$response['failure']);
        $this->assertArrayHasKey("items",$response);
        $this->assertEquals(3,$response['items']);
	}

	public function testAddWorkItemCallback() {

		$this->object->setDequeueCallback(function() {
			return array(
				array("desk","chair"),
				array("toaster","microwave"),
				array("couch","television")
			);
		});

		$cb = function($item) {
			return array_reverse($item);
		};

		$this->object->addWorkItemCallback($cb);
		$this->object->addWorkItemCallback($cb);
		$this->object->addWorkItemCallback($cb);

		$this->object->setQueueCallback(function($items) {
			for($i=0;$i<count($items);$i++) {
				switch($i) {
					case 0: 
						if($items[0][0] != "chair") {
							return false;
						}
						break;
					case 1: 
						if($items[1][0] != "microwave") {
							return false;
						}
						break;
					case 2: 
						if($items[2][0] != "television") {
							return false;
						}
						break;
				}
			}

			return true;
		});

		$response = $this->object->run();

        $this->assertArrayHasKey("success",$response);
        $this->assertEquals(1,$response['success']);
        $this->assertArrayHasKey("failure",$response);
        $this->assertEquals(0,$response['failure']);
        $this->assertArrayHasKey("items",$response);
        $this->assertEquals(3,$response['items']);
	}

	public function testSetQueueCallback() {

		$this->object->setQueueCallback(function($items) {
			return false;
		});

		$response = $this->object->run();

        $this->assertArrayHasKey("success",$response);
        $this->assertEquals(0,$response['success']);
        $this->assertArrayHasKey("failure",$response);
        $this->assertEquals(1,$response['failure']);
        $this->assertArrayHasKey("items",$response);
        $this->assertEquals(0,$response['items']);
	}

	public function testSetShutdownCallback() { 

		$this->object->setNumToProcess(1);
		$this->object->setShutdownTimeout("1 second");

		$this->object->setDequeueCallback(function() {
			return false;
		});

		$shutdown = null;
		$this->object->setShutdownCallback(function() use (&$shutdown) {
			$shutdown = "shutting down";
		});

		// test shutdown timeout and callback
		$response = $this->object->run();
		$this->assertEquals("shutting down",$shutdown);
	}

	public function testSetProcessExitCallback() {

		$num_to_process = 10;

		$this->object->setNumToProcess($num_to_process);

		$this->object->setDequeueCallback(function() {
			return array("item");
		});

		$process_exit = null;
		$this->object->setProcessExitCallback(function() use (&$process_exit) {
			$process_exit = "exiting due to processing limit";
		});

		// test num_to_process limit
		$response = $this->object->run();
		$this->assertEquals($num_to_process,$response['success']);
		$this->assertEquals($num_to_process,$response['items']);
		$this->assertEquals("exiting due to processing limit",$process_exit);
	}
}
