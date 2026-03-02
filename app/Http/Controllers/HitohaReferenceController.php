<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services;
use Inertia\Inertia;

class HitohaReferenceController extends Controller
{
    public function showItemReference(Request $request)
    {
        return Inertia::render('HitohaItemReference');
    }

    public function showVerification(Request $request)
    {
        return Inertia::render('HitohaVerification', [
            'department_code' => $request->department_code ?? 'EW',
            'line_sign' => $request->line_sign ?? 'WS',
            'process_name' => $request->process_name ?? 'processing'
        ]);
    }

    public function fetchItem(Request $request)
    {
        $service = self::HitohaVerificationFactory($request->department_code, $request->processName);
        if (is_null($service)) return [];

        return $service->getItemInformation($request->managementID);
    }

    public function registItem(Request $request)
    {
        $service = self::HitohaVerificationFactory($request->department_code, $request->processName);
        if (is_null($service)) return [];

        return $service->save($request->items);
    }

    public function HitohaVerificationFactory($department_code, $process_name)
    {
        switch ($department_code) {
            case 'EW':
                switch ($process_name) {
                    case 'cutting':
                        return new Services\HitohaVerificationCuttingEW();
                        break;
                    case 'processing':
                        return new Services\HitohaVerificationProcessingEW();
                        break;
                    default:
                        return null;
                }
                break;
            default:
                return ['err_message' => '対象のデータは対応していません。 部門：' .$department_code . ' 工程：' . $process_name];
        }
    }
}
