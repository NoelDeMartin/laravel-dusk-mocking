<?php

namespace Testing;

use Mockery;

use Symfony\Component\HttpFoundation\Response;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

use NoelDeMartin\LaravelDusk\Fakes\StorageFake;

use Testing\Stubs\StubDriver;

class DriverTest extends TestCase
{
    public function test_fake_storage()
    {
        $disk = $this->faker->word;

        $filesystemMock = Mockery::mock(Filesystem::class);

        $filesystemManagerMock = $this->prepareFacadeMock('filesystem');
        $filesystemManagerMock->shouldReceive('createLocalDriver')->once()->andReturn($filesystemMock);

        $appMock = $this->prepareFacadeMock('app');
        $appMock->shouldReceive('make')->andReturn($filesystemManagerMock);

        $driver = new StubDriver;
        $driver->mock(Storage::class, $disk);

        $this->assertTrue($driver->has(Storage::class));

        $storageFake = $driver->get(Storage::class);
        $this->assertInstanceOf(StorageFake::class, $storageFake);
        $this->assertTrue($storageFake->isFaking($disk));
    }

    public function test_fake_multiple_disks()
    {
        $firstDisk = $this->faker->word;
        $secondDisk = $this->faker->word;

        $filesystemMock = Mockery::mock(Filesystem::class);
        $filesystemManagerMock = $this->prepareFacadeMock('filesystem');
        $filesystemManagerMock->shouldReceive('createLocalDriver')->andReturn($filesystemMock);
        $filesystemManagerMock->shouldReceive('set');
        $filesystemManagerMock->shouldReceive('disk')->andReturn($filesystemMock);

        $appMock = $this->prepareFacadeMock('app');
        $appMock->shouldReceive('make')->andReturn($filesystemManagerMock);

        $driver = new StubDriver;
        $driver->mock(Storage::class, $firstDisk);
        $driver->mock(Storage::class, $secondDisk);

        $storageFake = $driver->get(Storage::class);
        $this->assertTrue($storageFake->isFaking($firstDisk));
        $this->assertTrue($storageFake->isFaking($secondDisk));
    }
}
