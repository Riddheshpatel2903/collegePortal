<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\LibraryReservation;

class ReservationsController extends Controller
{
    public function index()
    {
        $reservations = LibraryReservation::query()->latest()->paginate(15);

        return view('librarian.reservations.index', compact('reservations'));
    }
}
