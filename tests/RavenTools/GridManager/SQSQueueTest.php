<?php

use \RavenTools\GridManager\SQSQueue;
use \Mockery as m;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-05-29 at 09:37:05.
 */
class SQSQueueTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var SQSQueue
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$queue_url = m::mock('Guzzle\Service\Resource\Model');
		$queue_url->shouldReceive('get')->andReturn("https://sqs.amazon.com/test_queue");

		$this->sqs_client = m::mock('Aws\Sqs\SqsClient');
		$this->sqs_client->shouldReceive('GetQueueUrl')->andReturn($queue_url);

		$this->object = new SQSQueue(array(
			'sqs_client' => $this->sqs_client,
			'queue_name' => 'test_queue'
		));
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}

	/**
	 * @covers SQSQueue::send
	 */
	public function testSend()
	{

		$guzzle_resource_model = m::mock('Guzzle\Service\Resource\Model');
		$this->sqs_client->shouldReceive('sendMessage')->once()->andReturn($guzzle_resource_model);
		$this->sqs_client->shouldReceive('sendMessage')->once()->andThrow(new Exception("failed to send"));

		$message = array(
			"one" => 1,
			"two" => 2,
			"three" => 3
		);

		$response = $this->object->send($message);
		$this->assertInstanceOf('Guzzle\Service\Resource\Model',$response);

		$response = $this->object->send($message);
		$this->assertFalse($response);
	}

	/**
	 * @covers SQSQueue::receive
	 */
	public function testReceive()
	{
		$response_messages = array(
			array(
				"ReceiptHandle" => "deadbeef1",
				"Body" => '{"Message":"My important sqs message 1"}'
			),
			array(
				"ReceiptHandle" => "deadbeef2",
				"Body" => '{"Message":"My important sqs message 2"}'
			)
		);

		$guzzle_resource_model = m::mock('Guzzle\Service\Resource\Model');
		$guzzle_resource_model->shouldReceive('get')->once()->andReturn($response_messages);

		$this->sqs_client->shouldReceive('receiveMessage')->once()->andReturn($guzzle_resource_model);
		$this->sqs_client->shouldReceive('receiveMessage')->once()->andThrow(new Exception("failed to receive"));

		$response = $this->object->receive();
		$this->assertInternalType('array',$response);
		$this->assertCount(2,$response);

		foreach($response as $r) {
			$this->assertObjectHasAttribute('handle',$r);
			$this->assertObjectHasAttribute('body',$r);
		}

		$response = $this->object->receive();
		$this->assertFalse($response);
	}

	/**
	 * @covers SQSQueue::delete
	 */
	public function testDelete()
	{
		$guzzle_resource_model = m::mock('Guzzle\Service\Resource\Model');
		$this->sqs_client->shouldReceive('deleteMessage')->once()->andReturn($guzzle_resource_model);
		$this->sqs_client->shouldReceive('deleteMessage')->once()->andThrow(new Exception("failed to delete"));

		$response = $this->object->delete("deadbeef1");
		$this->assertInstanceOf('Guzzle\Service\Resource\Model',$response);

		$response = $this->object->delete("deadbeef1");
		$this->assertFalse($response);
	}

	/**
	 * @covers SQSQueue::length
	 */
	public function testLength()
	{
		$guzzle_resource_model = m::mock('Guzzle\Service\Resource\Model');
		$guzzle_resource_model->shouldReceive('getPath')->once()->andReturn(1234);
		$this->sqs_client->shouldReceive('getQueueAttributes')->once()->andReturn($guzzle_resource_model);
		$this->sqs_client->shouldReceive('getQueueAttributes')->once()->andThrow(new Exception("failed to get length"));

		$response = $this->object->length();
		$this->assertEquals(1234,$response);

		$response = $this->object->length();
		$this->assertFalse($response);
	}
}
