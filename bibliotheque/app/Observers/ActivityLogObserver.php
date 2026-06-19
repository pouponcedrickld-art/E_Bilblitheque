<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLogObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model);
    }

    public function updated(Model $model): void
    {
        $this->log('updated', $model);
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model);
    }

    public function restored(Model $model): void
    {
        $this->log('restored', $model);
    }

    private function log(string $action, Model $model): void
    {
        if (!auth()->check()) return;
        if ($model instanceof ActivityLog) return;

        ActivityLog::withoutEvents(fn () => ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'target_table' => $model->getTable(),
            'target_id' => $model->getKey(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]));
    }
}
