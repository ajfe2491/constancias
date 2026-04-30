<?php

namespace App\Http\Controllers;

use App\Services\SystemSettingService;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    protected $settingService;

    public function __construct(SystemSettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index()
    {
        return view('settings.index', [
            'eventTypes' => $this->settingService->getEventTypes(),
            'documentTypes' => $this->settingService->getDocumentTypes(),
        ]);
    }

    public function storeEventType(Request $request)
    {
        $request->validate(['type' => 'required|string|max:50']);
        $this->settingService->addEventType($request->type);
        return back()->with('success', 'Tipo de evento agregado.');
    }

    public function destroyEventType(Request $request)
    {
        $request->validate(['type' => 'required|string']);
        $this->settingService->removeEventType($request->type);
        return back()->with('success', 'Tipo de evento eliminado.');
    }

    public function storeDocumentType(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:50|alpha_dash',
            'label' => 'required|string|max:50',
        ]);
        $this->settingService->addDocumentType($request->key, $request->label);
        return back()->with('success', 'Tipo de documento agregado.');
    }

    public function destroyDocumentType(Request $request)
    {
        $request->validate(['key' => 'required|string']);
        $this->settingService->removeDocumentType($request->key);
        return back()->with('success', 'Tipo de documento eliminado.');
    }
}
