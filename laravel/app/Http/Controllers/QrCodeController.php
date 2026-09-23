<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * Display QR Code generator interface.
     */
    public function index()
    {
        $forms = Form::where('is_active', true)
            ->with(['fields' => function ($query) {
                $query->orderBy('sort_order', 'asc');
            }])
            ->get();

        return view('qrcode.index', compact('forms'));
    }
}
