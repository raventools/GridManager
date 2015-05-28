<?php
use \RavenTools\GridManager\Output;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-05-28 at 10:46:14.
 */
class OutputTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Output
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Output(array(
			'dequeue_callback' => function($batch_size) {
				return array(
						array("one","two","three"),
						array("one","two","three"),
						array("one","two","three"),
						array("four","five","six")
						);
			},
			'output_item_callback' => function($output_item) {
				return $output_item;
			},
			'write_data_callback' => function($data) {
				return true;
			}
		));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

	public function testRun() {
		$response = $this->object->run();

		$this->assertArrayHasKey("success",$response);
        $this->assertEquals(1,$response['success']);
        $this->assertArrayHasKey("failure",$response);
        $this->assertEquals(0,$response['failure']);
        $this->assertArrayHasKey("items",$response);
        $this->assertEquals(4,$response['items']);
	}

	public function testSetDequeueCallback() {

		$this->object->setDequeueCallback(function($batch_size) {
			return array(
				array("dog","cat"),
				array("cat","dog"),
				array("monkey","elephant")
			);
		});

		$this->object->setWriteDataCallback(function($data) {

			for($i=0;$i<count($data);$i++) {
				switch($i) {
					case 0:
						if($data[0][0] != "dog") {
							return false;
						}
						break;
					case 1:
						if($data[1][0] != "cat") {
							return false;
						}
						break;
					case 2:
						if($data[2][0] != "monkey") {
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

	public function testAddOutputItemCallback() {

		$this->object->setDequeueCallback(function($batch_size) {
			return array(
				array("dog","cat"),
				array("cat","dog"),
				array("monkey","elephant")
			);
		});

		$output_callback = function($item) {
			return array_reverse($item);
		};

		$this->object->addOutputItemCallback($output_callback);
		$this->object->addOutputItemCallback($output_callback);
		$this->object->addOutputItemCallback($output_callback);

		$this->object->setWriteDataCallback(function($data) {

			for($i=0;$i<count($data);$i++) {
				switch($i) {
					case 0:
						if($data[0][0] != "cat") {
							return false;
						}
						break;
					case 1:
						if($data[1][0] != "dog") {
							return false;
						}
						break;
					case 2:
						if($data[2][0] != "elephant") {
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

	public function testSetWriteDataCallback() {

		$this->object->setWriteDataCallback(function($data) {
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
}
