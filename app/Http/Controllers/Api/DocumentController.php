<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;

class DocumentController
{
    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');

            // On génère un nom unique
            $fileName = time() . '_' . $file->getClientOriginalName();

            // On stocke dans le dossier public/uploads
            $file->move(public_path('uploads/intranet'), $fileName);

            // CKEditor attend EXACTEMENT ce format de réponse JSON
            return response()->json([
                'url' => asset('uploads/intranet/' . $fileName)
            ]);
        }

        return response()->json(['error' => 'Aucun fichier reçu'], 400);
    }
}
