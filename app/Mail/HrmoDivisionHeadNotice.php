<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Enhancement Spec Sec. 3/4 — generic notice to the HRMO Division Head recipient(s). */
class HrmoDivisionHeadNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $noticeSubject,
        public string $noticeMessage,
    ) {
    }

    public function build()
    {
        return $this->subject($this->noticeSubject)
            ->view('emails.hrmo-division-head-notice');
    }
}
