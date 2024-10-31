<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class Job implements ShouldQueue {
    /*
      |--------------------------------------------------------------------------
      | Queueable Jobs
      |--------------------------------------------------------------------------
      |
      | This job base class provides a central location to place any logic that
      | is shared across all of your jobs. The trait included with the class
      | provides access to the "queueOn" and "delay" queue helper methods.
      |
     */

use InteractsWithQueue,
    Queueable,
    SerializesModels;

    public $tries = 3;
    public $timeout = 3600;
    public $maxExceptions = 10;
    public $failOnTimeout = false;

}
