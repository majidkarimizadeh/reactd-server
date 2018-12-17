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
            $path = $request->file->store('images/wysiwyg');
            return response()->json([
                'link' =>  url($path)
            ]);
        }
    }

    public function wysiwygDestroy(Request $request)
    {
        if($request->has('name')) 
        {
            $name = $request->name;
            if(Storage::exists('images/wysiwyg/' . $name)) 
            {
                Storage::delete('images/wysiwyg/' . $name);
            }
        }
    }
    
}
