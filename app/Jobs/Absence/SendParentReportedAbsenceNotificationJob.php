<?php

namespace App\Jobs\Absence;

use App\Models\Absence;
use App\Models\ParentUser;
use App\Models\User;
use App\Notifications\Absence\ParentReportedAbsenceNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendParentReportedAbsenceNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $absenceId,
        private int $parentId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $absence = Absence::query()
            ->with('child')
            ->findOrFail($this->absenceId);
        $parent = ParentUser::findOrFail($this->parentId);

        User::admins()
            ->each(function (User $admin) use ($absence, $parent): void {
                $admin->notify(new ParentReportedAbsenceNotification(
                    absence: $absence,
                    parent: $parent
                ));
            });

    }
}
