<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan beranda/dashboard khusus guru (Mobile-First).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $teacher = $user->teacher;

        $assignments = $teacher
            ? $teacher->teachingAssignments()
                ->with(['classroom', 'subject', 'academicYear'])
                ->get()
            : collect();

        $currentDate = Carbon::now('Asia/Makassar')->locale('id')->translatedFormat('l, d F Y');

        return view('teacher.dashboard', [
            'user' => $user,
            'teacher' => $teacher,
            'assignments' => $assignments,
            'currentDate' => $currentDate,
        ]);
    }
}

