<?php

namespace Assist\Engagement\Actions;

use Assist\Engagement\Models\EngagementResponse;
use Assist\Engagement\DataTransferObjects\EngagementResponseData;

class CreateEngagementResponse
{
    public function __invoke(EngagementResponseData $data): void
    {
        $findEngagementResponseSender = resolve(FindEngagementResponseSender::class);

        $sender = $findEngagementResponseSender($data->from);

        if (! is_null($sender)) {
            EngagementResponse::create([
                'sender_id' => $sender->id,
                'sender_type' => $sender->getMorphClass(),
                'content' => $data->body,
                // TODO We might need to retroactively get this data from the Twilio API
                // For now, we will assume that the message was sent at the time it was received
                'sent_at' => now(),
            ]);
        }
    }
}
