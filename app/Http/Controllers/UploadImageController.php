<?php

namespace App\Http\Controllers;

use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UploadImageController extends Controller
{
    public $path;
    public $dimensiosn;

    public function __construct()
    {
        $this->path = storage_path('app/public/image');
        $this->dimensiosn = ['250', '500'];
    }

    public function upload(Request $request)
    {
        $this->validate($request,[
            'image' => 'required|image|mimes:jpg, png, jpeg'
        ]);

        if (!File::isDirectory($this->path)){
            File::makeDirectory($this->path);
        };
        
        // upload original image
        $file = $request->file('image');
        $filename = Carbon::now()->timestamp . uniqid() . '.' . $file->getClientOriginalExtension();
        Image::make($file)->save($this->path . '/' . $filename);

        // upload resize image
        foreach($this->dimensiosn as $row){
            $canvas = Image::canvas($row, $row);

            $resizeImage = Image::make($file)->resize($row, $row, function($constraint){
                $constraint->aspectRatio();
            });

            // make folder dimension
            if(!File::isDirectory($this->path . '/' . $row)){
                File::makeDirectory($this->path . '/' . $row);
            };

            // save image
            $canvas->insert($resizeImage, 'center');
            $canvas->save($this->path . '/' . $row . '/' . $filename);
        };

        return redirect()->back()->with(['success' => 'Upload Success']);
    }
}
