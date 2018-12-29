<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Storage;

class WysiwygController extends Controller
{

    public function wysiwygUpdate(Request $request)
    {
        if ($request->hasFile('file')) 
        {
            $path = $request->file->store('public/images/wysiwyg');
            return response()->json([
                'link' =>  url(Storage::url($path))
            ]);
        }
    }

    public function wysiwygDestroy(Request $request)
    {
        if($request->has('name')) 
        {
            $name = $request->name;
            if(Storage::exists('public/images/wysiwyg/' . $name)) 
            {
                Storage::delete('public/images/wysiwyg/' . $name);
            }
        }
    }
    
}
