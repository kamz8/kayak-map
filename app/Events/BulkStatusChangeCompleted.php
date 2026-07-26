<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BulkStatusChangeCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly string $batchId,
        public readonly int $userId,
        public readonly int $totalProcessed,
        public readonly int $successCount,
        public readonly int $failedCount,
        public readonly string $status
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->userId.'.bulk-operations'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'batch_id' => $this->batchId,
            'user_id' => $this->userId,
            'total_processed' => $this->totalProcessed,
            'success_count' => $this->successCount,
            'failed_count' => $this->failedCount,
            'status' => $this->status,
            'message' => "Zaktualizowano status {$this->successCount} szlaków",
            'completed_at' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'bulk-status-change.completed';
    }
}
