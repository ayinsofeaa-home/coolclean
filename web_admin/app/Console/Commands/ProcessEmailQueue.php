<?php

namespace App\Console\Commands;

use App\Models\QueuedEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ProcessEmailQueue extends Command
{
    protected $signature = 'coolclean:process-emails {--limit=20}';
    protected $description = 'Send pending messages from the existing EMAIL_QUEUE table';

    public function handle(): int
    {
        $emails = QueuedEmail::where('STATUS', 'PENDING')->oldest('CREATED_AT')->limit((int) $this->option('limit'))->get();

        foreach ($emails as $email) {
            try {
                Mail::html($email->HTML_BODY, function ($message) use ($email) {
                    $message->to($email->RECIPIENT)->subject($email->SUBJECT);
                });
                $email->update(['STATUS' => 'SENT', 'SENT_AT' => now(), 'LAST_ATTEMPT_AT' => now(), 'ATTEMPT_COUNT' => $email->ATTEMPT_COUNT + 1, 'LAST_ERROR' => null]);
            } catch (Throwable $exception) {
                $email->update(['STATUS' => 'FAILED', 'LAST_ATTEMPT_AT' => now(), 'ATTEMPT_COUNT' => $email->ATTEMPT_COUNT + 1, 'LAST_ERROR' => str($exception->getMessage())->limit(1000)]);
            }
        }

        $this->info($emails->count().' email(s) processed.');
        return self::SUCCESS;
    }
}
