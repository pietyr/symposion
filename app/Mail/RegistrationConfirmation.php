<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\Attendance;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Number;

/**
 * Sent after participants register themselves; a pending one carries the transfer details (ADR-020).
 */
final class RegistrationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Registration $registration) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Registration for :title', ['title' => $this->registration->event->title]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $registration = $this->registration;
        $event = $registration->event;

        return new Content(
            markdown: 'mail.registrations.confirmation',
            with: [
                'title' => $event->title,
                'eventUrl' => route('events.show', ['event' => $event, 'slug' => $event->slug]),
                'dates' => $this->dates($event),
                'place' => $registration->attendance === Attendance::InPerson
                    ? implode(', ', array_filter([$event->city, $event->venue]))
                    : null,
                'attendance' => $registration->attendance->label(),
                'amount' => $this->price($registration->amount),
                'confirmed' => $registration->status === RegistrationStatus::Paid,
                'transfer' => $registration->transferDetails(),
            ],
        );
    }

    /**
     * 22.11.2026, 09:00–17:00 or 22.11.2026, 09:00 – 23.11.2026, 16:00
     */
    private function dates(Event $event): string
    {
        $start = $event->starts_at->format('d.m.Y, H:i');

        return $event->starts_at->isSameDay($event->ends_at)
            ? $start.'–'.$event->ends_at->format('H:i')
            : $start.' – '.$event->ends_at->format('d.m.Y, H:i');
    }

    private function price(int $grosze): string
    {
        return $grosze === 0
            ? __('Free')
            : (string) Number::currency($grosze / 100, in: 'PLN', locale: app()->getLocale());
    }
}
