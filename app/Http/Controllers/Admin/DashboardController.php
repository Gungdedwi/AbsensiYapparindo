<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan beranda/dashboard operasional admin sekolah.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $activeYear = AcademicYear::where('is_active', true)->first();
        $teacherCount = Teacher::count();
        $studentCount = Student::count();
        $classCount = Classroom::count();
        $subjectCount = Subject::count();

        $currentDate = Carbon::now('Asia/Makassar')->locale('id')->translatedFormat('l, d F Y');

        return view('admin.dashboard', [
            'user' => $user,
            'activeYear' => $activeYear,
            'teacherCount' => $teacherCount,
            'studentCount' => $studentCount,
            'classCount' => $classCount,
            'subjectCount' => $subjectCount,
            'currentDate' => $currentDate,
        ]);
    }
}

