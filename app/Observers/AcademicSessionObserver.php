<?php

namespace App\Observers;

use App\Models\AcademicSession;

class AcademicSessionObserver
{
    public function saving(AcademicSession $session): void
    {
        if ($session->is_current) {
            AcademicSession::whereKeyNot($session->id)->update([
                'is_current' => false,
                'status' => 'completed',
            ]);
            $session->status = 'active';
        }
    }
}
