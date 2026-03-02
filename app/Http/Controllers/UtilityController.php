<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UtilityController extends Controller
{
    public function fetchUsers(Request $request)
    {
        $department_code = $request->department_code ?? null;
        $line_sign = $request->line_sign ?? null;

        $users = User::select('社員番号 as user_id', '社員名 as user_name')
            ->DepartmentCode($department_code)
            ->LineSign($line_sign)
            ->orderBy('社員番号', 'asc')
            ->get()->toArray();

        return $users;
    }
}
