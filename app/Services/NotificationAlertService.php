<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;

class NotificationAlertService
{
    /**
     * Notify opted-in business users when an FBR submission is rejected or uncertain.
     */
    public function notifyFbrSubmissionIssue(Business $business, Invoice $invoice, string $reason): void
    {
        try {
            $recipients = $business->users()
                ->where('users.status', 'active')
                ->where('users.email_notifications_enabled', true)
                ->get(['users.id', 'users.name', 'users.email']);

            foreach ($recipients as $user) {
                $subject = "[InvoiceGO] FBR Submission Issue — Invoice {$invoice->invoice_number}";
                $body = implode("\n", [
                    "Hello {$user->name},",
                    '',
                    "An FBR Digital Invoicing submission for {$business->name} requires your attention.",
                    "Invoice Number: {$invoice->invoice_number}",
                    "Environment: " . strtoupper((string) ($invoice->environment ?? 'sandbox')),
                    "Status: " . strtoupper((string) ($invoice->status ?? 'failed')),
                    "Details: {$reason}",
                    '',
                    'You can review this invoice inside your InvoiceGO portal.',
                    'To manage email alerts, visit Settings -> Account & security.',
                ]);

                Mail::raw($body, function ($message) use ($user, $subject) {
                    $message->to($user->email, $user->name)->subject($subject);
                });
            }
        } catch (\Throwable $e) {
            // Never interrupt core invoicing workflow if mail transport is not configured
            report($e);
        }
    }
}
