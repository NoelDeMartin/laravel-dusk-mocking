<?php

namespace NoelDeMartin\LaravelDusk\Fakes;

use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Facades\App;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;

class StorageFake implements Factory
{
    protected $diskRoots = [];
    protected $disks = [];

    /**
     * Replace the given disk with a local testing disk.
     *
     * @param  string|null  $disk
     *
     * @return void
     */
    public function fake($disk = null)
    {
        $disk = $disk ?: Config::get('filesystems.default');

        (new Filesystem)->cleanDirectory(
            $root = storage_path('framework/testing/disks/'.$disk)
        );

        $this->diskRoots[$disk] = $root;
        $this->disks[$disk] = App::make('filesystem')->createLocalDriver(['root' => $root]);
    }

    /**
     * Checks if the current disk being faked.
     *
     * @return bool
     */
    public function isFaking($disk)
    {
        return isset($this->disks[$disk]);
    }

    /**
     * Get a filesystem instance.
     *
     * @param  string  $disk
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function disk($disk = null)
    {
        $disk = $disk ?: Config::get('filesystems.default');

        return isset($this->disks[$disk])
            ? $this->disks[$disk]
            : App::make('filesystem')->disk($disk);
    }

    /**
     * Dynamically call the default disk instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->disk()->$method(...$parameters);
    }

    /**
     * Prepare object for serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        return ['diskRoots'];
    }

    /**
     * Restore object after deserialization.
     *
     * @return void
     */
    public function __wakeup()
    {
        foreach ($this->diskRoots as $disk => $root) {
            $this->disks[$disk] = App::make('filesystem')->createLocalDriver(['root' => $root]);
        }
    }
}
