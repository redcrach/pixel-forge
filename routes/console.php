<?php

use App\Jobs\PollStuckGenerations;
use Illuminate\Support\Facades\Schedule;

// Fallback for missed webhooks: re-check generations stuck in queued/processing with Pixelforge
Schedule::job(new PollStuckGenerations)->everyThirtyMinutes();
