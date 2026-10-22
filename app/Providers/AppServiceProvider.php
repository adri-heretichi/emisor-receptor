<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use App\Events\RemoteStyleUpdate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // LOG BROADCAST: registro del evento RemoteStyleUpdate
        if (config('app.log_broadcast', env('LOG_BROADCAST', true))) {
            
            Event::listen(RemoteStyleUpdate::class, function (RemoteStyleUpdate $event) {
            Log::channel('broadcast')->info('RemoteStyleUpdate emitido', [
                'event' => 'RemoteStyleUpdate',
                'channel' => 'style-channel',
                'text' => $event->newText,
                'style' => $event->newStyle,
                'triggered_by' => $event->triggeredById,
                'triggered_ip' => $event->triggeredByIp,
    ]);
});

        }

        // LOG SQL: consultas con umbral y control de bindings
        if (config('app.log_sql', env('LOG_SQL', false))) {
            DB::listen(function ($query) {
                $thresholdMs = (int) env('LOG_SQL_MIN_MS', 0);

                if ($thresholdMs && ($query->time < $thresholdMs)) {
                    return;
                }

                $logBindings = (bool) env('LOG_SQL_BINDINGS', false);

                Log::channel('sql')->info('DB query', [
                    'sql' => $query->sql,
                    'time_ms' => $query->time,
                    'bindings' => $logBindings ? $query->bindings : '[hidden]',
                    'connection' => $query->connectionName,
                ]);
            });
        }
    }
}