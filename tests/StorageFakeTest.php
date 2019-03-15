<?php

namespace Testing;

use Mockery;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

use NoelDeMartin\LaravelDusk\Facades\Mocking;
use NoelDeMartin\LaravelDusk\Fakes\StorageFake;

class StorageFakeTest extends TestCase
{
    private $filesystemMock;
    private $filesystemManagerMock;

    public function setUp()
    {
        parent::setUp();

        $this->filesystemMock = Mockery::mock(Filesystem::class);

        $this->filesystemManagerMock = $this->prepareFacadeMock('filesystem');
        $this->filesystemManagerMock->shouldReceive('createLocalDriver')->andReturn($this->filesystemMock);

        $appMock = $this->prepareFacadeMock('app');
        $appMock->shouldReceive('make')->andReturn($this->filesystemManagerMock);
    }

    public function test_fake_disk()
    {
        $disk = $this->faker->word;

        $storageFake = new StorageFake();

        $storageFake->fake($disk);

        $filename = $this->faker->word;
        $fileContents = str_random();

        $this->filesystemMock->shouldReceive('put')->with($filename, $fileContents)->once();

        $storageFake->disk($disk)->put($filename, $fileContents);
    }

    public function test_fake_default_disk()
    {
        $disk = $this->faker->word;

        $this->prepareFacadeMock('config')
            ->shouldReceive('get')
            ->with('filesystems.default')
            ->andReturn($disk);

        $storageFake = new StorageFake();

        $storageFake->fake();

        $filename = $this->faker->word;
        $fileContents = str_random();

        $this->filesystemMock->shouldReceive('put')->with($filename, $fileContents)->once();

        $storageFake->put($filename, $fileContents);
    }

    public function test_real_disk()
    {
        $disk = $this->faker->word;

        $storageFake = new StorageFake();

        $filename = $this->faker->word;
        $fileContents = str_random();

        $this->filesystemMock->shouldReceive('put')->with($filename, $fileContents)->once();
        $this->filesystemManagerMock
            ->shouldReceive('disk')
            ->with($disk)
            ->andReturn($this->filesystemMock);

        $storageFake->disk($disk)->put($filename, $fileContents);
    }

    public function test_serializes_without_disks()
    {
        $disk = $this->faker->word;

        $storageFake = new StorageFake();

        $storageFake->fake($disk);

        // This would fail if disks are serialized because a Mock is not serializable
        $storageFake = unserialize(serialize($storageFake));

        $filename = $this->faker->word;
        $fileContents = str_random();

        $this->filesystemMock->shouldReceive('put')->with($filename, $fileContents)->once();

        $storageFake->disk($disk)->put($filename, $fileContents);
    }
}
