<?php

namespace App\Support\Notifications;

use App\Mail\HrmoDivisionHeadNotice;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

/**
 * Enhancement Spec Sec. 3/4 — routes system-generated notices to the HRMO
 * Division Head. There is no dedicated "HRMO Division Head" user field
 * anywhere in the schema, so the recipient is resolved from a configurable
 * setting (so a real person can be designated without a code change) and
 * falls back to every "System & Administration" role user so the notice
 * always reaches someone even before that setting is configured.
 */
class HrmoNotifier
{
    private const SETTING_KEY = 'hrmo_division_head_user_id';

    /** @return array<int, string> emails actually notified */
    public function notifyDivisionHead(string $subject, string $message): array
    {
        $recipients = $this->resolveRecipients();

        foreach ($recipients as $user) {
            try {
                Mail::to($user->email)->send(new HrmoDivisionHeadNotice($subject, $message));
            } catch (\Throwable $e) {
                // Never let a mail-transport failure break the flagging job or the
                // violation/case linkage flow that triggered this notice.
                Log::warning("HrmoNotifier: failed to email {$user->email} — {$e->getMessage()}");
            }
        }

        return $recipients->pluck('email')->all();
    }

    /** Lets an admin designate a specific person via Setting::setVal('hrmo_division_head_user_id', $userId). */
    private function resolveRecipients()
    {
        $designatedId = Setting::getVal(self::SETTING_KEY);
        if ($designatedId) {
            $user = User::find($designatedId);
            if ($user) {
                return collect([$user]);
            }
        }

        return User::whereHas('roles', fn ($q) => $q->where('name', 'System & Administration'))
            ->whereNotNull('email')
            ->get();
    }
}
