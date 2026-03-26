<?php

namespace App\Services\Surreal\Queue;

use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\DatabaseJobRecord;
use Illuminate\Support\Carbon;
use stdClass;

class SurrealQueue extends DatabaseQueue
{
    public function pop($queue = null): ?DatabaseJob
    {
        $queue = $this->getQueue($queue);

        foreach ($this->nextAvailableJobs($queue) as $jobRecord) {
            $reservedJob = $this->attemptToReserve($jobRecord);

            if ($reservedJob !== null) {
                return new DatabaseJob(
                    $this->container,
                    $this,
                    $reservedJob,
                    $this->connectionName,
                    $queue,
                );
            }
        }

        return null;
    }

    public function deleteReserved($queue, $id): void
    {
        $this->database->table($this->table)
            ->where('id', $id)
            ->delete();
    }

    public function deleteAndRelease($queue, $job, $delay): void
    {
        $this->deleteReserved($queue, $job->getJobId());

        $this->release($queue, $job->getJobRecord(), $delay);
    }

    /**
     * @return list<DatabaseJobRecord>
     */
    private function nextAvailableJobs(string $queue): array
    {
        $expiration = Carbon::now()->subSeconds($this->retryAfter)->getTimestamp();

        return $this->database->table($this->table)
            ->where('queue', $queue)
            ->where(function ($query) use ($expiration): void {
                $query->where(function ($query): void {
                    $query->whereNull('reserved_at')
                        ->where('available_at', '<=', $this->currentTime());
                })->orWhere(function ($query) use ($expiration): void {
                    $query->whereNotNull('reserved_at')
                        ->where('reserved_at', '<=', $expiration);
                });
            })
            ->orderBy('id', 'asc')
            ->limit(5)
            ->get()
            ->map(static function (stdClass $record): DatabaseJobRecord {
                $record->reserved_at ??= null;

                return new DatabaseJobRecord($record);
            })
            ->all();
    }

    private function attemptToReserve(DatabaseJobRecord $job): ?DatabaseJobRecord
    {
        $reservedAt = $this->currentTime();
        $attempts = $job->attempts + 1;
        $existingReservedAt = property_exists($job, 'reserved_at') ? $job->reserved_at : null;

        $query = $this->database->table($this->table)
            ->where('id', $job->id);

        if ($existingReservedAt === null) {
            $query->whereNull('reserved_at')
                ->where('available_at', '<=', $reservedAt);
        } else {
            $query->where('reserved_at', $existingReservedAt);
        }

        $updated = $query->update([
            'reserved_at' => $reservedAt,
            'attempts' => $attempts,
        ]);

        if ($updated !== 1) {
            return null;
        }

        return new DatabaseJobRecord((object) [
            'id' => $job->id,
            'queue' => $job->queue,
            'payload' => $job->payload,
            'attempts' => $attempts,
            'reserved_at' => $reservedAt,
            'available_at' => $job->available_at,
            'created_at' => $job->created_at,
        ]);
    }
}
